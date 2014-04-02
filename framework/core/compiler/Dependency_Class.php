<?php
namespace SAF\Framework;

/**
 * A class for use in dependency :
 */
class Dependency_Class
{

	//----------------------------------------------------------------------------------------- $line
	/**
	 * The line where the class / interface / trait keyword of the class is
	 *
	 * @var integer
	 */
	public $line;

	//----------------------------------------------------------------------------------------- $name
	/**
	 * The name of the class
	 *
	 * @var string
	 */
	public $name;

	//----------------------------------------------------------------------------------------- $stop
	/**
	 * The line where the class source code ends : where the '}' closing bracket of the class is
	 *
	 * @var integer
	 */
	public $stop;

	//----------------------------------------------------------------------------------------- $type
	/**
	 * The type of the class
	 *
	 * @values T_CLASS, T_INTERFACE, T_TRAIT
	 * @var integer
	 */
	public $type;

}
