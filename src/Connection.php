<?php declare(strict_types=1);

namespace AO;

use function Safe\unpack;
use Amp\ByteStream\{ReadableStream, WritableStream};

use Amp\Cancellation;
use AO\Client\Statistics;
use AO\Package\Type;
use Closure;
use Psr\Log\LoggerInterface;

/**
 * A traversable stream reader for AO connections
 *
 * @implements \IteratorAggregate<int, BinaryPackage\In>
 *
 * @package AO\Connection
 */
final class Connection implements \IteratorAggregate, WritableStream {
	private Tokenizer $tokenizer;
	private Statistics $statistics;

	public function __construct(
		ReadableStream $reader,
		private WritableStream $writer,
		private ?LoggerInterface $logger=null,
		?Statistics $statistics=null,
	) {
		$this->tokenizer = new Tokenizer($reader);
		$this->statistics = $statistics ?? new Statistics();
	}

	public function getStatistics(): Statistics {
		return clone $this->statistics;
	}

	public function read(?Cancellation $cancellation=null): ?BinaryPackage\In {
		$binPackage = $this->tokenizer->read($cancellation);
		if ($binPackage === null) {
			return null;
		}
		$this->statistics->packagesRead++;
		$this->statistics->bytesRead += \strlen($binPackage);
		$header = unpack("ntype/nlength", $binPackage);
		$package = new BinaryPackage\In(
			type: Type::from($header['type']),
			length: $header['length'],
			body: substr($binPackage, 4),
		);
		$this->logger?->debug("Received {package}", ["package" => $package]);
		return $package;
	}

	/** @return \Traversable<int, BinaryPackage\In> */
	public function getIterator(): \Traversable {
		while (($chunk = $this->read()) !== null) {
			yield $chunk;
		}
	}

	public function close(): void {
		$this->writer->close();
	}

	public function isClosed(): bool {
		return $this->writer->isClosed();
	}

	public function onClose(Closure $onClose): void {
		$this->writer->onClose($onClose);
	}

	public function write(string|BinaryPackage\Out $bytes): void {
		$type = 0;
		if ($bytes instanceof BinaryPackage\Out) {
			$type = $bytes->type->value;
			$bytes = $bytes->toBinary();
		} else {
			$unpacked = unpack("ntype", $bytes);
			$type = (int)$unpacked["type"];
		}
		$this->statistics->bytesWritten += \strlen($bytes);
		$this->statistics->packagesWritten[$type] ??= 0;
		$this->statistics->packagesWritten[$type]++;
		$this->writer->write($bytes);
	}

	public function isWritable(): bool {
		return $this->writer->isWritable();
	}

	public function end(): void {
		$this->writer->end();
	}
}
