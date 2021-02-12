# yii2-geoip

An extension that allows you to obtain the visitor's location information from any IP address. Uses http://sypexgeo.net/ for online and http://www.ip2location.com/ library for offline data retrieval.

Расширение, позволяющее получать информацию о местоположении посетителя с любого IP-адреса. Использует http://sypexgeo.net/ для онлайн и http://www.ip2location.com/ библиотеки для офлайн получения данных.

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
// uncomment next line if you register on sypexgeo.net and paste your key        
//        'keySypex' => 'key-sypexgeo-net-this',
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

## Return Data

Online
```php
     * Returned information by IP address with following paramters:
     * - `ip`               - Visitor IP address, or IP address specified as parameter.
     * - `city`             - Object City information
     * -- [id] => 625144
     * -- [lat] => 53.9
     * -- [lon] => 27.56667
     * -- [name_ru] => Минск
     * -- [name_en] => Minsk
     * -- [name_de] => Minsk
     * -- [name_fr] => Minsk
     * -- [name_it] => Minsk
     * -- [name_es] => Minsk
     * -- [name_pt] => Minsk
     * -- [okato] => 5000000000
     * -- [vk] => 282
     * -- [population] => 1742124
     * - `region`           - Object Region information
     * -- [id] => 625143
     * -- [lat] => 53.9
     * -- [lon] => 27.57
     * -- [name_ru] => Минск
     * -- [name_en] => Horad Minsk
     * -- [name_de] => Minsk
     * -- [name_fr] => Minsk
     * -- [name_it] => Minsk
     * -- [name_es] => Minsk
     * -- [name_pt] => Minsk
     * -- [iso] => BY-HM
     * -- [timezone] => Europe/Minsk
     * -- [okato] => 5
     * -- [auto] => 7
     * -- [vk] => 0
     * -- [utc] => 3
     * - `country`          - Object Country information
     * -- [id] => 36
     * -- [iso] => BY
     * -- [continent] => EU
     * -- [lat] => 53
     * -- [lon] => 28
     * -- [name_ru] => Беларусь
     * -- [name_en] => Belarus
     * -- [name_de] => Weißrussland
     * -- [name_fr] => Biélorussie
     * -- [name_it] => Bielorussia
     * -- [name_es] => Bielorrusia
     * -- [name_pt] => Bielorrússia
     * -- [timezone] => Europe/Minsk
     * -- [area] => 207600
     * -- [population] => 9685000
     * -- [capital_id] => 625144
     * -- [capital_ru] => Минск
     * -- [capital_en] => Minsk
     * -- [cur_code] => BYR
     * -- [phone] => 375
     * -- [neighbours] => PL,LT,UA,RU,LV
     * -- [vk] => 3
     * -- [utc] => 3
     * - `error`            - Error data.
     * - `request`          - Request code.
     * - `created`          - Date created info in database.
     * - `timestamp`        - Timestamp request.
     *
     * @return array|false
     */
```

Offline
```php
     * Returned information by IP address with following paramters:
     * - `ipAddress`       - Visitor IP address, or IP address specified as parameter.
     * - `countryName`     - Name Country in English.
     * - `countryCode`     - Two-letter ISO 3166-1 alpha-2 country code.
     *
     * @return array|false
     */
```

## Usage Session for minimize requests to Sypexgeo

PHP for controller (for examle)

```php
/** Get User Location */
        if (isset($session['_location'])) {
			$app->params['userCountry'] = $session['_location']['userCountry'];
			$app->params['userCountryCode'] = $session['_location']['userCountryCode'];
			$app->params['userCountryRegion'] = $session['_location']['userCountryRegion'];
			$app->params['userCountryCity'] = $session['_location']['userCountryCity'];
			$app->params['userPhoneCode'] = $session['_location']['userPhoneCode'];
		} else {
			$geoip = $app->geoIp->info;
			if (isset($geoip['ip'])) {
				$name = 'name_' . $app->language;
				if (isset($geoip['country'][$name]))
					$app->params['userCountry'] = $geoip['country'][$name];
				else
					$app->params['userCountry'] = $geoip['country']['name_en'];
				$app->params['userCountryCode'] = mb_strtolower($geoip['country']['iso'], 'UTF-8');
				if (isset($geoip['region'][$name]))
					$app->params['userCountryRegion'] = $geoip['region'][$name];
				else
					$app->params['userCountryRegion'] = $geoip['region']['name_en'];
				if (isset($geoip['city'][$name]))
					$app->params['userCountryCity'] = $geoip['city'][$name];
				else
					$app->params['userCountryCity'] = $geoip['city']['name_en'];
				if (isset($geoip['country']['phone']))
					$app->params['userPhoneCode'] = $geoip['country']['phone'];
			} else {
				$geoip = $app->geoIp->infoDb;
				if (isset($geoip['ipAddress'])) {
					$app->params['userCountry'] = $geoip['countryName'];
					$app->params['userCountryCode'] = mb_strtolower($geoip['countryCode'], 'UTF-8');
				}
			}
			$session['_location'] = [
				'userCountry' => $app->params['userCountry'],
				'userCountryCode' => $app->params['userCountryCode'],
				'userCountryRegion' => $app->params['userCountryRegion'],
				'userCountryCity' => $app->params['userCountryCity'],
				'userPhoneCode' => $app->params['userPhoneCode'],
			];
		}
```

params.php

```php
return [
    ...
    'userCountry' => 'World',
    'userCountryCode' => 'wl',
    'userCountryRegion' => '',
    'userCountryCity' => '',
    'userPhoneCode' => '375',
    ...
];
```

## License

yii2-geoip is released under the BSD 3-Clause License.
