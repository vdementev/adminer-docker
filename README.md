# adminer

Adminer 6 with every database driver it knows about, native parallel dumps, a
fast restore path, and a worker pool that sizes itself to the container's CPU
allowance.

Published as [`dementev/adminer`](https://hub.docker.com/r/dementev/adminer).

```yaml
services:
  adminer:
    image: dementev/adminer:latest
    ports: ["127.0.0.1:8080:8080"]
```

Bind it to loopback or put it behind the authentication you already have — this
is a database client with a login form, not something to publish.

## Tags

| Tag | What it is |
|---|---|
| `latest`, `nginx` | nginx + php-fpm on `:8080`. Use this one unless you have a reason not to. |
| `standalone` | PHP's built-in server on `:8080`. Fewer moving parts. |
| `fpm` | Bare php-fpm on `:9000`, for an existing web server in front. |
| `6.0.2-nginx`, `6.0-nginx`, … | The same flavors, pinned to an Adminer version. |

Version tags are read out of the image *after* it is built and tested, so a tag
can never claim an Adminer version the image does not contain. Lifecycle and
pinning: [SUPPORT.md](SUPPORT.md).

The old `mysql-nginx` / `mysql-standalone` / `mysql-fpm` tags still point at the
same digests so nothing breaks, but they are aliases now — the images stopped
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

## Tests

`./tests.sh` builds all three flavors and asserts what the image promises: it
runs as `www-data`, dumb-init is PID 1, every database extension and client
binary is present, all ten drivers are offered on the login page, the pool sizes
itself, and the nginx flavor sends its hardening headers and compresses HTML but
not the export streams. CI runs the same script per flavor against the built
image before anything can be published
(`IMAGE=… FLAVOR=nginx ./tests.sh` to test one you already have).

## Security and provenance

Every published digest is built by the shared pipeline in
[vdementev/docker-workflows](https://github.com/vdementev/docker-workflows).
Pull requests build, test and scan without publishing; `main` is
branch-protected, so nothing reaches Docker Hub without a green check behind it.
A Trivy gate fails the build on any *fixable* CRITICAL or HIGH finding, and each
published digest carries an SBOM, max-mode SLSA provenance and a keyless Cosign
signature.

Verify what you pulled:

```sh
cosign verify \
  --certificate-oidc-issuer https://token.actions.githubusercontent.com \
  --certificate-identity-regexp 'github.com/vdementev/' \
  dementev/adminer:latest
```

[SECURITY.md](SECURITY.md) is the reporting channel and the response
targets; [SUPPORT.md](SUPPORT.md) covers tag lifecycle, pinning and
patch cadence.

## Related images

One family, built by the same pipeline, meant to run together — a proxy in
front, an app runtime, a database, and a way into it.

| Image | What it does |
|---|---|
| [`dementev/angie`](https://hub.docker.com/r/dementev/angie) — [source](https://github.com/vdementev/angie-docker) | Public-facing reverse proxy and TLS terminator — Angie, the nginx fork, with brotli, zstd and cache-purge |
| [`dementev/nginx`](https://hub.docker.com/r/dementev/nginx) — [source](https://github.com/vdementev/nginx-docker) | Static sites and SPAs behind that proxy — brotli/zstd siblings, Prometheus stub_status |
| [`dementev/php-fpm-with-ext`](https://hub.docker.com/r/dementev/php-fpm-with-ext) — [source](https://github.com/vdementev/docker-php-fpm-with-ext) | PHP-FPM and CLI, PHP 7.0 → 8.5, with the extensions most projects reach for |
| [`dementev/mysql-percona`](https://hub.docker.com/r/dementev/mysql-percona) — [source](https://github.com/vdementev/mysql-percona-docker) | Percona Server for MySQL 8.4 LTS, XtraBackup built in, no root inside |
| **[`dementev/adminer`](https://hub.docker.com/r/dementev/adminer)** — this image | Adminer 6 with every driver it supports, for reaching any of the above |

## Maintainer

Built and maintained by [Vasilii Dementev](https://vasiliidementev.com) at
[Lotus Web Agency](https://lotuswebagency.com). These images are not a side
project — they are the base layer under the client and product systems we run,
which is why they are gated, tested and signed rather than pushed by hand.

Issues and pull requests:
[github.com/vdementev/adminer-docker](https://github.com/vdementev/adminer-docker).
Need this kind of infrastructure built or maintained for your own stack?
[lotuswebagency.com](https://lotuswebagency.com).

Packaging, the plugins under `app/adminer-plugins/` and the configuration in
this repository are MIT licensed — see [LICENSE](LICENSE). Adminer itself is
vendored from [adminer.org](https://www.adminer.org/) and keeps its own license
(Apache 2.0 or GPL 2), as do the upstream driver plugins.
