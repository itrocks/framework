<?php
namespace ITRocks\Framework\Configuration\File;

use ITRocks\Framework\Dao;
use ITRocks\Framework\PHP\Dependency;

/**
 * Compatibility class management
 *
 * - On adding trait : if a compatibility trait exists for this trait and all traits of the
 *   compatibility trait are set, replace these traits by the compatibility trait
 * - On removing trait : if it is a compatibility trait or part of an active compatibility trait,
 *   remove the compatibility trait and place the removed traits back
 *
 * This class is used by Builder and by Source for the trait's assembly.
 * It does not affect the list of installed plugins and so on... only for replacements in files
 */
class Compatibility_Class
{

	//--------------------------------------------------------------------------------- allComponents
	/**
	 * Replace compatibility components by their own components
	 *
	 * @param $components string[]
	 * @return string[]
	 */
	public function allComponents(array $components) : array
	{
		$result = [];
		foreach ($components as $component) {
			$compatibility_components = $this->components($component);
			if (!$compatibility_components) {
				$result[$component] = $component;
			}
			foreach ($compatibility_components as $compatibility_component) {
				$result[$compatibility_component] = $compatibility_component;
			}
		}
		return $result;
	}

	//------------------------------------------------------------------------------- compatibilities
	/**
	 * Return all compatibility classes which component is part of
	 *
	 * @param $component string
	 * @return array [$compatibility_class_name => [$component_class_name => $component_class_name]]
	 */
	protected function compatibilities(string $component) : array
	{
		$compatibilities = [];
		$dependencies    = Dao::search(
			['dependency_name' => $component, 'type' => Dependency::T_COMPATIBILITY],
			Dependency::class
		);
		foreach ($dependencies as $dependency) {
			$compatibilities[$dependency->class_name] = $this->components($dependency->class_name);
		}
		return $compatibilities;
	}

	//------------------------------------------------------------------------------------ components
	/**
	 * @param $class_name string
	 * @return string[]
	 */
	public function components(string $class_name) : array
	{
		$components   = [];
		$dependencies = Dao::search(
			['class_name' => $class_name, 'type' => Dependency::T_COMPATIBILITY],
			Dependency::class
		);
		foreach ($dependencies as $dependency) {
			$components[$dependency->dependency_name] = $dependency->dependency_name;
		}
		return $components;
	}

	//--------------------------------------------------------------------------------------- replace
	/**
	 * Search if the new component and some old components can be replaced by a compatibility trait
	 *
	 * It calculates what must change into components :
	 * - add compatibility component, remove part components, if all matching components were found
	 * - empty array if nothing must be change and the new component must simply be added, alone
	 *
	 * @param $new_component string
	 * @param $components    string[]
	 * @return array ['add' => $compatibility_component, 'remove' => [$component_name]], empty if not
	 */
	public function replace(string $new_component, array $components) : array
	{
		foreach ($this->compatibilities($new_component) as $compatibility_class => $compatibility) {
			foreach ($compatibility as $component) {
				if (!isset($components[$component])) {
					continue 2;
				}
			}
			unset($compatibility[$new_component]);
			return ['add' => $compatibility_class, 'remove' => $compatibility];
		}
		return [];
	}

}
