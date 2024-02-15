<?php declare(strict_types=1);

namespace AO\Client;

use Amp\ByteStream\BufferedReader;
use Amp\ByteStream\WritableStream;
use Amp\Cancellation;
use Closure;

use function Safe\unpack;

final class PacketReader implements \Traversable,WritableStream {
	public function __construct(
		private BufferedReader $reader,
		private WritableStream $writer,
	) {
	}

	public function read(?Cancellation $cancellation = null): Packet {
		$binHeader = $this->reader->readLength(4, $cancellation);
		$header = unpack("ntype/nlength", $binHeader);
		$binBody = $this->reader->readLength($header['length'] - 4, $cancellation);
		$packet = new Packet(
			type: $header['type'],
			length: $header['length'],
			body: $binBody,
		);
		return $packet;
	}
	
	public function isReadable(): bool {
		return $this->reader->isReadable();
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

	public function write(string $bytes): void {
		$this->writer->write($bytes);
	}

	public function isWritable(): bool {
		return $this->writer->isWritable();
	}

	public function end(): void {
		$this->writer->end();
	}
}
