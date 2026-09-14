# Adminer 6 — every database driver, native parallel dumps

Adminer 6 packaged with the drivers and client binaries it actually needs:
MySQL, MariaDB, PostgreSQL, SQLite, MS SQL, Oracle, MongoDB, ClickHouse,
Elasticsearch, Redis and SimpleDB, all from one image.

Dumps and restores shell out to the native clients instead of crawling
through PHP row by row, and the worker pools size themselves to whatever
CPU the container was given.

## Tags

| Tag | Description |
|---|---|
| `latest`, `nginx` | nginx + php-fpm on port 8080. **Recommended.** |
| `standalone` | PHP's built-in server on port 8080. One process tree, no nginx. |
| `fpm` | Bare php-fpm on port 9000, for an existing web server. |

`mysql-nginx`, `mysql-standalone` and `mysql-fpm` still resolve to the same
digests. They're aliases now — the images stopped being MySQL-only — and
they'll be dropped once nobody pulls them.

`linux/amd64` only. SBOM, max-mode build provenance and a Cosign keyless
signature on every published digest. Every merge to `main` publishes, plus a
weekly rebuild for package updates.

## Quick start

```bash
docker run -d -p 8080:8080 dementev/adminer
```

```yaml
services:
  adminer:
    image: dementev/adminer
    ports: ["8080:8080"]
    networks: [database]
```

## Databases

| Database | Driver | Provided by |
|---|---|---|
| MySQL, MariaDB | built in | ext-mysqli, ext-pdo_mysql |
| PostgreSQL, CockroachDB | built in | ext-pgsql, ext-pdo_pgsql |
| SQLite | built in | ext-sqlite3, ext-pdo_sqlite |
| MS SQL | built in | ext-sqlsrv, ext-pdo_sqlsrv, ext-pdo_dblib (FreeTDS fallback) |
| Oracle (beta) | built in | ext-oci8, ext-pdo_oci |
| MongoDB | driver plugin | ext-mongodb |
| ClickHouse | driver plugin | HTTP |
| Elasticsearch, OpenSearch | driver plugin | HTTP |
| Redis, KeyDB | driver plugin | sockets |
| Amazon SimpleDB | driver plugin | HTTP |

Firebird is the one Adminer driver missing: it needs ext-interbase, which
doesn't exist for PHP 8.4.

The Oracle Instant Client is a sizeable chunk of the image. Building from
source with `--build-arg WITH_ORACLE=false` drops it along with ext-oci8 and
ext-pdo_oci.

## Faster dumps and restores

The export page grows extra output modes, all of which keep the data out of
PHP's row-by-row loop:

- **mysqldump → zstd** and **pg_dump → zstd** — the native client piped
  straight into `zstd -T0`. An order of magnitude quicker than Adminer's own
  dump on a large database. Each one only appears on its own driver.
- **gzip (pigz)** and **Zstandard (zstd CLI)**, on every driver — Adminer
  generates the dump, the CLI compressor uses all cores instead of PHP's
  single-threaded zlib.

**Fast Restore** in the sidebar takes an uploaded `.sql`, `.sql.gz` or
`.sql.zst` and pipes it into `mysql` or `psql`. The PostgreSQL side runs in a
single transaction with `ON_ERROR_STOP`, so a bad dump rolls back instead of
leaving half a database behind.

The PostgreSQL client comes from PGDG rather than Debian's archive, because
pg_dump 17 refuses to dump a PostgreSQL 18 server.

## Tuning

The entrypoint reads the container's CPU allowance — cgroup quota included,
so `--cpus=2` is respected rather than the host's core count — and sizes the
worker pools from it. Everything it picks can be overridden:

| Variable | Default | Applies to |
|---|---|---|
| `PHP_FPM_PM` | `dynamic` | nginx, fpm |
| `PHP_FPM_MAX_CHILDREN` | `cpus × 4`, clamped 8–64 | nginx, fpm |
| `PHP_FPM_START_SERVERS` | `max_children / 4` | nginx, fpm |
| `PHP_FPM_MIN_SPARE_SERVERS` | `max_children / 8` | nginx, fpm |
| `PHP_FPM_MAX_SPARE_SERVERS` | `max_children / 2` | nginx, fpm |
| `PHP_FPM_MAX_REQUESTS` | `1000` | nginx, fpm |
| `PHP_CLI_SERVER_WORKERS` | `cpus`, clamped 4–32 | standalone |
| `ADMINER_PORT` | `8080` | standalone |
| `MONGO_AUTH_SOURCE` | — | the MongoDB driver |

## What's inside

- **Debian trixie** on `php:8.4-fpm-trixie` / `php:8.4-cli-trixie`.
- Adminer 6 (the full build), plugins for parallel dumps, fast restore and a
  dark-mode switcher.
- Client binaries: `mysql`, `mysqldump`, `psql`, `pg_dump`, `sqlite3`,
  `zstd`, `pigz`.
- Runs as **www-data**, `dumb-init` as PID 1, healthcheck on every flavor.
- `server_tokens off`, `X-Robots-Tag` on every response, `open_basedir`
  confined to what the app and the clients actually touch.
- nginx serves HTML, CSS and JS with **gzip and brotli**. The export content
  types are in neither filter's list, so a dump is never compressed twice or
  buffered on its way out.

## Disclaimer

I created this Docker image for my personal workflow, so it hasn't been
tested in use cases beyond those I regularly work with.

## Source

[github.com/vdementev/adminer-docker](https://github.com/vdementev/adminer-docker) · MIT license
