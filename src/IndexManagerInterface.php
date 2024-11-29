<?php
/** @author: Adam Pawełczyk */

namespace ATPawelczyk\Elastic;

use ATPawelczyk\Elastic\Exception\IndexDoesNotExist;

/**
 * Interface IndexManagerInterface
 * @package ATPawelczyk\Elastic
 */
interface IndexManagerInterface
{
    /**
     * @param IndexConfig $config
     * @return void
     */
    public function addConfiguration(IndexConfig $config): void;

    /**
     * @return IndexConfig[]
     */
    public function getConfigurations(): array;

    /**
     * @return IndexInterface[]
     */
    public function getIndexes(): array;

    /**
     * @param string $indexKeyOrName
     * @return bool
     */
    public function hasIndex(string $indexKeyOrName): bool;

    /**
     * @param string $indexKeyOrName
     * @return IndexInterface
     */
    public function getIndex(string $indexKeyOrName): IndexInterface;

    /**
     * Chceck index exist
     * @param string $indexKeyOrName
     * @return bool
     */
    public function isIndexExist(string $indexKeyOrName): bool;

    /**
     * Create index when not exist
     * @param string $indexKeyOrName
     * @return void
     */
    public function createIndex(string $indexKeyOrName): void;

    /**
     * Update index map and settings
     * @param string $indexKeyOrName
     * @return void
     */
    public function updateIndex(string $indexKeyOrName): void;

    /**
     * @param string $indexKeyOrName
     * @return void
     * @throws IndexDoesNotExist
     */
    public function updateIndexMapping(string $indexKeyOrName): void;

    /**
     * @param string $indexKeyOrName
     * @return array
     */
    public function deleteIndex(string $indexKeyOrName): array;
}
