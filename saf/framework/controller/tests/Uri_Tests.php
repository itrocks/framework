<?php
namespace SAF\Framework\Controller\Tests;

use SAF\Framework\Controller\Feature;
use SAF\Framework\Controller\Parameters;
use SAF\Framework\Controller\Uri;
use SAF\Framework\Tests\Objects\Order;
use SAF\Framework\Tests\Test;
use SAF\Framework\Tools\Names;
use SAF\Framework\Widget\Tab;
use SAF\Framework\Widget\Trashcan;

/**
 * Controller uri features tests
 */
class Uri_Tests extends Test
{

	//------------------------------------------------------------------------- testDeleteControllers
	public function testDeleteControllers()
	{
		$controller_uri = new Uri(
			'/SAF/Framework/Widget/Tab/remove/'
				. Names::classToSet(Order::class) . SL . Feature::F_LIST . '/date/number',
			['as_widget' => 1, '_' => 2]
		);
		$this->assume(
			__METHOD__,
			[
				'controller_name' => $controller_uri->controller_name,
				'feature_name'    => $controller_uri->feature_name,
				'parameters'      => $controller_uri->parameters->getRawParameters()
			],
			[
				'controller_name' => Tab::class,
				'feature_name' => 'remove',
				'parameters' => (new Parameters())->addValue(Names::classToSet(Order::class))
					->addValue(Feature::F_LIST)->addValue('date')->addValue('number')->set('as_widget', 1)
					->set('_', 2)->getRawParameters()
			]
		);
	}

	//---------------------------------------------------------------------------- testExplicitOutput
	public function testExplicitOutput()
	{
		$controller_uri = new Uri('/SAF/Framework/Tests/Objects/Order/1/' . Feature::F_OUTPUT, []);
		$this->assume(
				__METHOD__,
				[
						'controller_name' => $controller_uri->controller_name,
						'feature_name'    => $controller_uri->feature_name,
						'parameters'      => $controller_uri->parameters->getRawParameters()
				],
				[
						'controller_name' => Order::class,
						'feature_name' => Feature::F_OUTPUT,
						'parameters' => (new Parameters())->set(Order::class, 1)->getRawParameters()
				]
		);
	}

	//------------------------------------------------------------------------------ testImplicitList
	public function testImplicitList()
	{
		$controller_uri = new Uri('/SAF/Framework/Tests/Objects/Orders', []);
		$this->assume(
			__METHOD__,
			[
				'controller_name' => $controller_uri->controller_name,
				'feature_name'    => $controller_uri->feature_name,
				'parameters'      => $controller_uri->parameters->getRawParameters()
			],
			[
				'controller_name' => 'SAF\Framework\Tests\Objects\Orders',
				'feature_name' => Feature::F_LIST,
				'parameters' => (new Parameters())->getRawParameters()
			]
		);
	}

	//---------------------------------------------------------------------------- testImplicitOutput
	public function testImplicitOutput()
	{
		$controller_uri = new Uri('/SAF/Framework/Tests/Objects/Order/1', []);
		$this->assume(
			__METHOD__,
			[
				'controller_name' => $controller_uri->controller_name,
				'feature_name'    => $controller_uri->feature_name,
				'parameters'      => $controller_uri->parameters->getRawParameters()
			],
			[
				'controller_name' => Order::class,
				'feature_name' => Feature::F_OUTPUT,
				'parameters' => (new Parameters())->set(Order::class, 1)->getRawParameters()
			]
		);
	}

	//----------------------------------------------------------------------- testListRemoveParameter
	public function testListRemoveParameter()
	{
		$controller_uri = new Uri('/SAF/Framework/Tests/Objects/Orders/listRemove/date');
		$this->assume(
			__METHOD__,
			[
				'controller_name' => $controller_uri->controller_name,
				'feature_name'    => $controller_uri->feature_name,
				'parameters'      => $controller_uri->parameters->getRawParameters()
			],
			[
				'controller_name' => 'SAF\Framework\Tests\Objects\Orders',
				'feature_name' => 'listRemove',
				'parameters' => (new Parameters())->addValue('date')->getRawParameters()
			]
		);
	}

	//---------------------------------------------------------------------- testListRemoveParameters
	public function testListRemoveParameters()
	{
		$controller_uri = new Uri('/SAF/Framework/Tests/Objects/Orders/listRemove/date/number');
		$this->assume(
			__METHOD__,
			[
				'controller_name' => $controller_uri->controller_name,
				'feature_name'    => $controller_uri->feature_name,
				'parameters'      => $controller_uri->parameters->getRawParameters()
			],
			[
				'controller_name' => 'SAF\Framework\Tests\Objects\Orders',
				'feature_name' => 'listRemove',
				'parameters' => (new Parameters())->addValue('date')->addValue('number')
					->getRawParameters()
			]
		);
	}

	//------------------------------------------------------------------- testListRemoveWithArguments
	public function testListRemoveWithArguments()
	{
		$controller_uri = new Uri(
			'/SAF/Framework/Tests/Objects/Orders/listRemove/date/number', ['as_widget' => 1, '_' => 2]
		);
		$this->assume(
			__METHOD__,
			[
				'controller_name' => $controller_uri->controller_name,
				'feature_name'    => $controller_uri->feature_name,
				'parameters'      => $controller_uri->parameters->getRawParameters()
			],
			[
				'controller_name' => 'SAF\Framework\Tests\Objects\Orders',
				'feature_name' => 'listRemove',
				'parameters' => (new Parameters())->addValue('date')->addValue('number')
					->set('as_widget', 1)->set('_', 2)->getRawParameters()
			]
		);
	}

	//------------------------------------------------------------------------ testTrashcanDropOutput
	public function testTrashcanDropOutput()
	{
		$controller_uri = new Uri(
			'/SAF/Framework/Widget/Trashcan/drop/Order/1/' . Feature::F_OUTPUT . '/date/number',
			['as_widget' => 1, '_' => 2]
		);
		$this->assume(
			__METHOD__,
			[
				'controller_name' => $controller_uri->controller_name,
				'feature_name'    => $controller_uri->feature_name,
				'parameters'      => $controller_uri->parameters->getRawParameters()
			],
			[
				'controller_name' => Trashcan::class,
				'feature_name' => 'drop',
				'parameters' => (new Parameters())->set('Order', 1)->addValue(Feature::F_OUTPUT)
					->addValue('date')->addValue('number')->set('as_widget', 1)->set('_', 2)
					->getRawParameters()
			]
		);
	}

	//-------------------------------------------------------------------- testTrashcanDropParameters
	public function testTrashcanDropParameters()
	{
		$controller_uri = new Uri(
			'/SAF/Framework/Widget/Trashcan/drop/Orders/' . Feature::F_LIST . '/date/number'
		);
		$this->assume(
			__METHOD__,
			[
				'controller_name' => $controller_uri->controller_name,
				'feature_name'    => $controller_uri->feature_name,
				'parameters'      => $controller_uri->parameters->getRawParameters()
			],
			[
				'controller_name' => Trashcan::class,
				'feature_name' => 'drop',
				'parameters' => (new Parameters())->addValue('Orders')->addValue(Feature::F_LIST)
					->addValue('date')->addValue('number')->getRawParameters()
			]
		);
	}

	//----------------------------------------------------------------- testTrashcanDropWithArguments
	public function testTrashcanDropWithArguments()
	{
		$controller_uri = new Uri(
			'/SAF/Framework/Widget/Trashcan/drop/Orders/' . Feature::F_LIST . '/date/number',
			['as_widget' => 1, '_' => 2]
		);
		$this->assume(
			__METHOD__,
			[
				'controller_name' => $controller_uri->controller_name,
				'feature_name'    => $controller_uri->feature_name,
				'parameters'      => $controller_uri->parameters->getRawParameters()
			],
			[
				'controller_name' => Trashcan::class,
				'feature_name' => 'drop',
				'parameters' => (new Parameters())->addValue('Orders')->addValue(Feature::F_LIST)
					->addValue('date')->addValue('number')->set('as_widget', 1)->set('_', 2)
					->getRawParameters()
			]
		);
	}

}
