<?php
namespace ITRocks\Framework\Widget\Condition;

use Bappli\Sfkgroup\Insurance\Contract;
use ITRocks\Framework\Controller\Default_Feature_Controller;
use ITRocks\Framework\Controller\Parameters;
use ITRocks\Framework\Dao;
use ITRocks\Framework\Dao\Func;
use ITRocks\Framework\Reflection\Annotation\Property\User_Annotation;
use ITRocks\Framework\Reflection\Reflection_Class;
use ITRocks\Framework\Tools\Date_Time;
use ITRocks\Framework\View;
use ITRocks\Framework\Widget\Condition;
use ITRocks\Framework\Widget\Validate\Property\Mandatory_Annotation;
use Sfkgroup\Agency;
use Sfkgroup\Contract\Status;
use Sfkgroup\Insurance\Contract\Package;

/**
 * Condition controller
 *
 * Applies on Set
 */
class Controller implements Default_Feature_Controller
{

	//--------------------------------------------------------------------------------------- FEATURE
	const FEATURE = 'condition';

	//------------------------------------------------------------------------ $mandatory_annotations
	/**
	 * @var Mandatory_Annotation[]
	 */
	protected $mandatory_annotations = [];

	//------------------------------------------------------------------------ $read_only_annotations
	/**
	 * @var User_Annotation[]
	 */
	protected $read_only_annotations = [];

	//----------------------------------------------------------------------------- prepareProperties
	/**
	 * Prepare properties to be fully editable for search criteria :
	 *
	 * - remove @user readonly
	 *
	 * @param $class_name string
	 */
	protected function prepareProperties($class_name)
	{
		foreach ((new Reflection_Class($class_name))->getProperties([T_EXTENDS, T_USE]) as $property) {
			$mandatory = Mandatory_Annotation::of($property);
			if ($mandatory->value) {
				$mandatory->value = false;
				$this->mandatory_annotations[] = $mandatory;
			}
			$user = User_Annotation::of($property);
			if ($user->has(User_Annotation::READONLY)) {
				$user->remove(User_Annotation::READONLY);
				$this->read_only_annotations[] = $user;
			}
		}
	}

	//------------------------------------------------------------------------------- resetProperties
	/**
	 * Reset properties as they were before working on condition view :
	 *
	 * - get back @user readonly
	 */
	protected function resetProperties()
	{
		foreach ($this->mandatory_annotations as $mandatory) {
			$mandatory->value = true;
		}
		foreach ($this->read_only_annotations as $user) {
			$user->add(User_Annotation::READONLY);
		}
	}

	//------------------------------------------------------------------------------------------- run
	/**
	 * Run method for a feature controller working for any class
	 *
	 * @param $parameters Parameters
	 * @param $form       array
	 * @param $files      array[]
	 * @param $class_name string
	 * @return mixed
	 */
	public function run(Parameters $parameters, array $form, array $files, $class_name)
	{
		// For testing purpose : a condition on a contract
		$condition = new Condition($class_name, Func::andOp([]));
		if (is_a($class_name, Contract::class, true)) {
			$condition = new Condition($class_name, Func::andOp([
				'package' => Func::in([
					Dao::searchOne(['name' => 'Infinity'], Package::class),
					Dao::searchOne(['name' => 'Infinity Web'], Package::class)
				]),
				//Func::now(true) => Func::greaterOrEqual(new Date_Time('2018-01-02')),
				'status'        => Func::in([Status::INCOMPLETE, Status::VALID]),
				Func::orOp([
					'agency'             => Func::equal(Dao::searchOne(['name' => 'FNAC St Nazaire'], Agency::class)),
					'agency.main_agency' => Func::equal(Dao::searchOne(['name' => 'FNAC ACCES'], Agency::class))
				])
			]));
		}
//echo PRE . print_r($condition, true) . _PRE;
		$parameters->set(self::FEATURE, $condition);
		$parameters->getMainObject($class_name);
		$this->prepareProperties($class_name);
		$parameters = $parameters->getObjects();
		$output = View::run($parameters, $form, $files, $class_name, self::FEATURE);
		$this->resetProperties();
		return $output;
	}

}
