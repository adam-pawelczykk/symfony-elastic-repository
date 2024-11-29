<?php
/** @author Adam Pawełczyk */

namespace ATPawelczyk\Elastic\DSL;

/**
 * Class FilterQuery
 * @package ATPawelczyk\Elastic\DSL
 */
class FilterQuery implements FilterInterface
{
    private $query;

    /**
     * FilterQuery constructor.
     * SQL to DSL online converter https://www.overvyu.com/sqltoelasticsearch
     * @param mixed[] $query
     */
    public function __construct(array $query)
    {
        $this->query = $query;
    }

    /**
     * @return mixed[]
     */
    public function toArray(): array
    {
        return $this->query;
    }
}
