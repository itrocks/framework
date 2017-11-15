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

	//------------------------------------------------------------------------------------- $template
	/**
	 * @composite
	 * @link Object
	 * @var Settings_Template
	 */
	public $template;

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

	//----------------------------------------------------------------------------------------- $type
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
