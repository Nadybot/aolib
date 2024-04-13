<?php declare(strict_types=1);

namespace AO;

use function Safe\unpack;
use Amp\ByteStream\{BufferException, BufferedReader, ReadableStream, ReadableStreamIteratorAggregate};

use Amp\Cancellation;

/**
 * A traversable stream tokenizer for AO connections
 *
 * @implements \IteratorAggregate<int, string>
 *
 * @package AO\Tokenizer
 */
class Tokenizer implements ReadableStream, \IteratorAggregate {
	use ReadableStreamIteratorAggregate;

	private BufferedReader $reader;

	public function __construct(
		private ReadableStream $stream
	) {
		$this->reader = new BufferedReader($this->stream);
	}

	public function read(?Cancellation $cancellation=null): ?string {
		try {
			$binHeader = $this->reader->readLength(4, $cancellation);
			$header = unpack('ntype/nlength', $binHeader);
			assert(is_int($header['length']));
			assert($header['length'] >= 0);
			$binBody = '';
			if ($header['length'] > 0) {
				$binBody = $this->reader->readLength($header['length'], $cancellation);
			}
			return $binHeader . $binBody;
		} catch (BufferException) {
			return null;
		}
	}

	public function isReadable(): bool {
		return $this->reader->isReadable();
	}

	public function close(): void {
		$this->stream->close();
	}

	public function isClosed(): bool {
		return $this->stream->isClosed();
	}

	public function onClose(\Closure $onClose): void {
		$this->stream->onClose($onClose);
	}
}
