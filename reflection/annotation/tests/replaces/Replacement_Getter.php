<?php
namespace ITRocks\Framework\Reflection\Annotation\Tests\Replaces;

/**
 * Replacement property with getter test
 */
class Replacement_Getter
{

	//------------------------------------------------------------------------------------- $replaced
	/**
	 * @var string
	 */
	public $replaced;

	//---------------------------------------------------------------------------------- $replacement
	/**
	 * @getter
	 * @replaces replaced
	 * @var string
	 */
	public $replacement;

	//-------------------------------------------------------------------------------- getReplacement
	/**
	 * @return string
	 */
	protected function getReplacement()
	{
		return $this->replacement . '(get)';
	}

}
