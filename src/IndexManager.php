<?php
/** @author Adam Pawełczyk */

namespace ATPawelczyk\Elastic;

use ATPawelczyk\Elastic\Exception\IndexConfigurationNotExist;
use ATPawelczyk\Elastic\Exception\IndexDoesNotExist;
use Elasticsearch\Client;
use InvalidArgumentException;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * Class IndexManager
 * @package ATPawelczyk\Elastic
 */
class IndexManager implements IndexManagerInterface
{
    private $client;
    private $prefix;
    /** @var IndexConfig[] */
    private $configurations = [];
    private $managers = [];
    private $bus;

    /**
     * IndexManager constructor.
     * @param Client $client
     * @param string $prefix
     * @param array $indexes
     * @param MessageBusInterface|null $bus
     */
    public function __construct(
        Client               $client,
        string               $prefix,
        array                $indexes,
        ?MessageBusInterface $bus = null
    ) {
        $this->client = $client;
        $this->prefix = $prefix;
        $this->bus = $bus;

        foreach ($indexes as $indexKey => $data) {
            $config = IndexConfig::create($indexKey, $this->prefix, $data);

            $this->addConfiguration($config);
        }
    }

    public function addConfiguration(IndexConfig $config): void
    {
        if (isset($this->configurations[$config->getIndexKey()])) {
            throw new InvalidArgumentException(
                sprintf(
                    'Configuration key "%s" already exist',
                    $config->getIndexKey()
                )
            );
        }

        $this->configurations[$config->getIndexKey()] = $config;

        $this->addUniqueConfiguration($config->getIndexName(), $config);
        $this->addUniqueConfiguration($config->getIndexFullName(), $config);

        if (!empty($config->getClass())) {
            $this->addUniqueConfiguration($config->getClass(), $config);
        }
    }

    public function getIndexes(): array
    {
        $indexes = [];

        foreach ($this->getConfigurations() as $config) {
            $indexes[] = $this->getIndex($config->getIndexName());
        }

        return array_values($indexes);
    }

    public function getConfigurations(): array
    {
        return array_unique($this->configurations, SORT_REGULAR);
    }

    /**
     * @inheritDoc
     */
    public function getIndex(string $indexKeyOrName): IndexInterface
    {
        if (!isset($this->managers[$indexKeyOrName])) {
            $this->managers[$indexKeyOrName] = $this->createIndexInstance($indexKeyOrName);
        }

        return $this->managers[$indexKeyOrName];
    }

    /**
     * @param string $indexKeyOrName
     * @return Index
     * @throws IndexConfigurationNotExist
     */
    private function createIndexInstance(string $indexKeyOrName): IndexInterface
    {
        return new Index(
            $this->getClient(),
            $this->getConfig($indexKeyOrName),
            $this->bus
        );
    }

    /**
     * @return Client
     */
    public function getClient(): Client
    {
        return $this->client;
    }

    /**
     * @param string $indexKeyOrName
     * @return IndexConfig
     * @throws IndexConfigurationNotExist
     */
    public function getConfig(string $indexKeyOrName): IndexConfig
    {
        if (!$this->hasIndex($indexKeyOrName)) {
            throw new IndexConfigurationNotExist($indexKeyOrName);
        }

        return $this->configurations[$indexKeyOrName];
    }

    /**
     * @inheritDoc
     */
    public function hasIndex(string $indexKeyOrName): bool
    {
        return isset($this->configurations[$indexKeyOrName]);
    }

    /**
     * @inheritDoc
     */
    public function updateIndex(string $indexKeyOrName): void
    {
        if (!$this->isIndexExist($indexKeyOrName)) {
            $this->createIndex($indexKeyOrName);

            return;
        }

        $config = $this->getConfig($indexKeyOrName);
        $result = $this->client->indices()->putMapping([
            'index' => $config->getIndexFullName(),
            'body' => [
                'properties' => $config->getProperties()
            ]
        ])['acknowledged'] ?? false;

        if ($result) {
            $this->client->indices()->putSettings([
                'index' => $config->getIndexFullName(),
                'body' => [
                    'settings' => (object)$config->getSettings()
                ]
            ]);
        }
    }

    /**
     * @inheritDoc
     */
    public function isIndexExist(string $indexKeyOrName): bool
    {
        return $this->client->indices()->exists([
            'index' => $this->getConfig($indexKeyOrName)->getIndexFullName()
        ]);
    }

    /**
     * @inheritDoc
     */
    public function createIndex(string $indexKeyOrName): void
    {
        // When index exist do nothing
        if ($this->isIndexExist($indexKeyOrName)) {
            return;
        }

        $config = $this->getConfig($indexKeyOrName);

        $this->client->indices()->create([
            'index' => $config->getIndexFullName(),
            'body' => [
                'settings' => (object) $config->getSettings(),
                'mappings' => [
                    'properties' => $config->getProperties()
                ]
            ]
        ]);
    }

    /**
     * @inheritDoc
     */
    public function deleteIndex(string $indexKeyOrName): array
    {
        return $this->client->indices()->delete([
            'index' => $this->getConfig($indexKeyOrName)->getIndexFullName()
        ]);
    }

    /**
     * @inheritDoc
     */
    public function updateIndexMapping(string $indexKeyOrName): void
    {
        if (!$this->isIndexExist($indexKeyOrName)) {
            throw new IndexDoesNotExist($indexKeyOrName);
        }
        $config = $this->getConfig($indexKeyOrName);
        $this->client->indices()->putMapping([
            'index' => $config->getIndexFullName(),
            'body' => [
                'properties' => $config->getProperties()
            ]
        ]);
    }

    private function addUniqueConfiguration(string $key, IndexConfig $config): void
    {
        // Set configuration for key only when not exist
        if (!isset($this->configurations[$key])) {
            $this->configurations[$key] = $config;
            return;
        }
        // Display notice when configuration is different and has same key
        if ($this->configurations[$key] !== $config) {
            if ($key === $config->getIndexName()) {
                $type = 'index name';
            } elseif ($key === $config->getIndexFullName()) {
                $type = 'index full name';
            } elseif ($key === $config->getClass()) {
                $type = 'class';
            } else {
                $type = 'key';
            }

            trigger_error(
                sprintf('Configuration with %s "%s" already exist, skipped', $type, $key),
                E_USER_NOTICE
            );
        }
    }
}
