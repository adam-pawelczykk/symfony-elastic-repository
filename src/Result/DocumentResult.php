<?php
/** @author: Adam Pawełczyk */

namespace ATPawelczyk\Elastic\Result;

class DocumentResult
{
    public const RESULT_CREATED = 'created';
    public const RESULT_UPDATED = 'updated';
    public const RESULT_DELETED = 'deleted';
    public const RESULT_NOT_FOUND = 'not_found';
    public const RESULT_NOOP = 'noop';

    /**
     * Document identity
     * @var string
     */
    private $id;

    /**
     * Equals created, updated, deleted, not_found, noop
     * @var string
     */
    private $result;

    public function __construct(string $id, string $result)
    {
        $this->id = $id;
        $this->result = $result;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getResult(): string
    {
        return $this->result;
    }
}
