<?php
/** @author Adam Pawełczyk */

namespace ATPawelczyk\Elastic\DSL;

/**
 * Interface FilterInterface
 * @package ATPawelczyk\Elastic\DSL
 */
interface FilterInterface
{
    public function toArray(): array;
}
