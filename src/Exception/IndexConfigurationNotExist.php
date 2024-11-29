<?php
/** @author: Adam Pawełczyk */

namespace ATPawelczyk\Elastic\Exception;

use Exception;
use Throwable;

class IndexConfigurationNotExist extends Exception
{
    private $indexName;

    public function __construct(string $indexName, Throwable $previous = null)
    {
        $this->indexName = $indexName;

        $message = sprintf("Index with name %s not exist! Chceck gd_elastic YAML configurations", $indexName);

        parent::__construct($message, 0, $previous);
    }

    public function getIndexName(): string
    {
        return $this->indexName;
    }
}
