<?php
/** @author: Adam Pawełczyk */

namespace ATPawelczyk\Elastic\CLI;

use ATPawelczyk\Elastic\Exception\IndexConfigurationNotExist;
use ATPawelczyk\Elastic\IndexInterface;
use ATPawelczyk\Elastic\IndexManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;

class AbstractIncexCLI extends Command
{
    public const SUCCESS = 0;
    public const INVALID = 2;

    protected $manager;

    public function __construct(IndexManagerInterface $manager)
    {
        parent::__construct();

        $this->manager = $manager;
    }

    /**
     * @param InputInterface $input
     * @return IndexInterface[]
     * @throws IndexConfigurationNotExist
     */
    protected function getIndexes(InputInterface $input): array
    {
        $indexName = $input->getArgument('index');

        if ($indexName === 'all') {
            return $this->manager->getIndexes();
        }

        if ($this->manager->hasIndex($indexName)) {
            return [$this->manager->getIndex($indexName)];
        }

        throw new IndexConfigurationNotExist($indexName);
    }
}
