<?php declare(strict_types=1);

namespace AO;

use function Safe\unpack;
use Amp\ByteStream\{ReadableStream, WritableStream};

use Amp\Cancellation;
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

	public function __construct(
		private LoggerInterface $logger,
		ReadableStream $reader,
		private WritableStream $writer,
	) {
		$this->tokenizer = new Tokenizer($reader);
	}

	public function read(?Cancellation $cancellation=null): ?BinaryPackage\In {
		$binPackage = $this->tokenizer->read($cancellation);
		if ($binPackage === null) {
			return null;
		}
		$header = unpack("ntype/nlength", $binPackage);
		$package = new BinaryPackage\In(
			type: Type::from($header['type']),
			length: $header['length'],
			body: substr($binPackage, 4),
		);
		$this->logger->debug("Received {package}", ["package" => $package]);
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
		if ($bytes instanceof BinaryPackage\Out) {
			$bytes = $bytes->toBinary();
		}
		$this->writer->write($bytes);
	}

	public function isWritable(): bool {
		return $this->writer->isWritable();
	}

	public function end(): void {
		$this->writer->end();
	}
}
