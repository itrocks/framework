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
	public string $replacement;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $class_name  string|null
	 * @param $replacement string|null
	 */
	public function __construct(string $class_name = null, string $replacement = null)
	{
		parent::__construct($class_name);
		if (isset($replacement)) {
			$this->replacement = $replacement;
		}
	}

}
