#!/bin/sh
# Smoke tests for the three adminer image flavors.
#
#   ./tests.sh                                            # builds all three
#                                                          # flavors, tests all of them
#   IMAGE=<ref> FLAVOR=<nginx|standalone|fpm> ./tests.sh  # tests one already-built
#                                                          # image, builds nothing (this
#                                                          # is how CI calls it, via the
#                                                          # reusable workflow's
#                                                          # test-command input)
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

# Every database Adminer can reach needs either a PHP extension or, for the
# HTTP/socket drivers, just the vendored driver plugin.
EXTENSIONS="mysqli pdo_mysql pgsql pdo_pgsql sqlite3 pdo_sqlite pdo_dblib sqlsrv pdo_sqlsrv oci8 mongodb zip zstd"

check_extensions() {
    for ext in $EXTENSIONS; do
        check "ext $ext" "has_ext $1 $ext"
    done
}

check_clients() {
    check "client mysql"      "has_bin $1 /usr/bin/mysql"
    check "client mysqldump"  "has_bin $1 /usr/bin/mysqldump"
    check "client psql"       "has_bin $1 /usr/bin/psql"
    check "client pg_dump"    "has_bin $1 /usr/bin/pg_dump"
    check "client sqlite3"    "has_bin $1 /usr/bin/sqlite3"
    check "client zstd/pigz"  "has_bin $1 /usr/bin/zstd && has_bin $1 /usr/bin/pigz"
}

# The login page lists one <option> per registered driver, so a single fetch
# proves the compiled-in drivers AND the vendored driver plugins loaded.
check_drivers() {
    body=$(curl -s "$1")
    for driver in "MySQL" "PostgreSQL" "SQLite" "MS SQL" "Oracle" "ClickHouse" "Elasticsearch" "MongoDB" "Redis" "SimpleDB"; do
        if printf '%s' "$body" | grep -q "$driver"; then
            pass "driver $driver offered"
        else
            fail "driver $driver offered"
        fi
    done
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
    check "dumb-init PID 1"            'docker exec '"$name"' cat /proc/1/cmdline | tr "\0" " " | grep -q dumb-init'
    check "forks CLI server workers"   '[ "$(docker top '"$name"' | grep -c "php -S")" -gt 1 ]'
    check "opcache on in CLI SAPI"     'docker exec '"$name"' php -r "exit(is_array(opcache_get_status(false)) ? 0 : 1);"'
    check_extensions "$name"
    check_clients "$name"
    check_drivers "$base/"
    check "cleanup: no php.ini-dev"    '! docker exec '"$name"' test -f /usr/local/etc/php/php.ini-development'
    check "cleanup: no ipe binary"     '! docker exec '"$name"' test -f /usr/local/bin/install-php-extensions'
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
    check "dumb-init PID 1"            'docker exec '"$name"' cat /proc/1/cmdline | tr "\0" " " | grep -q dumb-init'
    check "fpm pool scaled to CPUs"    '[ "$(docker top '"$name"' | grep -c "pool www")" -gt 1 ]'
    check "gzip on html"               'curl -s -H "Accept-Encoding: gzip" -D - -o /dev/null '"$base"'/ | grep -qi "content-encoding: gzip"'
    check "brotli on html"             'curl -s -H "Accept-Encoding: br" -D - -o /dev/null '"$base"'/ | grep -qi "content-encoding: br"'
    check "Vary: Accept-Encoding"      'curl -s -H "Accept-Encoding: br" -D - -o /dev/null '"$base"'/ | grep -qi "vary:.*accept-encoding"'
    check_extensions "$name"
    check_clients "$name"
    check_drivers "$base/"
    check "cleanup: no php.ini-dev"    '! docker exec '"$name"' test -f /usr/local/etc/php/php.ini-development'
    check "cleanup: no ipe binary"     '! docker exec '"$name"' test -f /usr/local/bin/install-php-extensions'
}

test_fpm() {
    image="$1"
    name="test-fpm"
    printf "\n${BOLD}fpm (php-fpm :9000)${NC}\n"
    start "$name" "$image"

    check "container running"          '[ "$(docker inspect -f "{{.State.Running}}" '"$name"')" = "true" ]'
    check "runs as www-data"           '[ "$(docker exec '"$name"' whoami)" = "www-data" ]'
    check "php-fpm on :9000"           'docker exec '"$name"' php -r "@fsockopen(\"127.0.0.1\",9000) or exit(1);"'
    check "dumb-init PID 1"            'docker exec '"$name"' cat /proc/1/cmdline | tr "\0" " " | grep -q dumb-init'
    check_extensions "$name"
    check_clients "$name"
    check "cleanup: no php.ini-dev"    '! docker exec '"$name"' test -f /usr/local/etc/php/php.ini-development'
    check "cleanup: no ipe binary"     '! docker exec '"$name"' test -f /usr/local/bin/install-php-extensions'

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
    docker build -q -f Dockerfile.standalone -t adminer-test:standalone .
    pass "standalone built"
    docker build -q -f Dockerfile.nginx -t adminer-test:nginx .
    pass "nginx built"
    docker build -q -f Dockerfile.fpm -t adminer-test:fpm .
    pass "fpm built"

    test_standalone adminer-test:standalone
    test_nginx adminer-test:nginx
    test_fpm adminer-test:fpm
fi

# Results
printf "\n${BOLD}Results: ${GREEN}%d passed${NC}" "$PASS"
if [ "$FAIL" -gt 0 ]; then
    printf ", ${RED}%d failed${NC}\n" "$FAIL"
    exit 1
else
    printf ", %d failed${NC}\n" "$FAIL"
fi
