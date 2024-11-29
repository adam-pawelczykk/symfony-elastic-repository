<?php
/** @author: Adam Pawełczyk */

namespace ATPawelczyk\Elastic;

/**
 * Class InMemoryIndexManager
 * @package ATPawelczyk\Elastic
 */
class InMemoryIndexManager implements IndexManagerInterface
{
    /** @var IndexInterface[] */
    private $indexes = [];

    /**
     * @inheritDoc
     */
    public function addConfiguration(IndexConfig $config): void
    {
    }

    /**
     * @inheritDoc
     */
    public function getConfigurations(): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function getIndexes(): array
    {
        return $this->indexes;
    }

    public function addIndex(string $indexKeyOrName, IndexInterface $index): void
    {
        $this->indexes[$indexKeyOrName] = $index;
    }

    /**
     * @inheritDoc
     */
    public function hasIndex(string $indexKeyOrName): bool
    {
        return isset($this->indexes[$indexKeyOrName]);
    }

    /**
     * @inheritDoc
     */
    public function getIndex(string $indexKeyOrName): IndexInterface
    {
        if (!isset($this->indexes[$indexKeyOrName])) {
            $this->indexes[$indexKeyOrName] = new InMemoryIndex();
        }

        return $this->indexes[$indexKeyOrName];
    }

    /**
     * @inheritDoc
     */
    public function isIndexExist(string $indexKeyOrName): bool
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function createIndex(string $indexKeyOrName): void
    {
    }

    /**
     * @inheritDoc
     */
    public function updateIndex(string $indexKeyOrName): void
    {
    }

    /**
     * @inheritDoc
     */
    public function updateIndexMapping(string $indexKeyOrName): void
    {
    }

    /**
     * @inheritDoc
     */
    public function deleteIndex(string $indexKeyOrName): array
    {
        unset($this->indexes[$indexKeyOrName]);
        return [];
    }
}
