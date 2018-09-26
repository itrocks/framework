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
	use Current;

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
	 * @var View\Engine
	 */
	private static $view_backup;

	//------------------------------------------------------------------------------------ acceptJson
	/**
	 * @return boolean true if request wants json response
	 */
	public static function acceptJson()
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
	/**
	 */
	public function afterRun()
	{
		if (static::acceptJson() && isset(self::$view_backup)) {
			View::current(self::$view_backup);
			self::$view_backup = null;
		}
	}

	//------------------------------------------------------------------------------------- beforeRun
	/**
	 */
	public function beforeRun()
	{
		if (static::acceptJson()) {
			self::$view_backup = View::current();
			View::current(static::current());
		}
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
		$class_name, array $feature_names, $template = '',
		$template_file_type = self::JSON_TEMPLATE_FILE_EXTENSION
	) {
		return View\Html\Engine::getTemplateFile(
			$class_name, $feature_names, ($template ?: self::JSON_TEMPLATE), $template_file_type
		);
	}

	//------------------------------------------------------------------------------------------ link
	/**
	 * Generates a link for to an object and feature, using parameters if needed
	 *
	 * @param $object     object|string|array linked object or class name
	 *                    Some internal calls may all this with [$class_name, $id]
	 * @param $feature    string linked feature name
	 * @param $parameters string|string[]|object|object[] optional parameters list
	 * @param $arguments  string|string[] optional arguments list
	 * @return string
	 */
	public function link($object, $feature = null, $parameters = null, $arguments = null)
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
	public function redirect($link, $options)
	{
		// TODO: Implement redirect() method.
		return '';
	}

	//-------------------------------------------------------------------------------------- register
	/**
	 * Registration code for the plugin
	 *
	 * @param $register Register
	 */
	public function register(Register $register)
	{
		$aop = $register->aop;
		$aop->afterMethod( [View::class, 'run'], [$this, 'afterRun' ]);
		$aop->beforeMethod([View::class, 'run'], [$this, 'beforeRun']);
	}

}
