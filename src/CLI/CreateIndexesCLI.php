<?php
/** @author: Adam Pawełczyk */

namespace ATPawelczyk\Elastic\CLI;

use Exception;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class CreateIndexesCLI extends AbstractIncexCLI
{

    protected function configure(): void
    {
        $this
            ->setName('elastic:index:create')
            ->setDescription('Create all registered index if not exist')
            ->addArgument('index', InputArgument::REQUIRED)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $indexes = $this->getIndexes($input);
            $counted = count($indexes);
        } catch (Exception $exception) {
            $io->error($exception->getMessage());

            return self::INVALID;
        }

        $io->writeln("<info>Founded {$counted} indexes to register.</info>");
        $io->progressStart($counted);

        foreach ($indexes as $index) {
            $indexFullName = $index->getConfig()->getIndexFullName();
            $indexKey = $index->getConfig()->getIndexKey();

            $io->progressAdvance();

            if ($this->manager->isIndexExist($indexKey)) {
                $io->writeln(" <fg=yellow>[ INFO ] Index {$indexFullName} already exist, skipped</>");
            } else {
                $this->manager->createIndex($indexKey);

                $io->writeln(" <info>[  OK  ] Index {$indexFullName} was created</info>");
            }
        }

        $io->success('Index registration has been completed');
        $io->progressFinish();

        return self::SUCCESS;
    }
}
