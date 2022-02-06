<?php
namespace ITRocks\Framework\Feature\List_;

use ITRocks\Framework\Builder;
use ITRocks\Framework\Controller\Parameters;
use ITRocks\Framework\Dao;
use ITRocks\Framework\Feature\List_;
use ITRocks\Framework\Feature\List_\Navigation\Class_;
use ITRocks\Framework\Feature\List_Setting;

/**
 * Previous / next object on list
 */
class Navigation
{

	//-------------------------------------------------------------------------------------- $classes
	/**
	 * @var Class_[] [string $class_name => Class_]
	 */
	protected array $classes = [];

	//----------------------------------------------------------------------------------- __serialize
	public function __serialize() : array
	{
		return ['classes' => $this->classes];
	}

	//--------------------------------------------------------------------------------- __unserialize
	public function __unserialize(array $data)
	{
		$this->classes = $data['classes'];
	}

	//-------------------------------------------------------------------------------------- addClass
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $class_name string
	 * @param $object     object
	 */
	protected function addClass(string $class_name, object $object)
	{
		$list_settings = List_Setting\Set::current($class_name);
		$search        = (new List_\Controller)->applySearchParameters($list_settings);
		$total_count   = Dao::count($search, $class_name, Dao::groupBy());
		/** @noinspection PhpUnhandledExceptionInspection class */
		$class              = Builder::create(Class_::class);
		$class->class_name  = $class_name;
		$class->total_count = $total_count;
		$this->classes[$class_name] = $class;
		$class->navigate($object, 0);
	}

	//-------------------------------------------------------------------------------------- navigate
	/**
	 * @param $parameters Parameters
	 * @param $direction  integer @values -1, 1
	 * @return object
	 */
	public function navigate(Parameters $parameters, int $direction) : object
	{
		$object     = $parameters->getMainObject();
		$class_name = Builder::current()->sourceClassName(get_class($object));
		if (!isset($this->classes[$class_name])) {
			$this->addClass($class_name, $object);
		}
		$class = $this->classes[$class_name];
		$parameters->set($class_name, $object = $class->navigate($object, $direction));
		return $object;
	}

}
