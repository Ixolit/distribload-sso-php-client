<?php

namespace DistribLoad\SSO;

/**
 * The SSO response object
 *
 * @package DistribLoad\SSO
 */
class SSOResponse {
	/**
	 * Request received and processed successfully.
	 */
	const RESULT_SUCCESS = 'SUCCESS';

	/**
	 * The appended signature was invalid.
	 */
	const RESULT_INVALID = 'INVALID';

	/**
	 * Some data is missing from the request. See “message” for details.
	 */
	const RESULT_INCOMPLETE = 'INCOMPLETE';
	/**
	 * Some error occurred while processing the request. See “message” for details.
	 */
	const RESULT_ERROR = 'ERROR';

	/**
	 * @var int
	 */
	protected $responseId;
	/**
	 * @var string|int
	 */
	protected $requestId;
	/**
	 * @var string
	 * @see SSOResponse::RESULT_*
	 */
	protected $result;
	/**
	 * @var bool
	 */
	protected $duplicate;
	/**
	 * @var string
	 */
	protected $message;
	/**
	 * @var array
	 */
	protected $payload;

	/**
	 * @var int
	 */
	protected $timestamp;

	/**
	 * @var string
	 */
	protected $apiKey;

	/**
	 * @var
	 */
	protected $apiSecret;

	/**
	 * @var string
	 */
	protected $signature;

	/**
	 * @var int
	 */
	protected $time;

	/**
	 * @param string   $apiKey
	 * @param string   $apiSecret
	 * @param bool|int $time      Timestamp to use for verification. Defaults to current time.
	 */
	public function __construct($apiKey, $apiSecret, $time = false) {
		$this->apiKey    = $apiKey;
		$this->apiSecret = $apiSecret;
		if ($time) {
			$this->time = $time;
		} else {
			$this->time = time();
		}
		$this->timestamp = $this->time;
	}

	/**
	 * Sets the duplicate flag for the response.
	 *
	 * @param boolean $duplicate
	 *
	 * @return void
	 *
	 * @internal
	 */
	public function setDuplicate($duplicate) {
		$this->duplicate = $duplicate;
	}

	/**
	 * Returns the indicator that this message was already received. Except for that, the response will be the same
	 * as the original.
	 *
	 * @return boolean
	 */
	public function getDuplicate() {
		return $this->duplicate;
	}

	/**
	 * Sets the response message
	 *
	 * @param string $message
	 *
	 * @return void
	 *
	 * @internal
	 */
	public function setMessage($message) {
		$this->message = $message;
	}

	/**
	 * Returns the internal technical message associated with the response. This should be used for debugging only.
	 * The message MUST NOT be parsed or assumed to be the same every time.
	 *
	 * @return string
	 */
	public function getMessage() {
		return $this->message;
	}

	/**
	 * Sets the payload for the response. The payload is specific to the call.
	 *
	 * @param array $payload
	 *
	 * @return void
	 *
	 * @internal
	 */
	public function setPayload($payload) {
		$this->payload = $payload;
	}

	/**
	 * Returns the payload for the response. The payload is specific to the call.
	 *
	 * @return array
	 */
	public function getPayload() {
		return $this->payload;
	}

	/**
	 * Sets the request ID for the response. This is used to avoid duplicate requests.
	 *
	 * @param int|string $requestId
	 *
	 * @return void
	 *
	 * @internal
	 */
	public function setRequestId($requestId) {
		$this->requestId = $requestId;
	}

	/**
	 * Returns the request ID for the response. This is used to avoid duplicate requests. The request ID is guaranteed
	 * to be unique within a 24 hour window of usage.
	 *
	 * @return int|string
	 */
	public function getRequestId() {
		return $this->requestId;
	}

	/**
	 * Sets the response ID for the request. The response ID can be looked up in the DistribLoad admin interface for
	 * errors within 24 hours of the request.
	 *
	 * @param int $responseId
	 *
	 * @return void
	 *
	 * @internal
	 */
	public function setResponseId($responseId) {
		$this->responseId = $responseId;
	}

	/**
	 * Returns the response ID for the request. The response ID can be looked up in the DistribLoad admin interface for
	 * errors within 24 hours of the request.
	 *
	 * @return int
	 */
	public function getResponseId() {
		return $this->responseId;
	}

	/**
	 * Sets the result code for the request.
	 *
	 * @param string $result
	 *
	 * @return void
	 *
	 * @internal
	 */
	public function setResult($result) {
		$this->result = $result;
	}

	/**
	 * Returns the result code from the request.
	 *
	 * @see SSOResponse::RESULT_*
	 *
	 * @return string
	 */
	public function getResult() {
		return $this->result;
	}

	/**
	 * Sets the API key associated with this response.
	 *
	 * @param string $apiKey
	 *
	 * @return void
	 *
	 * @internal
	 */
	public function setApiKey($apiKey) {
		$this->apiKey = $apiKey;
	}

	/**
	 * Returns the API key used with this response.
	 *
	 * @return string
	 */
	public function getApiKey() {
		return $this->apiKey;
	}

	/**
	 * Sets the API secret
	 *
	 * @param string $apiSecret
	 *
	 * @return void
	 */
	public function setApiSecret($apiSecret) {
		$this->apiSecret = $apiSecret;
	}

	/**
	 * Returns the API secret
	 *
	 * @return string
	 */
	public function getApiSecret() {
		return $this->apiSecret;
	}

	/**
	 * Sets the signature on the response.
	 *
	 * @param string $signature
	 *
	 * @return void
	 *
	 * @internal
	 */
	public function setSignature($signature) {
		$this->signature = $signature;
	}

	/**
	 * Returns the signature
	 *
	 * @return string
	 */
	public function getSignature() {
		return $this->signature;
	}

	/**
	 * Sets the timestamp for this response. This is used for signature verification.
	 *
	 * @param int $timestamp
	 *
	 * @return void
	 *
	 * @internal
	 */
	public function setTimestamp($timestamp) {
		$this->timestamp = $timestamp;
	}

	/**
	 * Returns the timestamp for this response. This is used for signature verification.
	 *
	 * @return int
	 */
	public function getTimestamp() {
		return $this->timestamp;
	}

	/**
	 * Checks if a key exists and sets it.
	 *
	 * @param array       $data
	 * @param string      $key
	 * @param bool|string $variable
	 *
	 * @return void
	 *
	 * @throws SSODataIncompleteException
	 */
	protected function checkAndSet($data, $key, $variable = false) {
		if (!array_key_exists($key, $data)) {
			throw new SSODataIncompleteException();
		}
		if ($variable) {
			$this->$variable = $data[$key];
		} else {
			$this->$key = $data[$key];
		}
	}

	/**
	 *
	 * @param array  $responseHeaders key-value pair of HTTP response headers.
	 * @param string $responseBody
	 *
	 * @return void
	 *
	 * @throws SSOException
	 * @throws SSOMissingSignatureException
	 * @throws SSOSignatureVerificationFailedException
	 * @throws SSODataCorruptException
	 */
	public function setFromResponse($responseHeaders, $responseBody) {
		if (!array_key_exists('X-Signature', $responseHeaders)) {
			throw new SSOMissingSignatureException();
		}

		if (!array_search('md5', hash_algos())) {
			throw new SSOException('MD5 support is missing for signature verification ( See hash_algos() )');
		}

		$signature = hash_hmac('md5', $responseBody, $this->apiSecret);

		if ($signature != $responseHeaders['X-Signature']) {
			throw new SSOSignatureVerificationFailedException();
		}

		$data = json_decode($responseBody, true);
		if (json_last_error() !== JSON_ERROR_NONE) {
			throw new SSODataCorruptException('JSON decoding error: ' . json_last_error_msg());
		}

		if (!array_key_exists('api_key', $data)) {
			throw new SSODataIncompleteException();
		}
		if ($data['api_key'] !== $this->apiKey) {
			// We are not the recipients of this message
			throw new SSOApiKeyMismatchException();
		}
		if (!array_key_exists('timestamp', $data)) {
			throw new SSODataIncompleteException();
		}
		$time = $this->time;
		if ((int)$data['timestamp'] < $time - 150 || (int)$data['timestamp'] > $time + 150) {
			throw new SSOSignatureVerificationFailedException('Timestamp (' . (int)$data['timestamp'] . ') out of range ('
				. ($time - 150) . '-' . ($time + 150) . ')');
		}
		$this->timestamp = $data['timestamp'];

		$this->checkAndSet($data, 'response_id', 'responseId');
		$this->checkAndSet($data, 'request_id', 'requestId');
		$this->checkAndSet($data, 'result');
		$this->checkAndSet($data, 'duplicate');
		$this->checkAndSet($data, 'message');
		$this->checkAndSet($data, 'payload');
	}

	/**
	 * Returns a 2-member list. The first member is a list of headers (key-value), the second is the response body.
	 *
	 * Usage:
	 *
	 *     list($headers, $body) = $response->createResponse();
	 *
	 * @return array
	 *
	 * @throws SSODataCorruptException
	 */
	public function createResponse() {
		$headers = array();

		$data = array();
		$data['timestamp']   = $this->timestamp;
		$data['api_key']     = $this->apiKey;
		$data['response_id'] = $this->responseId;
		$data['request_id']  = $this->requestId;
		$data['result']      = $this->result;
		$data['duplicate']   = $this->duplicate;
		$data['message']     = $this->message;
		$data['payload']     = $this->payload;
		$body = json_encode($data);
		if (json_last_error() !== JSON_ERROR_NONE) {
			throw new SSODataCorruptException('JSON encoding error: ' . json_last_error_msg());
		}

		$headers['X-Signature'] = hash_hmac('md5', $body, $this->apiSecret);

		return array($headers, $body);
	}
}

