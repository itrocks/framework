<?php
namespace ITRocks\Framework\Configuration\File\Builder;

use ITRocks\Framework\Configuration\File\Builder;
use ITRocks\Framework\Configuration\File\Compatibility_Class;

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
	public array $components = [];

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $class_name string|null
	 * @param $components string[]|null
	 */
	public function __construct(string $class_name = null, array $components = null)
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
	public function add(array|string $interfaces_traits, Builder $builder)
	{
		$compatibility  = new Compatibility_Class();
		$all_components = $compatibility->allComponents($this->components);
		if (is_string($interfaces_traits)) {
			$interfaces_traits = [$interfaces_traits];
		}
		foreach ($interfaces_traits as $interface_trait) {
			if (!isset($all_components[$interface_trait])) {
				$all_components[$interface_trait] = $interface_trait;
				if (beginsWith($interface_trait, AT) && !beginsWith($interface_trait, '@override')) {
					$annotation_name = lParse($interface_trait, SP);
					foreach ($this->components as $key => $component) {
						if (beginsWith($component, $annotation_name)) {
							unset($this->components[$key]);
						}
					}
				}
				else {
					$changes = $compatibility->replace($interface_trait, $all_components);
					if ($changes) {
						foreach ($changes['remove'] as $remove) {
							$this->simpleRemove($remove);
						}
						$interface_trait = $changes['add'];
					}
					$builder->addUseFor($interface_trait, 2);
				}
				// the comparison is done alphabetically, with the short name of the interface / trait
				$this->insertSorted($interface_trait, $builder);
			}
		}
	}

	//---------------------------------------------------------------------------------- insertSorted
	/**
	 * @param $interface_trait string
	 * @param $builder         Builder
	 */
	public function insertSorted(string $interface_trait, Builder $builder)
	{
		$this->components = arrayInsertSorted(
			$this->components,
			$interface_trait,
			function($class1, $class2) use ($builder) {
				$class1 = $builder->shortClassNameOf($class1);
				$class2 = $builder->shortClassNameOf($class2);
				if (beginsWith($class1, AT)) $class1 = SP . $class1;
				if (beginsWith($class2, AT)) $class2 = SP . $class2;
				return strcmp($class1, $class2);
			}
		);
	}

	//---------------------------------------------------------------------------------------- remove
	/**
	 * @param $interfaces_traits string|string[]
	 * @param $builder           Builder
	 */
	public function remove(array|string $interfaces_traits, Builder $builder)
	{
		$compatibility  = new Compatibility_Class();
		$all_components = $compatibility->allComponents($this->components);
		if (is_string($interfaces_traits)) {
			$interfaces_traits = [$interfaces_traits];
		}
		foreach ($interfaces_traits as $interface_trait) {
			if (
				isset($all_components[$interface_trait])
				&& !in_array($interface_trait, $this->components)
			) {
				$changes = $compatibility->replace($interface_trait, $all_components);
				unset($all_components[$interface_trait]);
				$interface_trait = $changes['add'];
			}
			else {
				unset($all_components[$interface_trait]);
				$changes = false;
			}
			$this->simpleRemove($interface_trait);
			if ($changes) {
				foreach ($changes['remove'] as $place) {
					$this->add($place, $builder);
				}
			}
		}
	}

	//---------------------------------------------------------------------------------- simpleRemove
	/**
	 * Simple remove of an interface/trait from components, without compatibility class control
	 *
	 * @param $interface_trait string
	 */
	protected function simpleRemove(string $interface_trait)
	{
		$key = array_search($interface_trait, $this->components);
		if ($key > -1) {
			unset($this->components[$key]);
		}
	}

}
