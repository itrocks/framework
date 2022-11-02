<?php
namespace ITRocks\Framework\Http;

/**
 * HTTP response codes
 */
class Response
{

	//--------------------------------------------------------------------------- HTTP response codes
	const ACCEPTED = 202;
	const BAD_GATEWAY = 502;
	const BAD_REQUEST = 400;
	const CONFLICT = 409;
	const CONTINUE = 100;
	const CREATED = 201;
	const EXPECTATION_FAILED = 417;
	const FORBIDDEN = 403;
	const FOUND = 302;
	const GATEWAY_TIMEOUT = 504;
	const GONE = 410;
	const INTERNAL_SERVER_ERROR = 500;
	const LENGTH_REQUIRED = 411;
	const METHOD_NOT_ALLOWED = 405;
	const MOVED_PERMANENTLY = 301;
	const MULTIPLE_CHOICES = 300;
	const NO_CONTENT = 204;
	const NON_AUTHORITATIVE_INFORMATION = 203;
	const NOT_ACCEPTABLE = 406;
	const NOT_FOUND = 404;
	const NOT_IMPLEMENTED = 501;
	const NOT_MODIFIED = 304;
	const OK = 200;
	const PARTIAL_CONTENT = 206;
	const PAYMENT_REQUIRED = 402;
	const PRECONDITION_FAILED = 412;
	const PROXY_AUTHENTICATION_REQUIRED = 407;
	const REQUEST_ENTITY_TOO_LARGE = 413;
	const REQUEST_TIMEOUT = 408;
	const REQUEST_URI_TOO_LONG = 414;
	const REQUESTED_RANGE_NOT_SATISFIABLE = 416;
	const RESET_CONTENT = 205;
	const SEE_OTHER = 303;
	const SERVICE_UNAVAILABLE = 503;
	const SWITCHING_PROTOCOLS = 101;
	const TEMPORARY_REDIRECT = 307;
	const UNAUTHORIZED = 401;
	const UNSUPPORTED_MEDIA_TYPE = 415;
	const UNUSED = 306;
	const USE_PROXY = 305;
	const VERSION_NOT_SUPPORTED = 505;

}
