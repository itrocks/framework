<?php
namespace ITrocks\Framework\View\Json;

use ITRocks\Framework\Http\Response;

/**
 * Format a json error response based on OAuth2 specification
 * http://tools.ietf.org/html/rfc6749#page-45
  */
class Json_Error_Response
{

	//----------------------------------------------------------------------------------------- $code
	/**
	 * @var $code integer
	 */
	private $code;

	//---------------------------------------------------------------------------------- $description
	/**
	 * @var $description string
	 */
	private $description;

	//---------------------------------------------------------------------------------------- $error
	/**
	 * @var $error string
	 */
	private $error;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * Json_Error_Response constructor.
	 * @param $code integer
	 * @param $description string
	 * @param $error null
	 */
	public function __construct($code, $description='', $error=null)
	{
		$this->code = $code;
		$this->error = !$error ? $this->getError() : $error;
		$this->setDescription($description);
	}

	//-------------------------------------------------------------------------------------- getError
	/**
	 * get the default error message
	 */
	private function getError() {
		$error = '';
		switch ($this->code) {
			case Response::BAD_REQUEST:
				$error = 'invalid_request';
				break;
			case Response::FORBIDDEN:
				$error = 'not_allowed';
				break;
			case Response::INTERNAL_SERVER_ERROR:
				$error = 'error_description';
				break;
			case Response::METHOD_NOT_ALLOWED:
				$error = 'method_does_not_make_sense';
				break;
			case Response::NOT_ACCEPTABLE:
				$error = 'not_acceptable';
				break;
			case Response::NOT_FOUND:
				$error = 'not_found';
				break;
			case Response::UNAUTHORIZED:
				$error = 'no_credentials';
				break;
		}
		return $error;
	}

	//--------------------------------------------------------------------------------- getHeaderCode
	/**
	 * return formatted header http
	 *
	 * @return string
	 */
	private function getHeaderCode()
	{
		$header_code = null;
		switch($this->code) {
			case Response::FORBIDDEN:
				$header_code = 'Forbidden';
				break;
			case Response::INTERNAL_SERVER_ERROR:
				$header_code = 'Internal server error';
				break;
			case Response::METHOD_NOT_ALLOWED:
				$header_code = 'Method not allowed';
				break;
			case Response::NOT_ACCEPTABLE:
				$header_code = 'Not Acceptable';
				break;
			case Response::NOT_FOUND:
				$header_code = 'Not Found';
				break;
			case Response::UNAUTHORIZED:
				$header_code = 'Unauthorized';
				break;
		}

		return 'HTTP/1.1 '. (string)$this->code . SP . $header_code;
	}

	//----------------------------------------------------------------------------------- getResponse
	/**
	 * Return json encode error response
	 *
	 * @return string
	 */
	public function getResponse() {
		header($this->getHeaderCode(), true, $this->code);
		if (Engine::acceptJson()) {
			header('Content-Type: application/json; charset=utf-8');
		}
		return \GuzzleHttp\json_encode([
			'error'             => $this->error,
			'error_description' => $this->description,
			'status_code'       => $this->code
		]);
	}

	//-------------------------------------------------------------------------------- setDescription
	/**
	 * Set the default error description
	 *
	 * @param $description string
	 */
	private function setDescription($description) {

		if (!$description) {
			switch ($this->code) {
				case Response::FORBIDDEN:
					$this->description = "You're not allowed to perform this request";
					break;
				case Response::INTERNAL_SERVER_ERROR:
					$this->description = 'Oops! Something went wrong...';
					break;
				case Response::UNAUTHORIZED:
					$this->description = 'This resource is under permission, you must be authenticated with'
						. SP . 'the right rights to have access to it';
					break;
			}
		}
		else {
			$this->description = $description;
		}
	}

}
