<?php
/** @author: Adam Pawełczyk */

namespace ATPawelczyk\Elastic;

use ATPawelczyk\Elastic\DSL\DSLQueryStackInterface;
use ATPawelczyk\Elastic\Result\BulkResult;
use ATPawelczyk\Elastic\Result\DocumentResult;

/**
 * Class InMemoryIndex
 * @package ATPawelczyk\Elastic
 */
class InMemoryIndex implements IndexInterface
{
    /** @var array[] */
    private $documents = [];

    public function getConfig(): IndexConfig
    {
        throw new \Exception('InMemoryIndex does not have configuration');
    }

    public function getDocuments(): array
    {
        return $this->documents;
    }

    /**
     * @inheritDoc
     */
    public function sync(Document $document): void
    {
        $this->documents[$document->getId()] = $document->getBody();
    }

    /**
     * @inheritDoc
     */
    public function bulk(array $documents): BulkResult
    {
        $result = new BulkResult(true);

        foreach ($documents as $document) {
            if (isset($this->documents[$document->getId()])) {
                $result->addDetail(
                    new DocumentResult($document->getId(), 'updated')
                );
            } else {
                $result->addDetail(
                    new DocumentResult($document->getId(), 'created')
                );
            }

            $this->sync($document);
        }

        return $result;
    }

    /**
     * @inheritDoc
     */
    public function delete(string $id): array
    {
        unset($this->documents[$id]);

        return [];
    }

    /**
     * @param string $id
     * @return array|null
     */
    public function source(string $id): ?array
    {
        if (!isset($this->documents[$id])) {
            return null;
        }

        return $this->documents[$id];
    }

    /**
     * @param string $id
     * @return array|null
     */
    public function get(string $id): ?array
    {
        // Todo
        return null;
    }

    /**
     * @param DSLQueryStackInterface $queryStack
     * @return array
     */
    public function search(DSLQueryStackInterface $queryStack): array
    {
        return [];
    }
}
