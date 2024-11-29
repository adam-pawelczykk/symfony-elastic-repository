<?php
/** @author Adam Pawełczyk */

namespace ATPawelczyk\Elastic;

/**
 * Class Document
 * @package ATPawelczyk\Elastic
 */
class Document
{
    /** @var string */
    private $id;
    /** @var array */
    private $body;

    /**
     * @param string $id
     * @param array $body
     */
    public function __construct(string $id, array $body)
    {
        $this->id = $id;
        $this->body = $body;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getBody(): array
    {
        return $this->body;
    }
}
