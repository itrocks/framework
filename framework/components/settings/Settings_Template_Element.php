<?php
namespace SAF\Framework;

/**
 * @set Settings_Templates_Elements
 */
class Settings_Template_Element
{
	use Component;

	//----------------------------------------------------------------------------- $setting_template
	/**
	 * @composite
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

}
