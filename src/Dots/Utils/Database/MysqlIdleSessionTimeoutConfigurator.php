<?php

/**
 * Description of MysqlIdleSessionTimeoutConfigurator.php.
 *
 * Adds "SET SESSION wait_timeout=<n>" as the PDO init command to every MySQL connection
 * of a "database.connections" config array, so MySQL closes an idle session by itself.
 *
 * An init command that a connection already has is never replaced: the service set it on
 * purpose (for example a longer idle timeout for the Aurora reader).
 *
 * Laravel builds the read and write PDOs from array_merge($connection, $connection['read'|'write']),
 * so an "options" key inside a read/write sub-array replaces the top-level "options" as a whole.
 * That is why such sub-arrays get the init command too, unless they already have one.
 *
 * @copyright Copyright (c) DOTSPLATFORM, LLC
 * @author    Bogdan Mamontov <bohdan.mamontov@dotsplatform.com>
 */

namespace Dots\Utils\Database;

use Pdo\Mysql;

class MysqlIdleSessionTimeoutConfigurator
{
    private const string MYSQL_DRIVER = 'mysql';

    private const array READ_WRITE_SECTIONS = ['read', 'write'];

    /**
     * @param array<mixed> $connectionsConfig the "database.connections" config array
     * @return array<mixed>
     */
    public function addIdleTimeout(array $connectionsConfig, int $idleTimeoutSeconds): array
    {
        $initCommand = $this->buildInitCommand($idleTimeoutSeconds);

        foreach ($connectionsConfig as $connectionName => $connectionConfig) {
            if (! $this->isMysqlConnection($connectionConfig)) {
                continue;
            }

            /** @var array<mixed> $connectionConfig */
            $connectionsConfig[$connectionName] = $this->addInitCommandToConnection($connectionConfig, $initCommand);
        }

        return $connectionsConfig;
    }

    public function buildInitCommand(int $idleTimeoutSeconds): string
    {
        return sprintf('SET SESSION wait_timeout=%d', $idleTimeoutSeconds);
    }

    private function isMysqlConnection(mixed $connectionConfig): bool
    {
        if (! is_array($connectionConfig)) {
            return false;
        }

        $driver = $connectionConfig['driver'] ?? null;

        return $driver === self::MYSQL_DRIVER;
    }

    /**
     * @param array<mixed> $connectionConfig
     * @return array<mixed>
     */
    private function addInitCommandToConnection(array $connectionConfig, string $initCommand): array
    {
        $connectionConfig = $this->addInitCommandToOptionsOf($connectionConfig, $initCommand);

        foreach (self::READ_WRITE_SECTIONS as $sectionName) {
            $sectionConfig = $connectionConfig[$sectionName] ?? null;
            if (! is_array($sectionConfig)) {
                continue;
            }

            $connectionConfig[$sectionName] = $this->addInitCommandToReadWriteSection($sectionConfig, $initCommand);
        }

        return $connectionConfig;
    }

    /**
     * A read/write section is one array of settings, or a list of them (Laravel picks one at random).
     *
     * @param array<mixed> $sectionConfig
     * @return array<mixed>
     */
    private function addInitCommandToReadWriteSection(array $sectionConfig, string $initCommand): array
    {
        if (! array_is_list($sectionConfig)) {
            return $this->addInitCommandToOwnOptionsOf($sectionConfig, $initCommand);
        }

        foreach ($sectionConfig as $index => $hostConfig) {
            if (! is_array($hostConfig)) {
                continue;
            }

            $sectionConfig[$index] = $this->addInitCommandToOwnOptionsOf($hostConfig, $initCommand);
        }

        return $sectionConfig;
    }

    /**
     * Only a section with its own "options" needs the init command: without them it uses the top-level options.
     *
     * @param array<mixed> $sectionConfig
     * @return array<mixed>
     */
    private function addInitCommandToOwnOptionsOf(array $sectionConfig, string $initCommand): array
    {
        if (! array_key_exists('options', $sectionConfig)) {
            return $sectionConfig;
        }

        return $this->addInitCommandToOptionsOf($sectionConfig, $initCommand);
    }

    /**
     * @param array<mixed> $config a connection, or one of its read/write sections
     * @return array<mixed>
     */
    private function addInitCommandToOptionsOf(array $config, string $initCommand): array
    {
        $pdoOptions = $config['options'] ?? [];
        if (! is_array($pdoOptions)) {
            return $config;
        }

        if ($this->hasInitCommand($pdoOptions)) {
            return $config;
        }

        $pdoOptions[Mysql::ATTR_INIT_COMMAND] = $initCommand;
        $config['options'] = $pdoOptions;

        return $config;
    }

    /**
     * @param array<mixed> $pdoOptions
     */
    private function hasInitCommand(array $pdoOptions): bool
    {
        $existingInitCommand = $pdoOptions[Mysql::ATTR_INIT_COMMAND] ?? null;

        return $existingInitCommand !== null && $existingInitCommand !== '';
    }
}
