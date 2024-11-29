<?php
/** @author: Adam Pawełczyk */

namespace ATPawelczyk\Elastic\Result;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use Traversable;

class DocumentResultCollection implements IteratorAggregate, Countable
{
    private $results;

    public function __construct(array $results = [])
    {
        $this->results = $results;
    }

    public function add(DocumentResult $result): void
    {
        $this->results[] = $result;
    }

    public function remove(DocumentResult $result): void
    {
        $key = array_search($result, $this->results, true);

        if ($key !== false) {
            unset($this->results[$key]);
        }
    }

    public function contains(DocumentResult $result): bool
    {
        return in_array($result, $this->results, true);
    }

    public function filter(callable $callback): self
    {
        return new self(array_filter($this->results, $callback));
    }

    public function getCreatedResults(): self
    {
        return $this->filter(function (DocumentResult $result) {
            return DocumentResult::RESULT_CREATED === $result->getResult();
        });
    }

    public function getUpdatedResults(): self
    {
        return $this->filter(function (DocumentResult $result) {
            return DocumentResult::RESULT_UPDATED === $result->getResult();
        });
    }

    public function getDeletedResults(): self
    {
        return $this->filter(function (DocumentResult $result) {
            return DocumentResult::RESULT_DELETED === $result->getResult();
        });
    }

    public function getNotFoundResults(): self
    {
        return $this->filter(function (DocumentResult $result) {
            return DocumentResult::RESULT_NOT_FOUND === $result->getResult();
        });
    }

    public function getNoopResults(): self
    {
        return $this->filter(function (DocumentResult $result) {
            return DocumentResult::RESULT_NOOP === $result->getResult();
        });
    }

    public function toArray(): array
    {
        return $this->results;
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->results);
    }

    public function count(): int
    {
        return count($this->results);
    }
}
