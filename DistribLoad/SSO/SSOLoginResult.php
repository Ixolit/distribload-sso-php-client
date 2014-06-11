<?php

namespace DistribLoad\SSO;

/**
 * This object contains the result of an SSO login.
 *
 * @package DistribLoad\SSO
 */
class SSOLoginResult extends SSOUserData {
	/**
	 * @var string
	 */
	protected $accessToken;

	/**
	 * Sets the access token used to later fetch user data.
	 *
	 * @param string $accessToken
	 *
	 * @return void
	 */
	public function setAccessToken($accessToken) {
		$this->accessToken = $accessToken;
	}

	/**
	 * Returns the access token used to later fetch user data.
	 *
	 * @return string
	 */
	public function getAccessToken() {
		return $this->accessToken;
	}
}

