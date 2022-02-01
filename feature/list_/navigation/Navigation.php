<?php
namespace ITRocks\Framework\Feature\List_;

use ITRocks\Framework\Builder;
use ITRocks\Framework\Controller\Parameters;
use ITRocks\Framework\Dao;
use ITRocks\Framework\Feature\List_\Navigation\Class_;
use ITRocks\Framework\Feature\List_Setting;
use Serializable;

/**
 * Previous / next object on list
 */
class Navigation implements Serializable
{

	//-------------------------------------------------------------------------------------- $classes
	/**
	 * @var Class_[] [string $class_name => Class_]
	 */
	protected array $classes = [];

	//-------------------------------------------------------------------------------------- addClass
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $class_name string
	 */
	protected function addClass(string $class_name)
	{
		$list_settings = List_Setting\Set::current($class_name);
		$total_count   = Dao::count($list_settings->search, $class_name, Dao::groupBy());

		/** @noinspection PhpUnhandledExceptionInspection class */
		$class = Builder::create(Class_::class);

		$class->class_name  = $class_name;
		$class->total_count = $total_count;

		$this->classes[$class_name] = $class;
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
			$this->addClass($class_name);
		}
		$class = $this->classes[$class_name];
		$parameters->set($class_name, $object = $class->navigate($object, $direction));
		return $object;
	}

	//------------------------------------------------------------------------------------- serialize
	public function serialize() : string
	{
		return serialize(['classes' => $this->classes]);
	}

	//----------------------------------------------------------------------------------- unserialize
	public function unserialize(string $data)
	{
		$data = unserialize($data);
		$this->classes = $data['classes'];
	}

}
