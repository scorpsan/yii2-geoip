<?php

namespace scorpsan\geoip;

use Yii;
use yii\base\Component;
use linslin\yii2\curl;
use IP2Location\Database;

class GeoIp extends Component {
    /**
     * URL of API methods.
     */ 
    const URL_API = 'http://api.sypexgeo.net/';
    
    /**
     * @var boolean Whether set `true` then IP address of visitor will be get via API. 
     * Else, via \yii\web\Request::$userIP.
     */
    public $externalIp = false;

    /**
     * Returned information by IP address with following paramters:
     * - `ip`               - Visitor IP address, or IP address specified as parameter.
     * - `city`             - Object Region information
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
     * - `created`          - Date create info in dstsbase.
     * - `timestamp`        - Timestanp request.
     *
     * @return array|false
     */
    public function getInfo($id = null) {
        $is_bot = preg_match(
            "~(Google|Yahoo|Rambler|Bot|Yandex|Spider|Snoopy|Crawler|Finder|Mail|curl)~i",
            $_SERVER['HTTP_USER_AGENT']
        );
        $geo = !$is_bot ? json_decode(file_get_contents('http://api.sypexgeo.net/json/'), true) : [];
        var_dump($geo);
        if (!$is_bot) :
            $curl = new curl\Curl();
            $response = json_decode($curl->get('http://api.sypexgeo.net/json/'));
            if (empty($response->ip))
                return false;
            return $response;
        endif;
        return false;
    }

    /**
     * Returned information by IP address with following paramters:
     * - `ipAddress`       - Visitor IP address, or IP address specified as parameter.
     * - `countryName`     - Name Country in English.
     * - `countryCode`     - Two-letter ISO 3166-1 alpha-2 country code.
     *
     * @return array|false
     */
    public function getInfoDb() {
        $response = new Database(Yii::getAlias('@vendor') . '/ip2location/ip2location-php/databases/IP2LOCATION-LITE-DB1.BIN');
        if (!$this->externalIp) :
            echo '1';
            $result = $response->lookup(Yii::$app->request->userIP);
        else :
            echo $this->getIp();
            $result = $response->lookup($this->getIp());
        endif;
        if ($result['countryCode'] == 'Invalid IP address.') :
            return false;
        endif;
        return $result;
    }

    /**
     * Returned IP address of visitor if successful.
     * @return string|false
     */
    public function getIp() {
        $curl = new curl\Curl();
        $response = json_decode($curl->get(self::URL_API . '/json/'));
        if ($curl->get(self::URL_API . '/json/')) {
            if (empty($response->ip)) {
                return false;
            }
            return $response->ip;
        }
        return false;
    }
}
