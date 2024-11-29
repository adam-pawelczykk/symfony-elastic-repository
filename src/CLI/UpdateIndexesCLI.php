<?php
/** @author: Adam Pawełczyk */

namespace ATPawelczyk\Elastic\CLI;

use ATPawelczyk\Elastic\Exception\IndexConfigurationNotExist;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class UpdateIndexesCLI extends AbstractIncexCLI
{
    protected function configure(): void
    {
        $this
            ->setName('elastic:index:update')
            ->setDescription('Update index')
            ->addArgument('index', InputArgument::REQUIRED)
        ;
    }

    /**
     * @throws IndexConfigurationNotExist
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $indexes = $this->getIndexes($input);

        foreach ($indexes as $index) {
            $indexFullName = $index->getConfig()->getIndexFullName();
            $indexKey = $index->getConfig()->getIndexKey();

            $this->manager->updateIndex($indexKey);

            $io->newLine();
            $io->writeln(" <info>[  OK  ] Index {$indexFullName} was updated</info>");
        }

        $io->success('Index update has been completed');

        return self::SUCCESS;
    }
}
