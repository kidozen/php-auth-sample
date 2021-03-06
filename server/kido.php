<?php
class Kido {

	private $client;
	private $marketplace;
	private $app;
	private $assertion;
	private $config;
	private $plainToken;
	private $token;
	private $inDevMode;
	private $retries = 0;

	function __construct($marketplace, $app, $mode = 'development', $assertion = null) {
		$this->client = new GuzzleHttp\Client();
		$this->marketplace = $marketplace;
		$this->app = $app;
		$this->assertion = $assertion;
		$this->inDevMode = $mode == 'development';
		if (!is_null($assertion)) {
			$this->config = $this->getAppConfig();
			$this->token = $this->getToken();
		}
	}

	public function getKidoToken() {
		return $this->plainToken;
	}

	public function getObjectSets() {
		$url = $this->config['url'] . 'storage/local';
		$options = [
			'headers' => [
				'authorization' => $this->token['token']
			]
		];
		if ($this->inDevMode) {
			$options['verify'] = false;
		}
		try {
			$result = $this->client->get($url, $options)->json();
			$this->retries = 0;
			return $result;
		} catch (Exception $e) {
			$this->token['access_token'] = $this->refreshToken();
			$this->retries++;
			if ($this->retries <= 5) {
				return $this->getObjectSets();
			} else {
				throw new Exception('Error Processing Request');
			}
		}
	}

	public function setAssertion($assertion) {
		if (is_null($this->assertion)) {
			$this->assertion = $assertion;
			$this->config = $this->getAppConfig();
			$this->token = $this->getToken();
		}
	}

	private function getPlainToken() {
		$options = [
			'body' => [
				'client_id' => 'dcda76f2-f2de-444d-ba7b-a2645d418e6c',
				'client_secret' => 'GkDf4BNhHy5LOO4qokiUuCd++V9SBBrzl2KtBU6PBM4=',
				'assertion' => html_entity_decode($this->assertion),
				'scope' => 'tasks',
				'grant_type' => 'urn:ietf:params:oauth:grant-type:saml2-bearer',
			]
		];
		if ($this->inDevMode) {
			$options['verify'] = false;
		}
		$token = $this->client->post($this->config['authConfig']['oauthTokenEndpoint'], $options)->json();

		if (!$token || (!isset($token['access_token']) && !isset($token['rawToken']))) {
			throw new Exception('Unable to retrieve KidoZen token');
		}

		return $token;
	}

	private function getToken() {
		$token = $this->plainToken = $this->getPlainToken();
		$access_token = isset($token['access_token']) ? $token['access_token'] : $token['rawToken'];
		$token['token'] = 'WRAP access_token="' . $access_token . '"';
		$token_data = urldecode($access_token);
		$claims = explode('&', $token_data);
		$token['claims'] = $claims;
		foreach ($claims as $key => $value) {
			if (strpos($value, 'ExpiresOn')) {
				$token['expiresOn'] = intval(explode('=', $value)[1]) * 1000 - 20 * 1000;
				break;
			}
		}
		return $token;
	}

	private function refreshToken() {
		$options = [
			'body' => [
				'client_id' => 'dcda76f2-f2de-444d-ba7b-a2645d418e6c',
				'client_secret' => 'GkDf4BNhHy5LOO4qokiUuCd++V9SBBrzl2KtBU6PBM4=',
				'scope' => 'tasks',
				'grant_type' => 'refresh_token',
				'refresh_token' => $this->token['refresh_token'],
			]
		];
		if ($this->inDevMode) {
			$options['verify'] = false;
		}
		$token = $this->client->post($this->config['authConfig']['oauthTokenEndpoint'], $options)->json();

		if (!$token || !isset($token['access_token'])) {
			throw new Exception('Unable to refresh KidoZen token');
		}

		return $token['access_token'];
	}

	private function getAppConfig() {
		$options = [];
		if ($this->inDevMode) {
			$options['verify'] = false;
		}
		return $this->client->get($this->marketplace . '/publicapi/apps?name=' . $this->app, $options)->json()[0];
	}

}