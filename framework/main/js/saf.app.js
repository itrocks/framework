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
	 * @var string
	 */
	this.project_uri = project_uri;

	//----------------------------------------------------------------------------------- script_name
	/**
	 * @var string
	 */
	this.script_name = script_name;

	//-------------------------------------------------------------------------------------- uri_base
	/**
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
	 * @var string
	 */
	this.uri_root = uri_root;

	//---------------------------------------------------------------------------------------- andSID
	/**
	 * Adds session id expression to a given URI
	 *
	 * @return string the URI with PHPSESSID request argument if session is not stored into a cookie
	 */
	this.addSID = function(uri)
	{
		return uri + ((uri.indexOf("?") >= -1) ? this.andSID() : this.askSID());
	}

	//---------------------------------------------------------------------------------------- andSID
	/**
	 * Gets session id expression
	 *
	 * @return string "" if session id is stored into a cookie, else "&PHPSESSID=xxxx"
	 */
	this.andSID = function()
	{
		return this.use_cookies ? "" : ("&amp;PHPSESSID=" + this.PHPSESSID);
	};

	//---------------------------------------------------------------------------------------- askSID
	/**
	 * Gets session id expression
	 *
	 * @return string "" if session id is stored into a cookie, else "?PHPSESSID=xxxx"
	 */
	this.askSID = function()
	{
		return this.use_cookies ? "" : ("?PHPSESSID=" + this.PHPSESSID);
	};

	/**
	 * Gets session id expression
	 *
	 * @return string "?" if session id is stored into a cookie, else "?PHPSESSID=xxxx&"
	 */
	this.askSIDand = function()
	{
		return this.use_cookies ? "?" : ("?PHPSESSID=" + this.PHPSESSID + "&amp;");
	}

};
