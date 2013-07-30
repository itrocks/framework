/**
 * The App object stores application data for general use (locale, paths)
 *
 * @param PHPSESSID   string
 * @param uri_root    string
 * @param script_name string
 * @param project_uri string
 * @param language    string
 * @param date_format string
 * @constructor
 */
App = function(PHPSESSID, uri_root, script_name, project_uri, language, date_format)
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

	//-------------------------------------------------------------------------------------- uri_root
	/**
	 * @var string
	 */
	this.uri_root = uri_root;

};
