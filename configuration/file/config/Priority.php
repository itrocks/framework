<?php
namespace ITRocks\Framework\Configuration\File\Config;

use /** @noinspection PhpUnusedAliasInspection @values */ ITRocks\Framework\Plugin;

/**
 * Priority block
 *
 * Contains plugin configurations
 */
class Priority
{

	//-------------------------------------------------------------------------------------- $plugins
	/**
	 * @var Plugin[]|string[] plugin or free code
	 */
	public $plugins;

	//------------------------------------------------------------------------------------- $priority
	/**
	 * The priority constant name
	 *
	 * @values Plugin\Priority::const
	 * @var string
	 */
	public $priority;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * Priority constructor
	 *
	 * @param $priority string can be the lower/upper case version of any Plugin\Priority constant
	 */
	public function __construct($priority)
	{
		$this->priority = strtolower($priority);
	}

}
