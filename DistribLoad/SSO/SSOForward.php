<?php

namespace DistribLoad\SSO;

/**
 * This class is used to construct or validate an URL for user forwarding.
 *
 * @package DistribLoad\SSO
 */
class SSOForward {
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
	protected $requestUri;
	/**
	 * @var string
	 */
	protected $signature;

	/**
	 * @var array
	 */
	protected $payload;

	/**
	 * @var int
	 */
	protected $timestamp;
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
	 * Builds the URL.
	 *
	 * @return string
	 *
	 * @throws SSOException
	 */
	public function createUrl() {
		$url = rtrim($this->endpoint, '/');
		if (!preg_match('/^\//', $this->requestUri)) {
			$url .= '/';
		}

		$requestUri = $this->requestUri;
		if (strstr($requestUri, '?') !== false) {
			$requestUri .= '&';
		} else {
			$requestUri .= '?';
		}

		$data = $this->payload;

		$data['api_key']   = $this->apiKey;
		$data['timestamp'] = $this->timestamp;

		$requestUri .= http_build_query($data);

		$url .= $requestUri;


		if (!array_search('md5', hash_algos())) {
			throw new SSOException('MD5 support is missing for signature verification ( See hash_algos() )');
		}
		$signature = hash_hmac('md5', $requestUri, $this->apiSecret);
		$url .= '&signature=' . urlencode($signature);

		return $url;
	}

	/**
	 * Fills the request from the raw data. The signature is also verified.
	 *
	 * @param string $requestUrl
	 *
	 * @return void
	 *
	 * @throws SSOMissingSignatureException
	 * @throws SSOSignatureVerificationFailedException
	 * @throws SSOException
	 * @throws SSODataCorruptException
	 */
	public function setFromUrl($requestUrl) {
		//Remove the endpoint URL from the request URI.
		$relativeRequestUri = '/' . ltrim(str_replace($this->endpoint, '', $requestUrl), '/');
		$uriParts = explode('?', $relativeRequestUri, 2);
		if (!isset($uriParts[1])) {
			throw new SSOMissingSignatureException();
		}
		parse_str($uriParts[1], $data);
		if (!isset($data['signature'])) {
			throw new SSOMissingSignatureException();
		}

		//Remove signature
		$relativeRequestUri = preg_replace('/(&|\?)signature=([a-zA-Z0-9]+)$/', '', $relativeRequestUri);

		if (!array_search('md5', hash_algos())) {
			throw new SSOException('MD5 support is missing for signature verification ( See hash_algos() )');
		}
		$signature = hash_hmac('md5', $relativeRequestUri, $this->apiSecret);

		if ($signature != $data['signature']) {
			throw new SSOSignatureVerificationFailedException('Provided signature ' . $data['signature'] . ' does not match ' . $signature);
		}

		if (!isset($data['api_key']) || $data['api_key'] !== $this->apiKey) {
			throw new SSODataIncompleteException('Missing api_key');
		}

		if (!array_key_exists('timestamp', $data)) {
			throw new SSODataIncompleteException('Missing timestamp');
		}
		$time = $this->time;
		if ((int)$data['timestamp'] < $time - 3600 || (int)$data['timestamp'] > $time + 600) {
			throw new SSOSignatureVerificationFailedException('Timestamp (' . (int)$data['timestamp'] .
				') out of range (' . ($time - 3600) . '-' . ($time + 600) . ')');
		}
		$this->timestamp = $data['timestamp'];

		$this->signature = $data['signature'];
	}
}

