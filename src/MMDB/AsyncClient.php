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
class AsyncClient implements Client {
	/** @var array<int,array<int,string>> */
	private array $cache = [];

	public function __construct(
		public LoggerInterface $logger,
		public File $mmdb,
	) {
		$this->mmdb->seek(0);
		$entry = $this->readEntry();
		if ($entry->id !== 1111772493) {
			throw new InvalidArgumentException("Argument \$mmdb to " . __CLASS__ . " is not an mmdb file: '" . $mmdb->getPath() . "'");
		}
	}

	public static function createDefault(LoggerInterface $logger): self {
		$file = openFile(dirname(__DIR__, 2) . "/data/text.mdb", "rb");
		return new self($logger, $file);
	}

	public function getMessageString(int $categoryId, int $instanceId): ?string {
		$this->logger->info("Looking up instanceId={instance_id}, categoryId={category_id}", [
			"category_id"=> $categoryId,
			"instance_id" => $instanceId,
		]);
		// check for entry in cache
		if (isset($this->cache[$categoryId][$instanceId])) {
			return $this->cache[$categoryId][$instanceId];
		}

		$this->mmdb->seek(0);

		// start at offset = 8 since that is where the categories start
		// find the category
		$category = $this->findEntry($categoryId, 8);
		if ($category === null) {
			$this->logger->error("Could not find categoryID {category_id}", [
				"category_id"=> $categoryId,
			]);
			return null;
		}

		// find the instance
		$instance = $this->findEntry($instanceId, $category->offset);
		if ($instance === null) {
			$this->logger->error("Could not find instanceId {instance_id} for categoryId {category_id}", [
				"category_id"=> $categoryId,
				"instance_id" => $instanceId,
			]);
			return null;
		}

		$this->mmdb->seek($instance->offset);
		$message = $this->readString();
		$this->cache[$categoryId][$instanceId] = $message;

		return $message;
	}

	/** @return Entry[]|null */
	public function findAllInstancesInCategory(int $categoryId): ?array {
		// start at offset = 8 since that is where the categories start
		// find the category
		$category = $this->findEntry($categoryId, 8);
		if ($category === null) {
			$this->logger->error("Could not find categoryID {category_id}", [
				"category_id" => $categoryId,
			]);
			return null;
		}

		$this->mmdb->seek($category->offset);

		// find all instances
		$instances = [];
		$instance = $this->readEntry();
		$previousInstance = null;
		while ($previousInstance == null || $instance->id > $previousInstance->id) {
			$instances[] = $instance;
			$previousInstance = $instance;
			$instance = $this->readEntry();
		}

		return $instances;
	}

	/** @return null|Entry[] */
	public function getCategories(): ?array {
		// start at offset = 8 since that is where the categories start
		$this->mmdb->seek(8);

		// find all categories
		$categories = [];
		$category = $this->readEntry();
		$previousCategory = null;
		while ($previousCategory == null || $category->id > $previousCategory->id) {
			$categories[] = $category;
			$previousCategory = $category;
			$category = $this->readEntry();
		}

		return $categories;
	}

	/**
	 * Find an entry in the MMDB
	 *
	 * @param int $id     The category ID
	 * @param int $offset Offset where to read
	 */
	private function findEntry(int $id, int $offset): ?Entry {
		$this->mmdb->seek($offset);
		$entry = null;

		do {
			$previousEntry = $entry;
			$entry = $this->readEntry();

			if ($previousEntry != null && $entry->id < $previousEntry->id) {
				return null;
			}
		} while ($id != $entry->id);

		return $entry;
	}

	private function readEntry(): Entry {
		return new Entry(
			id: $this->readLong(),
			offset: $this->readLong(),
		);
	}

	private function readLong(): int {
		$packed = $this->mmdb->read(length: 4);
		$unpacked = unpack("L", $packed);
		return array_pop($unpacked);
	}

	private function readString(): string {
		$message = '';
		$char = '';

		$char = $this->mmdb->read(length: 1);
		while ($char !== "\0" && !$this->mmdb->eof()) {
			$message .= $char;
			$char = $this->mmdb->read(length: 1);
		}

		return $message;
	}
}
