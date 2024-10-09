# Adminer Evo docker image


## Tags

`latest`, `mysql-standalone` - standalone version using php build-in web server on port 8080.
<!-- 
## Environment Variables

All variables are optional.

- `ADMINER_DEFAULT_DB_HOST`
- `ADMINER_DEFAULT_USERNAME`
- `ADMINER_DEFAULT_PASSWORD`
- `ADMINER_DEFAULT_DATABASE`

### Docker run example

```bash
docker run \
  --rm \
  -p 127.0.0.1:8080:8080/tcp \
  --env ADMINER_DEFAULT_DB_HOST=testhost \
  --env ADMINER_DEFAULT_USERNAME=testuser \
  --env ADMINER_DEFAULT_PASSWORD=testpass \
  adminer

```

### Docker compose example

```yaml
services:
  adminer:
    image: dementev/adminer:standalone
    ports:
      - 8080:8080
    environment:
      ADMINER_DEFAULT_DB_HOST: db
      ADMINER_DEFAULT_USERNAME: bestuser
      ADMINER_DEFAULT_PASSWORD: bestpass
      ADMINER_DEFAULT_DATABASE: bestdatabase
``` -->

## Disclaimer

I made this Docker image for myself, so I have not tested it in use cases that I do not use myself.
