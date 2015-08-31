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
 * @constructor
 */
App = function(PHPSESSID, uri_root, script_name, project_uri, language, date_format, use_cookies)
{

	//----------------------------------------------------------------------------------- date_format
	/**
	 * @var string
	 */
	this.date_format = date_format;

	//-------------------------------------------------------------------------------------- language
	/**
	 * @var string
	 */
	this.language = language;

	//------------------------------------------------------------------------------------- PHPSESSID
	/**
	 * @var string
	 */
	this.PHPSESSID = PHPSESSID;

	//----------------------------------------------------------------------------------- project_uri
	/**
	 * @example '/the/project/root/uri'
	 * @var string
	 */
	this.project_uri = project_uri;

	//----------------------------------------------------------------------------------- script_name
	/**
	 * @example 'saf'
	 * @var string
	 */
	this.script_name = script_name;

	//-------------------------------------------------------------------------------------- uri_base
	/**
	 * @example '/a/folder/saf'
	 * @type string
	 */
	this.uri_base = uri_root + script_name;

	//----------------------------------------------------------------------------------- use_cookies
	/**
	 * @type boolean
	 */
	this.use_cookies = use_cookies;

	//-------------------------------------------------------------------------------------- uri_root
	/**
	 * @example '/a/folder/'
	 * @var string
	 */
	this.uri_root = uri_root;

};

//------------------------------------------------------------------------------------------ andSID
/**
 * Adds session id expression to a given URI
 *
 * @return string the URI with PHPSESSID request argument if session is not stored into a cookie
 */
App.prototype.addSID = function(uri)
{
	return uri + ((uri.indexOf('?') > -1) ? this.andSID() : this.askSID());
};

//------------------------------------------------------------------------------------------ andSID
/**
 * Gets session id expression
 *
 * @return string '' if session id is stored into a cookie, else '&PHPSESSID=xxxx'
 */
App.prototype.andSID = function()
{
	return this.use_cookies ? '' : ('&PHPSESSID=' + this.PHPSESSID);
};

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
	return params ? (uri + ((uri.indexOf('?') > -1) ? '&' : '?') + params) : uri;
};

//------------------------------------------------------------------------------------------ askSID
/**
 * Gets session id expression
 *
 * @return string '' if session id is stored into a cookie, else '?PHPSESSID=xxxx'
 */
App.prototype.askSID = function()
{
	return this.use_cookies ? '' : ('?PHPSESSID=' + this.PHPSESSID);
};

/**
 * Gets session id expression
 *
 * @return string '?' if session id is stored into a cookie, else '?PHPSESSID=xxxx&'
 */
App.prototype.askSIDand = function()
{
	return this.use_cookies ? '?' : ('?PHPSESSID=' + this.PHPSESSID + '&');
};
