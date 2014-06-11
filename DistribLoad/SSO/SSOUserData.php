<?php

namespace DistribLoad\SSO;

/**
 * This class contains the response data for a user fetch request.
 *
 * @package DistribLoad\SSO
 */
class SSOUserData {
	/**
	 * @var bool
	 */
	protected $success;

	/**
	 * @var int
	 */
	protected $userId;

	/**
	 * @var string[]
	 */
	protected $metadata;

	/**
	 * Sets if the login procedure was successful.
	 *
	 * @param boolean $success
	 *
	 * @return void
	 */
	public function setSuccess($success) {
		$this->success = $success;
	}

	/**
	 * Returns if the login procedure was successful.
	 *
	 * @return boolean
	 */
	public function getSuccess() {
		return $this->success;
	}

	/**
	 * Sets the numeric user ID used to uniquely identify the user.
	 *
	 * @param int $userId
	 *
	 * @return int
	 */
	public function setUserId($userId) {
		$this->userId = $userId;
	}

	/**
	 * Returns the numeric user ID used to uniquely identify the user.
	 *
	 * @return int
	 */
	public function getUserId() {
		return $this->userId;
	}

	/**
	 * Sets a key-value store of user metadata.
	 *
	 * @param string[] $metadata
	 *
	 * @return void
	 */
	public function setMetadata($metadata) {
		$this->metadata = $metadata;
	}

	/**
	 * Returns a key-value store of user metadata. Remember, not all fields will be set for all users. Prepare your
	 * code to handle missing fields gracefully, including identification data such as e-mail address.
	 *
	 * @return string[]
	 */
	public function getMetadata() {
		return $this->metadata;
	}

}

