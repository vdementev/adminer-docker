#!/bin/sh
# Shared entrypoint for all three image flavors.
#
#   docker-entrypoint.sh nginx        php-fpm behind nginx on :8080
#   docker-entrypoint.sh fpm          bare php-fpm on :9000
#   docker-entrypoint.sh standalone   php -S on :8080
#   docker-entrypoint.sh <anything>   exec'd verbatim (so `docker run … php -v` works)
#
# Its job before handing over is sizing the worker pools to the CPU the
# container actually got. Both php-fpm and the CLI server are process-per-
# request: a pool of 2 on a 16-core host serialises everything, a pool of 64
# in a 0.5-CPU container just thrashes. php-fpm.conf reads the PHP_FPM_*
# values through ${ENV} interpolation, `php -S` reads PHP_CLI_SERVER_WORKERS.
# Anything already set in the environment wins — we only fill in blanks.
set -eu

# Online CPUs, capped by the cgroup CPU quota when one is set (nproc reports
# the host's core count inside a quota-limited container).
cpu_count() {
    _cpus=$(getconf _NPROCESSORS_ONLN 2>/dev/null || echo 1)
    _quota=""

    if [ -r /sys/fs/cgroup/cpu.max ]; then                        # cgroup v2
        read -r _q _p < /sys/fs/cgroup/cpu.max || true
        if [ "${_q:-max}" != "max" ] && [ "${_p:-0}" -gt 0 ]; then
            _quota=$(( (_q + _p - 1) / _p ))
        fi
    elif [ -r /sys/fs/cgroup/cpu/cpu.cfs_quota_us ]; then         # cgroup v1
        _q=$(cat /sys/fs/cgroup/cpu/cpu.cfs_quota_us)
        _p=$(cat /sys/fs/cgroup/cpu/cpu.cfs_period_us)
        if [ "$_q" -gt 0 ] && [ "$_p" -gt 0 ]; then
            _quota=$(( (_q + _p - 1) / _p ))
        fi
    fi

    if [ -n "$_quota" ] && [ "$_quota" -ge 1 ] && [ "$_quota" -lt "$_cpus" ]; then
        _cpus=$_quota
    fi
    echo "$_cpus"
}

clamp() {
    if [ "$1" -lt "$2" ]; then echo "$2"; elif [ "$1" -gt "$3" ]; then echo "$3"; else echo "$1"; fi
}

CPUS=$(cpu_count)

# php-fpm children are the unit of concurrency. Adminer spends most of a
# request waiting on the database, so oversubscribing cores pays off; the
# ceiling keeps memory_limit * children from running the container out of RAM.
: "${PHP_FPM_PM:=dynamic}"
: "${PHP_FPM_MAX_CHILDREN:=$(clamp $((CPUS * 4)) 8 64)}"
: "${PHP_FPM_START_SERVERS:=$(clamp $((PHP_FPM_MAX_CHILDREN / 4)) 2 16)}"
: "${PHP_FPM_MIN_SPARE_SERVERS:=$(clamp $((PHP_FPM_MAX_CHILDREN / 8)) 1 8)}"
: "${PHP_FPM_MAX_SPARE_SERVERS:=$(clamp $((PHP_FPM_MAX_CHILDREN / 2)) 2 32)}"
: "${PHP_FPM_MAX_REQUESTS:=1000}"
: "${PHP_FPM_PROCESS_IDLE_TIMEOUT:=30s}"

# php -S is single-process unless told otherwise: without this a running dump
# blocks every other tab.
: "${PHP_CLI_SERVER_WORKERS:=$(clamp "$CPUS" 4 32)}"

export PHP_FPM_PM PHP_FPM_MAX_CHILDREN PHP_FPM_START_SERVERS \
    PHP_FPM_MIN_SPARE_SERVERS PHP_FPM_MAX_SPARE_SERVERS PHP_FPM_MAX_REQUESTS \
    PHP_FPM_PROCESS_IDLE_TIMEOUT PHP_CLI_SERVER_WORKERS

case "${1:-nginx}" in
    nginx)
        php-fpm -F &
        exec nginx -g 'daemon off;'
        ;;
    fpm)
        exec php-fpm -F
        ;;
    standalone)
        exec php -S "[::]:${ADMINER_PORT:-8080}" -t /app
        ;;
    *)
        exec "$@"
        ;;
esac
