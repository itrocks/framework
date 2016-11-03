<?php
namespace ITRocks\Framework\Reflection\Annotation\Tests\Replaces;

/**
 * Replaced property with getter test
 */
class Replaced_Getter
{

	//------------------------------------------------------------------------------------- $replaced
	/**
	 * @getter
	 * @var string
	 */
	public $replaced;

	//---------------------------------------------------------------------------------- $replacement
	/**
	 * @replaces replaced
	 * @var string
	 */
	public $replacement;

	//----------------------------------------------------------------------------------- getReplaced
	/** @noinspection PhpUnusedPrivateMethodInspection @getter */
	/**
	 * @return string
	 */
	private function getReplaced()
	{
		return $this->replaced . '(get)';
	}

}
