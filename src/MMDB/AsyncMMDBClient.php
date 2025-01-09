<?php declare(strict_types=1);

namespace AO\MMDB;

use function Amp\File\openFile;
use function Safe\unpack;

use Amp\File\File;
use InvalidArgumentException;
use Psr\Log\LoggerInterface;

/**
 * Reads entries from the text.mdb file
 */
class AsyncMMDBClient implements MMDBClient {
	/**
	 * A cache to quickly access the most common lookups
	 *
	 * @var array<int,array<int,string>>
	 */
	private array $cache = [];

	public function __construct(
		private File $mmdb,
		private ?LoggerInterface $logger=null,
	) {
		$this->mmdb->seek(0);
		$entry = $this->readEntry();
		if ($entry->id !== 1_111_772_493) {
			throw new InvalidArgumentException('Argument $mmdb to ' . __CLASS__ . " is not an mmdb file: '" . $mmdb->getPath() . "'");
		}
	}

	public static function createDefault(): self {
		$file = openFile(dirname(__DIR__, 2) . '/data/text.mdb', 'rb');
		return new self(mmdb: $file);
	}

	public function getMessageString(int $categoryId, int $messageId): ?string {
		$this->logger?->info('Looking up messageId={message_id}, categoryId={category_id}', [
			'category_id'=> $categoryId,
			'message_id' => $messageId,
		]);
		// check for entry in cache
		if (isset($this->cache[$categoryId][$messageId])) {
			return $this->cache[$categoryId][$messageId];
		}

		$this->mmdb->seek(0);

		// start at offset = 8 since that is where the categories start
		// find the category
		$category = $this->findEntry($categoryId, 8);
		if ($category === null) {
			$this->logger?->error('Could not find categoryID {category_id}', [
				'category_id'=> $categoryId,
			]);
			return null;
		}

		// find the instance
		$instance = $this->findEntry($messageId, $category->offset);
		if ($instance === null) {
			$this->logger?->error('Could not find messageId {message_id} for categoryId {category_id}', [
				'category_id'=> $categoryId,
				'message_id' => $messageId,
			]);
			return null;
		}

		$this->mmdb->seek($instance->offset);
		$message = $this->readString();
		$this->cache[$categoryId][$messageId] = $message;

		return $message;
	}

	/** @return MMDBEntry[]|null */
	public function findAllInstancesInCategory(int $categoryId): ?array {
		// start at offset = 8 since that is where the categories start
		// find the category
		$category = $this->findEntry($categoryId, 8);
		if ($category === null) {
			$this->logger?->error('Could not find categoryID {category_id}', [
				'category_id' => $categoryId,
			]);
			return null;
		}

		$this->mmdb->seek($category->offset);

		// find all instances
		$instances = [];
		$instance = $this->readEntry();
		do {
			$instances[] = $instance;
			$previousInstance = $instance;
			$instance = $this->readEntry();
		} while ($instance->id > $previousInstance->id);

		return $instances;
	}

	/** @return null|MMDBEntry[] */
	public function getCategories(): ?array {
		// start at offset = 8 since that is where the categories start
		$this->mmdb->seek(8);

		// find all categories
		$categories = [];
		$category = $this->readEntry();
		do {
			$categories[] = $category;
			$previousCategory = $category;
			$category = $this->readEntry();
		} while ($category->id > $previousCategory->id);

		return $categories;
	}

	/**
	 * Find an entry in the MMDB
	 *
	 * @param int $id     The category ID
	 * @param int $offset Offset where to read
	 */
	private function findEntry(int $id, int $offset): ?MMDBEntry {
		$this->mmdb->seek($offset);
		$entry = null;

		do {
			$previousEntry = $entry;
			$entry = $this->readEntry();

			if ($previousEntry !== null && $entry->id < $previousEntry->id) {
				return null;
			}
		} while ($id !== $entry->id);

		return $entry;
	}

	private function readEntry(): MMDBEntry {
		return new MMDBEntry(
			id: $this->readLong(),
			offset: $this->readLong(),
		);
	}

	private function readLong(): int {
		$packed = $this->mmdb->read(length: 4);
		if ($packed === null || strlen($packed) < 4) {
			throw new \Exception('The MMDB file is broken');
		}
		$unpacked = unpack('V', $packed);
		return array_pop($unpacked);
	}

	private function readString(): string {
		$message = '';
		$char = '';

		$char = $this->mmdb->read(length: 1);
		if ($char === null || strlen($char) < 1) {
			throw new \Exception('The MMDB file is broken');
		}
		while ($char !== "\0" && !$this->mmdb->eof()) {
			$message .= $char;
			$char = $this->mmdb->read(length: 1);
			if ($char === null) {
				throw new \Exception('The MMDB file is broken');
			}
		}

		return $message;
	}
}
