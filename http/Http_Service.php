<?php

namespace ITRocks\Framework\Http;

use ITRocks\Framework\Http\Exceptions\Http_Client_Logic_Exception;
use ITRocks\Framework\Http\Exceptions\Psr_Exception;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;
use Psr\Http\Client\ClientExceptionInterface as PsrClientExceptionInterface;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface as SymfonyClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Component\HttpClient\Psr18Client;
use Symfony\Contracts\HttpClient\ResponseInterface as SymfonyResponseInterface;

/**
 * Class Http_Service
 * @package ITRocks\Framework\Http
 */
class Http_Service
{

	//------------------------------------------------------------------------------------------- GET
	public const GET           = 'GET';

	//------------------------------------------------------------------------------------------ POST
	public const POST          = 'POST';

	//--------------------------------------------------------------------------------- PSR18_ADAPTER
	public const PSR18_ADAPTER = 'PSR18';

	//-------------------------------------------------------------------------------------- $adapter
	/**
	 * @var string $adapter
	 */
	private string $adapter;

	//--------------------------------------------------------------------------------- $content_type
	/**
	 * @var string $content_type
	 */
	private string $content_type;

	//---------------------------------------------------------------------------- $httpClientHandler
	/**
	 * @var ClientInterface|HttpClientInterface $http_client
	 */
	private HttpClientInterface|ClientInterface $http_client;


	//-------------------------------------------------------------------------------- $max_redirects
	/**
	 * @var int
	 */
	private int $max_redirects;

	//----------------------------------------------------------------------------- $query_parameters
	/**
	 * @var string|null $query_parameters
	 */
	private ?string $query_parameters;

	//------------------------------------------------------------------------------------- $response
	/**
	 * @var SymfonyResponseInterface|PsrResponseInterface $response
	 */
	private PsrResponseInterface|SymfonyResponseInterface $response;

	//--------------------------------------------------------------------------------------- request
	/**
	 * Instantiates a new HttpClient object.
	 *
	 * @param HttpClientInterface|null $httpClient
	 */
	public function __construct(HttpClientInterface $httpClient = null)
	{
		$this->http_client = ($httpClient)??HttpClient::create();
	}

	//--------------------------------------------------------- formatHttpClientLogicExceptionMessage
	/**
	 * @param string $msg
	 * @return string
	 */
	private function formatHttpClientLogicExceptionMessage(string $msg): string
	{
		return sprintf('Warning : You cannot set "%s" with a Psr18 Adapter', $msg);
	}

	//--------------------------------------------------------------------- formatPsrExceptionMessage
	/**
	 * @return string
	 */
	private function formatPsrExceptionMessage(): string
	{
		return sprintf('Warning : Method "%s" cannot be provided because you are using a PSR18 Adapter',
			__FUNCTION__
		);
	}

	//------------------------------------------------------------------------------------------- get
	/**
	 * Sends a GET request
	 * @throws TransportExceptionInterface
	 * @throws PsrClientExceptionInterface
	 */
	public function get($url, $options): void
	{
		/** @var  SymfonyResponseInterface|PsrResponseInterface $response */
		if ($this->getAdapter() === self::PSR18_ADAPTER) {
			$http_client = new Psr18Client($this->getHttpClient());
			$this->http_client = $http_client;
			$request = $http_client->createRequest(self::GET, $url);
			$response = $http_client->sendRequest($request);
		}
		else {
			$response = $this->getHttpClient()->request(self::GET, $url, $options);
		}
		$this->setResponse($response);
	}

	//------------------------------------------------------------------------------------ getAdapter
	/**
	 * @return string
	 */
	public function getAdapter(): string
	{
		return $this->adapter;
	}

	//--------------------------------------------------------------------------------- getHttpClient
	/**
	 * Gets the response body as a string.
	 *
	 * @param bool $throw Whether an exception should be thrown on 3/4/5xx status codes
	 *
	 * @throws TransportExceptionInterface   When a network error occurs
	 * @throws RedirectionExceptionInterface On a 3xx when $throw is true and the "max_redirects"
	 *  option has been reached
	 * @throws SymfonyClientExceptionInterface      On a 4xx when $throw is true
	 * @throws ServerExceptionInterface      On a 5xx when $throw is true
	 */
	public function getContent(bool $throw = true): string
	{
		return $this->getResponse()->getContent($throw);
	}

	//-------------------------------------------------------------------------------- getContentType
	/**
	 * Returns the content type.
	 *
	 * @return string
	 */
	public function getContentType(): string
	{
		return $this->content_type;
	}

	//------------------------------------------------------------------------------------ getHeaders
	/**
	 * Gets the HTTP headers of the response.
	 *
	 * @param bool $throw
	 * @return array
	 *
	 * @throws RedirectionExceptionInterface On a 3xx when $throw is true and the "max_redirects"
	 *  option has been reached
	 * @throws ServerExceptionInterface On a 5xx when $throw is true
	 * @throws SymfonyClientExceptionInterface On a 4xx when $throw is true
	 * @throws TransportExceptionInterface When a network error occurs
	 */
	public function getHeaders(bool $throw = true): array
	{
		if ($this->getAdapter() === self::PSR18_ADAPTER) {
			$msg = $this->formatPsrExceptionMessage();
			throw new Psr_Exception($msg);
		}
		else {
			$headers = $this->getResponse()->getHeaders($throw);
		}
		return $headers;
	}

	//--------------------------------------------------------------------------------- getHttpClient
	/**
	 * Returns the HTTP client handler.
	 *
	 * @return HttpClientInterface
	 */
	public function getHttpClient(): HttpClientInterface
	{
		return $this->http_client;
	}

	//--------------------------------------------------------------------------------------- getInfo
	/**
	 * Returns info coming from the transport layer.
	 * C.f. official Symfony doc
	 *
	 * @return mixed An array of all available info, or one of them when $type is
	 *               provided, or null when an unsupported type is requested
	 */
	public function getInfo(string $type = null): mixed
	{
		if ($this->getAdapter() === self::PSR18_ADAPTER) {
			$msg = $this->formatPsrExceptionMessage();
			throw new Psr_Exception($msg);
		}
		else {
			$info = $this->getResponse()->getInfo($type);
		}
		return $info;
	}

	//------------------------------------------------------------------------------- getMaxRedirects
	/**
	 * @return int
	 *
	 */
	public function getMaxRedirects(): int
	{
		return $this->max_redirects;
	}

	//---------------------------------------------------------------------------- getQueryParameters
	/**
	 * @return string
	 */
	public function getQueryParameters(): string
	{
		return $this->query_parameters;
	}

	//----------------------------------------------------------------------------------- getResponse
	/**
	 * @return SymfonyResponseInterface|PsrResponseInterface
	 */
	public function getResponse(): SymfonyResponseInterface|PsrResponseInterface
	{
		return $this->response;
	}

	//--------------------------------------------------------------------------------- getStatusCode
	/**
	 * @return int
	 * @throws TransportExceptionInterface
	 */
	public function getStatusCode(): int
	{
		if ($this->getAdapter() === self::PSR18_ADAPTER) {
			$msg = $this->formatPsrExceptionMessage();
			throw new Psr_Exception($msg);
		}
		else {
			return $this->getResponse()->getStatusCode();
		}
	}

	//------------------------------------------------------------------------ instantiatePsr18Client
	/**
	 * @param $http_client
	 * @return Psr18Client
	 */
	private function instantiatePsr18Client($http_client): Psr18Client
	{
		return new Psr18Client($http_client);
	}

	//------------------------------------------------------------------------------------------ post
	/**
	 * Sends a POST request
	 * @param $url
	 * @param $data : Cf official Symfony docs to send raw data, keys/values data or something else
	 * @throws TransportExceptionInterface
	 * @throws PsrClientExceptionInterface
	 */
	public function post($url, $data): void
	{
		if ($this->getAdapter() === self::PSR18_ADAPTER) {
			$http_client = $this->instantiatePsr18Client($this->getHttpClient());
			$request = $http_client->createRequest(self::POST, $url);
			$response = $http_client->sendRequest($request);
		}
		else {
			$response = $this->getHttpClient()->request(self::POST, $url, [
				'body' => $data
			]);
		}
		$this->setResponse($response);
	}

	//------------------------------------------------------------------------------------ setAdapter
	/**
	 * Sets an adapter tu use other Http Client (for instance : Psr18client)
	 * @param $adapter string
	 */
	public function setAdapter(string $adapter): void
	{
		$this->adapter = $adapter;
	}

	//-------------------------------------------------------------------------------- setContentType
	/**
	 * Sets the content type.
	 *
	 * @param string $content_type
	 */
	public function setContentType(string $content_type): void
	{
		$this->content_type = $content_type;
	}

	//------------------------------------------------------------------------------- setMaxRedirects
	/**
	 * @param $max_redirects int
	 */
	public function setMaxRedirects(int $max_redirects): void
	{
		if ($this->getAdapter() === self::PSR18_ADAPTER) {
			$msg = $this->formatHttpClientLogicExceptionMessage('max redirects');
			$this->max_redirects = -1;
			throw new Http_Client_Logic_Exception($msg);
		}
		else {
			$this->max_redirects = $max_redirects;
		}
	}

	//---------------------------------------------------------------------------- setQueryParameters
	/**
	 * @param $query_parameters string
	 */
	public function setQueryParameters(string $query_parameters): void
	{
		if ($this->getAdapter() === self::PSR18_ADAPTER) {
			$msg = $this->formatHttpClientLogicExceptionMessage('query parameters');
			$this->query_parameters = null;
			throw new Http_Client_Logic_Exception($msg);
		}
		else {
			$this->query_parameters = $query_parameters;
		}
	}

	//--------------------------------------------------------------------------------------- toArray
	/**
	 * @param $response SymfonyResponseInterface|PsrResponseInterface
	 */
	public function setResponse(SymfonyResponseInterface|PsrResponseInterface $response): void
	{
		$this->response = $response;
	}

	/**
	 * Gets the response body decoded as array, typically from a JSON payload.
	 *
	 * @param bool $throw Whether an exception should be thrown on 3/4/5xx status codes
	 *
	 * @throws DecodingExceptionInterface    When the body cannot be decoded to an array
	 * @throws TransportExceptionInterface   When a network error occurs
	 * @throws RedirectionExceptionInterface On a 3xx when $throw is true and the "max_redirects"
	 *  option has been reached
	 * @throws SymfonyClientExceptionInterface      On a 4xx when $throw is true
	 * @throws ServerExceptionInterface      On a 5xx when $throw is true
	 */
	public function toArray(bool $throw = true): array
	{
		if ($this->getAdapter() === self::PSR18_ADAPTER) {
			$msg = $this->formatPsrExceptionMessage();
			throw new Psr_Exception($msg);
		}
		return $this->getResponse()->toArray($throw);
	}


}
