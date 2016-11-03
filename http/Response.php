<?php
namespace ITRocks\Framework\Http;

/**
 * HTTP response codes
 */
class Response
{

	//-------------------------------------------------------------------------------------- ACCEPTED
	const ACCEPTED = 202;

	//----------------------------------------------------------------------------------- BAD_GATEWAY
	const BAD_GATEWAY = 502;

	//----------------------------------------------------------------------------------- BAD_REQUEST
	const BAD_REQUEST = 400;

	//-------------------------------------------------------------------------------------- CONFLICT
	const CONFLICT = 409;

	//------------------------------------------------------------------------------------- CONTINUE_
	/**
	 * TODO LOWEST Remove _ will crash with php 5.6-, but works with php 7+ : please wait
	 */
	const CONTINUE_ = 100;

	//--------------------------------------------------------------------------------------- CREATED
	const CREATED = 201;

	//---------------------------------------------------------------------------- EXPECTATION_FAILED
	const EXPECTATION_FAILED = 417;

	//------------------------------------------------------------------------------------- FORBIDDEN
	const FORBIDDEN = 403;

	//----------------------------------------------------------------------------------------- FOUND
	const FOUND = 302;

	//------------------------------------------------------------------------------- GATEWAY_TIMEOUT
	const GATEWAY_TIMEOUT = 504;

	//------------------------------------------------------------------------------------------ GONE
	const GONE = 410;

	//------------------------------------------------------------------------- INTERNAL_SERVER_ERROR
	const INTERNAL_SERVER_ERROR = 500;

	//------------------------------------------------------------------------------- LENGTH_REQUIRED
	const LENGTH_REQUIRED = 411;

	//---------------------------------------------------------------------------- METHOD_NOT_ALLOWED
	const METHOD_NOT_ALLOWED = 405;

	//----------------------------------------------------------------------------- MOVED_PERMANENTLY
	const MOVED_PERMANENTLY = 301;

	//------------------------------------------------------------------------------ MULTIPLE_CHOICES
	const MULTIPLE_CHOICES = 300;

	//----------------------------------------------------------------- NON_AUTHORITATIVE_INFORMATION
	const NON_AUTHORITATIVE_INFORMATION = 203;

	//-------------------------------------------------------------------------------- NOT_ACCEPTABLE
	const NOT_ACCEPTABLE = 406;

	//------------------------------------------------------------------------------------- NOT_FOUND
	const NOT_FOUND = 404;

	//------------------------------------------------------------------------------- NOT_IMPLEMENTED
	const NOT_IMPLEMENTED = 501;

	//---------------------------------------------------------------------------------- NOT_MODIFIED
	const NOT_MODIFIED = 304;

	//------------------------------------------------------------------------------------ NO_CONTENT
	const NO_CONTENT = 204;

	//-------------------------------------------------------------------------------------------- OK
	const OK = 200;

	//------------------------------------------------------------------------------- PARTIAL_CONTENT
	const PARTIAL_CONTENT = 206;

	//------------------------------------------------------------------------------ PAYMENT_REQUIRED
	const PAYMENT_REQUIRED = 402;

	//--------------------------------------------------------------------------- PRECONDITION_FAILED
	const PRECONDITION_FAILED = 412;

	//----------------------------------------------------------------- PROXY_AUTHENTICATION_REQUIRED
	const PROXY_AUTHENTICATION_REQUIRED = 407;

	//--------------------------------------------------------------- REQUESTED_RANGE_NOT_SATISFIABLE
	const REQUESTED_RANGE_NOT_SATISFIABLE = 416;

	//---------------------------------------------------------------------- REQUEST_ENTITY_TOO_LARGE
	const REQUEST_ENTITY_TOO_LARGE = 413;

	//------------------------------------------------------------------------------- REQUEST_TIMEOUT
	const REQUEST_TIMEOUT = 408;

	//-------------------------------------------------------------------------- REQUEST_URI_TOO_LONG
	const REQUEST_URI_TOO_LONG = 414;

	//--------------------------------------------------------------------------------- RESET_CONTENT
	const RESET_CONTENT = 205;

	//------------------------------------------------------------------------------------- SEE_OTHER
	const SEE_OTHER = 303;

	//--------------------------------------------------------------------------- SERVICE_UNAVAILABLE
	const SERVICE_UNAVAILABLE = 503;

	//--------------------------------------------------------------------------- SWITCHING_PROTOCOLS
	const SWITCHING_PROTOCOLS = 101;

	//---------------------------------------------------------------------------- TEMPORARY_REDIRECT
	const TEMPORARY_REDIRECT = 307;

	//---------------------------------------------------------------------------------- UNAUTHORIZED
	const UNAUTHORIZED = 401;

	//------------------------------------------------------------------------ UNSUPPORTED_MEDIA_TYPE
	const UNSUPPORTED_MEDIA_TYPE = 415;

	//---------------------------------------------------------------------------------------- UNUSED
	const UNUSED = 306;

	//------------------------------------------------------------------------------------- USE_PROXY
	const USE_PROXY = 305;

	//------------------------------------------------------------------------- VERSION_NOT_SUPPORTED
	const VERSION_NOT_SUPPORTED = 505;

}
