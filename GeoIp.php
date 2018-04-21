<?php

namespace scorpsan\geoip;

use app\controllers\AppController;
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
     * @var string Whether set `` then only 10000/month request available to Sypexgeo API.
     * if set key (for register users on Sypexgeo) min 30000/month free or more for paid
     * more info on https://sypexgeo.net/
     */
    public $keySypex = '';

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
        if (!$is_bot) :
            if (!$this->externalIp) :
                $userip = Yii::$app->request->userIP;
            else :
                $userip = '';
            endif;
            $curl = new curl\Curl();
            $response = json_decode($curl->get(self::URL_API . (($this->keySypex) ? $this->keySypex.'/' : '') . 'json/' . $userip));
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
    public function getInfoDb($id = null) {
        $response = new Database(Yii::getAlias('@vendor') . '/ip2location/ip2location-php/databases/IP2LOCATION-LITE-DB1.BIN');
        $result = $response->lookup($this->ip);
        if ($result['countryCode'] == 'Invalid IP address.')
            return false;
        return $result;
    }

    /**
     * Returned IP address of visitor if successful.
     * @return string|false
     */
    public function getIp() {
        if (!$this->externalIp) :
            return Yii::$app->request->userIP;
        else :
            $is_bot = preg_match(
                "~(Google|Yahoo|Rambler|Bot|Yandex|Spider|Snoopy|Crawler|Finder|Mail|curl)~i",
                $_SERVER['HTTP_USER_AGENT']
            );
            if (!$is_bot) :
                $curl = new curl\Curl();
                $response = json_decode($curl->get(self::URL_API . (($this->keySypex) ? $this->keySypex.'/' : '') . 'json/'));
                AppController::debug($response);
                if (empty($response->ip))
                    return false;
                return $response->ip;
            endif;
        endif;
        return false;
    }
}
