<?php declare(strict_types=1);

namespace AO\Internal;

use Stringable;

final class MaybeBinaryString implements Stringable {
	public function __construct(private string $data) {
	}

	public function __toString(): string {
		$bin = '"';
		for ($i = 0; $i < strlen($this->data); $i++) {
			$ord = ord($this->data[$i]);
			switch ($ord) {
				case 9: // <tab>
					$bin .= "\\t";
					break;
				case 10: // <newline>
					$bin .= "\\n";
					break;
				case 34: // "
					$bin .= "\\\"";
					break;
				case 92: // \
					$bin .= "\\\\";
					break;
				default:
					if ($ord < 32 || $ord > 127) {
						$bin .= "\\x" . sprintf("%02X", $ord);
					} else {
						$bin .= $this->data[$i];
					}
			}
		}
		$bin .= '"';
		return $bin;
	}
}
