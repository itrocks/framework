<?php
namespace ITRocks\Framework\Tools;

use ITRocks\Framework\Plugin\Configurable;
use ITRocks\Framework\Session;

/**
 * This class allows you to configure online editor (WYSIWYG), activated with annotation
 * @editor editor_name
 *
 * @example config.php : Editor::class => ['ckeditor' => ['version' => 'full']] for full version,
 * or ['version' => 'standard'] for basic version (http://ckeditor.com/demo#full)
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
		foreach ($configuration as $editor => $setting) {
			$this->settings[$editor] = $setting;
		}
	}

	//----------------------------------------------------------------------------------- getSettings
	/**
	 * @param $editor string
	 * @return array
	 */
	public static function getSettings($editor)
	{
		$settings = Session::current()->plugins->get(Editor::class)->settings;
		return $settings[$editor];
	}

}
