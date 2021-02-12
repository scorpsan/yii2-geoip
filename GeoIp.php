<?php
namespace scorpsan\geoip;

use Yii;
use yii\base\Component;
use GuzzleHttp\RequestOptions;
use IP2Location\Database;

class GeoIp extends Component
{
	private $httpClient;
    /**
     * URL of API methods.
     */ 
    const URL_API = 'http://api.sypexgeo.net/';

    /**
     * Array local IP
     */
    public $localIp = ['127.0.0.1', '::1'];

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
    public function getInfo($ip = null)
	{
        $is_bot = preg_match(
            "~(Google|Yahoo|Rambler|Bot|Yandex|Spider|Snoopy|Crawler|Finder|Mail|curl)~i",
            $_SERVER['HTTP_USER_AGENT']
        );
        if ($is_bot) return false;

        if ($ip)
			$userip = $ip;
		elseif (in_array(Yii::$app->request->userIP, $this->localIp))
			$userip = '';
		else
			$userip = Yii::$app->request->userIP;

		$this->httpClient = new \GuzzleHttp\Client([
			'base_uri' => self::URL_API,
			'timeout' => 36000,
			'verify' => false,
		]);
		$response = json_decode($this->httpClient->get((($this->keySypex) ? $this->keySypex.'/' : '') . 'json/' . $userip));
		if (empty($response->ip))
			return false;
		return $response;
    }

    /**
     * Returned information by IP address with following paramters:
     * - `ipAddress`       - Visitor IP address, or IP address specified as parameter.
     * - `countryName`     - Name Country in English.
     * - `countryCode`     - Two-letter ISO 3166-1 alpha-2 country code.
     *
     * @return array|false
     */
    public function getInfoDb($ip = null)
	{
        if ($ip)
			$userip = $ip;
		else
			$userip = $this->ip;

        $response = new Database(Yii::getAlias('@vendor') . '/ip2location/ip2location-php/databases/IP2LOCATION-LITE-DB1.BIN');
        $result = $response->lookup($userip);
        if ($result['countryCode'] == 'Invalid IP address.')
            return false;
        return $result;
    }

    /**
     * Returned IP address of visitor if successful.
     * @return string|false
     */
    public function getIp()
	{
		$is_bot = preg_match(
			"~(Google|Yahoo|Rambler|Bot|Yandex|Spider|Snoopy|Crawler|Finder|Mail|curl)~i",
			$_SERVER['HTTP_USER_AGENT']
		);
		if ($is_bot) return false;

		if (in_array(Yii::$app->request->userIP, $this->localIp)) {
			$this->httpClient = new \GuzzleHttp\Client([
				'base_uri' => self::URL_API,
				'timeout' => 36000,
				'verify' => false,
			]);
			$response = json_decode($this->httpClient->get((($this->keySypex) ? $this->keySypex . '/' : '') . 'json/'));
			if (empty($response->ip))
				return Yii::$app->request->userIP;
			return $response->ip;
		} else {
			return Yii::$app->request->userIP;
		}
    }

}
