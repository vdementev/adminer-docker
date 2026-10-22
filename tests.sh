#!/bin/sh
# Smoke tests for the three adminer image flavors.
#
#   ./tests.sh                                            # builds all three
#                                                          # flavors in both driver
#                                                          # sets, tests all of them
#   IMAGE=<ref> FLAVOR=<nginx|standalone|fpm> ./tests.sh  # tests one already-built
#                                                          # image, builds nothing (this
#                                                          # is how CI calls it, via the
#                                                          # reusable workflow's
#                                                          # test-command input)
#   DRIVERS=mysql                                         # with IMAGE: the image is a
#                                                          # MySQL-only build
set -eu

GREEN='\033[0;32m'
RED='\033[0;31m'
BOLD='\033[1m'
NC='\033[0m'

PASS=0
FAIL=0

pass() { PASS=$((PASS + 1)); printf "  ${GREEN}✓${NC} %s\n" "$1"; }
fail() { FAIL=$((FAIL + 1)); printf "  ${RED}✗${NC} %s\n" "$1"; }

check() {
    desc="$1"; shift
    if eval "$@" > /dev/null 2>&1; then
        pass "$desc"
    else
        fail "$desc"
    fi
}

has_ext() { docker exec "$1" php -m | grep -qix "$2"; }
has_bin() { docker exec "$1" test -x "$2"; }

DRIVERS="${DRIVERS:-all}"

# Every database Adminer can reach needs either a PHP extension or, for the
# HTTP/socket drivers, just the vendored driver plugin. MS SQL goes through
# pdo_dblib (FreeTDS); see build/ext.sh for why there is no sqlsrv.
ALL_EXTENSIONS="mysqli pdo_mysql pgsql pdo_pgsql sqlite3 pdo_sqlite pdo_dblib oci8 mongodb zip zstd"
MYSQL_EXTENSIONS="mysqli pdo_mysql zip zstd"
ALL_DRIVERS="MySQL PostgreSQL SQLite MS_SQL Oracle ClickHouse Elasticsearch MongoDB Redis SimpleDB"

check_extensions() {
    if [ "$DRIVERS" = "mysql" ]; then exts=$MYSQL_EXTENSIONS; else exts=$ALL_EXTENSIONS; fi
    for ext in $exts; do
        check "ext $ext" "has_ext $1 $ext"
    done
    # The PHP side has to load cleanly: a missing shared library behind
    # pdo_dblib or oci8 shows up as a startup warning, not as a missing module.
    check "php starts without warnings" '[ -z "$(docker exec '"$1"' php -r "" 2>&1)" ]'
}

check_clients() {
    check "client mysql"      "has_bin $1 /usr/bin/mysql"
    check "client mysqldump"  "has_bin $1 /usr/bin/mysqldump"
    check "client zstd/pigz"  "has_bin $1 /usr/bin/zstd && has_bin $1 /usr/bin/pigz"
    # MySQL 8 accounts default to caching_sha2_password; the client loads it
    # as a plugin, and a missing one only shows up at login.
    check "mysql auth plugin caching_sha2_password" \
        "docker exec $1 test -f /usr/lib/mariadb/plugin/caching_sha2_password.so && docker exec $1 grep -q plugin-dir=/usr/lib/mariadb/plugin /etc/my.cnf"
    [ "$DRIVERS" = "mysql" ] && return
    check "client psql"       "has_bin $1 /usr/bin/psql"
    check "client pg_dump"    "has_bin $1 /usr/bin/pg_dump"
    check "client sqlite3"    "has_bin $1 /usr/bin/sqlite3"
    check "pg_dump allowed by open_basedir" \
        'docker exec '"$1"' php -d open_basedir=/app:/tmp:/usr/bin:/usr/libexec/postgresql18 -r "exit(is_executable(\"/usr/bin/pg_dump\") ? 0 : 1);"'
}

# The login page lists one <option> per registered driver, so a single fetch
# proves the compiled-in drivers AND the vendored driver plugins loaded. The
# MySQL-only build must offer MySQL and nothing else.
check_drivers() {
    body=$(curl -s "$1")
    for driver in $ALL_DRIVERS; do
        driver=$(printf '%s' "$driver" | tr _ ' ')
        if [ "$DRIVERS" = "mysql" ] && [ "$driver" != "MySQL" ]; then
            if printf '%s' "$body" | grep -q ">$driver<"; then
                fail "driver $driver not offered"
            else
                pass "driver $driver not offered"
            fi
        elif printf '%s' "$body" | grep -q "$driver"; then
            pass "driver $driver offered"
        else
            fail "driver $driver offered"
        fi
    done
}

check_runtime() {
    check "tini PID 1"                 'docker exec '"$1"' cat /proc/1/cmdline | tr "\0" " " | grep -q tini'
    check "no build toolchain"         '! docker exec '"$1"' sh -c "command -v gcc || command -v phpize"'
    check "no stray adminer-mysql.php" '! docker exec '"$1"' test -e /app/adminer-mysql.php'
}

# Every flavor ships a Docker HEALTHCHECK; wait on it instead of a fixed
# sleep so a slow-starting (or CPU-starved CI) container doesn't produce
# spurious failures.
wait_healthy() {
    n=0
    while [ "$n" -lt 30 ]; do
        [ "$(docker inspect -f '{{.State.Health.Status}}' "$1" 2>/dev/null)" = "healthy" ] && return 0
        n=$((n + 1))
        sleep 1
    done
    return 1
}

STARTED=""
start() {
    name="$1"; shift
    docker rm -f "$name" > /dev/null 2>&1 || true
    docker run -d --name "$name" "$@" > /dev/null
    STARTED="$STARTED $name"
    # If it never goes healthy the checks below will report the real failure.
    wait_healthy "$name" || true
}

cleanup() {
    printf "\n${BOLD}Cleaning up...${NC}\n"
    if [ -n "$STARTED" ]; then
        # shellcheck disable=SC2086 # STARTED is a space-separated list of names
        docker rm -f $STARTED > /dev/null 2>&1 || true
    fi
}
trap cleanup EXIT

cd "$(dirname "$0")"

test_standalone() {
    image="$1"
    name="test-standalone"
    printf "\n${BOLD}standalone (php -S :8080)${NC}\n"
    # Ephemeral host port: a fixed one collides with a parallel CI matrix job.
    start "$name" -p 127.0.0.1::8080 "$image"
    base="http://$(docker port "$name" 8080/tcp | head -1)"

    check "container running"          '[ "$(docker inspect -f "{{.State.Running}}" '"$name"')" = "true" ]'
    check "runs as www-data"           '[ "$(docker exec '"$name"' whoami)" = "www-data" ]'
    check "HTTP 200"                   'curl -sf '"$base"'/'
    check "forks CLI server workers"   '[ "$(docker top '"$name"' | grep -c "php -S")" -gt 1 ]'
    check "opcache on in CLI SAPI"     'docker exec '"$name"' php -r "exit(is_array(opcache_get_status(false)) ? 0 : 1);"'
    check_extensions "$name"
    check_clients "$name"
    check_drivers "$base/"
    check_runtime "$name"
}

test_nginx() {
    image="$1"
    name="test-nginx"
    printf "\n${BOLD}nginx (php-fpm + nginx :8080)${NC}\n"
    start "$name" -p 127.0.0.1::8080 "$image"
    base="http://$(docker port "$name" 8080/tcp | head -1)"

    check "container running"          '[ "$(docker inspect -f "{{.State.Running}}" '"$name"')" = "true" ]'
    check "HTTP 200"                   'curl -sf '"$base"'/'
    check "healthcheck ping"           'docker exec '"$name"' curl -sf http://127.0.0.1:8080/ping.php | grep -q pong'
    check "server_tokens off"          '! curl -sI '"$base"'/ 2>&1 | grep -q "nginx/"'
    check "X-Robots-Tag header"        'curl -sI '"$base"'/robots.txt 2>&1 | grep -qi X-Robots-Tag'
    check "fpm pool scaled to CPUs"    '[ "$(docker top '"$name"' | grep -c "pool www")" -gt 1 ]'
    check "gzip on html"               'curl -s -H "Accept-Encoding: gzip" -D - -o /dev/null '"$base"'/ | grep -qi "content-encoding: gzip"'
    check "Vary: Accept-Encoding"      'curl -s -H "Accept-Encoding: gzip" -D - -o /dev/null '"$base"'/ | grep -qi "vary:.*accept-encoding"'
    check_extensions "$name"
    check_clients "$name"
    check_drivers "$base/"
    check_runtime "$name"
}

test_fpm() {
    image="$1"
    name="test-fpm"
    printf "\n${BOLD}fpm (php-fpm :9000)${NC}\n"
    start "$name" "$image"

    check "container running"          '[ "$(docker inspect -f "{{.State.Running}}" '"$name"')" = "true" ]'
    check "runs as www-data"           '[ "$(docker exec '"$name"' whoami)" = "www-data" ]'
    check "php-fpm on :9000"           'docker exec '"$name"' php -r "@fsockopen(\"127.0.0.1\",9000) or exit(1);"'
    check_extensions "$name"
    check_clients "$name"
    check_runtime "$name"

    printf "\n${BOLD}pool sizing${NC}\n"
    scale="test-scale"
    start "$scale" -e PHP_FPM_PM=static -e PHP_FPM_MAX_CHILDREN=3 "$image"
    check "PHP_FPM_MAX_CHILDREN honored" '[ "$(docker top '"$scale"' | grep -c "pool www")" -eq 3 ]'
}

IMAGE="${IMAGE:-}"
FLAVOR="${FLAVOR:-}"

if [ -n "$IMAGE" ]; then
    case "$FLAVOR" in
        standalone) test_standalone "$IMAGE" ;;
        nginx)      test_nginx "$IMAGE" ;;
        fpm)        test_fpm "$IMAGE" ;;
        *)
            echo "FLAVOR must be one of: standalone, nginx, fpm (got '${FLAVOR:-<unset>}')" >&2
            exit 1
            ;;
    esac
else
    printf "${BOLD}Building images...${NC}\n"
    for drivers in all mysql; do
        for flavor in standalone nginx fpm; do
            docker build -q --build-arg DRIVERS="$drivers" -f "Dockerfile.$flavor" \
                -t "adminer-test:$drivers-$flavor" .
            pass "$drivers-$flavor built"
        done
    done

    for DRIVERS in all mysql; do
        printf "\n${BOLD}== DRIVERS=%s${NC}\n" "$DRIVERS"
        test_standalone "adminer-test:$DRIVERS-standalone"
        test_nginx "adminer-test:$DRIVERS-nginx"
        test_fpm "adminer-test:$DRIVERS-fpm"
    done
fi

# Results
printf "\n${BOLD}Results: ${GREEN}%d passed${NC}" "$PASS"
if [ "$FAIL" -gt 0 ]; then
    printf ", ${RED}%d failed${NC}\n" "$FAIL"
    exit 1
else
    printf ", %d failed${NC}\n" "$FAIL"
fi
