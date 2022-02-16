<?php
namespace ITRocks\Framework\View\Json;

use ITRocks\Framework\Plugin\Register;
use ITRocks\Framework\Plugin\Registerable;
use ITRocks\Framework\Tools\Current;
use ITRocks\Framework\View;

/**
 * Engine for json views
 */
class Engine implements Registerable, View\Engine
{
	use Current { current as private parentCurrent; }

	//--------------------------------------------------------------------------------- JSON_TEMPLATE
	/**
	 * Suffix of json template file name (before file extension)
	 *
	 * @example Json_Template => Json_Output_Template | Json_List_Template...
	 * @todo naming should follow this example : Engine_Feature_Template is the standard naming !
	 */
	const JSON_TEMPLATE = 'Json_Template';

	//------------------------------------------------------------------ JSON_TEMPLATE_FILE_EXTENSION
	/**
	 * Extension without the dot
	 * Eg 'json.inc' for a file myTemplate.json.inc
	 */
	const JSON_TEMPLATE_FILE_EXTENSION = 'php';

	//---------------------------------------------------------------------------------- $view_backup
	/**
	 * Keep a backup of configured current view before we replace by json view for,
	 * in order to restore it after processing
	 *
	 * @var ?View\Engine
	 */
	private static ?View\Engine $view_backup;

	//------------------------------------------------------------------------------------ acceptJson
	/**
	 * @return boolean true if request wants json response
	 */
	public static function acceptJson() : bool
	{
		static $accept_json = null;
		if (!isset($accept_json)) {
			$accept_json = (
				isset($_SERVER['HTTP_ACCEPT'])
				&& (count($accepts = explode(',', $_SERVER['HTTP_ACCEPT'])) === 1)
				&& (reset($accepts) === 'application/json')
			);
		}
		return $accept_json;
	}

	//-------------------------------------------------------------------------------------- afterRun
	public function afterRun()
	{
		if (static::acceptJson() && isset(self::$view_backup)) {
			View::current(self::$view_backup);
			self::$view_backup = null;
		}
	}

	//------------------------------------------------------------------------------------- beforeRun
	public function beforeRun()
	{
		if (static::acceptJson()) {
			self::$view_backup = View::current();
			View::current(static::current());
		}
	}

	//--------------------------------------------------------------------------------------- current
	/**
	 * Gets/sets current environment's object
	 *
	 * @param $set_current object|null
	 * @return static|null
	 */
	public static function current(object $set_current = null) : View\Engine|null
	{
		/** @noinspection PhpIncompatibleReturnTypeInspection View\Engine */
		return static::parentCurrent($set_current);
	}

	//------------------------------------------------------------------------------- getTemplateFile
	/**
	 * @param $class_name         string   the associated data class name
	 * @param $feature_names      string[] feature and inherited feature which view will be searched
	 * @param $template           string   if a specific template is set, the view named with it will
	 *                                     be searched into the view / feature namespace first
	 * @param $template_file_type string   can search template files with another extension than .html
	 * @return string the resulting path of the found template file
	 * @todo HIGH View\Html\Engine::getTemplateFile() should be factorized in a View\Engine class
	 */
	public static function getTemplateFile(
		string $class_name, array $feature_names, string $template = '',
		string $template_file_type = self::JSON_TEMPLATE_FILE_EXTENSION
	) : string
	{
		return View\Html\Engine::getTemplateFile(
			$class_name, $feature_names, ($template ?: self::JSON_TEMPLATE), $template_file_type
		);
	}

	//------------------------------------------------------------------------------------------ link
	/**
	 * Generates a link for to an object and feature, using parameters if needed
	 *
	 * @param $object     array|object|string|null linked object or class name
	 *                    Some internal calls may all this with [$class_name, $id]
	 * @param $feature    string|string[]|null linked feature name, forced if in array
	 * @param $parameters string|string[]|object|object[]|null optional parameters list
	 * @param $arguments  string|string[]|null optional arguments list
	 * @return string
	 */
	public function link(
		array|object|string|null $object,
		array|string             $feature    = null,
		array|object|string      $parameters = null,
		array|string             $arguments  = null
	) : string
	{
		// TODO: Implement link() method.
		return '';
	}

	//-------------------------------------------------------------------------------------- redirect
	/**
	 * Generates a redirection link for to an object and feature, using parameters if needed
	 *
	 * @param $link    string a link generated by self::link()
	 * @param $options array|string Single or multiple options eg Target::MAIN
	 * @return string
	 */
	public function redirect(string $link, array|string $options) : string
	{
		// TODO: Implement redirect() method.
		return '';
	}

	//-------------------------------------------------------------------------------------- register
	/**
	 * @param $register Register
	 */
	public function register(Register $register)
	{
		$aop = $register->aop;
		$aop->afterMethod( [View::class, 'run'], [$this, 'afterRun' ]);
		$aop->beforeMethod([View::class, 'run'], [$this, 'beforeRun']);
	}

	//----------------------------------------------------------------------------------- setLocation
	/**
	 * Generate code for the current view to set the current location without redirecting to it
	 *
	 * @param $uri   string
	 * @param $title ?string
	 * @return ?string
	 */
	public function setLocation(string $uri, ?string $title) : ?string
	{
		return null;
	}

}
