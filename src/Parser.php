<?php declare(strict_types=1);

namespace AO;

use function Safe\unpack;

use AO\BinaryPackage\BinaryPackageIn;
use AO\Internal\MaybeBinaryString;
use AO\Package\Attributes\Param;
use AO\Package\{In, InPackage, OutPackage};
use Psr\Log\LoggerInterface;

class Parser {
	final public function __construct(
		protected MMDB\MMDBClient $mmdb,
		protected ?LoggerInterface $logger=null,
	) {
	}

	public static function createDefault(): static {
		return new static(
			mmdb: MMDB\AsyncMMDBClient::createDefault()
		);
	}

	/** @phpstan-return ($package is BinaryPackageIn ? InPackage : OutPackage) */
	public function parseBinaryPackage(BinaryPackage $package): Package {
		$class = ($package instanceof BinaryPackageIn)
			? $package->type->classIn()
			: $package->type->classOut();
		if (!class_exists($class)) {
			throw new \Exception("Non-existing class {$class} requested for package");
		}
		if (!is_a($class, Package::class, true)) {
			throw new \Exception("Requested class {$class} is not an AO package");
		}
		$format = $class::getFormat();

		$args = [];
		if (isset($package->body)) {
			$args = $this->parseFormat($format, $package->body);
		}
		switch ($class) {
			case In\GroupMessage::class:
				$args[4] = null;
				assert(count($args) >= 3);
				assert(is_int($args[1]));
				assert(is_string($args[2]));
				/* Hack to support extended messages */
				if ($args[1] === 0 && substr($args[2], 0, 2) === '~&') {
					$this->logger?->debug('Extended message {message} found', [
						'message' => $args[2],
					]);
					$extMsg = $this->readExtendedMessage($args[2]);
					if (isset($extMsg)) {
						$args[2] = $extMsg->message;
						$args[4] = $extMsg;
					}
				}
				break;
			case In\SystemMessage::class:
				assert(count($args) === 4);
				assert(is_int($args[2]));
				assert(is_string($args[3]));
				$categoryId = 20_000;
				$extMsg = $this->mmdb->getMessageString($categoryId, $args[2]);
				if ($extMsg !== null) {
					$extParams = $this->parseExtParams($args[3]);
					if ($extParams !== null) {
						$args[3] = vsprintf($extMsg, $extParams);
					} else {
						$this->logger?->error('Could not parse chat notice', [
							'packet' => $args,
						]);
					}
				}
				break;

			default:
				break;
		}
		$args = $this->orderArgs($class, ...$args);
		$result = new $class(...$args);
		if ($package instanceof BinaryPackageIn) {
			assert($result instanceof InPackage);
		} else {
			assert($result instanceof OutPackage);
		}
		$this->logger?->debug('Parsed {binary_package} into {package}', [
			'binary_package' => $package,
			'package' => $result,
		]);
		return $result;
	}

	/**
	 * Read an extended message and return it
	 *
	 * New "extended" messages, parser and abstraction.
	 * These were introduced in 16.1.  The messages use postscript
	 * base85 encoding (not ipv6 / rfc 1924 base85).  They also use
	 * some custom encoding and references to further confuse things.
	 *
	 * Messages start with the magic marker ~& and end with ~
	 * Messages begin with two base85 encoded numbers that define
	 * the category and instance of the message.  After that there
	 * are an category/instance defined amount of variables which
	 * are prefixed by the variable type.  A base85 encoded number
	 * takes 5 bytes.  Variable types:
	 *
	 * s: string, first byte is the length of the string
	 * i: signed integer (b85)
	 * u: unsigned integer (b85)
	 * f: float (b85)
	 * R: reference, b85 category and instance
	 * F: recursive encoding
	 * ~: end of message
	 */
	private function readExtendedMessage(string $msg): ?ExtendedMessage {
		if (!strlen($msg)) {
			return null;
		}
		$origMessage = $msg;

		$message = '';
		while (substr($msg, 0, 2) === '~&') {
			// remove header '~&'
			$msg = substr($msg, 2);

			$category = $this->b85g($msg);
			$instance = $this->b85g($msg);

			$args = $this->parseExtParams($msg);
			$messageString = null;
			if ($args === null) {
				$this->logger?->warning("Error parsing parameters for category '{category}', instance '{instance}' string '{message}'", [
					'category' => $category,
					'instance' => $instance,
					'message' => $msg,
				]);
			} else {
				$messageString = $this->mmdb->getMessageString($category, $instance);
				if ($messageString !== null) {
					$message .= trim(vsprintf($messageString, $args));
				}
			}
		}

		$extMessage = new ExtendedMessage(
			rawMessage: $origMessage,
			args: $args ?? [],
			category: $category ?? 0,
			instance: $instance ?? 0,
			message: $message,
			messageString: $messageString ?? '',
		);
		$this->logger?->debug('Extended message {message} composed', ['message' => $extMessage]);
		return $extMessage;
	}

	/**
	 * Parse parameters of extended Messages
	 *
	 * @param string $msg The extended message without header
	 *
	 * @return mixed[] The extracted parameters
	 */
	private function parseExtParams(string &$msg): ?array {
		$args = [];
		while ($msg !== '') {
			$dataType = $msg[0];
			$msg = substr($msg, 1); // skip the data type id
			switch ($dataType) {
				case 'S':
					$len = ord($msg[0]) * 256 + ord($msg[1]);
					$str = substr($msg, 2, $len);
					$msg = substr($msg, $len + 2);
					$args[] = $str;
					break;

				case 's':
					$len = ord($msg[0]);
					$str = substr($msg, 1, $len - 1);
					$msg = substr($msg, $len);
					$args[] = $str;
					break;

				case 'I':
					$array = unpack('N', $msg);
					if (!is_array($array)) {
						throw new \Exception('Invalid packet data received.');
					}
					$args[] = $array[1];
					$msg = substr($msg, 4);
					break;

				case 'i':
				case 'u':
					$num = $this->b85g($msg);
					$args[] = $num;
					break;

				case 'R':
					$cat = $this->b85g($msg);
					$ins = $this->b85g($msg);
					$str = $this->mmdb->getMessageString($cat, $ins);
					if ($str === null) {
						$str = "Unknown ({$cat}, {$ins})";
					}
					$args[] = $str;
					break;

				case 'l':
					$array = unpack('N', $msg);
					if (!is_array($array)) {
						throw new \Exception('Invalid packet data received.');
					}
					$msg = substr($msg, 4);
					$cat = 20_000;
					$ins = $array[1];
					$str = $this->mmdb->getMessageString($cat, $ins);
					if ($str === null) {
						$str = "Unknown ({$cat}, {$ins})";
					}
					$args[] = $str;
					break;

				case '~':
					// reached end of message
					break 2;

				default:
					$this->logger?->warning("Unknown data type '{data_type}'", [
						'data_type' => $dataType,
					]);
					return null;
			}
		}

		return $args;
	}

	/**
	 * Decode the next 5-byte block of 4 ascii85-encoded bytes and move the pointer
	 *
	 * @param string $str The stream to decode, will be modified to point to the next block
	 *
	 * @return int The decoded 32bit value
	 */
	private function b85g(string &$str): int {
		$n = 0;
		for ($i = 0; $i < 5; $i++) {
			$n = $n * 85 + ord($str[$i]) - 33;
		}
		$str = substr($str, 5);
		return $n;
	}

	/**
	 * @psalm-param class-string $class
	 *
	 * @param null|bool|int|string|ExtendedMessage|bool[]|string[]|int[]|Group\GroupId $args
	 *
	 * @return list<null|bool|int|string|ExtendedMessage|bool[]|string[]|int[]|Group\GroupId>
	 */
	private function orderArgs(string $class, null|bool|int|string|ExtendedMessage|array|Group\GroupId ...$args): array {
		$orderedArgs = [];
		$refClass = new \ReflectionClass($class);
		$refFunc = $refClass->getMethod('__construct');
		$refParams = $refFunc->getParameters();
		$pos = 0;
		foreach ($refParams as $refParam) {
			$refProp = new \ReflectionProperty($class, $refParam->getName());
			$paramAttrs = $refProp->getAttributes(Param::class);
			if (count($paramAttrs)) {
				$paramAttr = $paramAttrs[0]->newInstance();
				$orderedArgs [] = $args[$paramAttr->position];
			} else {
				$orderedArgs []= $args[$pos];
			}
			$pos++;
		}
		ksort($orderedArgs);
		return $orderedArgs;
	}

	/** @return list<string|int|bool|Group\GroupId|bool[]|string[]|int[]> */
	private function parseFormat(string $format, string $data): array {
		if ($format === '') {
			return [];
		}
		$this->logger?->debug('Parsing AO binary format {format}', ['format' => $format]);
		switch (substr($format, 0, 1)) {
			case 'B':
				$unp = unpack('Nnumber', $data);
				$this->logger?->debug('Parsed bool {value}', ['value' => $unp['number'] ? 'true' : 'false']);
				return [
					(bool)$unp['number'],
					...$this->parseFormat(substr($format, 1), substr($data, 4)),
				];
			case 'I':
				$unp = unpack('Nnumber', $data);
				$this->logger?->debug('Parsed int {value}', ['value' => $unp['number']]);
				return [
					$unp['number'],
					...$this->parseFormat(substr($format, 1), substr($data, 4)),
				];
			case 'S':
				$unp  = unpack('nlength', $data);
				$len  = $unp['length'];
				$this->logger?->debug('Parsed string length {length}: {value}', [
					'length' => $unp['length'],
					'value' => new MaybeBinaryString(substr($data, 2, $len)),
				]);
				return [
					substr($data, 2, $len),
					...$this->parseFormat(substr($format, 1), substr($data, 2 + $len)),
				];
			case 'G':
				$unp = unpack('Ctype/Nid', $data);
				$this->logger?->debug('Parsed group type={type}, id={id}', [
					'type' => $unp['type'],
					'id' => $unp['id'],
				]);
				return [
					new Group\GroupId(type: $unp['type'], number: $unp['id']),
					...$this->parseFormat(substr($format, 1), substr($data, 5)),
				];
			case 'i':
				$unp  = unpack('nlength', $data);
				$len  = $unp['length'];
				$this->logger?->debug('Parsed int[] length {length}', ['length' => $len]);
				$unp = unpack('N' . $len, substr($data, 2));

				/** @var int[] */
				$res = array_values($unp);
				$this->logger?->debug('Parsed int[] length {length}, values {values}', [
					'length' => $len,
					'values' => $res,
				]);
				return [
					$res,
					...$this->parseFormat(substr($format, 1), substr($data, 2 + 4 * $len)),
				];
			case 'b':
				$unp  = unpack('nlength', $data);
				$len  = $unp['length'];
				$this->logger?->debug('Parsed bool[] length {length}', ['length' => $len]);
				$unp = unpack('N' . $len, substr($data, 2));

				/** @var bool[] */
				$res = array_map(boolval(...), array_values($unp));
				$this->logger?->debug('Parsed bool[] length {length}, values {values}', [
					'length' => $len,
					'values' => array_map(static fn (bool $val): string => $val ? 'true' : 'false', $res),
				]);
				return [
					$res,
					...$this->parseFormat(substr($format, 1), substr($data, 2 + 4 * $len)),
				];
			case 's':
				$unp  = unpack('nlength', $data);
				$arrayLength = $len = $unp['length'];
				$this->logger?->debug('Parsed string[] length {length}', ['length' => $len]);
				$data = substr($data, 2);
				$res  = [];
				while ($len--) {
					$unp   = unpack('nstrlen', $data);
					$slen  = $unp['strlen'];
					$res []= substr($data, 2, $slen);
					$data  = substr($data, 2+$slen);
				}
				$this->logger?->debug('Parsed string[] length {length}, values {values}', [
					'length' => $arrayLength,
					'values' => $res,
				]);
				return [
					$res,
					...$this->parseFormat(substr($format, 1), $data),
				];

			default:
				throw new \Exception('Unknown packet format type ' . substr($format, 0, 1));
		}
	}
}
