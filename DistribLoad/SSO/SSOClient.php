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
	}

	/**
	 * Finalises a login process by using a token from the return URL.
	 *
	 * @param string $token
	 *
	 * @return SSOLoginResult
	 */
	public function finaliseLogin($token) {

	}

	/**
	 * Fetches the user data with an access token.
	 *
	 * @param string $accessToken
	 *
	 * @return SSOUserData
	 */
	public function fetchUser($accessToken) {

	}
}

