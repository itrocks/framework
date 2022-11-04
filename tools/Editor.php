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
	private array $settings;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $configuration array
	 */
	public function __construct(mixed $configuration = [])
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
	 * @example ckeditor full version : class name is name-version (ckeditor-full)
	 * @param $version string
	 * @return string
	 */
	public static function buildClassName(string $version) : string
	{
		$settings = Session::current()->plugins->get(Editor::class)->settings;
		$version  = trim($version);
		if (str_contains($version, SP)) {
			$build_name    = trim(lLastParse($version, SP));
			$build_version = trim(rLastParse($version, SP));
		}
		// Always keep default config value if no parameters in parameter tags
		else {
			$build_name    = $settings['name'];
			$build_version = $settings['default_version'];
		}
		return $build_name . '-' . $build_version;
	}

}
