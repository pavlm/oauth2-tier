OAuth2 tier
===

Proxy server for secure access to http backend, with authorization on external OAuth2 servers. 
It's similar to oauth2-proxy project.

## Features

* Several OAuth providers simultaneously can be used (only two for now).
* Uses amphp v3 asynchronous framework, so quite performant.
* Configuration via env file.
* Generic OAuth provider may be configured without coding.
* Multiple host names can be used when it works behind trusted proxy.

## Configuration

Copy `.env.example` file to `.env` and change variable values.


```
OA2T_HTTP_PORT=8089                           # external http port of container
OA2T_HTTP_ADDRESS=0.0.0.0:${OA2T_HTTP_PORT}   # server socket bind address
OA2T_HTTP_ROOT_URL=http://192.168.1.10:8089/  #
OA2T_POST_LOGIN_URL=/oauth2/sign_in           #
OA2T_UPSTREAM=http://192.168.1.10:8088/       # secured backend
OA2T_EMAIL_DOMAINS=*                          # allowed user email domains
OA2T_COOKIE_SECURE=false                      # cookie secure flag 
OA2T_COOKIE_EXPIRE=PT33H                      # cookie and session duration, in PHP DateInterval format
OA2T_PROVIDERS='["yandex", "keycloak"]'       # configured OAuth providers

## OAuth2 providers configuration

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

## Demo

Demo application is available as preconfigured docker compose services.

1. Run demo: `make build_image; cd docker/demo; docker compose up -d`
2. Open proxy page http://192.168.234.2:8191/
3. Error page (status 401) must be shown.
4. Follow the `Login` link to http://192.168.234.2:8191/oauth2/sign_in page.
5. Try to login with keycloak provider using `test` / `test` credentials.
6. Backend resources will be available after successful login.
