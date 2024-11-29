<?php
/** @author Adam Pawełczyk */

namespace ATPawelczyk\Elastic\DSL;

use stdClass;

/**
 * Interface DSLQueryStackInterface
 * @package ATPawelczyk\Elastic\DSL
 */
interface DSLQueryStackInterface
{
    /** @param stdClass $query */
    public function addQueryToStack(stdClass $query): void;

    /** @return array<stdClass> */
    public function getQueryStack(): array;

    /** @param array<stdClass> $stack */
    public function setQueryStack(array $stack): void;

    /** @return array<object> */
    public function getQueries(): array;

    /**
     * If true stack contains more than one query and if action is search we should use _msearch endpoint in elasticsearch
     * @return bool
     */
    public function isMultiQuery(): bool;

    /**
     * Method allow to add filters for query object no worries to broke it.
     * You can use it to restrict access form some document by access policy like locations access
     * @param FilterInterface $filter
     */
    public function addFilter(FilterInterface $filter): void;
}
