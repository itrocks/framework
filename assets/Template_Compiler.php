<?php
namespace ITRocks\Framework\Assets;

use ITRocks\Framework\AOP\Include_Filter;
use ITRocks\Framework\Plugin\Register;
use ITRocks\Framework\Plugin\Registerable;
use ITRocks\Framework\Tools\Paths;
use ITRocks\Framework\Updater\Application_Updater;
use ITRocks\Framework\Updater\Updatable;
use ITRocks\Framework\View\Html\Template;

/**
 * Assets template compiler plugin
 */
class Template_Compiler implements Registerable, Updatable
{

	//------------------------------------------------------------------------------------------ HOOK
	const HOOK = '<!--assets-->';

	//-------------------------------------------------------------------------------- $configuration
	/**
	 * @var Configuration
	 */
	public Configuration $configuration;

	//--------------------------------------------------------------------------- $main_template_path
	/**
	 * Path to source main.html used as base to create compiled main.html
	 *
	 * @var string
	 */
	public string $main_template_path;

	//----------------------------------------------------------------------- getCompiledMainTemplate
	/**
	 * Sets up main template to compile one if exists
	 *
	 * @param $object Template
	 */
	public function getCompiledMainTemplate(Template $object)
	{
		if (!isset($this->main_template) && file_exists($this->getCompiledPath())) {
			$object->main_template = $this->getCompiledPath();
		}
	}

	//------------------------------------------------------------------------------- getCompiledPath
	/**
	 * @return string
	 */
	protected function getCompiledPath() : string
	{
		return Include_Filter::getCacheDir() . SL . 'main.html';
	}

	//-------------------------------------------------------------------------------------- register
	/**
	 * @param $register Register
	 */
	public function register(Register $register) : void
	{
		Application_Updater::get()->addUpdatable($this);
		$register->aop->beforeMethod(
			[Template::class, 'getMainTemplateFile'],
			[$this, 'getCompiledMainTemplate']
		);
	}

	//---------------------------------------------------------------------------------------- update
	/**
	 * @param $last_time integer
	 * @see Updatable
	 * @throws Assets_Exception
	 */
	public function update(int $last_time)
	{
		unlinkIfExists($this->getCompiledPath());
		$this->configuration      = Configuration::get();
		$this->main_template_path = Paths::$project_root . SL . (new Template())->getMainTemplateFile();
		$content                  = file_get_contents($this->main_template_path);
		$assets                   = [];
		foreach ($this->configuration->getStringElements() as $asset) {
			$assets[] = TAB . $asset;
		}
		$content = str_replace(static::HOOK, join('', $assets), $content);
		script_put_contents($this->getCompiledPath(), $content);
	}

}
