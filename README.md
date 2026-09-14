# Adminer 6 docker image

Adminer 6 with every database driver it knows about, native parallel dumps and
a pool that sizes itself to the container's CPU.

## Tags

- `latest`, `nginx` - nginx and php-fpm version on port 8080. RECOMENDED
- `standalone` - standalone version using php build-in web server on port 8080.
- `fpm` - bare php-fpm on port 9000, for an existing web server.

The old `mysql-nginx` / `mysql-standalone` / `mysql-fpm` tags still point at the
same images so nothing breaks, but they're aliases now — the images stopped
being MySQL-only. `linux/amd64` only.

## Databases

| Database | Driver | Provided by |
|---|---|---|
| MySQL, MariaDB | built in | ext-mysqli, ext-pdo_mysql |
| PostgreSQL, CockroachDB | built in | ext-pgsql, ext-pdo_pgsql |
| SQLite | built in | ext-sqlite3, ext-pdo_sqlite |
| MS SQL | built in | ext-sqlsrv, ext-pdo_sqlsrv, ext-pdo_dblib (FreeTDS fallback) |
| Oracle (beta) | built in | ext-oci8, ext-pdo_oci |
| MongoDB | `adminer-drivers/mongo.php` | ext-mongodb |
| ClickHouse | `adminer-drivers/clickhouse.php` | HTTP |
| Elasticsearch, OpenSearch | `adminer-drivers/elastic.php` | HTTP |
| Redis, KeyDB | `adminer-drivers/redis.php` | sockets |
| Amazon SimpleDB | `adminer-drivers/simpledb.php` | HTTP |

Firebird is the one Adminer driver missing: it needs ext-interbase, which
doesn't exist for PHP 8.4.

The Oracle Instant Client is roughly a third of the image. Build with
`--build-arg WITH_ORACLE=false` to drop it along with ext-oci8/ext-pdo_oci.

## Faster dumps

The export page picks up extra output modes, all of which keep the data out of
PHP's row-by-row loop:

- **mysqldump → zstd** (MySQL) and **pg_dump → zstd** (PostgreSQL) — native
  client piped into `zstd -T0`, an order of magnitude quicker than Adminer's
  own dump on a large database.
- **gzip (pigz, parallel)** and **Zstandard (zstd CLI, parallel)** — available
  on every driver; Adminer generates the dump, the CLI compressor uses all
  cores instead of PHP's single-threaded zlib.

**Fast Restore** in the sidebar pipes an uploaded `.sql` / `.sql.gz` /
`.sql.zst` straight into the `mysql` or `psql` client — the PostgreSQL side
runs inside a single transaction with `ON_ERROR_STOP`, so a bad dump rolls
back instead of leaving half a database. It shows up on MySQL and PostgreSQL.

nginx serves HTML, CSS and JS with gzip and brotli; the export streams are
left alone (their content types aren't in either filter's list), so a dump is
never compressed twice.

## Tuning

`docker-entrypoint.sh` reads the container's CPU allowance (cgroup quota
included, so `--cpus=2` is respected) and sizes the worker pools from it.
Everything it sets can be overridden:

| Variable | Default | Applies to |
|---|---|---|
| `PHP_FPM_PM` | `dynamic` | nginx, fpm |
| `PHP_FPM_MAX_CHILDREN` | `cpus × 4`, 8–64 | nginx, fpm |
| `PHP_FPM_START_SERVERS` | `max_children / 4` | nginx, fpm |
| `PHP_FPM_MIN_SPARE_SERVERS` | `max_children / 8` | nginx, fpm |
| `PHP_FPM_MAX_SPARE_SERVERS` | `max_children / 2` | nginx, fpm |
| `PHP_FPM_MAX_REQUESTS` | `1000` | nginx, fpm |
| `PHP_CLI_SERVER_WORKERS` | `cpus`, 4–32 | standalone |
| `ADMINER_PORT` | `8080` | standalone |
| `MONGO_AUTH_SOURCE` | — | the MongoDB driver |

## Disclaimer

I created this Docker image for my personal workflow, so it hasn't been tested in use cases beyond those I regularly work with.
