<?php
namespace ITRocks\Framework\Tools;

use ITRocks\Framework\Plugin\Configurable;
use ITRocks\Framework\Session;

/**
 * Editor
 */
class Editor implements Configurable
{

	//------------------------------------------------------------------------------------- CKEDITOR
	const CKEDITOR = 'ckeditor';

	//------------------------------------------------------------------------------------ $settings
	/**
	 * @var array
	 */
	private $settings;

	//---------------------------------------------------------------------------------- __construct
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

	//---------------------------------------------------------------------------------- getSettings
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
