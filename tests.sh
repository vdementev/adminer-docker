#!/bin/sh
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

cleanup() {
    printf "\n${BOLD}Cleaning up...${NC}\n"
    docker rm -f test-standalone test-nginx test-fpm test-scale > /dev/null 2>&1 || true
}
trap cleanup EXIT

cd "$(dirname "$0")"

# Remove any leftover containers from a previous run
docker rm -f test-standalone test-nginx test-fpm test-scale > /dev/null 2>&1 || true

printf "${BOLD}Building images...${NC}\n"
docker build -q -f Dockerfile.standalone -t adminer-test:standalone .
pass "standalone built"
docker build -q -f Dockerfile.nginx -t adminer-test:nginx .
pass "nginx built"
docker build -q -f Dockerfile.fpm -t adminer-test:fpm .
pass "fpm built"

printf "\n${BOLD}Starting containers...${NC}\n"
docker run -d --name test-standalone -p 18080:8080 adminer-test:standalone > /dev/null
docker run -d --name test-nginx -p 18081:8080 adminer-test:nginx > /dev/null
docker run -d --name test-fpm adminer-test:fpm > /dev/null
sleep 4

# --- standalone ---
printf "\n${BOLD}standalone (php -S :8080)${NC}\n"
check "container running"          'docker ps --format "{{.Names}}" | grep -q test-standalone'
check "runs as www-data"           '[ "$(docker exec test-standalone whoami)" = "www-data" ]'
check "HTTP 200"                   'curl -sf http://127.0.0.1:18080/'
check "dumb-init PID 1"            'docker exec test-standalone cat /proc/1/cmdline | tr "\0" " " | grep -q dumb-init'
check "forks CLI server workers"   '[ "$(docker top test-standalone | grep -c "php -S")" -gt 1 ]'
check "opcache on in CLI SAPI"     'docker exec test-standalone php -r "exit(is_array(opcache_get_status(false)) ? 0 : 1);"'
check_extensions test-standalone
check_clients test-standalone
check_drivers http://127.0.0.1:18080/
check "cleanup: no php.ini-dev"    '! docker exec test-standalone test -f /usr/local/etc/php/php.ini-development'
check "cleanup: no ipe binary"     '! docker exec test-standalone test -f /usr/local/bin/install-php-extensions'

# --- nginx ---
printf "\n${BOLD}nginx (php-fpm + nginx :8080)${NC}\n"
check "container running"          'docker ps --format "{{.Names}}" | grep -q test-nginx'
check "HTTP 200"                   'curl -sf http://127.0.0.1:18081/'
check "healthcheck ping"           'docker exec test-nginx curl -sf http://127.0.0.1:8080/ping.php | grep -q pong'
check "server_tokens off"          '! curl -sI http://127.0.0.1:18081/ 2>&1 | grep -q "nginx/"'
check "X-Robots-Tag header"        'curl -sI http://127.0.0.1:18081/robots.txt 2>&1 | grep -qi X-Robots-Tag'
check "dumb-init PID 1"            'docker exec test-nginx cat /proc/1/cmdline | tr "\0" " " | grep -q dumb-init'
check "fpm pool scaled to CPUs"    '[ "$(docker top test-nginx | grep -c "pool www")" -gt 1 ]'
check "gzip on html"               'curl -s -H "Accept-Encoding: gzip" -D - -o /dev/null http://127.0.0.1:18081/ | grep -qi "content-encoding: gzip"'
check "brotli on html"             'curl -s -H "Accept-Encoding: br" -D - -o /dev/null http://127.0.0.1:18081/ | grep -qi "content-encoding: br"'
check "Vary: Accept-Encoding"      'curl -s -H "Accept-Encoding: br" -D - -o /dev/null http://127.0.0.1:18081/ | grep -qi "vary:.*accept-encoding"'
check_extensions test-nginx
check_clients test-nginx
check_drivers http://127.0.0.1:18081/
check "cleanup: no php.ini-dev"    '! docker exec test-nginx test -f /usr/local/etc/php/php.ini-development'
check "cleanup: no ipe binary"     '! docker exec test-nginx test -f /usr/local/bin/install-php-extensions'

# --- fpm ---
printf "\n${BOLD}fpm (php-fpm :9000)${NC}\n"
check "container running"          'docker ps --format "{{.Names}}" | grep -q test-fpm'
check "runs as www-data"           '[ "$(docker exec test-fpm whoami)" = "www-data" ]'
check "php-fpm on :9000"           'docker exec test-fpm php -r "@fsockopen(\"127.0.0.1\",9000) or exit(1);"'
check "dumb-init PID 1"            'docker exec test-fpm cat /proc/1/cmdline | tr "\0" " " | grep -q dumb-init'
check_extensions test-fpm
check_clients test-fpm
check "cleanup: no php.ini-dev"    '! docker exec test-fpm test -f /usr/local/etc/php/php.ini-development'
check "cleanup: no ipe binary"     '! docker exec test-fpm test -f /usr/local/bin/install-php-extensions'

# --- pool sizing knobs ---
printf "\n${BOLD}pool sizing${NC}\n"
docker run -d --name test-scale -e PHP_FPM_PM=static -e PHP_FPM_MAX_CHILDREN=3 adminer-test:fpm > /dev/null
sleep 3
check "PHP_FPM_MAX_CHILDREN honored" '[ "$(docker top test-scale | grep -c "pool www")" -eq 3 ]'
docker rm -f test-scale > /dev/null 2>&1 || true

# Results
printf "\n${BOLD}Results: ${GREEN}%d passed${NC}" "$PASS"
if [ "$FAIL" -gt 0 ]; then
    printf ", ${RED}%d failed${NC}\n" "$FAIL"
    exit 1
else
    printf ", %d failed${NC}\n" "$FAIL"
fi
