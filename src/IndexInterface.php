<?php
/** @author: Adam Pawełczyk */

namespace ATPawelczyk\Elastic;

use ATPawelczyk\Elastic\DSL\DSLQueryStackInterface;
use ATPawelczyk\Elastic\Result\BulkResult;

/**
 * Interface IndexInterface
 * @package ATPawelczyk\Elastic
 */
interface IndexInterface
{
    /**
     * Return configurations
     * @return IndexConfig
     */
    public function getConfig(): IndexConfig;

    /**
     * Create or update document in index
     * @param Document $document
     */
    public function sync(Document $document): void;

    /**
     * Create or update many documents in index
     * @param Document[] $documents
     * @return BulkResult
     */
    public function bulk(array $documents): BulkResult;

    /**
     * Delete document from index
     * @param string $id
     * @return array
     */
    public function delete(string $id): array;

    /**
     * Get source of document
     * @param string $id
     * @return array|null
     */
    public function source(string $id): ?array;

    /**
     * Get document by id
     * @param string $id
     * @return array|null
     */
    public function get(string $id): ?array;

    /**
     * Seqrch documents by query
     * @param DSLQueryStackInterface $queryStack
     * @return array
     */
    public function search(DSLQueryStackInterface $queryStack): array;
}
