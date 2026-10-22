#!/bin/sh
# Builds what Adminer needs on top of lotuswebagency/php, run in its ext-builder
# stage. Everything lands under /out laid out like the runtime root filesystem,
# so the runtime stage takes it with a single `COPY --from=ext /out/ /`.
#
#   pdo_dblib  MS SQL through FreeTDS. Bundled with php-src rather than PECL,
#              so it is built from the source tarball of the exact PHP release
#              in the base image, checksum from php.net.
#   oci8       Oracle, against Oracle Instant Client (skipped when the first
#              argument is `false`).
#
# Microsoft's sqlsrv/pdo_sqlsrv are not built: they need msodbcsql18, which is
# linked against OpenSSL 3, and the base carries only OpenSSL 4. Adminer's
# MS SQL driver falls back to pdo_dblib on its own.
set -eux

WITH_ORACLE="${1:-true}"
EXT_DIR=$(php -r 'echo ini_get("extension_dir");')
CONF_DIR=/usr/local/etc/php/conf.d
OUT=/out
IC=/opt/oracle/instantclient

mkdir -p "$OUT$EXT_DIR" "$OUT$CONF_DIR" /src
apk add --no-cache freetds-dev

install_ext() {
    cp "modules/$1.so" "$OUT$EXT_DIR/"
    echo "extension=$1.so" > "$OUT$CONF_DIR/20-$1.ini"
}

php_version=$(php -r 'echo PHP_VERSION;')
php_sha256=$(curl -fsSL "https://www.php.net/releases/index.php?json=1&version=$php_version" \
    | php -r 'foreach (json_decode(stream_get_contents(STDIN), true)["source"] as $s) { if (str_ends_with($s["filename"], ".tar.gz")) { echo $s["sha256"]; } }')
curl -fsSLo /src/php.tar.gz "https://www.php.net/distributions/php-$php_version.tar.gz"
echo "$php_sha256  /src/php.tar.gz" | sha256sum -c -
tar -xzf /src/php.tar.gz -C /src
cd "/src/php-$php_version/ext/pdo_dblib"
phpize
./configure --with-pdo-dblib=/usr
make -j"$(nproc)"
install_ext pdo_dblib

if [ "$WITH_ORACLE" = "true" ]; then
    apk add --no-cache libaio unzip
    # Oracle's "latest" permalinks; the zips carry no checksum of their own.
    for pkg in basic sdk; do
        curl -fsSLo "/src/ic-$pkg.zip" \
            "https://download.oracle.com/otn_software/linux/instantclient/instantclient-$pkg-linuxx64.zip"
        unzip -q -o "/src/ic-$pkg.zip" -d /src/ic
    done
    mkdir -p "$(dirname "$IC")"
    mv /src/ic/instantclient_* "$IC"

    curl -fsSLo /src/oci8.tgz https://pecl.php.net/get/oci8
    mkdir /src/oci8
    tar -xzf /src/oci8.tgz -C /src/oci8 --strip-components=1
    cd /src/oci8
    phpize
    ./configure --with-oci8="instantclient,$IC"
    make -j"$(nproc)"
    install_ext oci8

    # The runtime needs the shared libraries only: no SDK, JDBC or tools.
    rm -rf "$IC/sdk" "$IC"/*.jar "$IC/adrci" "$IC/genezi" "$IC/uidrvci" "$IC"/libocijdbc*
    mkdir -p "$OUT$IC" "$OUT/etc/ld.so.conf.d"
    cp -a "$IC/." "$OUT$IC/"
    echo "$IC" > "$OUT/etc/ld.so.conf.d/oracle-instantclient.conf"
fi
