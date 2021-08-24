<?php
namespace ITRocks\Framework\Dao\Mysql;

use ITRocks\Framework\Builder;
use ITRocks\Framework\Console;
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

	//-------------------------------------------------------------------------- $create_empty_tables
	/**
	 * true to create all business class tables (this value is not used for implicit link tables)
	 *
	 * @var boolean
	 */
	protected bool $create_empty_tables;

	//-------------------------------------------------------------------------------------- $verbose
	/**
	 * @var boolean
	 */
	protected bool $verbose;

	//------------------------------------------------------------------------------------ classNamed
	/**
	 * @param $class_name string
	 * @return ?Reflection_Class
	 */
	protected function classNamed(string $class_name) : ?Reflection_Class
	{
		static $reflection_classes = [];
		if (isset($reflection_classes[$class_name])) {
			return $reflection_classes[$class_name];
		}
		try {
			$class = new Reflection_Class($class_name);
		}
		catch (ReflectionException) {
			if ($this->verbose && ($class_name !== Console::class)) {
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
	 * @return Reflection_Class[] key is the name of the class
	 */
	protected function getClasses() : array
	{
		// cache hierarchy
		$children = [];
		foreach (Dao::search(['type' => Dependency::T_EXTENDS], Dependency::class) as $dependency) {
			$children[$dependency->dependency_name][$dependency->class_name] = $dependency->class_name;
		}
		// tables
		/** @var $mysql Mysql\Link */
		$mysql = Dao::current();
		$table_names = array_flip($mysql->getConnection()->getTables());
		// @business classes + without Builder replacement + without children with same @store_name
		// + existing MySQL table
		$classes      = [];
		$dependencies = Dao::search(['declaration' => Dependency::T_CLASS], Dependency::class);
		foreach ($dependencies as $dependency) {
			$class_name = Builder::className($dependency->class_name);
			if (!isset($classes[$class_name])) {
				$class = $this->classNamed($class_name);
				if (
					$class
					&& !$class->isAbstract()
					&& $class->getAnnotation('business')->value
					&& !$class->getAnnotation('deprecated')->value
				) {
					$store = true;
					if (isset($children[$class_name])) {
						foreach ($children[$class_name] as $child_class_name) {
							$child_class = $this->classNamed($child_class_name);
							if (
								$child_class
								&& Store_Name_Annotation::equals($child_class, $class)
								&& !$child_class->isAbstract()
								&& $child_class->getAnnotation('business')->value
								&& !$child_class->getAnnotation('deprecated')->value
							) {
								$store = false;
								break;
							}
						}
					}
					if (
						$store
						&& (
							$this->create_empty_tables
							|| isset($table_names[Store_Name_Annotation::of($class)->value])
						)
					) {
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
	 * @return string
	 */
	public function run(Parameters $parameters, array $form, array $files) : string
	{
		upgradeTimeLimit(7200);

		$create_empty_tables = $parameters->getRawParameter('create_empty_tables')
			?: isset($parameters->getRawParameters()['create_empty_tables']);
		$simulation = !isset($parameters->getRawParameters()['valid']);
		$verbose    = isset($parameters->getRawParameters()['verbose']);

		$this->create_empty_tables = $create_empty_tables && ($create_empty_tables !== 'implicit');
		$this->verbose             = $verbose;

		$classes = $this->getClasses();

		$maintainer                      = Maintainer::get();
		$maintainer->create_empty_tables = $create_empty_tables;
		$maintainer->notice              = $verbose ? Maintainer::VERBOSE : Maintainer::OUTPUT;

		if ($simulation) {
			$maintainer->simulationStart();
		}

		$this->updateAllTables($classes, $simulation);

		if ($simulation) {
			$maintainer->simulationStop();
		}

		return 'Maintenance done';
	}

	//------------------------------------------------------------------------------- updateAllTables
	/**
	 * @param $classes    Reflection_Class[]
	 * @param $simulation boolean
	 */
	protected function updateAllTables(array $classes, bool $simulation)
	{
		foreach ($classes as $class) {
			$class_name = $class->name;
			$maintainer = Maintainer::get();
			$maintainer->verbose = $this->verbose;
			$maintainer->updateTable($class_name);
			if (count($maintainer->requests)) {
				echo '<h4>'
					. ($simulation ? '[Simulation] Requests' : 'Updated') . SP . $class_name
					. '</h4>';
				echo join(BRLF, $maintainer->requests) . BRLF;
				$maintainer->requests = [];
			}
			if (ob_get_length() === false) {
				ob_start();
			}
			ob_flush();
			flush();
		}
	}

}
