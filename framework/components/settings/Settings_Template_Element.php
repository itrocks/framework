<?php
namespace SAF\Framework;

/**
 * @set Settings_Templates_Elements
 */
class Settings_Template_Element implements Component
{

	//----------------------------------------------------------------------------- $setting_template
	/**
	 * @getter Aop::getObject
	 * @var Settings_Template
	 */
	public $template;

	//----------------------------------------------------------------------------------------- $path
	/**
	 * @var string
	 */
	public $path;

	//-------------------------------------------------------------------------------------- $subpath
	/**
	 * @var string|string[]
	 */
	public $subpaths;

	//-------------------------------------------------------------------------------------- $feature
	/**
	 * @var string
	 * @values boolean, values_list
	 */
	public $type;

	//--------------------------------------------------------------------------------------- $values
	/**
	 * @var string[]
	 * @values
	 */
	public $values;

	//--------------------------------------------------------------------------------------- dispose
	public function dispose()
	{
		$key = array_search($this, $this->template->elements);
		unset($this->template->elements[$key]);
	}

	//------------------------------------------------------------------------------------- getParent
	/**
	 * Get parent object
	 *
	 * @return Settings_Template
	 */
	public function getParent()
	{
		return $this->template;
	}

	//------------------------------------------------------------------------------------- setParent
	/**
	 * Set parents object
	 *
	 * @param $object Settings_Template
	 * @return Settings_Template_Element
	 */
	public function setParent($object)
	{
		$this->template = $object;
		return $this;
	}

}
