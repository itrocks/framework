<?php
namespace ITRocks\Framework\Configuration\File\Builder;

/**
 * Replaced built class
 */
class Replaced extends Built
{

	//---------------------------------------------------------------------------------- $replacement
	/**
	 * Replacement class name
	 *
	 * @var string
	 */
	public $replacement;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $class_name  string
	 * @param $replacement string
	 */
	public function __construct($class_name = null, $replacement = null)
	{
		parent::__construct($class_name);
		if (isset($replacement)) {
			$this->replacement = $replacement;
		}
	}

}
