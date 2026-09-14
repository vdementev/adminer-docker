# Vendored Adminer driver plugins

Upstream driver plugins for the databases that aren't compiled into
`adminer.php` itself. Same deal as `adminer.php`: vendored wholesale, not
patched. They belong to Adminer (Apache 2.0 / GPL 2), not to this repo.

Current version: **6.0.2** — must match `VERSION` in `app/adminer.php`.

    ver=6.0.2
    for d in clickhouse elastic mongo redis simpledb; do
        curl -sSLf -o "app/adminer-drivers/$d.php" \
            "https://www.adminer.org/static/download/$ver/drivers/$d.php"
    done

Take them from `adminer.org/static/download/<version>/drivers/`, **not** from
`plugins/drivers/` in the git repo — the git files are sources that still call
helpers the compiled `adminer.php` doesn't ship (`get_session()`, for one), so
they fatal at runtime. The downloaded ones are the compiled build, and their
CRC32 matches the checksum table baked into `adminer.php`, which is what stops
Adminer from flagging them as outdated on the databases page.

| File | Databases | Needs |
|---|---|---|
| `clickhouse.php` | ClickHouse | `allow_url_fopen` |
| `elastic.php` | Elasticsearch, OpenSearch | `allow_url_fopen` |
| `mongo.php` | MongoDB | ext-mongodb |
| `redis.php` | Redis, KeyDB | — |
| `simpledb.php` | Amazon SimpleDB | ext-simplexml, `allow_url_fopen` |

Not vendored: `firebird.php` needs ext-interbase, which doesn't exist for PHP
8.4 (only `pdo_firebird` does, and the driver doesn't use PDO). `igdb.php` and
`imap.php` aren't databases.
