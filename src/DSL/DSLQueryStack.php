<?php
/** @author Adam Pawełczyk */

namespace ATPawelczyk\Elastic\DSL;

use stdClass;

/**
 * Class DSLQueryStack
 * @package ATPawelczyk\Elastic\DSL
 */
class DSLQueryStack implements DSLQueryStackInterface
{
    /** @var array<stdClass> */
    private $queryObjectStack = [];
    /** @var FilterInterface[] */
    private $filters = [];

    /**
     * @inheritDoc
     */
    public function isMultiQuery(): bool
    {
        return count($this->queryObjectStack) > 1;
    }

    /**
     * @inheritDoc
     */
    public function addQueryToStack(stdClass $query): void
    {
        $this->queryObjectStack[] = (object) $query;
    }

    /**
     * @inheritDoc
     */
    public function getQueryStack(): array
    {
        return $this->queryObjectStack;
    }

    /**
     * @inheritDoc
     */
    public function setQueryStack(array $stack): void
    {
        $this->queryObjectStack = [];

        foreach ($stack as $query) {
            $this->addQueryToStack($query);
        }
    }

    /**
     * @inheritDoc
     */
    public function addFilter(FilterInterface $filter): void
    {
        $this->filters[] = $filter;
    }

    /**
     * @inheritDoc
     */
    public function getQueries(): array
    {
        $this->injectFiltersToQueryStack();

        return $this->queryObjectStack;
    }

    private function injectFiltersToQueryStack(): void
    {
        if (count($this->filters) === 0) {
            return;
        } elseif (0 === count($this->queryObjectStack)) {
            $this->queryObjectStack[] = new \stdClass();
        }

        foreach ($this->queryObjectStack as $query) {
            if (isset($query->preference)) {
                continue;
            }

            $this->wrapQueryConditionsAsBoolAndSetFilterCollection($query);

            foreach ($this->filters as $filter) {
                $this->injectFilterToQuery($filter, $query);
            }
        }
    }

    private function injectFilterToQuery(FilterInterface $filter, stdClass $query): void
    {
        $query->query->bool->filter[] = $filter->toArray();
    }

    /**
     * Wrapping conditions passing in query to bool.must and add filter key to object
     * @param stdClass $object
     */
    private function wrapQueryConditionsAsBoolAndSetFilterCollection(stdClass $object): void
    {
        $object->query = (object) [
            'bool' => (object) [
                'must' => $object->query ?? [],
                'filter' => [],
            ]
        ];
    }
}
