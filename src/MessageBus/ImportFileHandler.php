<?php

namespace App\MessageBus;

use App\Config\ProvidersConfig;
use App\Helper\LogParser;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class ImportFileHandler
{
    /**
     * @var ProvidersConfig
     */
    protected $providersConfig;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var LogParser
     */
    protected $logParser;

    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    public function __construct(
        ProvidersConfig $providersConfig,
        LoggerInterface $logger,
        LogParser $logParser
    ) {
        $this->providersConfig = $providersConfig;
        $this->logger = $logger;
        $this->logParser = $logParser;
    }

    /**
     *
     * @required
     *
     * @param EntityManagerInterface $entityManager
     */
    public function setEntityManager(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function handle(ImportFileMessage $message)
    {
        $provider = $this->providersConfig->getProvider($message->getProviderName());
        $this->logger->debug(
            'parse start',
            ['filename' => $message->getFilename(), 'provider' => $provider->getName()]
        );

        $io = $message->getSymfonyStyle();
        if ($io) {
            $io->progressStart($this->getLineCount($message->getFilename()));
        }

        $handle = @fopen($message->getFilename(), "r");
        if ($handle) {
            fseek($handle, 0, SEEK_SET);
            $batchSize = 500;
            $i = 0;
            $this->entityManager->getConnection()->getConfiguration()->setSQLLogger(null);
            while (($buffer = fgets($handle)) !== false) {
                if ($message->isTruncate() && $i == 0) {
                    $this->truncateTable($provider->getEntityClassName());
                }

                $tokens = $this->logParser->parseLine($buffer, $provider);
                if (!$tokens) {
                    $this->logger->error('Something wrong with line', ['line' => $buffer]);
                    continue;
                }
                $this->logger->debug('', $tokens);

                $i++;
                $entity = $this->makeEntity($provider->getEntityClassName(), $tokens);
                $this->entityManager->persist($entity);

                // IMPORTANT - Temporary store entities (of course, must be defined first outside of the loop)
                $tempObjects[] = $entity;
                if (($i % $batchSize) === 0) {
                    $this->entityManager->flush();
                    $this->entityManager->clear();
                    $this->logger->debug('flush', ['i' => $i, 'batch_size' => $batchSize]);

                    if ($io) {
                        $io->progressAdvance($batchSize);
                    }

                    gc_enable();
                    gc_collect_cycles();
                }
            }
            $this->entityManager->flush(); //Persist objects that did not make up an entire batch
            $this->entityManager->clear(); // Detaches all objects from Doctrine!
            if ($io) {
                $io->progressFinish();
            }
            if (!feof($handle)) {
                throw new \RuntimeException('Error: unexpected fgets() fail');
            }
            fclose($handle);
        }

        $this->logger->debug('parse complete');
    }

    protected function getLineCount($filename)
    {
        $file = new \SplFileObject($filename, 'r');
        $file->seek(PHP_INT_MAX);

        return $file->key() + 1;
    }

    protected function truncateTable($className)
    {
        $cmd = $this->entityManager->getClassMetadata($className);
        $connection = $this->entityManager->getConnection();
        $dbPlatform = $connection->getDatabasePlatform();
        $connection->beginTransaction();
        try {
            $connection->query('SET FOREIGN_KEY_CHECKS=0');
            $q = $dbPlatform->getTruncateTableSql($cmd->getTableName());
            $connection->executeUpdate($q);
            $connection->query('SET FOREIGN_KEY_CHECKS=1');
            $connection->commit();

            $this->logger->debug(sprintf('truncate table %s', $cmd->getTableName()));
        } catch (\Exception $e) {
            $connection->rollback();
        }
    }

    /**
     * @param string $entityClassName
     * @param array $data
     * @return object
     */
    protected function makeEntity($entityClassName, array $data)
    {
        $normalizer = new ObjectNormalizer(null, new CamelCaseToSnakeCaseNameConverter());
        $result = $normalizer->denormalize($data, $entityClassName);

        return $result;
    }
}
