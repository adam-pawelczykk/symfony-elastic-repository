<?php
/** @author Adam Pawełczyk */

namespace ATPawelczyk\Tests;

use ATPawelczyk\Elastic\Exception\IndexConfigurationNotExist;
use ATPawelczyk\Elastic\Index;
use ATPawelczyk\Elastic\IndexManager;
use Elasticsearch\Client;
use PHPUnit\Framework\TestCase;
use stdClass;
use Throwable;

/**
 * Class IndexManagerTest
 * @package ATPawelczyk\Tests
 */
class IndexManagerTest extends TestCase
{
    /** @var IndexManager */
    private $manager;

    protected function setUp(): void
    {
        $this->manager = new IndexManager(
            $this->createMock(Client::class),
            'test',
            [
                'stock' => [
                    'class' => \stdClass::class,
                    'prefix' => ''
                ],
                'stat' => [
                    'prefix' => 'static'
                ],
                'car' => [
                    'class' => ''
                ],
                'test' => [
                    'name' => 'some_index_name',
                    'prefix' => 'prefix'
                ]
            ]
        );
    }

    /**
     * @throws Throwable
     */
    public function testShouldSuccessReturnIndexByClass(): void
    {
        $index = $this->manager->getIndex(stdClass::class);
        $config = $this->manager->getConfig(stdClass::class);

        $this->assertInstanceOf(Index::class, $index);
        $this->assertEquals('test_stock', $config->getIndexFullName());
        $this->assertEquals(stdClass::class, $config->getClass());
    }

    /**
     * @throws Throwable
     */
    public function testShouldOverwriteDefaultPrefixByConfiguration(): void
    {
        $index = $this->manager->getIndex('stat');
        $config = $this->manager->getConfig('stat');

        $this->assertInstanceOf(Index::class, $index);
        $this->assertEquals('static_stat', $config->getIndexFullName());
        $this->assertEmpty($config->getClass());
    }

    /**
     * @throws Throwable
     */
    public function testShouldSuccessReturnIndexKey(): void
    {
        $index = $this->manager->getIndex('car');
        $config = $this->manager->getConfig('car');

        $this->assertInstanceOf(Index::class, $index);
        $this->assertEquals('test_car', $config->getIndexFullName());
        $this->assertEmpty($config->getClass());
    }

    public function testShouldThrowExceptionWhileConfigurationOfIndexDoesNotExist(): void
    {
        $this->expectException(IndexConfigurationNotExist::class);
        $this->expectExceptionMessage('Index with name unknown not exist! Chceck gd_elastic YAML configurations');
        $this->assertInstanceOf(Index::class, $this->manager->getIndex('unknown'));
    }

    public function testShouldOverwriteIndexNameWhenConfigHasNameValue(): void
    {
        $index = $this->manager->getIndex('test');

        $this->assertEquals('prefix_some_index_name', $index->getConfig()->getIndexFullName());
    }

    public function testShouldSuccessGetTestIndexOnEveryPossibleWay(): void
    {
        $this->assertEquals(
            'test',
            $this->manager
                ->getIndex('test')
                ->getConfig()
                ->getIndexKey()
        );
        $this->assertEquals(
            'some_index_name',
            $this->manager
                ->getIndex('some_index_name')
                ->getConfig()
                ->getIndexName()
        );
        $this->assertEquals(
            'prefix_some_index_name',
            $this->manager
                ->getIndex('prefix_some_index_name')
                ->getConfig()
                ->getIndexFullName()
        );
        $this->assertEquals(
            'test',
            $this->manager
                ->getConfig('test')
                ->getIndexKey()
        );
        $this->assertEquals(
            'some_index_name',
            $this->manager
                ->getConfig('some_index_name')
                ->getIndexName()
        );
        $this->assertEquals(
            'prefix_some_index_name',
            $this->manager
                ->getConfig('prefix_some_index_name')
                ->getIndexFullName()
        );
    }
}
