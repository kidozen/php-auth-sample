<?php
class Kido {

	private $client;
	private $marketplace;
	private $app;
	private $assertion;
	private $config;
	private $plainToken;
	private $token;

	function __construct($marketplace, $app, $assertion) {
		$this->client = new GuzzleHttp\Client();
		$this->marketplace = $marketplace;
		$this->app = $app;
		$this->assertion = $assertion;
		$this->config = $this->getAppConfig();
		$this->token = $this->getToken();
	}

	public function getKidoToken() {
		return $this->plainToken;
	}

	public function getObjectSets() {
		$url = $this->config['url'] . 'storage/local';
		return $this->client->get($url, [
			'verify' => false,
			'headers' => [
				'authorization' => $this->token['token']
			]
		])->json();
	}

	private function getPlainToken() {
		$token = $this->client->post($this->config['authConfig']['oauthTokenEndpoint'], [
			'verify' => false,
			'body' => [
				'client_id' => 'dcda76f2-f2de-444d-ba7b-a2645d418e6c',
				'client_secret' => 'GkDf4BNhHy5LOO4qokiUuCd++V9SBBrzl2KtBU6PBM4=',
				'assertion' => html_entity_decode($this->assertion),
				'scope' => 'tasks',
				'grant_type' => 'urn:ietf:params:oauth:grant-type:saml2-bearer',
			]
		])->json();

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

	private function getAppConfig() {
		return $this->client->get($this->marketplace . '/publicapi/apps?name=' . $this->app, ['verify' => false])->json()[0];
	}

}