<?php
namespace ITRocks\Framework\Controller\Tests;

use ITRocks\Framework\Component\Tab;
use ITRocks\Framework\Component\Trashcan;
use ITRocks\Framework\Controller\Feature;
use ITRocks\Framework\Controller\Parameter;
use ITRocks\Framework\Controller\Parameters;
use ITRocks\Framework\Controller\Uri;
use ITRocks\Framework\Tests\Objects\Order;
use ITRocks\Framework\Tests\Test;
use ITRocks\Framework\Tools\Names;

/**
 * Controller uri features tests
 */
class Uri_Test extends Test
{

	//------------------------------------------------------------------------- testDeleteControllers
	public function testDeleteControllers() : void
	{
		$controller_uri = new Uri(
			'/ITRocks/Framework/Component/Tab/remove/'
			. Names::classToSet(Order::class) . SL . Feature::F_LIST . '/date/number',
			[Parameter::AS_WIDGET => true, '_' => 2]
		);
		static::assertEquals(
			[
				'controller_name' => Tab::class,
				'feature_name'    => Feature::F_REMOVE,
				'parameters'      => (new Parameters())->addValue(Names::classToSet(Order::class))
					->addValue(Feature::F_LIST)->addValue('date')->addValue('number')
					->set(Parameter::AS_WIDGET, true)->set('_', 2)->getRawParameters()
			], [
				'controller_name' => $controller_uri->controller_name,
				'feature_name'    => $controller_uri->feature_name,
				'parameters'      => $controller_uri->parameters->getRawParameters()
			]
		);
	}

	//---------------------------------------------------------------------------- testExplicitOutput
	public function testExplicitOutput() : void
	{
		$controller_uri = new Uri('/ITRocks/Framework/Tests/Objects/Order/1/' . Feature::F_OUTPUT, []);
		static::assertEquals(
			[
				'controller_name' => Order::class,
				'feature_name'    => Feature::F_OUTPUT,
				'parameters'      => (new Parameters())->set(Order::class, 1)->getRawParameters()
			], [
				'controller_name' => $controller_uri->controller_name,
				'feature_name'    => $controller_uri->feature_name,
				'parameters'      => $controller_uri->parameters->getRawParameters()
			]
		);
	}

	//------------------------------------------------------------------------------ testImplicitList
	public function testImplicitList() : void
	{
		$controller_uri = new Uri('/ITRocks/Framework/Tests/Objects/Orders', []);
		static::assertEquals(
			[
				'controller_name' => 'ITRocks\Framework\Tests\Objects\Orders',
				'feature_name'    => Feature::F_LIST,
				'parameters'      => (new Parameters())->getRawParameters()
			], [
				'controller_name' => $controller_uri->controller_name,
				'feature_name'    => $controller_uri->feature_name,
				'parameters'      => $controller_uri->parameters->getRawParameters()
			]
		);
	}

	//---------------------------------------------------------------------------- testImplicitOutput
	public function testImplicitOutput() : void
	{
		$controller_uri = new Uri('/ITRocks/Framework/Tests/Objects/Order/1', []);
		static::assertEquals(
			[
				'controller_name' => Order::class,
				'feature_name'    => Feature::F_OUTPUT,
				'parameters'      => (new Parameters())->set(Order::class, 1)->getRawParameters()
			], [
				'controller_name' => $controller_uri->controller_name,
				'feature_name'    => $controller_uri->feature_name,
				'parameters'      => $controller_uri->parameters->getRawParameters()
			]
		);
	}

	//----------------------------------------------------------------------- testListRemoveParameter
	public function testListRemoveParameter() : void
	{
		$controller_uri = new Uri('/ITRocks/Framework/Tests/Objects/Orders/listRemove/date');
		static::assertEquals(
			[
				'controller_name' => 'ITRocks\Framework\Tests\Objects\Orders',
				'feature_name'    => 'listRemove',
				'parameters'      => (new Parameters())->addValue('date')->getRawParameters()
			], [
				'controller_name' => $controller_uri->controller_name,
				'feature_name'    => $controller_uri->feature_name,
				'parameters'      => $controller_uri->parameters->getRawParameters()
			]
		);
	}

	//---------------------------------------------------------------------- testListRemoveParameters
	public function testListRemoveParameters() : void
	{
		$controller_uri = new Uri('/ITRocks/Framework/Tests/Objects/Orders/listRemove/date/number');
		static::assertEquals(
			[
				'controller_name' => 'ITRocks\Framework\Tests\Objects\Orders',
				'feature_name'    => 'listRemove',
				'parameters'      => (new Parameters())->addValue('date')->addValue('number')
					->getRawParameters()
			], [
				'controller_name' => $controller_uri->controller_name,
				'feature_name'    => $controller_uri->feature_name,
				'parameters'      => $controller_uri->parameters->getRawParameters()
			]
		);
	}

	//------------------------------------------------------------------- testListRemoveWithArguments
	public function testListRemoveWithArguments()
	{
		$controller_uri = new Uri(
			'/ITRocks/Framework/Tests/Objects/Orders/listRemove/date/number',
			[Parameter::AS_WIDGET => true, '_' => 2]
		);
		static::assertEquals(
			[
				'controller_name' => 'ITRocks\Framework\Tests\Objects\Orders',
				'feature_name'    => 'listRemove',
				'parameters'      => (new Parameters())->addValue('date')->addValue('number')
					->set(Parameter::AS_WIDGET, true)->set('_', 2)->getRawParameters()
			], [
				'controller_name' => $controller_uri->controller_name,
				'feature_name'    => $controller_uri->feature_name,
				'parameters'      => $controller_uri->parameters->getRawParameters()
			]
		);
	}

	//------------------------------------------------------------------------ testTrashcanDropOutput
	public function testTrashcanDropOutput() : void
	{
		$controller_uri = new Uri(
			'/ITRocks/Framework/Component/Trashcan/drop/Order/1/' . Feature::F_OUTPUT . '/date/number',
			[Parameter::AS_WIDGET => true, '_' => 2]
		);
		static::assertEquals(
			[
				'controller_name' => Trashcan::class,
				'feature_name'    => 'drop',
				'parameters'      => (new Parameters())->set('Order', 1)->addValue(Feature::F_OUTPUT)
					->addValue('date')->addValue('number')->set(Parameter::AS_WIDGET, true)->set('_', 2)
					->getRawParameters()
			], [
				'controller_name' => $controller_uri->controller_name,
				'feature_name'    => $controller_uri->feature_name,
				'parameters'      => $controller_uri->parameters->getRawParameters()
			]
		);
	}

	//-------------------------------------------------------------------- testTrashcanDropParameters
	public function testTrashcanDropParameters() : void
	{
		$controller_uri = new Uri(
			'/ITRocks/Framework/Component/Trashcan/drop/Orders/' . Feature::F_LIST . '/date/number'
		);
		static::assertEquals(
			[
				'controller_name' => Trashcan::class,
				'feature_name'    => 'drop',
				'parameters'      => (new Parameters())->addValue('Orders')->addValue(Feature::F_LIST)
					->addValue('date')->addValue('number')->getRawParameters()
			], [
				'controller_name' => $controller_uri->controller_name,
				'feature_name'    => $controller_uri->feature_name,
				'parameters'      => $controller_uri->parameters->getRawParameters()
			]
		);
	}

	//----------------------------------------------------------------- testTrashcanDropWithArguments
	public function testTrashcanDropWithArguments() : void
	{
		$controller_uri = new Uri(
			'/ITRocks/Framework/Component/Trashcan/drop/Orders/' . Feature::F_LIST . '/date/number',
			[Parameter::AS_WIDGET => true, '_' => 2]
		);
		static::assertEquals(
			[
				'controller_name' => Trashcan::class,
				'feature_name'    => 'drop',
				'parameters'      => (new Parameters())->addValue('Orders')->addValue(Feature::F_LIST)
					->addValue('date')->addValue('number')->set(Parameter::AS_WIDGET, true)->set('_', 2)
					->getRawParameters()
			], [
				'controller_name' => $controller_uri->controller_name,
				'feature_name'    => $controller_uri->feature_name,
				'parameters'      => $controller_uri->parameters->getRawParameters()
			]
		);
	}

}
