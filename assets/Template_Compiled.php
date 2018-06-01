<?php
namespace ITRocks\Framework\Assets;

use ITRocks\Framework\AOP\Include_Filter;
use ITRocks\Framework\Plugin\Register;
use ITRocks\Framework\Plugin\Registerable;
use ITRocks\Framework\Tools\Paths;
use ITRocks\Framework\Updater\Application_Updater;
use ITRocks\Framework\Updater\Updatable;
use ITRocks\Framework\View\Html\Template;
use ReflectionException;

/**
 * Class Plugins
 */
class Template_Compiled implements Registerable, Updatable
{

	//------------------------------------------------------------------------------------------ HOOK
	const HOOK = '#\{.*\/assets\.html\}#';

	//-------------------------------------------------------------------------------- $configuration
	/**
	 * @var Configuration
	 */
	public $configuration;

	//---------------------------------------------------------------------------------- $source_main
	/**
	 * @var string
	 */
	public $source_main;

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
	protected function getCompiledPath()
	{
		return Include_Filter::getCacheDir() . SL . 'main.html';
	}

	//-------------------------------------------------------------------------------------- register
	/**
	 * Registration code for the plugin
	 *
	 * @param $register Register
	 */
	public function register(Register $register)
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
	 * @throws ReflectionException
	 */
	public function update($last_time)
	{
		unlinkIfExists($this->getCompiledPath());
		$this->configuration = Configuration::get();
		$this->source_main   = Paths::$project_root . SL . (new Template())->getMainTemplateFile();
		$content             = file_get_contents($this->source_main);
		$assets              = [];
		foreach ($this->configuration->getStringElements() as $asset) {
			$assets[] = TAB . $asset;
		}
		$content = preg_replace(static::HOOK, implode('', $assets), $content);
		file_put_contents($this->getCompiledPath(), $content);
	}

}
