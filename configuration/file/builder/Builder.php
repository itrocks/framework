<?php
namespace ITRocks\Framework\Configuration\File;

use ITRocks\Framework;
use ITRocks\Framework\Configuration\File;
use ITRocks\Framework\Configuration\File\Builder\Assembled;
use ITRocks\Framework\Configuration\File\Builder\Built;
use ITRocks\Framework\Configuration\File\Builder\Replaced;

/**
 * The builder.php configuration file
 */
class Builder extends File
{

	//-------------------------------------------------------------------------------------- $classes
	/**
	 * @var Built[]|string[] Built classes, or comments if trim begins with '/', or empty lines ''
	 */
	public array $classes = [];

	//------------------------------------------------------------------------------------------- add
	/**
	 * Add or update an existing built class, adding interfaces and traits
	 * or setting replacement class
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $class                   Built|string
	 * @param $class_interfaces_traits string|string[]
	 * @return Built
	 */
	public function add(Built|string $class, array|string $class_interfaces_traits) : Built
	{
		// create Assembled / Replaced Built class
		if (is_string($class)) {
			$found_class = $this->search($class);
			if ($found_class) {
				$class = $found_class;
			}
			else {
				/** @noinspection PhpUnhandledExceptionInspection Constant::class */
				$class = Framework\Builder::create(
					(
						is_string($class_interfaces_traits)
						&& class_exists($class_interfaces_traits)
						&& !interface_exists($class_interfaces_traits)
						&& !trait_exists($class_interfaces_traits)
					)
						? Replaced::class
						: Assembled::class,
					[$class]
				);
				$builder = $this;
				$this->addUseFor($class->class_name, 2);
				$this->classes = objectInsertSorted(
					$this->classes,
					$class,
					function(Built $object1, Built $object2) use ($builder) : int {
						$class1 = $builder->shortClassNameOf($object1->class_name);
						$class2 = $builder->shortClassNameOf($object2->class_name);
						return strcmp($class1, $class2);
					}
				);
			}
		}
		// update Assembled / Replaced Built class
		if ($class instanceof Assembled) {
			$class->add($class_interfaces_traits, $this);
		}
		elseif ($class instanceof Replaced) {
			if (
				is_string($class_interfaces_traits)
				&& class_exists($class_interfaces_traits)
				&& !interface_exists($class_interfaces_traits)
				&& !class_exists($class_interfaces_traits)
			) {
				$this->addUseFor($class_interfaces_traits, 2);
				$class->replacement = $class_interfaces_traits;
			}
			else {
				trigger_error(
					'Could not add interfaces/traits to replaced class ' . $class->class_name, E_USER_ERROR
				);
			}
		}
		else {
			trigger_error('Bad class ' . (is_object($class) ? get_class($class) : $class), E_USER_ERROR);
		}
		return $class;
	}

	//------------------------------------------------------------------------------------------ read
	/**
	 * Read from file
	 */
	public function read() : void
	{
		(new Builder\Reader($this))->read();
	}

	//---------------------------------------------------------------------------------------- remove
	/**
	 * @param $class Built|string
	 * @return boolean true if removed, false if not found
	 */
	public function remove(Built|string $class) : bool
	{
		$class_name = ($class instanceof Built) ? $class->class_name : $class;
		// remove class name
		foreach ($this->classes as $key => $built) {
			if (($built instanceof Built) && $built->class_name === $class_name) {
				unset($this->classes[$key]);
				return true;
			}
		}
		return false;
	}

	//---------------------------------------------------------------------------------------- search
	/**
	 * Search a built class, and return its object if exist
	 *
	 * @param $class_name string
	 * @return ?Built
	 */
	public function search(string $class_name) : ?Built
	{
		foreach ($this->classes as $built) {
			if (($built instanceof Built) && ($built->class_name === $class_name)) {
				return $built;
			}
		}
		return null;
	}

	//----------------------------------------------------------------------------------------- write
	/**
	 * Write to file
	 */
	public function write() : void
	{
		(new Builder\Writer($this))->write();
	}

}
