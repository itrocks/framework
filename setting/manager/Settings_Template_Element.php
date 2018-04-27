<?php
namespace ITRocks\Framework\Setting\Manager;

use ITRocks\Framework\Mapper\Component;

/**
 * A settings template element
 *
 * @business
 * @store_name settings_templates_elements
 * @todo store_name setting_template_elements (default)
 */
class Settings_Template_Element
{
	use Component;

	//----------------------------------------------------------------------------------------- $path
	/**
	 * @var string
	 */
	public $path;

	//------------------------------------------------------------------------------------- $subpaths
	/**
	 * @var string|string[]
	 */
	public $subpaths;

	//------------------------------------------------------------------------------------- $template
	/**
	 * @composite
	 * @link Object
	 * @var Settings_Template
	 */
	public $template;

	//----------------------------------------------------------------------------------------- $type
	/**
	 * @values boolean, values_list
	 * @var string
	 */
	public $type;

	//--------------------------------------------------------------------------------------- $values
	/**
	 * @values
	 * @var string[]
	 */
	public $values;

}
