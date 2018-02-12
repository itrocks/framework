<?php
namespace ITRocks\Framework\Widget\Condition;

use Bappli\Sfkgroup\Insurance\Contract;
use ITRocks\Framework\Controller\Default_Feature_Controller;
use ITRocks\Framework\Controller\Parameters;
use ITRocks\Framework\Dao;
use ITRocks\Framework\Dao\Func;
use ITRocks\Framework\Tools\Date_Time;
use ITRocks\Framework\View;
use ITRocks\Framework\Widget\Condition;
use Sfkgroup\Agency;
use Sfkgroup\Contract\Status;

/**
 * Condition controller
 *
 * Applies on Set
 */
class Controller implements Default_Feature_Controller
{

	//--------------------------------------------------------------------------------------- FEATURE
	const FEATURE = 'condition';

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
		$condition = new Condition($class_name, Func::andOp([null, null]));
		/*
		if (is_a($class_name, Contract::class, true)) {
			$condition = new Condition($class_name, Func::andOp([
				'package.name'  => Func::in(['Infinity', 'Infinity Web']),
				Func::now(true) => Func::greaterOrEqual(new Date_Time('2018-01-02')),
				'status'        => Func::in([Status::INCOMPLETE, Status::VALID]),
				Func::orOp([
					'agency'             => Dao::searchOne(['name' => 'FNAC St Nazaire'], Agency::class),
					'agency.main_agency' => Dao::searchOne(['name' => 'FNAC ACCES'     ], Agency::class)
				])
			]));
		}
		*/
//echo PRE . print_r($condition, true) . _PRE;
		$parameters->set(self::FEATURE, $condition);
		$parameters = $parameters->getObjects();
		return View::run($parameters, $form, $files, $class_name, self::FEATURE);
	}

}
