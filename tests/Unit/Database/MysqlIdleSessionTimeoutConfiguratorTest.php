<?php

/**
 * Description of MysqlIdleSessionTimeoutConfiguratorTest.php.
 *
 * @copyright Copyright (c) DOTSPLATFORM, LLC
 * @author    Bogdan Mamontov <bohdan.mamontov@dotsplatform.com>
 */

namespace Tests\Unit\Database;

use Dots\Utils\Database\MysqlIdleSessionTimeoutConfigurator;
use Pdo\Mysql;
use Tests\TestCase;

class MysqlIdleSessionTimeoutConfiguratorTest extends TestCase
{
    private const string IDLE_TIMEOUT_COMMAND = 'SET SESSION wait_timeout=120';

    private const string READER_TIMEOUT_COMMAND = 'SET SESSION wait_timeout=300';

    protected function setUp(): void
    {
        parent::setUp();
        if (! extension_loaded('pdo_mysql')) {
            $this->markTestSkipped('pdo_mysql is not loaded.');
        }
    }

    /**
     * @group unit
     * @group database
     */
    public function testMysqlConnectionGetsIdleTimeoutInitCommand(): void
    {
        $connectionsConfig = [
            'mysql' => [
                'driver' => 'mysql',
                'options' => [Mysql::ATTR_SSL_CA => '/ca.pem'],
            ],
        ];

        $result = $this->makeConfigurator()->addIdleTimeout($connectionsConfig, 120);

        $this->assertSame(
            [Mysql::ATTR_SSL_CA => '/ca.pem', Mysql::ATTR_INIT_COMMAND => self::IDLE_TIMEOUT_COMMAND],
            $result['mysql']['options'],
        );
    }

    /**
     * @group unit
     * @group database
     */
    public function testMysqlConnectionWithoutOptionsGetsOptions(): void
    {
        $connectionsConfig = ['mysql' => ['driver' => 'mysql']];

        $result = $this->makeConfigurator()->addIdleTimeout($connectionsConfig, 45);

        $this->assertSame([Mysql::ATTR_INIT_COMMAND => 'SET SESSION wait_timeout=45'], $result['mysql']['options']);
    }

    /**
     * @group unit
     * @group database
     */
    public function testExistingInitCommandIsNotReplaced(): void
    {
        $connectionsConfig = [
            'mysql' => [
                'driver' => 'mysql',
                'options' => [Mysql::ATTR_INIT_COMMAND => "SET time_zone='+00:00'"],
            ],
        ];

        $result = $this->makeConfigurator()->addIdleTimeout($connectionsConfig, 120);

        $this->assertSame($connectionsConfig, $result);
    }

    /**
     * @group unit
     * @group database
     */
    public function testNonMysqlConnectionsAreNotChanged(): void
    {
        $connectionsConfig = [
            'sqlite' => ['driver' => 'sqlite', 'database' => ':memory:'],
            'pgsql' => ['driver' => 'pgsql', 'options' => []],
            'broken' => 'not-an-array',
        ];

        $result = $this->makeConfigurator()->addIdleTimeout($connectionsConfig, 120);

        $this->assertSame($connectionsConfig, $result);
    }

    /**
     * The clients-api case: the reader has its own init command with a longer timeout, which must stay. Laravel uses
     * the read "options" instead of the top-level ones for the read PDO, so the top-level init command does not
     * reach it.
     *
     * @group unit
     * @group database
     */
    public function testReadSectionKeepsItsOwnInitCommandAndWriteUsesTopLevelOptions(): void
    {
        $connectionsConfig = [
            'mysql' => [
                'driver' => 'mysql',
                'read' => [
                    'host' => ['reader.example'],
                    'options' => [Mysql::ATTR_INIT_COMMAND => self::READER_TIMEOUT_COMMAND],
                ],
                'write' => ['host' => ['writer.example']],
                'options' => [],
            ],
        ];

        $result = $this->makeConfigurator()->addIdleTimeout($connectionsConfig, 120);

        $this->assertSame([Mysql::ATTR_INIT_COMMAND => self::READER_TIMEOUT_COMMAND], $result['mysql']['read']['options']);
        $this->assertSame(['host' => ['writer.example']], $result['mysql']['write']);
        $this->assertSame([Mysql::ATTR_INIT_COMMAND => self::IDLE_TIMEOUT_COMMAND], $result['mysql']['options']);
    }

    /**
     * @group unit
     * @group database
     */
    public function testReadSectionWithOwnOptionsWithoutInitCommandGetsIdleTimeout(): void
    {
        $connectionsConfig = [
            'mysql' => [
                'driver' => 'mysql',
                'read' => ['host' => ['reader.example'], 'options' => [Mysql::ATTR_SSL_CA => '/ca.pem']],
            ],
        ];

        $result = $this->makeConfigurator()->addIdleTimeout($connectionsConfig, 120);

        $this->assertSame(
            [Mysql::ATTR_SSL_CA => '/ca.pem', Mysql::ATTR_INIT_COMMAND => self::IDLE_TIMEOUT_COMMAND],
            $result['mysql']['read']['options'],
        );
    }

    /**
     * @group unit
     * @group database
     */
    public function testReadSectionAsListOfHostsGetsIdleTimeoutOnlyWhereItHasOwnOptions(): void
    {
        $connectionsConfig = [
            'mysql' => [
                'driver' => 'mysql',
                'read' => [
                    ['host' => 'reader-1.example', 'options' => []],
                    ['host' => 'reader-2.example'],
                ],
            ],
        ];

        $result = $this->makeConfigurator()->addIdleTimeout($connectionsConfig, 120);

        $this->assertSame([Mysql::ATTR_INIT_COMMAND => self::IDLE_TIMEOUT_COMMAND], $result['mysql']['read'][0]['options']);
        $this->assertSame(['host' => 'reader-2.example'], $result['mysql']['read'][1]);
    }

    private function makeConfigurator(): MysqlIdleSessionTimeoutConfigurator
    {
        return new MysqlIdleSessionTimeoutConfigurator();
    }
}
