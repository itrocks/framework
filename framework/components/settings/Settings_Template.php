<?php
namespace SAF\Framework;

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
	 * @getter Aop::getCollection
	 * @var Settings_Template_Element[]
	 * @component
	 */
	public $elements;

}
