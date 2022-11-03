<?php
namespace ITRocks\Framework\Locale\Option;

use ITRocks\Framework\Locale\Option;

/**
 * The context to use for translation, if forced
 *
 * The context is the full name of a class, an interface, or a trait.
 * The translator will search for a translation in any parent class / interface / trait.
 * It can be a Loc constant like FEMININE, MASCULINE, NEUTRAL.
 * Class contexts are automatically set on most cases (eg when from lists, templates).
 * Multiple contexts are not allowed yet.
 */
class Context extends Option
{

	//-------------------------------------------------------------------------------------- $context
	/**
	 * @var string
	 */
	public string $context;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * Context constructor.
	 *
	 * @param $context string
	 */
	public function __construct(string $context)
	{
		$this->context = $context;
	}

}
