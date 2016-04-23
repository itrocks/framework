<?php
namespace SAF\Framework\View\Html\Builder\Tests;

use SAF\Framework\Controller\Feature;
use SAF\Framework\Controller\Parameter;
use SAF\Framework\Controller\Parameters;
use SAF\Framework\Controller\Uri;
use SAF\Framework\Tests\Objects\Vehicle;
use SAF\Framework\Tests\Objects\Vehicle_Door;
use SAF\Framework\Tests\Objects\Vehicle_Door_Piece;
use SAF\Framework\Tests\Test;
use SAF\Framework\View;
use SAF\Framework\Widget\Edit\Edit_Controller;

/**
 * HTML view collection builder tests
 */
class Collection_Tests extends Test
{

	//---------------------------------------------------------------------------------- buildVehicle
	/**
	 * @return Vehicle
	 */
	private function buildVehicle()
	{
		// vehicle door pieces
		$lever = new Vehicle_Door_Piece();
		$lever->name = 'lever';
		$lock = new Vehicle_Door_Piece();
		$lock->name = 'lock';
		$window = new Vehicle_Door_Piece();
		$window->name = 'window';
		// the vehicle will have two doors : one with a lock, the other without a lock
		$door1 = new Vehicle_Door();
		$door1->code = 'fl';
		$door1->side = 'front-left';
		$door1->pieces = [$lever, $lock, $window];
		$door2 = new Vehicle_Door();
		$door2->code = 'fr';
		$door2->side = 'front-right';
		$door2->pieces = [$lever, $window];
		// vehicle assembly
		$vehicle = new Vehicle();
		$vehicle->name = 'Test Vehicle';
		$vehicle->doors = [$door1, $door2];
		return $vehicle;
	}

	//--------------------------------------------------------------------- callVehicleEditController
	/**
	 * @param $vehicle Vehicle
	 * @return string
	 */
	private function callVehicleEditController(Vehicle $vehicle)
	{
		$edit       = new Edit_Controller();
		$uri        = new Uri(View::link($vehicle, Feature::F_EDIT));
		$parameters = new Parameters($uri);
		$parameters->set(Parameter::IS_INCLUDED, true);
		$parameters->unshift($vehicle);
		return $edit->run($parameters, [], [], Vehicle::class);
	}

	//--------------------------------------------------------------------------------- getInputNames
	/**
	 * @param $html string
	 * @returns string[]
	 */
	private function getInputNames($html)
	{
		$input_names = [];
		foreach (explode('<input', $html) as $key => $input) {
			if ($key) {
				$name = mParse(lParse($input, '>'), 'name=' . DQ, DQ);
				if ($name) {
					$input_names[] = $name;
				}
			}
		}
		return $input_names;
	}

	//------------------------------------------------------------------------ getVehicleAssumedNames
	/**
	 * @return string[]
	 */
	private function getVehicleAssumedNames()
	{
		return [
			'doors[id][0]',
			'doors[code][0]',
			'doors[pieces][0][id][0]',
			'doors[pieces][0][name][0]',
			'doors[pieces][0][id][1]',
			'doors[pieces][0][name][1]',
			'doors[pieces][0][id][2]',
			'doors[pieces][0][name][2]',
			'doors[pieces][0][id][3]',
			'doors[pieces][0][name][3]',
			'doors[id][1]',
			'doors[code][1]',
			'doors[pieces][1][id][0]',
			'doors[pieces][1][name][0]',
			'doors[pieces][1][id][1]',
			'doors[pieces][1][name][1]',
			'doors[pieces][1][id][2]',
			'doors[pieces][1][name][2]',
			'doors[id][2]',
			'doors[code][2]',
			'doors[pieces][2][id][0]',
			'doors[pieces][2][name][0]',
			'name'
		];
	}

	//-------------------------------------------------------------------- testCollectionOfCollection
	/**
	 * A collection inside a collection
	 *
	 * @return boolean
	 */
	public function testCollectionOfCollection()
	{
		$vehicle       = $this->buildVehicle();
		$html          = $this->callVehicleEditController($vehicle);
		$input_names   = $this->getInputNames($html);
		$assumed_names = $this->getVehicleAssumedNames();
		return $this->assume(__METHOD__, $input_names, $assumed_names);
	}

}
