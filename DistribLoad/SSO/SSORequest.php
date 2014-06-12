<?php

namespace DistribLoad\SSO;

/**
 * This object contains a HTTP request issued to the SSO endpoint.
 *
 * @package DistribLoad\SSO
 */
class SSORequest {
	/**
	 * @var string
	 */
	protected $apiKey;
	/**
	 * @var string
	 */
	protected $apiSecret;
	/**
	 * @var string
	 */
	protected $endpoint;
	/**
	 * @var string
	 */
	protected $requestId;
	/**
	 * @var string
	 */
	protected $requestUri;
	/**
	 * @var string
	 */
	protected $signature;

	/**
	 * @var int
	 */
	protected $timestamp;

	/**
	 * @var array
	 */
	protected $payload;

	/**
	 * The current time for signature verification
	 *
	 * @var int
	 */
	protected $time;

	/**
	 * @param string $endpoint
	 * @param string $apiKey
	 * @param string $apiSecret
	 */
	public function __construct($endpoint, $apiKey, $apiSecret) {
		$this->endpoint  = $endpoint;
		$this->apiKey    = $apiKey;
		$this->apiSecret = $apiSecret;
		$this->time      = time();
		$this->timestamp = $this->time;
	}

	/**
	 * Overrides the timestamp for this request
	 *
	 * @param int $timestamp
	 *
	 * @return void
	 */
	public function setTimestamp($timestamp) {
		$this->timestamp = $timestamp;
	}

	/**
	 * Returns the timestamp for this request.
	 *
	 * @return int
	 */
	public function getTimestamp() {
		return $this->timestamp;
	}

	/**
	 * Sets the request ID for this request.
	 *
	 * @param string $requestId
	 *
	 * @return string
	 */
	public function setRequestId($requestId) {
		$this->requestId = $requestId;
	}

	/**
	 * Returns the request ID for this request.
	 *
	 * @return string
	 */
	public function getRequestId() {
		return $this->requestId;
	}

	/**
	 * Sets the request URI (relative to the endpoint).
	 *
	 * @param string $requestUri
	 *
	 * @return void
	 */
	public function setRequestUri($requestUri) {
		$this->requestUri = $requestUri;
	}

	/**
	 * Returns the relative request URI without the endpoint.
	 *
	 * @return string
	 */
	public function getRequestUri() {
		return $this->requestUri;
	}

	/**
	 * Sets the request payload.
	 *
	 * @param array $payload
	 *
	 * @return void
	 */
	public function setPayload($payload) {
		$this->payload = $payload;
	}

	/**
	 * Returns the request payload.
	 *
	 * @return array
	 */
	public function getPayload() {
		return $this->payload;
	}

	/**
	 * Builds the request.
	 *
	 * @return string[] A list containing the URL and the request body.
	 *
	 * @throws SSOException
	 */
	public function createRequest() {
		$url = rtrim($this->endpoint, '/');
		$requestUri = $this->requestUri;

		if (!preg_match('/^\//', $requestUri)) {
			$requestUri = '/' . $requestUri;
		}

		$data = array();

		if ($this->requestId) {
			$data['request_id'] = $this->requestId;
		}

		$data['api_key']   = $this->apiKey;
		$data['timestamp'] = $this->timestamp;

		$data['payload'] = $this->payload;

		if (!array_search('md5', hash_algos())) {
			throw new SSOException('MD5 support is missing for signature verification ( See hash_algos() )');
		}
		$requestBody = json_encode($data);
		if (json_last_error() !== JSON_ERROR_NONE) {
			throw new SSODataCorruptException('JSON encoding error: ' . json_last_error_msg());
		}
		$signature = hash_hmac('md5', $requestUri . $requestBody, $this->apiSecret);

		$url .= $requestUri;

		if (strstr($url, '?') !== false) {
			$url .= '&';
		} else {
			$url .= '?';
		}

		$url .= 'signature=' . urlencode($signature);

		return array($url, $requestBody);
	}

	/**
	 * Fills the request from the raw data. The signature is also verified.
	 *
	 * @param string $requestUri
	 * @param string $requestBody
	 *
	 * @return void
	 *
	 * @throws SSOMissingSignatureException
	 * @throws SSOSignatureVerificationFailedException
	 * @throws SSOException
	 * @throws SSODataCorruptException
	 */
	public function setFromRequest($requestUri, $requestBody) {
		//Remove the endpoint URL from the request URI.
		$relativeRequestUri = '/' . ltrim(str_replace($this->endpoint, '', $requestUri), '/');
		$uriParts = explode('?', $relativeRequestUri, 2);
		if (!isset($uriParts[1])) {
			throw new SSOMissingSignatureException();
		}
		parse_str($uriParts[1], $params);
		if (!isset($params['signature'])) {
			throw new SSOMissingSignatureException();
		}

		//Remove signature
		$relativeRequestUri = preg_replace('/(&|\?)signature=([a-zA-Z0-9]+)$/', '', $relativeRequestUri);

		if (!array_search('md5', hash_algos())) {
			throw new SSOException('MD5 support is missing for signature verification ( See hash_algos() )');
		}
		$signature = hash_hmac('md5', $relativeRequestUri . $requestBody, $this->apiSecret);

		if ($signature != $params['signature']) {
			throw new SSOSignatureVerificationFailedException('Invalid signature');
		}

		$data = json_decode($requestBody, true);
		if (json_last_error() !== JSON_ERROR_NONE) {
			throw new SSODataCorruptException('JSON decoding error: ' . json_last_error_msg());
		}

		$data = array_merge($data, $params);

		if (!isset($data['api_key']) || $data['api_key'] !== $this->apiKey) {
			throw new SSODataIncompleteException('Missing api_key');
		}

		if (!array_key_exists('timestamp', $data)) {
			throw new SSODataIncompleteException('Missing timestamp');
		}
		$time = $this->time;
		if ((int)$data['timestamp'] < $time - 150 || (int)$data['timestamp'] > $time + 150) {
			throw new SSOSignatureVerificationFailedException('Timestamp (' . (int)$data['timestamp'] .
				') out of range (' . ($time - 150) . '-' . ($time + 150) . ')');
		}
		$this->timestamp = $data['timestamp'];

		$this->signature  = $data['signature'];
		$this->requestUri = $uriParts[0];
		if (isset($data['request_id'])) {
			$this->requestId  = $data['request_id'];
		}
		if (!isset($data['payload'])) {
			throw new SSODataIncompleteException('Missing payload');
		}
		$this->payload = $data['payload'];
	}
}
