<?php
namespace SAF\Framework\Http;

/**
 * Http Proxy
 *
 * TODO SSL
 * TODO cookies translations when containing path= and domain= restrictions (ie on www.automotoboutic.com)
 */
class Proxy
{

	//---------------------------------------------------------------- __construct STANDARD parameter
	/**
	 * Use new Proxy(Proxy::STANDARD) to get a proxy ready with standard headers and parameters
	 * @var null
	 */
	const STANDARD = null;

	//------------------------------------------------------------------------------------ $automatic
	/**
	 * In automatic mode, set when calling the constructor :
	 * - $this->request_headers are automatically filled with apache_request_headers()
	 * - $this->response_headers are used for header() calls after a positive request response
	 *
	 * @var boolean
	 */
	public $automatic = true;

	//----------------------------------------------------------------------------------------- $data
	/**
	 * The request data to send
	 *
	 * @var string[]
	 */
	public $data = [];

	//---------------------------------------------------------------------------------------- $errno
	/**
	 * The last error number
	 *
	 * @var integer
	 */
	public $errno;

	//---------------------------------------------------------------------------------------- $error
	/**
	 * The last error message
	 *
	 * @var string
	 */
	public $error;

	//--------------------------------------------------------------------------------------- $method
	/**
	 * The method to use for the request
	 *
	 * @values GET, POST
	 * @var string
	 */
	public $method = Http::GET;

	//------------------------------------------------------------------------------ $request_headers
	/**
	 * The headers of the HTTP request
	 * The key is the name of the header, the value is its value
	 *
	 * @var string[]
	 */
	public $request_headers = [];

	//------------------------------------------------------------------------------------- $response
	/**
	 * The content of the HTTP response
	 *
	 * @var string
	 */
	private $response;

	//----------------------------------------------------------------------------- $response_headers
	/**
	 * The headers of the HTTP response
	 * The key is a numeric, the value is 'Header-Name: value'
	 *
	 * @var string[]
	 */
	public $response_headers;

	//------------------------------------------------------------------------------------------ $url
	/**
	 * The URL to call for the HTTP request
	 *
	 * @var string
	 */
	public $url = '';

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $automatic boolean|string
	 */
	public function __construct($automatic = true)
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

	//----------------------------------------------------------------------------- getRequestCookies
	/**
	 * Get HTTP response cookies
	 *
	 * @param $deleted boolean if true, get deleted cookies too
	 * @return Cookie[]
	 */
	public function getRequestCookies($deleted = true)
	{
		$cookies = [];
		if (isset($this->request_headers['Cookie'])) {
			foreach (explode('; ', $this->request_headers['Cookie']) as $text) {
				$cookie = new Cookie();
				list($cookie->name, $cookie->value) = explode('=', $text);
				if ($deleted || !($cookie->value == 'deleted')) {
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
	public function getResponse()
	{
		$response = $this->response;
		if ($this->getResponseHeader('Transfer-Encoding') == 'chunked') {
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
		$response = ($this->getResponseHeader('Content-Encoding') === 'gzip')
			? gzinflate(substr($response, 10, -8))
			: $response;
		return $response;
	}

	//---------------------------------------------------------------------------- getResponseCookies
	/**
	 * Get HTTP response cookies
	 *
	 * @param $deleted boolean if true, get deleted cookies too
	 * @return Cookie[]
	 */
	public function getResponseCookies($deleted = true)
	{
		$cookies = [];
		foreach ($this->response_headers as $header) {
			$pos = strpos($header, ': ');
			if ($pos !== false) {
				$name = substr($header, 0, $pos);
				$value = substr($header, $pos + 2);
				if ($name == 'Set-Cookie') {
					$cookie = new Cookie();
					$cookie->fromString($value);
					if ($deleted || !($cookie->value == 'deleted')) {
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
	 * @return string
	 */
	public function getResponseHeader($header)
	{
		foreach ($this->response_headers as $response_header) {
			$length = strlen($header) + 2;
			if (substr($response_header, 0, $length) === ($header . ': ')) {
				return substr($response_header, $length);
			}
		}
		return null;
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
	 */
	public function debugRedirect(&$buffer)
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

	//-------------------------------------------------------------------------- removeResponseHeader
	/**
	 * Remove reponse header having name $header
	 *
	 * @param $header string
	 */
	public function removeResponseHeader($header)
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
	 * @param $url    string
	 * @param $data   string[]
	 * @param $method string GET, POST
	 * @return boolean true if job done, false if any error occurred
	 */
	public function request($url = null, $data = null, $method = null)
	{
		if (isset($url))    $this->url    = $url;
		if (isset($method)) $this->method = $method;
		if (isset($data))   $this->data   = $data;
		// connection
		$url = parse_url($this->url);
		$host = $url['host'];
		$f = fsockopen(
			(($url['scheme'] == 'https') ? 'ssl://' : '') . $host,
			($url['scheme'] == 'https') ? 443 : 80,
			$errno, $error, 30
		);
		if ($f) {
			// parse and write request
			$data = '';
			foreach ($this->data as $key => $value) {
				$data .= urlencode($key) . '=' . urlencode($value) . '&';
			}
			$data = substr($data, 0, -1);
			if ($this->method === Http::GET) {
				if ($data && !strpos($url['path'], '?')) {
					$data = '?' . $data;
				}
				fputs($f, 'GET ' . $url['path'] . ($data ? $data : '') . ' HTTP/1.1' . CR . LF);
			}
			else {
				fputs($f, 'POST ' . $url['path'] . ' HTTP/1.1' . CR . LF);
			}
			fputs($f, 'Host: ' . $host . CR . LF);
			//fputs($f, 'X-Forwarded-For: ' . $_SERVER['REMOTE_ADDR'] . CR . LF);
			fputs($f, 'Accept-Charset: ISO-8859-1,utf-8;q=0.7,*;q=0.7' . CR . LF);
			foreach ($this->request_headers as $header => $value) {
				if ($header == 'Content-Length') {
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
				$result .= fgets($f, 128);
			}
			fclose($f);
			list($headers, $response) = explode(CR . LF . CR . LF, $result, 2);
			$this->response_headers   = explode(CR . LF, $headers);
			$this->response = $response;
			return true;
		}
		else {
			$this->errno = $errno;
			$this->error = $error;
			$this->response_headers = [];
			$this->response = '';
			return false;
		}
	}

	//---------------------------------------------------------------------------------- sendResponse
	/**
	 * Send content from $this->content to PHP standard output
	 *
	 * @param $send_headers boolean if set to false, headers won't be sent before response html source
	 */
	public function sendResponse($send_headers = true)
	{
		if ($send_headers) {
			$this->sendResponseHeaders();
		}
		echo $this->response;
	}

	//--------------------------------------------------------------------------- sendResponseHeaders
	/**
	 * Send response headers from $this->response_headers to PHP header() calls
	 */
	public function sendResponseHeaders()
	{
		foreach ($this->response_headers as $header) {
			header($header);
		}
	}

	//----------------------------------------------------------------------------- setRequestCookies
	/**
	 * @param $cookies Cookie[]
	 */
	public function setRequestCookies($cookies = [])
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
	public function setRequestHeaders($headers = [], $reset = false)
	{
		$this->request_headers = $reset ? $headers : array_merge($this->request_headers, $headers);
	}

	//----------------------------------------------------------------------------------- setResponse
	/**
	 * @param $response string
	 */
	public function setResponse($response)
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
	public function setResponseCookies($cookies = [], $replace = true)
	{
		if ($replace) {
			foreach ($this->response_headers as $key => $header) {
				$pos = strpos($header, ': ');
				if ($pos !== false) {
					if (substr($header, 0, $pos) == 'Set-Cookie') {
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
	public function setResponseHeader($header, $value, $create = false)
	{
		$found = false;
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
	 * @param string $method Http::GET or Http::POST
	 */
	public function setStandardRequestHeaders($method = Http::GET)
	{
		$this->method = $method;
		$this->request_headers = [
			'Host'            => isset($_SERVER['HTTP_HOST'])       ? $_SERVER['HTTP_HOST'] : 'local',
			'User-Agent'      => isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : 'saf',
			'Accept'          => 'text/html;q=0.9,*/*;q=0.8',
			'Accept-Language' => 'fr-FR,q=0.8,en-US;q=0.6,en;q=0.4',
			'Accept-Encoding' => 'gzip,deflate',
			'Accept-Charset'  => 'utf-8;q=0.8,ISO-8859-1,utf-8;q=0.7,*;q=0.6'
		];
		if ($method == Http::POST) {
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
	public function setUrlByPrefix($prefix, $default_uri = '')
	{
		$uri = isset($_SERVER['PATH_INFO']) ? substr($_SERVER['PATH_INFO'], 1) : '';
		$this->url = $prefix . ($uri ? $uri : $default_uri);
	}

}
