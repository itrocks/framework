<?php
namespace ITRocks\Framework\Dao\Mysql;

use ITRocks\Framework\Controller\Feature_Controller;
use ITRocks\Framework\Controller\Parameters;
use ITRocks\Framework\Dao;
use ITRocks\Framework\Dao\Func;
use ITRocks\Framework\PHP\Dependency;

/**
 * Runs maintainer on all classes
 */
class Maintain_Controller implements Feature_Controller
{

	//----------------------------------------------------------------------------------- getChildren
	/**
	 * @param $class_names string[]
	 * @return string[]
	 */
	public function getChildren(array $class_names)
	{
		/** @var string[] $child_dependencies */
		$child_dependencies = array_unique(
			array_map(
				function (Dependency $dependency) {
					return $dependency->class_name;
				},
				Dao::search(
					['dependency_name' => Func::in($class_names), 'type' => Dependency::T_EXTENDS],
					Dependency::class
				)
			)
		);

		if ($child_dependencies) {
			$class_names = array_merge(
				$class_names,
				$child_dependencies,
				$this->getChildren($child_dependencies)
			);
		}

		return $class_names;
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
		/** @var string[] $class_names */
		$class_names = array_unique(
			array_map(
				function (Dependency $dependency) {
					return $dependency->class_name;
				},
				Dao::search(
					['type' => Func::orOp([Dependency::T_STORE, Dependency::T_SET])], Dependency::class
				)
			)
		);
		$class_names = $this->getChildren($class_names);

		$simulation = !isset($parameters->getRawParameters()['valid']);
		$verbose    = isset($parameters->getRawParameters()['verbose']);

		Maintainer::get()->notice = false;
		if ($simulation) {
			Maintainer::get()->simulationStart();
		}

		// Creation only first (no constraint)
		Maintainer::get()->skip_foreign_keys = true;
		echo '<h3>Without foreign keys</h3>';
		$this->updateAllTables($class_names, $verbose, $simulation);

		// Then another full update
		Maintainer::get()->skip_foreign_keys = false;
		echo '<h3>With foreign keys first</h3>';
		$this->updateAllTables($class_names, $verbose, $simulation);

		if ($simulation) {
			Maintainer::get()->simulationStop();
		}
		echo '<h4>Maintenance done</h4>';
		return;
	}

	//------------------------------------------------------------------------------- updateAllTables
	/**
	 * @param $class_names  String[]
	 * @param $verbose      string
	 * @param $simulation   boolean
	 */
	protected function updateAllTables($class_names, $verbose, $simulation)
	{
		/** @var Dependency $dependency */
		foreach ($class_names as $class_name) {
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
