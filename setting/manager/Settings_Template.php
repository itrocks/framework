<?php
namespace SAF\Framework\Setting\Manager;

/**
 * A settings template
 *
 * @business
 */
class Settings_Template
{

	//---------------------------------------------------------------------------------------- $class
	/**
	 * @var string
	 */
	public $class;

	//-------------------------------------------------------------------------------------- $feature
	/**
	 * @var string
	 */
	public $feature;

	//------------------------------------------------------------------------------------- $elements
	/**
	 * @link Collection
	 * @var Settings_Template_Element[]
	 */
	public $elements;

}
