# dotsplatform/utils

Development tools shared by the Dots Platform services: DTO base classes (`Dots\Data`), distance and polygon helpers
(`Dots\Distance`), version checks (`Dots\Versions`) and small utilities (`Dots\Utils`).

```bash
composer require dotsplatform/utils
```

The package has no auto-discovered service providers. Everything that changes a service's behaviour is opt-in.

## Octane DB session timeout, set once per connection

`Dots\Utils\Database\OctaneDatabaseSessionServiceProvider` gives every MySQL connection of a **Vapor Octane web
worker** a short idle timeout (`SET SESSION wait_timeout=120`) as the PDO init command. MySQL runs it once, when the
connection opens or reconnects.

### Why

With `octane-database-session-ttl: <n>` (n > 0) Vapor sends two statements on every request for every connection the
request used: `SET SESSION wait_timeout=28800` before the first query and `SET SESSION wait_timeout=<n>` after the
response (about 0.6 ms each, inside the Lambda invocation). It also disconnects every connection the request did not
query, so the next request opens a new TLS connection (about 20 ms). With `octane-database-session-ttl: 0` Vapor does
none of that, and this provider keeps the short idle timeout that protects the Aurora writer from sessions left behind
by frozen or recycled Lambda containers.

### Usage

Do both steps in the same change. The provider alone does nothing useful while Vapor still sends its per-request
statements, and ttl 0 alone leaves web sessions with MySQL's default idle timeout (8 hours).

1. Register the provider explicitly, in `config/app.php`:

    ```php
    'providers' => ServiceProvider::defaultProviders()->merge([
        // ...
        Dots\Utils\Database\OctaneDatabaseSessionServiceProvider::class,
    ])->toArray(),
    ```

    or in `bootstrap/providers.php` on the Laravel 11+ skeleton.

2. In `vapor.yml`, for every environment that runs Octane with persistent sessions:

    ```yaml
    octane-database-session-persist: true
    octane-database-session-ttl: 0
    ```

Optional: a different idle timeout, in seconds, in `config/database.php`:

```php
'octane_idle_session_timeout' => 180,
```

### What it does and does not touch

- It runs only in the Vapor Octane web worker (`$_ENV['SERVER_SOFTWARE'] === 'vapor'`, which
  `Laravel\Vapor\Runtime\Octane\Octane::boot()` sets before it creates the application). Queue, CLI, FPM, local and
  test runs are not changed.
- Only connections with `driver` = `mysql`.
- An init command that a connection already has is **kept**. Laravel builds the read and write PDOs from
  `array_merge($connection, $connection['read'|'write'])`, so an `options` key inside a `read` / `write` section
  replaces the top-level `options`. Such a section gets the init command too, unless it already has one (for example
  a reader with its own, longer `wait_timeout`).
- If a connection's top-level options already have a different init command, that connection keeps it and gets no
  idle timeout. Check it after the rollout (below).

### Rollout check and rollback

- After the deploy, on the writer:

    ```sql
    SELECT t.PROCESSLIST_DB, v.VARIABLE_VALUE AS wait_timeout, COUNT(*) AS sessions
    FROM performance_schema.variables_by_thread v
    JOIN performance_schema.threads t USING (THREAD_ID)
    WHERE v.VARIABLE_NAME = 'wait_timeout' AND t.PROCESSLIST_DB = '<schema>'
    GROUP BY t.PROCESSLIST_DB, v.VARIABLE_VALUE;
    ```

    Web sessions of the service show `120` (queue and CLI sessions keep the server default).
- Watch the writer's `DatabaseConnections` in CloudWatch for an hour: it should stay flat.
- Rollback: set `octane-database-session-ttl` back to `120` and redeploy. The provider can stay registered; with a
  ttl above 0 Vapor sets the timeout after every request again.
