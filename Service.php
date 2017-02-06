<?php
/**
 * Created by PhpStorm.
 * User: Vitaly Voskobovich
 * Date: 10.06.14 16:03
 */
namespace webkadabra\bitly;

use \yii\helpers\Json;

class Service extends \yii\base\Component
{
	/**
	 * Сервер Api
	 */
	const API_SERVER = 'https://api-ssl.bitly.com';

	/**
	 * Логин в сервисе Bit.ly
	 * @var string
	 */
	public $login;

	/**
	 * Ключь доступа к апи сервиса
	 * @var string
	 */
	public $apiKey;

	/**
	 * Токен доступа
	 * @var string
	 */
	public $accessToken;

	/**
	 * Query vars
	 * @var array
	 */
	private $_vars = [];

	/**
	 * Инициализация
	 */
	public function init()
	{
		$this->_vars['login'] = $this->login;
		$this->_vars['apiKey'] = $this->apiKey;
		$this->_vars['access_token'] = $this->accessToken;
	}

	/**
	 * Отправка запроса на сервер
	 * @param string $url
	 *
	 * @return bool|mixed|string
	 */
	private function call($url)
	{
		$json = function_exists('curl_init') ? $this->curl($url) : file_get_contents($url);
		$json = Json::decode($json, true);

		return $json;
	}

	/**
	 * Отправляет запрос через CURL
	 * @param $url
	 *
	 * @return bool|mixed
	 */
	private function curl($url)
	{
		$param = parse_url($url);

		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $param['scheme'].'://'.$param['host'].$param['path']);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER,true);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $param['query']);
		$out = curl_exec($curl);

		curl_close($curl);

		return $out;
	}

	/**
	 * Пишем ошибки в логи
	 * @param $errorData
	 * @param string $category
	 */
	private function log($errorData, $category)
	{
		$message = print_r($errorData, true);
		Yii::error($message, $category);
	}

	/**
	 * Метод выполнения запросов
	 * @param $method
	 * @param array $vars
	 * @return array
	 */
	public function api($method, array $vars = [])
	{
		$this->_vars = array_merge_recursive($this->_vars, $vars);

		$params = http_build_query($this->_vars);

		$url = self::API_SERVER . "{$method}?{$params}";

		$response = $this->call($url);

		if($response['status_code'] != 200)
			$this->log($response, 'bitly.Api');
		else
			return (array)$response['data'];

		return false;
	}
}