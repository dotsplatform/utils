<?php

/**
 * Description of OctaneDatabaseSessionServiceProvider.php.
 *
 * Gives every MySQL connection of a Vapor Octane web worker a short idle timeout, set once when the connection opens.
 * Opt-in: it is not auto-discovered. Register it explicitly, in the same change that sets
 * "octane-database-session-ttl: 0" in vapor.yml (see README.md).
 *
 * The web Lambda keeps its DB connections between requests (octane-database-session-persist). A container that is
 * frozen or thrown away never closes them, so MySQL must drop an idle session by itself, or abandoned sessions pile up
 * on the Aurora writer.
 *
 * Vapor's own way (octane-database-session-ttl > 0) sends two statements on every request for every used connection:
 * "SET SESSION wait_timeout=28800" before the first query and "SET SESSION wait_timeout=<ttl>" after the response
 * (each about 0.6 ms, both inside the Lambda invocation), and it disconnects every connection the request did not
 * query, so the next request pays a new TLS connection (~20 ms). With ttl 0 Vapor does none of that, and this provider
 * sets the short timeout in the PDO init command instead: one statement when a connection opens or reconnects.
 *
 * The long in-request value is not needed: wait_timeout only counts the idle time between two statements, and a web
 * request ends long before the idle timeout.
 *
 * @copyright Copyright (c) DOTSPLATFORM, LLC
 * @author    Bogdan Mamontov <bohdan.mamontov@dotsplatform.com>
 */

namespace Dots\Utils\Database;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Support\ServiceProvider;
use RuntimeException;

class OctaneDatabaseSessionServiceProvider extends ServiceProvider
{
    public const int DEFAULT_IDLE_SESSION_TIMEOUT_SECONDS = 120;

    /**
     * Optional override of the idle timeout, in seconds (for example in config/database.php).
     */
    public const string IDLE_SESSION_TIMEOUT_CONFIG_KEY = 'database.octane_idle_session_timeout';

    private const string CONNECTIONS_CONFIG_KEY = 'database.connections';

    protected function getConfigRepository(): Repository
    {
        $configRepository = $this->app->make('config');
        if (! $configRepository instanceof Repository) {
            throw new RuntimeException('The "config" binding is not a config repository.');
        }

        return $configRepository;
    }

    protected function getIdleSessionTimeoutConfigurator(): MysqlIdleSessionTimeoutConfigurator
    {
        return new MysqlIdleSessionTimeoutConfigurator();
    }

    public function register(): void
    {
        if (! $this->isVaporOctaneWorker()) {
            return;
        }

        if (! extension_loaded('pdo_mysql')) {
            return;
        }

        $this->addIdleTimeoutToMysqlConnections();
    }

    private function isVaporOctaneWorker(): bool
    {
        // Laravel\Vapor\Runtime\Octane\Octane::boot() sets it before it creates the worker application. The queue and
        // CLI runtimes do not set it, and the FPM runtime passes it only as a request parameter in $_SERVER.
        $serverSoftware = $_ENV['SERVER_SOFTWARE'] ?? null;

        return $serverSoftware === 'vapor';
    }

    private function addIdleTimeoutToMysqlConnections(): void
    {
        $configRepository = $this->getConfigRepository();

        $connectionsConfig = $configRepository->get(self::CONNECTIONS_CONFIG_KEY, []);
        if (! is_array($connectionsConfig)) {
            return;
        }

        $updatedConnectionsConfig = $this->getIdleSessionTimeoutConfigurator()->addIdleTimeout(
            $connectionsConfig,
            $this->resolveIdleTimeoutSeconds($configRepository),
        );

        $configRepository->set(self::CONNECTIONS_CONFIG_KEY, $updatedConnectionsConfig);
    }

    private function resolveIdleTimeoutSeconds(Repository $configRepository): int
    {
        $configuredTimeout = $configRepository->get(self::IDLE_SESSION_TIMEOUT_CONFIG_KEY);
        if (! is_numeric($configuredTimeout)) {
            return self::DEFAULT_IDLE_SESSION_TIMEOUT_SECONDS;
        }

        $configuredTimeoutSeconds = (int) $configuredTimeout;
        if ($configuredTimeoutSeconds < 1) {
            return self::DEFAULT_IDLE_SESSION_TIMEOUT_SECONDS;
        }

        return $configuredTimeoutSeconds;
    }
}
