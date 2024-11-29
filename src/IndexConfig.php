<?php
/** @author: Adam Pawełczyk */

namespace ATPawelczyk\Elastic;

/**
 * Class IndexConfig
 * @package ATPawelczyk\Elastic
 */
class IndexConfig
{
    private $indexKey;
    private $name;
    private $class;
    private $prefix;
    private $settings = [];
    private $properties = [];

    private function __construct(
        string $indexKey,
        string $class,
        string $prefix
    ) {
        $this->indexKey = $indexKey;
        $this->name = $indexKey;
        $this->class = $class;
        $this->prefix = $prefix;
    }

    public function getIndexKey(): string
    {
        return $this->indexKey;
    }

    public function getIndexName(): string
    {
        return $this->name;
    }

    public function setIndexName(string $name): void
    {
        if (empty($name)) {
            throw new \InvalidArgumentException(
                'Index name can not be empty'
            );
        }

        $this->name = $name;
    }

    public function getIndexFullName(): string
    {
        if (empty($this->prefix)) {
            return $this->name;
        }

        return $this->prefix . '_' . $this->name;
    }

    public function getClass(): string
    {
        return $this->class;
    }

    public function getSettings(): array
    {
        return $this->settings;
    }

    public function setSettings(array $settings): void
    {
        $this->settings = $settings;
    }

    public function getProperties(): array
    {
        return $this->properties;
    }

    public function setProperties(array $properties): void
    {
        $this->properties = $properties;
    }

    public static function create(
        string $indexKey,
        string $defaultPrefix,
        array  $config
    ): self {
        $prefix = $config['prefix'] ?? null;
        $prefix = $prefix ?: $defaultPrefix;

        $instance = new self(
            $indexKey,
            $config['class'] ?? '',
            $prefix
        );

        if (!empty($config['name'] ?? '')) {
            $instance->setIndexName($config['name']);
        }

        $instance->setSettings($config['settings'] ?? []);
        $instance->setProperties($config['properties'] ?? []);

        return $instance;
    }
}
