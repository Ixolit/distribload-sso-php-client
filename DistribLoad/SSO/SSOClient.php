<?php

namespace DistribLoad\SSO;

/**
 * Contains an SSO client application.
 *
 * @package DistribLoad\SSO
 */
class SSOClient {
	/**
	 * @var string
	 */
	protected $endpoint;
	/**
	 * @var string
	 */
	protected $apiKey;
	/**
	 * @var string
	 */
	protected $apiSecret;

	/**
	 * @param string $endpoint
	 * @param string $apiKey
	 * @param string $apiSecret
	 */
	public function __construct($endpoint, $apiKey, $apiSecret) {
		$this->endpoint  = $endpoint;
		$this->apiKey    = $apiKey;
		$this->apiSecret = $apiSecret;
	}

	/**
	 * Creates a redirect URL to send the user to.
	 *
	 * @param string $returnUrl
	 * @param bool   $iframe
	 *
	 * @return string
	 */
	public function startLogin($returnUrl, $iframe = false) {
		return $this->redirect('/user/login', array('returnUrl' => $returnUrl, 'iframe' => (int)$iframe));
	}

	/**
	 * Finalises a login process by using a token from the return URL.
	 *
	 * @param string $token
	 *
	 * @return SSOLoginResult
	 */
	public function finaliseLogin($token) {
		$response        = $this->request('/user/fetch', array('token' => $token));
		$responsePayload = $response->getPayload();

		$result = new SSOLoginResult();
		if ($response->getResult() == SSOResponse::RESULT_SUCCESS) {
			$result->setSuccess(true);
			$result->setUserId($responsePayload['user_id']);
			$result->setAccessToken($responsePayload['access_token']);
			$result->setMetadata($responsePayload['metadata']);
		} else {
			$result->setSuccess(false);
		}

		return $result;
	}

	/**
	 * Fetches the user data with an access token.
	 *
	 * @param string $accessToken
	 *
	 * @return SSOUserData
	 */
	public function fetchUser($accessToken) {
		$response        = $this->request('/user/fetch', array('access_token' => $accessToken));
		$responsePayload = $response->getPayload();

		$result = new SSOUserData();
		if ($response->getResult() == SSOResponse::RESULT_SUCCESS) {
			$result->setSuccess(true);
			$result->setUserId($responsePayload['user_id']);
			$result->setMetadata($responsePayload['metadata']);
		} else {
			$result->setSuccess(false);
		}

		return $result;
	}

	/**
	 * Creates a signed redirect URL to the SSO endpoint.
	 *
	 * @param string   $uri       Relative URI starting with /
	 * @param array    $payload   Payload data.
	 * @param bool|int $timestamp Timestamp to use for signing.
	 *
	 * @return string
	 */
	public function redirect($uri, $payload, $timestamp = false) {

		$forward = new SSOForward($this->endpoint, $this->apiKey, $this->apiSecret);
		$forward->setRequestUri($uri);
		$forward->setPayload($payload);
		if ($timestamp) {
			$forward->setTimestamp($timestamp);
		}

		return $forward->createUrl();
	}

	/**
	 * Does a HTTP request to the endpoint.
	 *
	 * @param string   $uri       Relative request URI.
	 * @param array    $payload   Payload data.
	 * @param string   $requestId Unique request ID for idempotency. Optional.
	 * @param bool|int $timestamp Timestamp to use for signing.
	 *
	 * @return SSOResponse
	 */
	public function request($uri, $payload, $requestId = '', $timestamp = false) {
		$request = new SSORequest($this->endpoint, $this->apiKey, $this->apiSecret);
		$request->setRequestId($requestId);
		if($timestamp) {
			$request->setTimestamp($timestamp);
		}
		$request->setPayload($payload);
		$request->setRequestUri($uri);
		list($url, $body) = $request->createRequest();

		list($responseHeaders, $responseBody) = $this->doRequest($url, $body);

		$response = new SSOResponse($this->apiKey, $this->apiSecret);
		$response->setFromResponse($responseHeaders, $responseBody);
		return $response;
	}

	/**
	 * Run the request and return a response. The response is a list of headers and body.
	 *
	 * @param string      $url
	 * @param string      $body
	 * @param bool|string $proxy Proxy to use for request (curl semantics)
	 *
	 * @return string[]
	 *
	 * @throws SSOException
	 */
	protected function doRequest($url, $body, $proxy = false) {
		if (!function_exists('curl_init')) {
			throw new SSOException('curl support is missing');
		}

		$curl = curl_init($url);
		if ($proxy) {
			curl_setopt($curl, CURLOPT_PROXY, $proxy);
		}
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_VERBOSE, 1);
		curl_setopt($curl, CURLOPT_HEADER, 1);
		curl_setopt($curl, CURLOPT_HTTPHEADER, array(
			'Content-Type' => 'application/json'
		));
		curl_setopt($curl, CURLOPT_POST, 1);
		curl_setopt($curl, CURLOPT_POSTFIELDS, (string)$body);

		//Don't even THINK of changing this. If it doesn't work, fix your certificate store.
		//curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 1);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 1);

		$response = curl_exec($curl);
		if (curl_errno($curl)) {
			throw new SSOException(curl_error($curl));
		}

		$header_size = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
		$header = substr($response, 0, $header_size);
		$body = substr($response, $header_size);
		$httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		curl_close($curl);
		if ($httpCode !== 200) {
			throw new SSOException('HTTP status code ' , $httpCode);
		}

		$headerLines = explode("\r\n", $header);
		$headers = array();
		foreach($headerLines as $headerLine) {
			$headerLine = explode(":", $headerLine, 2);
			if(count($headerLine) > 1) {
				$headers[trim($headerLine[0])] = trim($headerLine[1]);
			}
		}

		return array($headers, $body);
	}
}

