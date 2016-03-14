<?php

namespace Unplag;

use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Subscriber\Oauth\Oauth1;
use Unplag\Exception\ApiException;
use Unplag\Exception\ResponseException;

if(!defined('UNPLAG_API_BASE_URL')) {
	define('UNPLAG_API_BASE_URL', 'https://unplag.com/api/v2/');
}

class Client
{
	protected static $keyRegex = '/^[A-z0-9]{16,32}$/';
	protected static $secretRegex = '/^[A-z0-9]{32,64}$/';

	protected $key;
	protected $secret;

	/**
	 * @var \GuzzleHttp\Client
	 */
	protected $client;

	public function __construct($key, $secret)
	{
		if(!preg_match(static::$keyRegex, $key)) {
			throw new \InvalidArgumentException("Invalid key $key");
		}

		if(!preg_match(static::$secretRegex, $secret)) {
			throw new \InvalidArgumentException("Invalid secret $secret");
		}

		$this->key = $key;
		$this->secret = $secret;

		$this->createGuzzleClient();
	}

	protected function createGuzzleClient() {
		$stack = HandlerStack::create();

		$middleware = new Oauth1([
			'consumer_key' => $this->key,
		    'consumer_secret' => $this->secret,
			'token_secret' => '',
			'token' => '',
		]);

		$stack->push($middleware);

		$this->client = new \GuzzleHttp\Client([
			'base_uri' => UNPLAG_API_BASE_URL,
		    'handler' => $stack,
			'auth' => 'oauth'
		]);
	}

	public function execute(Request $request) {

		try
		{
			$guzzle_response = $this->client->send($request->makeGuzzleRequest());
		}
		catch(RequestException $ex) {
			if(!$ex->hasResponse()) {
				throw new \Unplag\Exception\RequestException($ex->getMessage(), $ex->getCode(), $ex, $request);
			}

			try {
				$response = new Response($ex->getResponse());
			}
			catch(\Exception $ex2) {
				if($ex instanceof \InvalidArgumentException) {
					$code = ResponseException::CODE_INVALID_CONTENT_TYPE;
				}
				else {
					$code = ResponseException::CODE_RESPONSE_PARSE_FAIL;
				}
				throw new ResponseException("Failed to obtain error response. Resp: " . $ex->getResponse()->getBody()->getContents(), $code, $ex2, $request, null);
			}

			throw new ApiException($request, $response, $ex);
		}
		catch(\Exception $ex) {
			throw new \Unplag\Exception\RequestException($ex->getMessage(), $ex->getCode(), $ex, $request);
		}


		try {
			$response = new Response($guzzle_response);
		}
		catch(\Exception $ex) {
			if($ex instanceof \InvalidArgumentException) {
				$code = ResponseException::CODE_INVALID_CONTENT_TYPE;
			}
			else {
				$code = ResponseException::CODE_RESPONSE_PARSE_FAIL;
			}
			throw new ResponseException("Response parse failed. Resp: " . $guzzle_response->getBody()->getContents(), $code, $ex, $request, null);
		}

		if(!$response->isSuccess()) {
			throw new ApiException($request, $response);
		}

		return $response;
	}

	/**
	 * @return string
	 */
	public function getKey()
	{
		return $this->key;
	}
}