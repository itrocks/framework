<?php
namespace ITRocks\Framework\Configuration\File\Builder;

use ITRocks\Framework\Configuration\File\Builder;

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
	public $components = [];

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

	//------------------------------------------------------------------------------------------- add
	/**
	 * @param $interfaces_traits string|string[]
	 * @param $builder           Builder
	 */
	public function add($interfaces_traits, Builder $builder)
	{
		if (is_string($interfaces_traits)) {
			$interfaces_traits = [$interfaces_traits];
		}
		foreach ($interfaces_traits as $interface_trait) {
			if (!in_array($interface_trait, $this->components)) {
				// the comparison is done alphabetically, with the short name of the interface / trait
				$this->components = arrayInsertSorted(
					$this->components,
					$interface_trait,
					function($class1, $class2) use ($builder) {
						$class1 = $builder->shortClassNameOf($class1);
						$class2 = $builder->shortClassNameOf($class2);
						return strcmp($class1, $class2);
					}
				);
			}
		}
	}

}
