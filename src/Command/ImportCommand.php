<?php

namespace App\Command;

use App\Config\ProvidersConfig;
use App\MessageBus\ImportFileMessage;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;


class ImportCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('app:import-file')
            ->setDescription('Import log to database')
            ->addArgument('filename', InputArgument::OPTIONAL, 'Log filename')
            ->addArgument(
                'provider',
                InputArgument::OPTIONAL,
                'Provider name'
            )
            ->addOption('truncate', null, InputOption::VALUE_NONE, 'Truncate table before import');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Import log to database');

        $filename = $input->getArgument('filename');
        $providerName = $input->getArgument('provider');

        if ($this->validation($io, $filename, $providerName)) {
            $message = new ImportFileMessage($filename, $providerName, $input->getOption('truncate'));
            $message->setSymfonyStyle($io);
            $this->getContainer()->get('command_bus')->handle($message);
        }
    }

    protected function validation(SymfonyStyle $io, $filename, $providerName)
    {
        $result = true;
        if (empty($filename)) {
            $io->caution('Not enough arguments (missing: "filename").');
            $result = false;
        } elseif (!file_exists($filename) || !is_readable($filename)) {
            $io->caution(sprintf('File "%s" not found or is not readable', $filename));
            $result = false;
        }

        $providerNames = $this->getContainer()->get(ProvidersConfig::class)
            ->getProviderNames();
        if (!$providerName) {
            $io->caution('Not enough arguments (missing: "provider").');
            $io->note(sprintf('Provider possible values: %s', implode(', ', $providerNames)));
            $result = false;
        } elseif (!in_array($providerName, $providerNames)) {
            $io->caution(sprintf('Invalid provider name, possible values: %s', implode(', ', $providerNames)));
            $result = false;
        }

        return $result;
    }
}
