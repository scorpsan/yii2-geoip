# yii2-geoip

An extension that allows you to obtain the visitor's IP address and location information from any IP address. Uses http://sypexgeo.net/ for online and http://www.ip2location.com/ library for offline data retrieval.

## Installation

The preferred way to install this extension through [composer](http://getcomposer.org/download/).

You can set the console

```
$ composer require "scorpsan/yii2-geoip"
```

or add

```
"scorpsan/yii2-geoip": "*"
```

in ```require``` section in `composer.json` file.

## Using

Once the extension is installed, simply use it in your code by  :

Add following code to your configuration file of application:

```php
...
'components' => [
    ...
    'geoIp' => [
        'class' => 'scorpsan\geoip\GeoIp',
        'externalIp' => YII_ENV_DEV,
    ],
    ...
],
...
```

Get information from Ip User:
Online

```php
var_dump(Yii::$app->geoIp->info);
```

Offline

```php
var_dump(Yii::$app->geoIp->infoDb);
```

Get information from Select Ip:
Online

```php
var_dump(Yii::$app->geoIp->getInfo('255.255.255.255'));
```

Offline
```php
var_dump(Yii::$app->geoIp->getInfoDb('255.255.255.255'));
```

Get User Ip:

```php
var_dump(Yii::$app->geoIp->ip);
```

## License

yii2-widget-cropbox is released under the BSD 3-Clause License.
