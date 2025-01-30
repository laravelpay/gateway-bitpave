# BitPave integration for Laravel Pay
This package provides a simple way to integrate BitPave payment gateway with Laravel Pay.

Before you can install this package, make sure you have the composer package `laravelpay/framework` installed. Learn more here https://github.com/laravelpay/gateway-stripe

## Installation
Run this command inside your Laravel application

```
php artisan gateway:install laravelpay/gateway-bitpave
```

## Setup
1. Register an account at [BitPave](https://bitpave.com)
2. Go to [API Settings](https://bitpave.com/developer/api)
3. Copy your Client ID and Secret

```
php artisan gateway:setup bitpave
```
