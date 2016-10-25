<?php
namespace SAF\Framework\Locale\Option;

use SAF\Framework\Locale\Option;

/**
 * The context to use for translation, if forced
 */
class Context extends Option
{

	//-------------------------------------------------------------------------------------- $context
	/**
	 * @var string
	 */
	public $context;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * Context constructor.
	 *
	 * @param $context string
	 */
	public function __construct($context)
	{
		$this->context = $context;
	}

}
