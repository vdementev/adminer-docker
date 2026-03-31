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

has_ext() { docker exec "$1" php -m | grep -qx "$2"; }

cleanup() {
    printf "\n${BOLD}Cleaning up...${NC}\n"
    docker rm -f test-standalone test-nginx test-fcgi > /dev/null 2>&1 || true
}
trap cleanup EXIT

cd "$(dirname "$0")"

# Remove any leftover containers from a previous run
docker rm -f test-standalone test-nginx test-fcgi > /dev/null 2>&1 || true

printf "${BOLD}Building images...${NC}\n"
docker build -q -f Dockerfile.mysql-standalone -t adminer-test:standalone .
pass "standalone built"
docker build -q -f Dockerfile.mysql-nginx -t adminer-test:nginx .
pass "nginx built"
docker build -q -f Dockerfile.mysql-fcgi -t adminer-test:fcgi .
pass "fcgi built"

printf "\n${BOLD}Starting containers...${NC}\n"
docker run -d --name test-standalone -p 18080:8080 adminer-test:standalone > /dev/null
docker run -d --name test-nginx -p 18081:8080 adminer-test:nginx > /dev/null
docker run -d --name test-fcgi adminer-test:fcgi > /dev/null
sleep 4

# --- standalone ---
printf "\n${BOLD}standalone (php -S :8080)${NC}\n"
check "container running"          'docker ps --format "{{.Names}}" | grep -q test-standalone'
check "runs as www-data"           '[ "$(docker exec test-standalone whoami)" = "www-data" ]'
check "HTTP 200"                   'curl -sf http://127.0.0.1:18080/'
check "dumb-init PID 1"           'docker exec test-standalone cat /proc/1/cmdline | tr "\0" " " | grep -q dumb-init'
check "ext pdo_mysql"             'has_ext test-standalone pdo_mysql'
check "ext zip"                   'has_ext test-standalone zip'
check "ext zstd"                  'has_ext test-standalone zstd'
check "cleanup: no php.ini-dev"   '! docker exec test-standalone test -f /usr/local/etc/php/php.ini-development'
check "cleanup: no ipe binary"    '! docker exec test-standalone test -f /usr/local/bin/install-php-extensions'

# --- nginx ---
printf "\n${BOLD}nginx (php-fpm + nginx :8080)${NC}\n"
check "container running"          'docker ps --format "{{.Names}}" | grep -q test-nginx'
check "HTTP 200"                   'curl -sf http://127.0.0.1:18081/'
check "healthcheck ping"          'docker exec test-nginx curl -sf http://127.0.0.1:8080/ping.php | grep -q pong'
check "server_tokens off"         '! curl -sI http://127.0.0.1:18081/ 2>&1 | grep -q "nginx/"'
check "X-Robots-Tag header"       'curl -sI http://127.0.0.1:18081/robots.txt 2>&1 | grep -qi X-Robots-Tag'
check "dumb-init PID 1"           'docker exec test-nginx cat /proc/1/cmdline | tr "\0" " " | grep -q dumb-init'
check "ext pdo_mysql"             'has_ext test-nginx pdo_mysql'
check "ext pdo_pgsql"             'has_ext test-nginx pdo_pgsql'
check "ext mongodb"               'has_ext test-nginx mongodb'
check "ext zip"                   'has_ext test-nginx zip'
check "ext zstd"                  'has_ext test-nginx zstd'
check "cleanup: no php.ini-dev"   '! docker exec test-nginx test -f /usr/local/etc/php/php.ini-development'
check "cleanup: no ipe binary"    '! docker exec test-nginx test -f /usr/local/bin/install-php-extensions'

# --- fcgi ---
printf "\n${BOLD}fcgi (php-fpm :9000)${NC}\n"
check "container running"          'docker ps --format "{{.Names}}" | grep -q test-fcgi'
check "runs as www-data"           '[ "$(docker exec test-fcgi whoami)" = "www-data" ]'
check "php-fpm on :9000"          'docker exec test-fcgi php -r "@fsockopen(\"127.0.0.1\",9000) or exit(1);"'
check "dumb-init PID 1"           'docker exec test-fcgi cat /proc/1/cmdline | tr "\0" " " | grep -q dumb-init'
check "ext pdo_mysql"             'has_ext test-fcgi pdo_mysql'
check "ext pdo_pgsql"             'has_ext test-fcgi pdo_pgsql'
check "ext mongodb"               'has_ext test-fcgi mongodb'
check "ext zip"                   'has_ext test-fcgi zip'
check "ext zstd"                  'has_ext test-fcgi zstd'
check "cleanup: no php.ini-dev"   '! docker exec test-fcgi test -f /usr/local/etc/php/php.ini-development'
check "cleanup: no ipe binary"    '! docker exec test-fcgi test -f /usr/local/bin/install-php-extensions'

# Results
printf "\n${BOLD}Results: ${GREEN}%d passed${NC}" "$PASS"
if [ "$FAIL" -gt 0 ]; then
    printf ", ${RED}%d failed${NC}\n" "$FAIL"
    exit 1
else
    printf ", %d failed${NC}\n" "$FAIL"
fi
