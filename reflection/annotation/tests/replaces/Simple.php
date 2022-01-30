<?php
namespace ITRocks\Framework\Reflection\Annotation\Tests\Replaces;

/**
 * A simple test class with properties replacing each other
 */
class Simple
{

	//------------------------------------------------------------------------------------- $replaced
	/**
	 * @var string
	 */
	public string $replaced;

	//---------------------------------------------------------------------------------- $replacement
	/**
	 * @replaces replaced
	 * @var string
	 */
	public $replacement;

}
