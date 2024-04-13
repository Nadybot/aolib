<?php declare(strict_types=1);

namespace AO;

use function Safe\{json_encode, pack};

use AO\Group\GroupId;
use AO\Package\Attributes\Param;
use AO\Package\PackageType;
use Exception;
use ReflectionClass;
use Safe\Exceptions\JsonException;
use Stringable;

abstract class Package implements Stringable {
	public function __construct(public readonly PackageType $type) {
	}

	public function __toString() {
		$values = [];
		$refClass = new ReflectionClass($this);
		$props = get_object_vars($this);
		foreach ($props as $key => $value) {
			$refProp = $refClass->getProperty($key);
			if ($refProp->isInitialized($this) === false) {
				continue;
			}
			if ($value instanceof \Stringable) {
				$value = (string)$value;
			} elseif ($value instanceof \UnitEnum) {
				$value = $value->name;
			} elseif ($value instanceof \Closure) {
				$value = '<Closure>';
			} else {
				try {
					$value = json_encode(
						$value,
						\JSON_UNESCAPED_SLASHES|\JSON_UNESCAPED_UNICODE|\JSON_INVALID_UTF8_SUBSTITUTE
					);
				} catch (JsonException) {
					continue;
				}
			}
			$values []= "{$key}={$value}";
		}
		$classes = explode('\\', static::class);
		$class = array_pop($classes);
		return "<{$class}>{" . implode(',', $values) . '}';
	}

	public function toBinaryPackage(): BinaryPackage {
		$type = $this->type;
		$format = $this->getFormat();
		$values = $this->getPackageValues();
		$body = '';
		for ($i = 0; $i < strlen($format); $i++) {
			if (!isset($values[$i])) {
				throw new \Exception('The declaration of ' . self::class . 'and its binary representation is inconsistent.');
			}
			$body .= $this->toFormat(substr($format, $i, 1), $values[$i]);
		}
		return new BinaryPackage(
			type: $type,
			length: strlen($body),
			body: $body,
		);
	}

	abstract public static function getFormat(): string;

	/** @return list<bool|int|string|string[]|int[]|GroupId> */
	protected function getPackageValues(): array {
		$result = [];
		$refClass = new \ReflectionClass($this);
		$refFunc = $refClass->getMethod('__construct');
		$refParams = $refFunc->getParameters();
		$pos = 0;
		foreach ($refParams as $refParam) {
			if (!$refParam->isPromoted()) {
				throw new \Exception(self::class . " does not promote \${$refParam->name} in its constructor");
			}
			$refProp = new \ReflectionProperty($this, $refParam->getName());
			$refProp->setAccessible(true);
			$paramAttrs = $refProp->getAttributes(Param::class);
			if (count($paramAttrs)) {
				$paramAttr = $paramAttrs[0]->newInstance();
				$result[$paramAttr->position] = $refProp->getValue($this);
			} else {
				$result[$pos] = $refProp->getValue($this);
			}
			$pos++;
		}
		ksort($result);
		return $result;
	}

	/** @param bool|int|string|string[]|int[]|GroupId $value */
	protected function toFormat(string $format, bool|int|string|array|GroupId $value): string {
		switch ($format) {
			case 'B':
				assert(is_bool($value));
				return pack('N', (int)$value);
			case 'I':
				assert(is_int($value));
				return pack('N', $value);
			case 'S':
				assert(is_string($value));
				return pack('n', strlen($value)) . $value;
			case 'G':
				assert(is_object($value));
				assert($value instanceof GroupId);
				return pack('CN', $value->type->value, $value->number);
			case 'i':
				assert(is_array($value));
				$count = count($value);
				return pack("nN{$count}", $count, ...$value);
			case 's':
				assert(is_array($value));
				$count = count($value);
				$result = pack('n', $count);
				foreach ($value as $element) {
					assert(is_string($element));
					$result .= pack('n', strlen($element)) . $element;
				}
				return $result;
			default:
				throw new Exception("Unknown format '{$format}' encountered when encoding " . self::class);
		}
	}
}
