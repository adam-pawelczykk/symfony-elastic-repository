<?php
declare(strict_types=1);

namespace ATPawelczyk\Elastic\Exception;

use Exception;

class IndexDoesNotExist extends Exception
{
    private $indexName;

    public function __construct(string $indexName, Exception $previous = null)
    {
        $this->indexName = $indexName;

        $message = sprintf("Index with name %s does not exist! Create it first", $indexName);

        parent::__construct($message, 0, $previous);
    }

    public function getIndexName(): string
    {
        return $this->indexName;
    }
}
