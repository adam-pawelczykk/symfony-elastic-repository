<?php
/** @author: Adam Pawełczyk */

namespace ATPawelczyk\Elastic\CLI;

use ATPawelczyk\Elastic\Exception\IndexConfigurationNotExist;
use ATPawelczyk\Elastic\Exception\IndexDoesNotExist;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class UpdateIndexesMappingCLI extends AbstractIncexCLI
{
    protected function configure(): void
    {
        $this
            ->setName('elastic:index:update:mapping')
            ->setDescription('Update index mapping')
            ->addArgument('index', InputArgument::REQUIRED);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $indexes = $this->getIndexes($input);
        } catch (IndexConfigurationNotExist $e) {
            $io->error($e->getMessage());
            return self::FAILURE;
        }

        foreach ($indexes as $index) {
            $indexFullName = $index->getConfig()->getIndexFullName();
            $indexKey = $index->getConfig()->getIndexKey();

            try {
                $this->manager->updateIndexMapping($indexKey);
            } catch (IndexDoesNotExist $e) {
                $io->error($e->getMessage());
                return self::FAILURE;
            }

            $io->newLine();
            $io->writeln(" <info>[  OK  ] Index {$indexFullName} mapping was updated</info>");
        }

        $io->success('Index mapping update has been completed');

        return self::SUCCESS;
    }
}
