# Lyra URL

This library can be used to work with URLs in a PHP application. It
is very simple and has limited functionality but helps you with repetitive task with the URLs.

## Requirements

The following php extension are required to make this library work:

```lang=conf
php-curl
php-mbstring
```

## Configuration

This application accept proxy settings for configuration. Both Download and Check command can be used with the proxy option. By default they don't use proxies for queries to the given server. The configuration should be an array with the following parameters, the values with star are mandatory:

```lang=yaml
proxy_setting:
    host: your-hostname *
    port: your-port *
    auth: your-auth-settings (username:password)
    type: http
```

For the proxy type you can use one of the following:

```lang=conf
http
http1
socks4
socks4a
socks5
socks5h
```

## Usage

This library can be used like any other lyra library. Add the it to your `composer.json`

```lang=bash
composer require rzuw/lyra-url
composer update
```

Then create an instance of this library with:

```lang=php
$url = new URL($config, $logger);
$res = $url->check("google.com"); // Checks if the google is available:)
```

## Testing

To test this library you need `phpunit` installed on your machine. All test files are located in `tests` directory.

```lang=bash
cd your-local-copy
export PROXY_HOST=""
export PROXY_PORT=""
export PROXY_TYPE=""
export PROXY_AUTH=""
phpunit
```

## Contribution

This package is mirrored on Github, pull request are accepted, but can be only manually merged with the code base. Maybe with this Phabricator [task](https://secure.phabricator.com/T10538) implemented, we are going to be able to merge your commits automatically.

## Change log

See CHANGELOG file.

## License

See LICENSE file.

## TODO

- Write test cases for the download, headers and info
- Add more examples of usage to the Documentation