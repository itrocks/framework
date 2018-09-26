<?php
namespace ITRocks\Framework\Tools;

use ITRocks\Framework\Plugin\Configurable;
use ITRocks\Framework\Session;

/**
 * This class allows you to configure online editor (WYSIWYG), activated with annotation
 *
 * @editor editor_name
 * @example config.php : Editor::class => ['name' => 'ckeditor', 'default_version' => 'full']
 * for full version, or 'default_version' => 'standard' for basic version
 * (http://ckeditor.com/demo#full)
 *
 */
class Editor implements Configurable
{

	//-------------------------------------------------------------------------------------- CKEDITOR
	const CKEDITOR = 'ckeditor';

	//------------------------------------------------------------------------------------- $settings
	/**
	 * @var array
	 */
	private $settings;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $configuration array
	 */
	public function __construct($configuration = [])
	{
		$this->settings = [];
		foreach ($configuration as $key => $setting) {
			$this->settings[$key] = $setting;
		}
	}

	//-------------------------------------------------------------------------------- buildClassName
	/**
	 * Allow build the class that will generate the online editor.
	 *
	 * @example ckeditor full version : class name is 'ckeditor-' version (ckeditor-full)
	 * @param $version string
	 * @return string
	 */
	public static function buildClassName($version)
	{
		$settings = Session::current()->plugins->get(Editor::class)->settings;
		// If it's string character, a parameter has been passed to the property.
		// else not parameter, use default setting
		if (is_string($version)) {
			$class_name = $settings['name'] . '-' . $version;
		}
		else {
			$class_name = $settings['name'] . '-' . $settings['default_version'];
		}
		return $class_name;
	}

}
