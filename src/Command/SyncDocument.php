<?php
/** @author Adam Pawełczyk */

namespace ATPawelczyk\Elastic\Command;

use ATPawelczyk\Elastic\Document;

/**
 * Class SyncDocument
 * @package ATPawelczyk\Elastic\Command
 */
class SyncDocument
{
    private $index;
    private $document;

    /**
     * SyncDocument constructor.
     * @param string $index
     * @param Document $document
     */
    public function __construct(string $index, Document $document)
    {
        $this->index = $index;
        $this->document = $document;
    }

    /**
     * @return string
     */
    public function getIndex(): string
    {
        return $this->index;
    }

    /**
     * @return Document
     */
    public function getDocument(): Document
    {
        return $this->document;
    }
}
