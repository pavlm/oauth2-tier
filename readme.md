oauth2-tier
===

Proxy server for secure access to http backend or some contents, with authorization on external OAuth2 servers. 
It's similar to oauth2-proxy project.

## Features

* Several OAuth providers simultaneously can be used.
* Doesn't require provider to support OIDC as in oauth2-proxy. User email field can be specified in provider configuration.
* Uses Amphp v3 asynchronous framework, so quite performant.
* Configuration via env file.

* Multiple backend handlers can be configured at the same time:
    * Http upstream server
    * Server file browser with directory navigation and file viewer.
    * Http file server
    * PHP runner backend

* Access control is configurable.
* Some well known providers are available (only three for now).
* Typical OAuth provider may be configured without coding.
* Multiple host names can be used when it works behind trusted proxy.

## Configuration

Copy `.env.example` file to `.env` and change variable values.


```
OA2T_HTTP_PORT=8089                           # external http port of container
OA2T_HTTP_ADDRESS=0.0.0.0:${OA2T_HTTP_PORT}   # server socket bind address
OA2T_HTTP_ROOT_URL=http://192.168.1.10:8089/  # url with default hostname. Url path can also be specified. So all urls will be mounted to that path.
OA2T_POST_LOGIN_URL=                          # if empty then url dynamically detected

# multiple backend types can be configured
#OA2T_LOCATIONS='[["/", "proxy", "http://172.17.0.1:80"]]'  # secured http backend
#OA2T_LOCATIONS='[["/", "browser", "/app"]]'                # server file browser backend. directory navigation and file viewer.
#OA2T_LOCATIONS='[["/", "statics", "/app"]]'                # http file server backend
#OA2T_LOCATIONS='[["/index.php", "php", "/index.php"]]'     # php runner backend

OA2T_ACCESS_CONTROL='{"/": true}'             # access control rules. by default authorization is required, but some locations can be configured for public access
OA2T_EMAIL_DOMAINS=*                          # allowed user email domains, comma separated values
OA2T_COOKIE_SECURE=false                      # cookie secure flag 
OA2T_COOKIE_EXPIRE=PT33H                      # cookie and session duration, in PHP DateInterval format
OA2T_PROVIDERS='["google", "yandex", "keycloak"]' # configured OAuth providers. Provider identifiers in json array
OA2T_TRUSTED_FORWARDERS=127.0.0.0/8,172.16.0.0/12,192.168.0.0/16 # accept forwarded-x http headers from these proxies
OA2T_ACCESS_LOG=./access.log                  # access log file name
OA2T_APP_LOG=php://stdout                     # application log file name

## OAuth2 providers configuration

OA2T_PROVIDERS_GOOGLE_CLIENT_ID=
OA2T_PROVIDERS_GOOGLE_CLIENT_SECRET=

OA2T_PROVIDERS_KEYCLOAK_CLIENT_ID=
OA2T_PROVIDERS_KEYCLOAK_CLIENT_SECRET=
OA2T_PROVIDERS_KEYCLOAK_REALM_URL=http://keycloak-host/realms/test
OA2T_PROVIDERS_KEYCLOAK_SCOPES='["openid", "profile", "email"]'

OA2T_PROVIDERS_YANDEX_CLIENT_ID=
OA2T_PROVIDERS_YANDEX_CLIENT_SECRET=

```


## Building and start

```
make build_image && make up
```

## OAuth2 clients setup

* For each required OAuth provider register client with redirect url.  
Redirect page address is `https://<oauth2-tier-host>/oauth2/callback/<provider>`, 
where `<provider>` is an identifier of provider in `providers.yaml` file.  
Note: some providers support insecure http protocol and LAN host ip addresses.
* Edit `OA2T_PROVIDERS`, `OA2T_PROVIDERS_*_CLIENT_ID`, `OA2T_PROVIDERS_*_CLIENT_SECRET` env variables.
* Restart application: `make reup`.


## Demo

Demo application is available as preconfigured docker compose services. Authorization goes with Keycloak provider.

1. Run demo: `make build_image; cd docker/demo; docker compose up -d`
2. Open proxy page [http://192.168.234.2:8191/](http://192.168.234.2:8191/)
3. Error page (status 401) must be shown.
4. Follow the `Login` link to http://192.168.234.2:8191/oauth2/sign_in page.
5. Try to login with keycloak provider using `test` / `test` credentials.
6. Backend resources will be available after successful login.


## HTTPS setup

Server doesn't support https by itself, but it can works behind https reverse proxy. Example of nginx proxy is below.

```
server {
    listen 80;
    listen 443 ssl;
    server_name example.org;
    ssl_certificate    example.org.crt;
    ssl_certificate_key example.org.key;
    location / {
        proxy_pass http://127.0.0.1:8089; # oauth2-tier server
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
        proxy_set_header X-Forwarded-Host $host:$server_port;
        proxy_set_header connection keep-alive;
    }
}

```

## Server endpoints

Following url endpoints are available.

```
/oauth2/sign_in                # Sign in page with provider selection.
/oauth2/sign_out               # Sign out
/oauth2/start                  # Page starts OAuth2 sequence with redirect to provider
/oauth2/callback/{provider}    # OAuth2 redirect page. For google it will be https://<your-host>/oauth2/callback/google
/ping                          # Health check page. Prints 'pong'.
```
All other urls are handled by configured backends.
