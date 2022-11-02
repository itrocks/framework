<?php
namespace ITRocks\Framework\Http;

/**
 * Http Proxy
 *
 * TODO SSL
 * TODO cookies translations when containing path= and domain= restrictions (eg it.rocks)
 */
class Proxy
{

	//-------------------------------------------------------------------------------------- STANDARD
	/**
	 * Use new Proxy(Proxy::STANDARD) to get a proxy ready with standard headers and parameters
	 *
	 * @var null
	 */
	const STANDARD = null;

	//------------------------------------------------------------------------------- $accept_charset
	/**
	 * Accept charset header
	 * Unset this to avoid send it
	 *
	 * @var string
	 */
	public string $accept_charset = 'ISO-8859-1,utf-8;q=0.7,*;q=0.7';

	//------------------------------------------------------------------------------------ $automatic
	/**
	 * In automatic mode, set when calling the constructor :
	 * - $this->request_headers are automatically filled with apache_request_headers()
	 * - $this->response_headers are used for header() calls after a positive request response
	 *
	 * @var boolean
	 */
	public bool $automatic = true;

	//----------------------------------------------------------------------------------------- $data
	/**
	 * The request data to send
	 *
	 * @var string|string[]
	 */
	public array|string $data = [];

	//---------------------------------------------------------------------------------------- $errno
	/**
	 * The last error number
	 *
	 * @var integer
	 */
	public int $errno;

	//---------------------------------------------------------------------------------------- $error
	/**
	 * The last error message
	 *
	 * @var string
	 */
	public string $error;

	//--------------------------------------------------------------------------------------- $method
	/**
	 * The method to use for the request
	 *
	 * @values GET, POST
	 * @var string
	 */
	public string $method = Http::GET;

	//------------------------------------------------------------------------------ $request_headers
	/**
	 * The headers of the HTTP request
	 * The key is the name of the header, the value is its value
	 *
	 * @var string[]
	 */
	public array $request_headers = [];

	//------------------------------------------------------------------------------------- $response
	/**
	 * The content of the HTTP response
	 *
	 * @var string
	 */
	private string $response;

	//----------------------------------------------------------------------------- $response_headers
	/**
	 * The headers of the HTTP response
	 * The key is a numeric, the value is 'Header-Name: value'
	 *
	 * @var string[]
	 */
	public array $response_headers;

	//---------------------------------------------------------------------------------- $retry_count
	/**
	 * Retry if there is an error during reading the response of the call
	 *
	 * @var integer
	 */
	public int $retry_count = 0;

	//---------------------------------------------------------------------------------- $retry_delay
	/**
	 * Delay between each retry (default : 1000ms = 1s)
	 *
	 * @var integer milliseconds
	 */
	public int $retry_delay = 1000;

	//------------------------------------------------------------------------------------------ $url
	/**
	 * The URL to call for the HTTP request
	 *
	 * @var string
	 */
	public string $url = '';

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $automatic boolean|string
	 */
	public function __construct(bool|string $automatic = true)
	{
		$this->automatic = ($automatic === true);
		if (is_string($automatic)) {
			$this->setStandardRequestHeaders($automatic);
		}
		elseif ($automatic) {
			$this->data = empty($_POST) ? [] : $_POST;
			$this->method = isset($_SERVER['REQUEST_METHOD'])
				? (($_SERVER['REQUEST_METHOD'] === Http::POST) ? Http::POST : Http::GET)
				: (empty($_POST) ? Http::GET : Http::POST);
			$this->request_headers = apache_request_headers();
		}
	}

	//------------------------------------------------------------------------------------ dataEncode
	/**
	 * Return form content as an url component like var=val&var2=val2&...
	 * If prefix is given, this will return prefix[var]=val&prefix[var2]=val2&...
	 *
	 * @param $array  array|string data
	 * @param $prefix string|null for internal use only (prefix on recursion)
	 * @return string
	 */
	private function dataEncode(array|string $array, string $prefix = null) : string
	{
		$url = '';
		if (!is_array($array)) {
			$url .= "&$prefix=$array";
		}
		else {
			foreach ($array as $key => $val) {
				if (is_array($val)) {
					$url .= '&' . $this->dataEncode($val, $prefix ? ($prefix . "[$key]") : $key);
				}
				elseif ($prefix) {
					$url .= '&' . $prefix . "[$key]=$val";
				}
				else {
					$url .= "&$key=$val";
				}
			}
		}
		return substr($url, 1);
	}

	//--------------------------------------------------------------------------------- debugFullInfo
	/**
	 * Display debugging information
	 */
	public function debugFullInfo()
	{
		echo '<pre>REQUEST_HEADERS = '  . print_r($this->request_headers, true)  . '</pre>';
		echo '<pre>RESPONSE_HEADERS = ' . print_r($this->response_headers, true) . '</pre>';
		if (isset($_POST))   echo '<pre>_POST = '   . print_r($_POST, true)   . '</pre>';
		if (isset($_SERVER)) echo '<pre>_SERVER = ' . print_r($_SERVER, true) . '</pre>';
	}

	//--------------------------------------------------------------------------------- debugRedirect
	/**
	 * Call this instead of sendResponse() to send headers and response with redirections replaced
	 * by displayed links
	 *
	 * @param $buffer string
	 */
	public function debugRedirect(string $buffer)
	{
		if ($location = $this->getResponseHeader('Location')) {
			$this->setResponse(
				'<a href=' . DQ . $location . DQ . '>#REDIRECT ' . $location . '</a>' . $buffer
			);
			$this->removeResponseHeader('Location');
		}
		$this->sendResponseHeaders();
		if ($location) {
			echo '<pre>' . print_r($this->request_headers, true)  . '</pre>';
			echo '<pre>' . print_r($this->response_headers, true) . '</pre>';
		}
		$this->sendResponse(false);
	}

	//----------------------------------------------------------------------------- getRequestCookies
	/**
	 * Get HTTP response cookies
	 *
	 * @param $deleted boolean if true, get deleted cookies too
	 * @return Cookie[]
	 */
	public function getRequestCookies(bool $deleted = true) : array
	{
		$cookies = [];
		if (isset($this->request_headers['Cookie'])) {
			foreach (explode('; ', $this->request_headers['Cookie']) as $text) {
				$cookie = new Cookie();
				[$cookie->name, $cookie->value] = explode('=', $text);
				if ($deleted || !($cookie->value === 'deleted')) {
					$cookies[] = $cookie;
				}
			}
		}
		return $cookies;
	}

	//----------------------------------------------------------------------------------- getResponse
	/**
	 * Get HTTP response
	 *
	 * @return string
	 */
	public function getResponse() : string
	{
		$response = $this->response;
		if ($this->getResponseHeader('Transfer-Encoding') === 'chunked') {
			$new_response = '';
			$length = strlen($response);
			$position = 0;
			do {
				$size_position = $position;
				$position = strpos($response, CR . LF, $position);
				if ($position === false) {
					$position = $length;
				}
				$size = hexdec(substr($response, $size_position, $position - $size_position));
				$position += 2;
				$new_response .= substr($response, $position, $size);
				$position += $size + 2;
			} while ($size && ($position < $length));
			$response = $new_response;
		}
		return ($this->getResponseHeader('Content-Encoding') === 'gzip')
			? gzinflate(substr($response, 10, -8))
			: $response;
	}

	//---------------------------------------------------------------------------- getResponseCookies
	/**
	 * Get HTTP response cookies
	 *
	 * @param $deleted boolean if true, get deleted cookies too
	 * @return Cookie[]
	 */
	public function getResponseCookies(bool $deleted = true) : array
	{
		$cookies = [];
		foreach ($this->response_headers as $header) {
			$pos = strpos($header, ': ');
			if ($pos !== false) {
				$name  = substr($header, 0, $pos);
				$value = substr($header, $pos + 2);
				if ($name === 'Set-Cookie') {
					$cookie = Cookie::fromString($value);
					if ($deleted || !($cookie->value === 'deleted')) {
						$cookies[] = $cookie;
					}
				}
			}
		}
		return $cookies;
	}

	//----------------------------------------------------------------------------- getResponseHeader
	/**
	 * Get an HTTP response header from $response_headers
	 *
	 * @param $header string
	 * @return ?string
	 */
	public function getResponseHeader(string $header) : ?string
	{
		foreach ($this->response_headers as $response_header) {
			$length = strlen($header) + 2;
			if (substr($response_header, 0, $length) === ($header . ': ')) {
				return substr($response_header, $length);
			}
		}
		return null;
	}

	//-------------------------------------------------------------------------- removeResponseHeader
	/**
	 * Remove response header having name $header
	 *
	 * @param $header string
	 */
	public function removeResponseHeader(string $header)
	{
		$header .= ': ';
		$length = strlen($header);
		foreach ($this->response_headers as $key => $response_header) {
			if (substr($response_header, 0, $length ) === $header) {
				unset($this->response_headers[$key]);
			}
		}
	}

	//--------------------------------------------------------------------------------------- request
	/**
	 * Call HTTP request
	 *
	 * @param $url    string|null
	 * @param $data   string|string[]|null
	 * @param $method string|null GET, POST
	 * @param $retry  integer|null
	 * @return boolean true if job done, false if any error occurred
	 */
	public function request(
		string $url = null, array|string $data = null, string $method = null, int $retry = null
	) : bool
	{
		if (isset($url))    $this->url    = $url;
		if (isset($method)) $this->method = $method;
		if (isset($data))   $this->data   = $data;
		if (!isset($retry)) $retry        = $this->retry_count;
		// connection
		$url = parse_url($this->url);
		if (!isset($url['path'])) {
			$url['path'] = SL;
		}
		$host = $url['host'];
		/** @noinspection PhpUsageOfSilenceOperatorInspection managed */
		$f = @fsockopen(
			(($url['scheme'] === 'https') ? 'ssl://' : '') . $host,
			$url['port'] ?: (($url['scheme'] === 'https') ? 443 : 80),
			$errno, $error, 30
		);
		if (!$f) {
			$this->errno            = $errno;
			$this->error            = $error;
			$this->response_headers = [];
			$this->response         = '';
			return false;
		}
		// parse and write request
		$data = $this->dataEncode($this->data);
		if ($this->method === Http::GET) {
			if ($data && !str_contains($url['path'], '?')) {
				$data = '?' . $data;
			}
			fputs($f, 'GET ' . $url['path'] . ($data ?: '') . ' HTTP/1.1' . CR . LF);
		}
		else {
			fputs(
				$f,
				'POST ' . $url['path'] . (empty($url['query']) ? '' : ('?' . $url['query']))
				. ' HTTP/1.1' . CR . LF
			);
		}
		fputs($f, 'Host: ' . $host . CR . LF);
		if ($this->accept_charset) {
			fputs($f, 'Accept-Charset: ' . $this->accept_charset . CR . LF);
		}
		foreach ($this->request_headers as $header => $value) {
			if ($header === 'Content-Length') {
				if ($this->method === Http::POST) {
					fputs($f, 'Content-Length: ' . strlen($data) . CR . LF);
				}
			}
			elseif (!in_array($header, ['Connection', 'Host'])) {
				fputs($f, $header . ': ' . $value . CR . LF);
			}
		}
		fputs($f, 'Connection: close' . CR . LF . CR . LF);
		if ($this->method === Http::POST) {
			fputs($f, $data);
		}
		// read and parse response
		$result = '';
		while (!feof($f)) {
			/** @noinspection PhpUsageOfSilenceOperatorInspection retry if connexion reset by peer */
			$result .= @fgets($f, 128);
		}
		fclose($f);
		if (str_contains($result, CR . LF . CR . LF)) {
			[$headers, $response] = explode(CR . LF . CR . LF, $result, 2);
		}
		else {
			$headers = $response = '';
		}
		$this->response_headers = explode(CR . LF, $headers);
		$this->response         = $response;
		if ($retry && !$headers && !$response) {
			if ($this->retry_delay) {
				usleep($this->retry_delay * 1000);
			}
			trigger_error("$this->url : retry $retry of $this->retry_count");
			$this->request(null, null, null, $retry - 1);
		}
		return true;
	}

	//---------------------------------------------------------------------------------- sendResponse
	/**
	 * Send content from $this->content to PHP standard output
	 *
	 * @param $send_headers boolean if set to false, headers won't be sent before response html source
	 */
	public function sendResponse(bool $send_headers = true)
	{
		if ($send_headers) {
			$this->sendResponseHeaders();
		}
		echo $this->response;
	}

	//--------------------------------------------------------------------------- sendResponseHeaders
	/**
	 * Send response headers from $this->response_headers to PHP header() calls
	 *
	 * @param $only_headers string[]
	 */
	public function sendResponseHeaders(array $only_headers = [])
	{
		$only_headers = array_flip($only_headers);
		foreach ($this->response_headers as $header) {
			if (isset($only_headers[lParse($header, ':')]) || !$only_headers) {
				header($header);
			}
		}
	}

	//----------------------------------------------------------------------------- setRequestCookies
	/**
	 * @param $cookies Cookie[]
	 */
	public function setRequestCookies(array $cookies = [])
	{
		$text = '';
		foreach ($cookies as $cookie) {
			if ($text) $text .= '; ';
			$text .= $cookie->name . '=' . $cookie->value;
		}
		if ($text) {
			$this->request_headers['Cookie'] = $text;
		}
		elseif (isset($this->request_headers['Cookie'])) {
			unset($this->request_headers['Cookie']);
		}
	}

	//----------------------------------------------------------------------------- setRequestHeaders
	/**
	 * @param $headers string[]
	 * @param $reset   boolean
	 */
	public function setRequestHeaders(array $headers = [], bool $reset = false)
	{
		$this->request_headers = $reset ? $headers : array_merge($this->request_headers, $headers);
	}

	//----------------------------------------------------------------------------------- setResponse
	/**
	 * @param $response string
	 */
	public function setResponse(string $response)
	{
		$this->response = ($this->getResponseHeader('Content-Encoding') === 'gzip')
			? gzencode($response)
			: $response;
		$this->setResponseHeader('Content-Length', strlen($this->response));
	}

	//---------------------------------------------------------------------------- setResponseCookies
	/**
	 * @param $cookies Cookie[]
	 * @param $replace boolean
	 */
	public function setResponseCookies(array $cookies = [], bool $replace = true)
	{
		if ($replace) {
			foreach ($this->response_headers as $key => $header) {
				$pos = strpos($header, ': ');
				if ($pos !== false) {
					if (substr($header, 0, $pos) === 'Set-Cookie') {
						unset($this->response_headers[$key]);
					}
				}
			}
		}
		foreach ($cookies as $cookie) {
			$this->response_headers[] = 'Set-Cookie: ' . $cookie;
		}
	}

	//----------------------------------------------------------------------------- setResponseHeader
	/**
	 * @param $header string
	 * @param $value  string
	 * @param $create boolean if true, the header will be created if it did not exist
	 * @return boolean true if one or several headers have been changed or created
	 */
	public function setResponseHeader(string $header, string $value, bool $create = false) : bool
	{
		$found  = false;
		$length = strlen($header) + 2;
		foreach ($this->response_headers as $key => $response_header) {
			if (substr($response_header, 0, $length) === ($header . ': ')) {
				$this->response_headers[$key] = $header . ': ' . $value;
				$found = true;
			}
		}
		if ($create && !$found) {
			$this->response_headers[] = $header . ': ' . $value;
			$found = true;
		}
		return $found;
	}

	//--------------------------------------------------------------------- setStandardRequestHeaders
	/**
	 * Set standard request headers
	 *
	 * @param $method string Http::GET or Http::POST
	 */
	public function setStandardRequestHeaders(string $method = Http::GET)
	{
		$this->method = $method;
		$this->request_headers = [
			'Host'            => $_SERVER['HTTP_HOST']       ?? 'local',
			'User-Agent'      => $_SERVER['HTTP_USER_AGENT'] ?? 'itrocks',
			'Accept'          => 'text/html;q=0.9,*/*;q=0.8',
			'Accept-Language' => 'fr-FR,q=0.8,en-US;q=0.6,en;q=0.4',
			'Accept-Encoding' => 'gzip,deflate',
			'Accept-Charset'  => 'utf-8;q=0.8,ISO-8859-1,utf-8;q=0.7,*;q=0.6'
		];
		if ($method === Http::POST) {
			$this->request_headers['Content-Type'] = 'application/x-www-form-urlencoded';
			// content length will be automatically calculated when calling request()
			$this->request_headers['Content-Length'] = 0;
		}
	}

	//-------------------------------------------------------------------------------- setUrlByPrefix
	/**
	 * Set URL : adds prefix to current uri
	 *
	 * @param $prefix      string
	 * @param $default_uri string default uri if server's PATH_INFO is empty
	 */
	public function setUrlByPrefix(string $prefix, string $default_uri = '')
	{
		$uri = isset($_SERVER['PATH_INFO']) ? substr($_SERVER['PATH_INFO'], 1) : '';
		$this->url = $prefix . ($uri ?: $default_uri);
	}

}
