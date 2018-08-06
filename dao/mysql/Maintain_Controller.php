<?php
namespace ITRocks\Framework\Dao\Mysql;

use ITRocks\Framework\Builder;
use ITRocks\Framework\Controller\Feature_Controller;
use ITRocks\Framework\Controller\Parameters;
use ITRocks\Framework\Dao;
use ITRocks\Framework\Dao\Mysql;
use ITRocks\Framework\PHP\Dependency;
use ITRocks\Framework\Reflection\Annotation\Class_\Store_Name_Annotation;
use ITRocks\Framework\Reflection\Reflection_Class;
use ReflectionException;

/**
 * Runs maintainer on all classes
 */
class Maintain_Controller implements Feature_Controller
{

	//-------------------------------------------------------------------------------------- $verbose
	/**
	 * @var boolean
	 */
	protected $verbose;

	//------------------------------------------------------------------------------------ classNamed
	/**
	 * @param $class_name string
	 * @return Reflection_Class|null
	 */
	protected function classNamed($class_name)
	{
		static $reflection_classes = [];
		if (isset($reflection_classes[$class_name])) {
			return $reflection_classes[$class_name];
		}
		try {
			$class = new Reflection_Class($class_name);
		}
		catch (ReflectionException $exception) {
			if ($this->verbose) {
				echo "! ignore $class_name : NOT FOUND" . BRLF;
			}
			return null;
		}
		$reflection_classes[$class_name] = $class;
		return $class;
	}

	//------------------------------------------------------------------------------------ getClasses
	/**
	 * Get business classes that will be used for source for maintain
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 * @return Reflection_Class[] key is the name of the class
	 */
	protected function getClasses()
	{
		// cache hierarchy
		$children = [];
		$parents  = [];
		foreach (Dao::search(['type' => Dependency::T_EXTENDS], Dependency::class) as $dependency) {
			$children[$dependency->dependency_name][$dependency->class_name] = $dependency->class_name;
			$parents[$dependency->class_name] = $dependency->dependency_name;
		}
		// tables
		/** @var $mysql Mysql\Link */
		$mysql = Dao::current();
		/** @noinspection PhpUnhandledExceptionInspection no catch */
		$table_names = array_flip($mysql->getConnection()->getTables());
		// @business classes + without Builder replacement + without children with same @store_name
		// + existing MySQL table
		$classes = [];
		$dependencies = Dao::search(['declaration' => Dependency::T_CLASS], Dependency::class);
		foreach ($dependencies as $dependency) {
			$class_name = Builder::className($dependency->class_name);
			if (!isset($classes[$class_name])) {
				$class = $this->classNamed($class_name);
				if (
					$class
					&& !$class->isAbstract()
					&& $class->getAnnotation('business')->value
				) {
					$store = true;
					if (isset($children[$class_name])) {
						foreach ($children[$class_name] as $child_class_name) {
							$child_class = $this->classNamed($child_class_name);
							if ($child_class && Store_Name_Annotation::equals($child_class, $class)) {
								$store = false;
								break;
							}
						}
					}
					if ($store && isset($table_names[Store_Name_Annotation::of($class)->value])) {
						$classes[$class_name] = $class;
					}
				}
			}
		}
		return $classes;
	}

	//------------------------------------------------------------------------------------------- run
	/**
	 * This will be called for this controller, always.
	 *
	 * @param $parameters Parameters
	 * @param $form       array
	 * @param $files      array[]
	 * @return mixed
	 */
	public function run(Parameters $parameters, array $form, array $files)
	{
		$classes = $this->getClasses();

		$simulation = !isset($parameters->getRawParameters()['valid']);
		$verbose    = isset($parameters->getRawParameters()['verbose']);

		$this->verbose = $verbose;

		Maintainer::get()->notice = $verbose;
		if ($simulation) {
			Maintainer::get()->simulationStart();
		}

		$this->updateAllTables($classes, $verbose, $simulation);

		if ($simulation) {
			Maintainer::get()->simulationStop();
		}
		echo '<h4>Maintenance done</h4>';
		return;
	}

	//------------------------------------------------------------------------------- updateAllTables
	/**
	 * @param $classes    Reflection_Class[]
	 * @param $verbose    string
	 * @param $simulation boolean
	 */
	protected function updateAllTables(array $classes, $verbose, $simulation)
	{
		foreach ($classes as $class) {
			$class_name = $class->name;
			if ($verbose) {
				echo '<h5>' . ($simulation ? '[Simulation] For' : 'For') . SP . $class_name . '</h5>';
			}
			Maintainer::get()->updateTable($class_name, null);
			if (count(Maintainer::get()->requests)) {
				echo '<h4>'
					. ($simulation ? '[Simulation] Requests' : 'Updated') . SP . $class_name
					. '</h4>';
				echo join(BRLF, Maintainer::get()->requests) . BRLF;
				Maintainer::get()->requests = [];
			}
			if (ob_get_length() === false) {
				ob_start();
			}
			ob_flush();
			flush();
		}
	}

}
