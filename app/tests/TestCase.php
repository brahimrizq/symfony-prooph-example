<?php

declare(strict_types=1);

namespace Tests;

use App\Kernel;
use Doctrine\DBAL\Connection;
use Prooph\EventSourcing\Aggregate\AggregateType;
use Prooph\EventSourcing\AggregateRoot;
use Prooph\EventSourcing\EventStoreIntegration\AggregateTranslator;
use Symfony\Component\DependencyInjection\Container;

class TestCase extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Container|null
     */
    protected $container;

    /**
     * @var Kernel
     */
    private $kernel;
    /**
     * @var Connection
     */
    private $connection;

    protected function setUp(): void
    {
        parent::setUp(); // TODO: Change the autogenerated stub
        $this->kernel = new Kernel('test', true);
        $this->kernel->boot();
        $this->container = $this->kernel->getContainer();
        $this->connection = $this->container->get('doctrine')->getConnection();
        $this->connection->beginTransaction();
        $this->connection->query('SET FOREIGN_KEY_CHECKS=0');
        $this->connection->query('truncate table `_4228e4a00331b5d5e751db0481828e22a2c3c8ef`;');
        $this->connection->query('truncate table `author`;');
        $this->connection->query('truncate table `category`;');
        $this->connection->query('truncate table `book`;');
        $this->connection->query('truncate table `projections`;');
        $this->connection->query('SET FOREIGN_KEY_CHECKS=1');
        $this->connection->commit();
        $this->connection->close();
    }

    protected function tearDown(): void
    {
        parent::tearDown(); // TODO: Change the autogenerated stub
    }

    /**
     * @var AggregateTranslator
     */
    private $aggregateTranslator;

    protected function popRecordedEvent(AggregateRoot $aggregateRoot): array
    {
        return $this->getAggregateTranslator()->extractPendingStreamEvents($aggregateRoot);
    }

    /**
     * @return object
     */
    protected function reconstituteAggregateFromHistory(string $aggregateRootClass, array $events): object
    {
        return $this->getAggregateTranslator()->reconstituteAggregateFromHistory(
            AggregateType::fromAggregateRootClass($aggregateRootClass),
            new \ArrayIterator($events)
        );
    }

    private function getAggregateTranslator(): AggregateTranslator
    {
        if (null === $this->aggregateTranslator) {
            $this->aggregateTranslator = new AggregateTranslator();
        }

        return $this->aggregateTranslator;
    }
}
