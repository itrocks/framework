<?php
namespace ITRocks\Framework\Configuration\File\Builder;

/**
 * Assembled built class
 */
class Assembled extends Built
{

	//----------------------------------------------------------------------------------- $components
	/**
	 * Component class names, or comments if trim begins with '/', or empty lines ''
	 *
	 * @var string[]
	 */
	public $components;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $class_name string
	 * @param $components string[]
	 */
	public function __construct($class_name = null, array $components = null)
	{
		parent::__construct($class_name);
		if (isset($components)) {
			$this->$components = $components;
		}
	}

}
