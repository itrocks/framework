/**
 * The App object stores application data for general use (locale, paths)
 *
 * @param PHPSESSID   string
 * @param uri_root    string
 * @param script_name string
 * @param project_uri string
 * @param language    string
 * @param date_format string
 * @param use_cookies boolean
 * @param user_id     integer
 * @constructor
 */
App = function(
	PHPSESSID, uri_root, script_name, project_uri, language, date_format, use_cookies, user_id
) {

	//----------------------------------------------------------------------------------- date_format
	/**
	 * @const string
	 */
	this.date_format = date_format

	//-------------------------------------------------------------------------------------- language
	/**
	 * @const string
	 */
	this.language = language

	//------------------------------------------------------------------------------------- PHPSESSID
	/**
	 * @const string
	 */
	this.PHPSESSID = PHPSESSID

	//----------------------------------------------------------------------------------- project_uri
	/**
	 * @example '/the/project/root/uri'
	 * @const string
	 */
	this.project_uri = project_uri

	//----------------------------------------------------------------------------------- script_name
	/**
	 * @example 'itrocks'
	 * @const string
	 */
	this.script_name = script_name

	//-------------------------------------------------------------------------------------- uri_base
	/**
	 * @example '/a/folder/itrocks'
	 * @type string
	 */
	this.uri_base = uri_root + script_name

	//-------------------------------------------------------------------------------------- uri_root
	/**
	 * @example '/a/folder/'
	 * @const string
	 */
	this.uri_root = uri_root

	//----------------------------------------------------------------------------------- use_cookies
	/**
	 * @type boolean
	 */
	this.use_cookies = use_cookies

	//--------------------------------------------------------------------------------------- user_id
	/**
	 *  @example 1
	 *  @const integer|null
	 */
	this.user_id = (user_id === undefined) ? null : user_id

}

//------------------------------------------------------------------------------------------ andSID
/**
 * Adds session id expression to a given URI
 *
 * @return string the URI with PHPSESSID request argument if session is not stored into a cookie
 */
App.prototype.addSID = function(uri)
{
	return uri + ((uri.indexOf('?') > -1) ? this.andSID() : this.askSID())
}

//------------------------------------------------------------------------------------------ andSID
/**
 * Gets session id expression
 *
 * @return string '' if session id is stored into a cookie, else '&PHPSESSID=xxxx'
 */
App.prototype.andSID = function()
{
	return this.use_cookies ? '' : ('&PHPSESSID=' + this.PHPSESSID)
}

//------------------------------------------------------------------------------------------ askAnd
/**
 * Gets href parameter expression, with '?' or '&'
 *
 * @param uri    base uri, with ot without existing params, eg '/an/uri' or '/an/uri?with=params'
 * @param params more params, eg 'key1=value1&key2=value2'
 * @return string uri with the new params appended
 */
App.prototype.askAnd = function(uri, params)
{
	return params ? (uri + ((uri.indexOf('?') > -1) ? '&' : '?') + params) : uri
}

//------------------------------------------------------------------------------------------ askSID
/**
 * Gets session id expression
 *
 * @return string '' if session id is stored into a cookie, else '?PHPSESSID=xxxx'
 */
App.prototype.askSID = function()
{
	return this.use_cookies ? '' : ('?PHPSESSID=' + this.PHPSESSID)
}

/**
 * Gets session id expression
 *
 * @return string '?' if session id is stored into a cookie, else '?PHPSESSID=xxxx&'
 */
App.prototype.askSIDand = function()
{
	return this.use_cookies ? '?' : ('?PHPSESSID=' + this.PHPSESSID + '&')
}
