<?php
namespace ITRocks\Framework\Dao\Func\Select;

use ITRocks\Framework\Dao;
use ITRocks\Framework\Dao\Func;
use ITRocks\Framework\Dao\Func\Dao_Function;
use ITRocks\Framework\PHP\Dependency;
use ITRocks\Framework\Tools\Names;
use ReflectionClass;

/**
 * Func class for select controller and view
 */
class Func_Select extends Func
{

	//----------------------------------------------------------------------------------- $class_name
	/**
	 * The function class name
	 *
	 * @var string
	 */
	public string $class_name;

	//----------------------------------------------------------------------------------------- $name
	/**
	 * A readable name (display) for the function
	 *
	 * @var string
	 */
	public string $name;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $class_name string|null
	 */
	public function __construct(string $class_name = null)
	{
		if (isset($class_name)) {
			$this->class_name = $class_name;
			$this->name       = Names::classToDisplay($class_name);
		}
	}

	//------------------------------------------------------------------------------------- functions
	/**
	 * List all available functions
	 */
	public function functions() : array
	{
		$functions      = [];
		$parent_classes = [Dao_Function::class];
		while ($parent_classes) {
			$search = [
				'dependency_name' => $parent_classes,
				'type'            => [Dependency::T_EXTENDS, Dependency::T_IMPLEMENTS]
			];
			$parent_classes = [];
			foreach (Dao::search($search, Dependency::class) as $dependency) {
				/** @noinspection PhpUnhandledExceptionInspection dependency class name is valid */
				/** @var $dependency Dependency */
				if (!(new ReflectionClass($dependency->class_name))->isAbstract()) {
					$functions[] = new Func_Select($dependency->class_name);
				}
				$parent_classes[] = $dependency->class_name;
			}
		}
		return $functions;
	}

}
