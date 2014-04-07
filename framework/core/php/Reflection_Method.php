<?php
namespace SAF\PHP;

/**
 * The same as PHP's ReflectionMethod, but working with PHP source, without loading the class
 */
class Reflection_Method
{

	//----------------------------------------------------------------------------------------- $line
	/**
	 * @var integer
	 */
	public $line;

	//----------------------------------------------------------------------------------------- $name
	/**
	 * @var string
	 */
	public $name;

	//--------------------------------------------------------------------------------------- $source
	/**
	 * @var $source Reflection_Source
	 */
	private $source;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $source        Reflection_Source
	 * @param $property_name string
	 * @param $line          integer
	 */
	public function __construct(Reflection_Source $source, $property_name, $line)
	{
		$this->line   = $line;
		$this->name   = $property_name;
		$this->source = $source;
	}

}
