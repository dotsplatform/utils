<?php

/**
 * Description of OctaneDatabaseSessionServiceProviderTest.php.
 *
 * @copyright Copyright (c) DOTSPLATFORM, LLC
 * @author    Bogdan Mamontov <bohdan.mamontov@dotsplatform.com>
 */

namespace Tests\Unit\Database;

use Dots\Utils\Database\OctaneDatabaseSessionServiceProvider;
use Illuminate\Config\Repository;
use Illuminate\Container\Container;
use Pdo\Mysql;
use Tests\TestCase;

class OctaneDatabaseSessionServiceProviderTest extends TestCase
{
    private ?string $originalServerSoftware;

    private Container $container;

    private Repository $configRepository;

    protected function setUp(): void
    {
        parent::setUp();
        if (! extension_loaded('pdo_mysql')) {
            $this->markTestSkipped('pdo_mysql is not loaded.');
        }

        $this->originalServerSoftware = $_ENV['SERVER_SOFTWARE'] ?? null;
        $this->configRepository = new Repository([
            'database' => [
                'connections' => [
                    'mysql' => ['driver' => 'mysql', 'options' => []],
                    'sqlite' => ['driver' => 'sqlite', 'database' => ':memory:'],
                ],
            ],
        ]);
        $this->container = new Container();
        $this->container->instance('config', $this->configRepository);
    }

    protected function tearDown(): void
    {
        $this->restoreServerSoftware();
        parent::tearDown();
    }

    /**
     * @group unit
     * @group database
     */
    public function testOutsideVaporOctaneWorkerConfigIsNotChanged(): void
    {
        unset($_ENV['SERVER_SOFTWARE']);
        $connectionsBefore = $this->configRepository->get('database.connections');

        $this->registerProvider();

        $this->assertSame($connectionsBefore, $this->configRepository->get('database.connections'));
    }

    /**
     * The FPM runtime passes SERVER_SOFTWARE only in $_SERVER, per request; that must not switch the provider on.
     *
     * @group unit
     * @group database
     */
    public function testServerSoftwareOnlyInServerArrayDoesNotCount(): void
    {
        unset($_ENV['SERVER_SOFTWARE']);
        $_SERVER['SERVER_SOFTWARE'] = 'vapor';
        $connectionsBefore = $this->configRepository->get('database.connections');

        $this->registerProvider();

        unset($_SERVER['SERVER_SOFTWARE']);
        $this->assertSame($connectionsBefore, $this->configRepository->get('database.connections'));
    }

    /**
     * @group unit
     * @group database
     */
    public function testInVaporOctaneWorkerMysqlConnectionsGetDefaultIdleTimeout(): void
    {
        $_ENV['SERVER_SOFTWARE'] = 'vapor';

        $this->registerProvider();

        $expectedStatement = 'SET SESSION wait_timeout='.OctaneDatabaseSessionServiceProvider::DEFAULT_IDLE_SESSION_TIMEOUT_SECONDS;
        $this->assertSame(
            [Mysql::ATTR_INIT_COMMAND => $expectedStatement],
            $this->configRepository->get('database.connections.mysql.options'),
        );
        $this->assertSame(
            ['driver' => 'sqlite', 'database' => ':memory:'],
            $this->configRepository->get('database.connections.sqlite'),
        );
    }

    /**
     * @group unit
     * @group database
     */
    public function testIdleTimeoutComesFromConfigWhenSet(): void
    {
        $_ENV['SERVER_SOFTWARE'] = 'vapor';
        $this->configRepository->set(OctaneDatabaseSessionServiceProvider::IDLE_SESSION_TIMEOUT_CONFIG_KEY, '60');

        $this->registerProvider();

        $this->assertSame(
            [Mysql::ATTR_INIT_COMMAND => 'SET SESSION wait_timeout=60'],
            $this->configRepository->get('database.connections.mysql.options'),
        );
    }

    /**
     * @group unit
     * @group database
     */
    public function testInvalidConfiguredIdleTimeoutFallsBackToDefault(): void
    {
        $_ENV['SERVER_SOFTWARE'] = 'vapor';
        $this->configRepository->set(OctaneDatabaseSessionServiceProvider::IDLE_SESSION_TIMEOUT_CONFIG_KEY, 0);

        $this->registerProvider();

        $expectedStatement = 'SET SESSION wait_timeout='.OctaneDatabaseSessionServiceProvider::DEFAULT_IDLE_SESSION_TIMEOUT_SECONDS;
        $this->assertSame(
            [Mysql::ATTR_INIT_COMMAND => $expectedStatement],
            $this->configRepository->get('database.connections.mysql.options'),
        );
    }

    private function registerProvider(): void
    {
        $provider = new OctaneDatabaseSessionServiceProvider($this->container);
        $provider->register();
    }

    private function restoreServerSoftware(): void
    {
        if ($this->originalServerSoftware === null) {
            unset($_ENV['SERVER_SOFTWARE']);

            return;
        }

        $_ENV['SERVER_SOFTWARE'] = $this->originalServerSoftware;
    }
}
