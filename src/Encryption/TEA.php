<?php declare(strict_types=1);

namespace AO\TEA;

use function Safe\{pack, unpack};

use Exception;
use InvalidArgumentException;

class TEA {
	/**
	 * Generate a Diffie-Hellman login key
	 *
	 * This is 'half' Diffie-Hellman key exchange.
	 * 'Half' as in we already have the server's key ($dhY)
	 * $dhN is a prime and $dhG is generator for it.
	 *
	 * @see http://en.wikipedia.org/wiki/Diffie-Hellman_key_exchange
	 */
	public static function generateLoginKey(string $serverKey, string $username, string $password): string {
		$dhY = "0x9c32cc23d559ca90fc31be72df817d0e124769e809f936bc14360ff4b".
			"ed758f260a0d596584eacbbc2b88bdd410416163e11dbf62173393fbc0c6fe".
			"fb2d855f1a03dec8e9f105bbad91b3437d8eb73fe2f44159597aa4053cf788".
			"d2f9d7012fb8d7c4ce3876f7d6cd5d0c31754f4cd96166708641958de54a6d".
			"ef5657b9f2e92";
		$dhN = "0xeca2e8c85d863dcdc26a429a71a9815ad052f6139669dd659f98ae159".
			"d313d13c6bf2838e10a69b6478b64a24bd054ba8248e8fa778703b41840824".
			"9440b2c1edd28853e240d8a7e49540b76d120d3b1ad2878b1b99490eb4a2a5".
			"e84caa8a91cecbdb1aa7c816e8be343246f80c637abc653b893fd91686cf8d".
			"32d6cfe5f2a6f";
		$dhG = "0x5";
		$dhx = "0x".self::getRandomHexKey(256);

		$dhX = self::bcmathPowM($dhG, $dhx, $dhN);
		$dhK = self::bcmathPowM($dhY, $dhx, $dhN);

		$str = sprintf("%s|%s|%s", $username, $serverKey, $password);

		if (strlen($dhK) < 32) {
			$dhK = str_repeat("0", 32-strlen($dhK)) . $dhK;
		} else {
			$dhK = substr($dhK, 0, 32);
		}

		$prefix = pack("H16", self::getRandomHexKey(64));
		$length = 8 + 4 + strlen($str); // prefix, int, ...
		$pad    = str_repeat(" ", (8 - $length % 8) % 8);
		$strlen = pack("N", strlen($str));

		$plain   = $prefix . $strlen . $str . $pad;
		$encrypted = self::aoChatCrypt($dhK, $plain);

		return $dhX . "-" . $encrypted;
	}

	/**
	 * A safe network byte encoder
	 *
	 * On linux systems, unpack("H*", pack("L*", <value>)) returns differently than on Windows.
	 * This can be used instead of unpack/pack to get the value we need.
	 */
	private static function safeDecHexReverseEndian(float $value): string {
		$result = "";
		$value = self::reduceTo32Bit($value);
		$hex   = substr("00000000".dechex($value), -8);

		$bytes = str_split($hex, 2);

		for ($i = 3; $i >= 0; $i--) {
			$result .= $bytes[$i];
		}

		return $result;
	}

	/** Do an AOChat-conform encryption of $str with $key */
	private static function aoChatCrypt(string $key, string $str): string {
		if (strlen($key) !== 32 || strlen($str) % 8 !== 0) {
			throw new InvalidArgumentException("Invalid key or string received.");
		}

		$ret    = "";

		$keyarr  = unpack("V*", pack("H*", $key));
		$dataarr = unpack("V*", $str);

		$prev = [0, 0];
		for ($i = 1; $i <= count($dataarr); $i += 2) {
			$now = [
				self::reduceTo32Bit($dataarr[$i]) ^ self::reduceTo32Bit($prev[0]),
				self::reduceTo32Bit($dataarr[$i+1]) ^ self::reduceTo32Bit($prev[1]),
			];
			$prev   = self::aoCryptPermute($now, $keyarr);

			$ret .= self::safeDecHexReverseEndian($prev[0]);
			$ret .= self::safeDecHexReverseEndian($prev[1]);
		}

		return $ret;
	}

	/** Generate a random hex string with $bits bits length */
	private static function getRandomHexKey(int $bits): string {
		$str = "";
		do {
			$str .= sprintf('%02x', random_int(0, 0xFF));
		} while (($bits -= 8) > 0);
		return $str;
	}

	/** Raise an arbitrary precision number to another, reduced by a specified modulus */
	private static function bcmathPowM(string $base, string $exp, string $mod): string {
		if (function_exists("gmp_powm") && function_exists("gmp_strval")) {
			$r = gmp_powm($base, $exp, $mod);
			$r = gmp_strval($r);
		} else {
			$base = self::bigHexCec($base);
			$exp  = self::bigHexCec($exp);
			$mod  = self::bigHexCec($mod);
			if (!is_numeric($base) || !is_numeric($exp) || !is_numeric($mod)) {
				throw new Exception("Invalid numeric string encountered: {$base}^{$exp}%{$mod}");
			}

			$r = bcpowmod($base, $exp, $mod);
		}
		if (!is_string($r)) {
			throw new Exception("Error in AO encryption");
		}
		return self::bigDecHex($r);
	}

	/** Convert a HEX value into a decimal value */
	private static function bigHexCec(string $x): string {
		if (substr($x, 0, 2) !== "0x") {
			return $x;
		}
		$r = "0";
		for ($p = $q = strlen($x) - 1; $p >= 2; $p--) {
			$r = bcadd($r, bcmul((string)hexdec($x[$p]), bcpow("16", (string)($q - $p))));
		}
		return $r;
	}

	/** Convert a decimal value to HEX */
	private static function bigDecHex(string $x): string {
		if (!is_numeric($x)) {
			throw new InvalidArgumentException("Invalid numeric string encountered: {$x}");
		}
		$r = "";
		while ($x !== "0") {
			$r = dechex((int)bcmod($x, "16")) . $r;
			$x = bcdiv($x, "16");
		}
		return $r;
	}

	/**
	 * Takes a number and reduces it to a 32-bit value.
	 *
	 * The 32-bits remain a binary equivalent of 32-bits from the previous number.
	 * If the sign bit is set, the result will be negative, otherwise
	 * the result will be zero or positive.
	 *
	 * @author Feetus (RK1)
	 */
	private static function reduceTo32Bit(float $value): int {
		$strValue = (string)$value;
		// If its negative, lets go positive ... its easier to do everything as positive.
		if (bccomp($strValue, "0") === -1) {
			$strValue = self::negativeToUnsigned($value);
		}
		if (!is_numeric($strValue)) {
			throw new Exception("Invalid numeric string encountered: {$strValue}");
		}

		$bit32  = (string)0x80000000;
		$bit    = $bit32;
		$bits   = [];

		// Find the largest bit contained in $value above 32-bits
		while (bccomp($strValue, $bit) > -1) {
			$bit    = bcmul($bit, "2");
			$bits[] = $bit;
		}

		// Subtract out bits above 32 from $value
		while (null !== ($bit = array_pop($bits))) {
			if (bccomp($strValue, $bit) >= 0) {
				$strValue = bcsub($strValue, $bit);
			}
		}

		// Make negative if sign-bit is set in 32-bit value
		if (bccomp($strValue, $bit32) !== -1) {
			$strValue = bcsub($strValue, $bit32);
			$strValue = bcsub($strValue, $bit32);
		}

		return (int)$strValue;
	}

	/**
	 * This function returns the binary equivalent positive integer to a given negative integer of arbitrary length.
	 *
	 * This would be the same as taking a signed negative
	 * number and treating it as if it were unsigned. To see a simple example of this
	 * on Windows, open the Windows Calculator, punch in a negative number, select the
	 * hex display, and then switch back to the decimal display.
	 *
	 * @see http://www.hackersquest.com/boards/viewtopic.php?t=4884&start=75
	 */
	private static function negativeToUnsigned(float $value): string {
		$strValue = (string)$value;
		if (bccomp($strValue, "0") !== -1) {
			return $strValue;
		}

		$strValue = bcmul($strValue, "-1");
		$higherValue = (string)0xFFFFFFFF;

		// We don't know how many bytes the integer might be, so
		// start with one byte and then grow it byte by byte until
		// our negative number fits inside it. This will make the resulting
		// positive number fit in the same number of bytes.
		while (bccomp($strValue, $higherValue) === 1) {
			$higherValue = bcadd(bcmul($higherValue, (string)0x100), (string)0xFF);
		}

		$strValue = bcadd(bcsub($higherValue, $strValue), "1");

		return $strValue;
	}

	/**
	 * Internal encryption function
	 *
	 * @internal
	 *
	 * @param int[] $x
	 * @param int[] $y
	 *
	 * @return int[]
	 */
	private static function aoCryptPermute(array $x, array $y): array {
		$a = $x[0];
		$b = $x[1];
		$c = 0;
		$d = 0x9E3779B9;
		for ($i = 32; $i-- > 0;) {
			$c  = self::reduceTo32Bit($c + $d);
			$a += self::reduceTo32Bit(
				self::reduceTo32Bit(
					(self::reduceTo32Bit($b) << 4 & -16) + $y[1]
				) ^ self::reduceTo32Bit($b + $c)
			) ^ self::reduceTo32Bit(
				(self::reduceTo32Bit($b) >> 5 & 134217727) + $y[2]
			);
			$b += self::reduceTo32Bit(
				self::reduceTo32Bit(
					(self::reduceTo32Bit($a) << 4 & -16) + $y[3]
				) ^ self::reduceTo32Bit($a + $c)
			) ^ self::reduceTo32Bit(
				(self::reduceTo32Bit($a) >> 5 & 134217727) + $y[4]
			);
		}
		return [$a, $b];
	}
}
