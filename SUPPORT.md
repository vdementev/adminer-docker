# Support and lifecycle

## Tags

Three flavors of the same Adminer build. Pick by how you want it served.

| Tag | What it is |
|---|---|
| `latest`, `nginx` | nginx + php-fpm on `:8080`. The one to use unless you have a reason not to. |
| `standalone` | PHP's built-in server on `:8080`. No nginx, smallest moving parts. |
| `fpm` | Bare php-fpm on `:9000`, for an existing web server in front. |
| `6.0.2-nginx`, `6.0-nginx`, … | The same three flavors, pinned to an Adminer version. |
| `mysql-nginx`, `mysql-standalone`, `mysql-fpm` | Deprecated aliases. |

Version tags are read out of the image *after* it is built and tested, so a tag
can never claim an Adminer version the image does not contain.

Published for `linux/amd64` only — unlike the rest of the family, which is
multi-arch. If you need arm64, build from this repository;
`--build-arg WITH_ORACLE=false` drops the Oracle Instant Client, the heaviest
and least portable piece of the image.

## Deprecated tags

`mysql-nginx`, `mysql-standalone` and `mysql-fpm` date from when these images
carried only the MySQL build of Adminer. They now point at exactly the same
digests as the unprefixed tags and are kept so nothing breaks. They will stop
being published once they stop being pulled; move to `nginx`, `standalone` or
`fpm`.

## What "supported" means

`latest` and the flavor tags are rebuilt on every merge and weekly (Monday,
~03:40 UTC), which is what picks up base-image and PHP security updates. Older
Adminer version tags stay pullable but are frozen — no rebuilds.

Adminer itself is vendored into the image rather than pulled at build time, so
an Adminer release is a commit here (the app plus the compiled driver plugins),
reviewed and merged like any other change.

## Pinning

```yaml
services:
  adminer:
    image: dementev/adminer:6.0-nginx@sha256:...
```

## Exposure

This is a database client with a login form. It is meant to sit behind
authentication you already have — a VPN, a proxy with SSO, an IP allowlist — not
on the public internet. The image sets `X-Robots-Tag`, turns off
`server_tokens`, and restricts `security.limit_extensions`, but none of that is
a substitute for not publishing it.

## Patch cadence

| Trigger | What happens |
|---|---|
| Merge to `main` | All three flavors build, each runs its own suite from `tests.sh`, Trivy gate, publish, sign |
| Weekly cron | Same pipeline, no source change — picks up base-image and package updates |
| Fixable CRITICAL/HIGH CVE | The build fails and nothing is published until it is fixed or explicitly accepted in `.trivyignore` |
| Adminer release | A version-bump commit replacing the vendored app and driver plugins |

## Getting help

Open an issue at
[github.com/vdementev/adminer-docker/issues](https://github.com/vdementev/adminer-docker/issues).
Security reports go through [SECURITY.md](SECURITY.md) instead.
