<?php
/** @author Adam Pawełczyk */

namespace ATPawelczyk\Elastic;

use ATPawelczyk\Elastic\Command\SyncDocument;
use ATPawelczyk\Elastic\CommandHandler\SyncDocumentHandler;
use ATPawelczyk\Elastic\DSL\DSLQueryStackInterface;
use ATPawelczyk\Elastic\Result\BulkResult;
use Elasticsearch\Client;
use Elasticsearch\Common\Exceptions\Missing404Exception;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * Class Index
 * @package ATPawelczyk\Elastic
 */
class Index implements IndexInterface
{
    private $config;
    private $client;
    private $bus;

    /**
     * Index constructor.
     * @param Client $client
     * @param IndexConfig $config
     * @param MessageBusInterface|null $bus
     */
    public function __construct(
        Client               $client,
        IndexConfig          $config,
        ?MessageBusInterface $bus = null
    ) {
        $this->client = $client;
        $this->config = $config;
        $this->bus = $bus;
    }

    public function getConfig(): IndexConfig
    {
        return $this->config;
    }

    /**
     * @inheritDoc
     */
    public function sync(Document $document): void
    {
        $command = new SyncDocument($this->config->getIndexFullName(), $document);

        if (null !== $this->bus) {
            $this->bus->dispatch($command);
            return;
        }

        $handler = new SyncDocumentHandler($this->client);
        $handler($command);
    }

    /**
     * @inheritDoc
     */
    public function bulk(array $documents): BulkResult
    {
        $indexFullName = $this->config->getIndexFullName();
        $body = [];

        foreach ($documents as $document) {
            $body[] = [
                'index' => [
                    '_index' => $indexFullName,
                    '_id' => $document->getId()
                ]
            ];
            $body[] = $document->getBody();
        }

        return BulkResult::mapFromResponse(
            $this->client->bulk([
                'index' => $indexFullName,
                'body' => $body
            ])
        );
    }

    /**
     * @inheritDoc
     */
    public function delete(string $id): array
    {
        return $this->client->delete([
            'index' => $this->config->getIndexFullName(),
            'id' => $id
        ]);
    }

    /**
     * @param string $id
     * @return array|null
     */
    public function source(string $id): ?array
    {
        try {
            return $this->client->getSource([
                'id' => $id,
                'index' => $this->config->getIndexFullName()
            ]);
        } catch (Missing404Exception $exception) {
            return null;
        }
    }

    /**
     * @param string $id
     * @return array|null
     */
    public function get(string $id): ?array
    {
        try {
            return $this->client->get([
                'id' => $id,
                'index' => $this->config->getIndexFullName()
            ]);
        } catch (Missing404Exception $exception) {
            return null;
        }
    }

    /**
     * @param DSLQueryStackInterface $queryStack
     * @return array
     */
    public function search(DSLQueryStackInterface $queryStack): array
    {
        $queries = $queryStack->getQueries();

        try {
            if ($queryStack->isMultiQuery()) {
                return $this->client->msearch([
                    'index' => $this->config->getIndexFullName(),
                    'body' => $queries
                ]);
            }

            return $this->client->search([
                'index' => $this->config->getIndexFullName(),
                'body' => reset($queries) ?: []
            ]);
        } catch (Missing404Exception $exception) {
            return [];
        }
    }
}
