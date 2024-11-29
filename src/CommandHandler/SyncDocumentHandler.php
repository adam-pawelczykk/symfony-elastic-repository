<?php
/** @author Adam Pawełczyk */

namespace ATPawelczyk\Elastic\CommandHandler;

use ATPawelczyk\Elastic\Command\SyncDocument;
use Elasticsearch\Client;

/**
 * Class SyncDocumentHandler
 * @package ATPawelczyk\Elastic\CommandHandler
 */
class SyncDocumentHandler
{
    private $client;

    /**
     * SyncDocumentHandler constructor.
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * @param SyncDocument $command
     */
    public function __invoke(SyncDocument $command): void
    {
        $document = $command->getDocument();
        $params = [
            'index' => $command->getIndex(),
            'id' => $document->getId(),
            'body' => $document->getBody()
        ];

        $this->client->index($params);
    }
}
