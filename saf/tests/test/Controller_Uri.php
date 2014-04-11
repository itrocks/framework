<?php
namespace SAF\Tests\Test;

use SAF\Framework\Controller\Parameters;
use SAF\Framework\Controller\Uri;
use SAF\Framework\Test;
use SAF\Framework\Tools\Names;
use SAF\Framework\Widget\Tab;
use SAF\Framework\Widget\Trashcan;
use SAF\Tests\Objects\Order;

/**
 * Controller uri features tests
 */
class Controller_Uri extends Test
{

	//------------------------------------------------------------------------- testDeleteControllers
	public function testDeleteControllers()
	{
		$controller_uri = new Uri(
			'/Tab/remove/'. Names::classToSet(Order::class) . '/list/date/number',
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
					->addValue('list')->addValue('date')->addValue('number')->set('as_widget', 1)
					->set('_', 2)->getRawParameters()
			]
		);
	}

	//---------------------------------------------------------------------------- testExclicitOutput
	public function testExclicitOutput()
	{
		$controller_uri = new Uri('/Order/1/output', [], 'output', 'list');
		$this->assume(
				__METHOD__,
				[
						'controller_name' => $controller_uri->controller_name,
						'feature_name'    => $controller_uri->feature_name,
						'parameters'      => $controller_uri->parameters->getRawParameters()
				],
				[
						'controller_name' => Order::class,
						'feature_name' => 'output',
						'parameters' => (new Parameters())->set('Order', 1)->getRawParameters()
				]
		);
	}

	//------------------------------------------------------------------------------ testImplicitList
	public function testImplicitList()
	{
		$controller_uri = new Uri('/Orders', [], 'output', 'list');
		$this->assume(
			__METHOD__,
			[
				'controller_name' => $controller_uri->controller_name,
				'feature_name'    => $controller_uri->feature_name,
				'parameters'      => $controller_uri->parameters->getRawParameters()
			],
			[
				'controller_name' => 'Orders',
				'feature_name' => 'list',
				'parameters' => (new Parameters())->getRawParameters()
			]
		);
	}

	//---------------------------------------------------------------------------- testImplicitOutput
	public function testImplicitOutput()
	{
		$controller_uri = new Uri('/Order/1', [], 'output', 'list');
		$this->assume(
			__METHOD__,
			[
				'controller_name' => $controller_uri->controller_name,
				'feature_name'    => $controller_uri->feature_name,
				'parameters'      => $controller_uri->parameters->getRawParameters()
			],
			[
				'controller_name' => Order::class,
				'feature_name' => 'output',
				'parameters' => (new Parameters())->set('Order', 1)->getRawParameters()
			]
		);
	}

	//----------------------------------------------------------------------- testListRemoveParameter
	public function testListRemoveParameter()
	{
		$controller_uri = new Uri('/Orders/listRemove/date');
		$this->assume(
			__METHOD__,
			[
				'controller_name' => $controller_uri->controller_name,
				'feature_name'    => $controller_uri->feature_name,
				'parameters'      => $controller_uri->parameters->getRawParameters()
			],
			[
				'controller_name' => 'Orders',
				'feature_name' => 'listRemove',
				'parameters' => (new Parameters())->addValue('date')->getRawParameters()
			]
		);
	}

	//---------------------------------------------------------------------- testListRemoveParameters
	public function testListRemoveParameters()
	{
		$controller_uri = new Uri('/Orders/listRemove/date/number');
		$this->assume(
			__METHOD__,
			[
				'controller_name' => $controller_uri->controller_name,
				'feature_name'    => $controller_uri->feature_name,
				'parameters'      => $controller_uri->parameters->getRawParameters()
			],
			[
				'controller_name' => 'Orders',
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
			'/Orders/listRemove/date/number', ['as_widget' => 1, '_' => 2]
		);
		$this->assume(
			__METHOD__,
			[
				'controller_name' => $controller_uri->controller_name,
				'feature_name'    => $controller_uri->feature_name,
				'parameters'      => $controller_uri->parameters->getRawParameters()
			],
			[
				'controller_name' => 'Orders',
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
			'/Trashcan/drop/Order/1/output/date/number', ['as_widget' => 1, '_' => 2]
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
				'parameters' => (new Parameters())->set('Order', 1)->addValue('output')
					->addValue('date')->addValue('number')->set('as_widget', 1)->set('_', 2)
					->getRawParameters()
			]
		);
	}

	//-------------------------------------------------------------------- testTrashcanDropParameters
	public function testTrashcanDropParameters()
	{
		$controller_uri = new Uri('/Trashcan/drop/Orders/list/date/number');
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
				'parameters' => (new Parameters())->addValue('Orders')->addValue('list')
					->addValue('date')->addValue('number')->getRawParameters()
			]
		);
	}

	//----------------------------------------------------------------- testTrashcanDropWithArguments
	public function testTrashcanDropWithArguments()
	{
		$controller_uri = new Uri(
			'/Trashcan/drop/Orders/list/date/number', ['as_widget' => 1, '_' => 2]
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
				'parameters' => (new Parameters())->addValue('Orders')->addValue('list')
					->addValue('date')->addValue('number')->set('as_widget', 1)->set('_', 2)
					->getRawParameters()
			]
		);
	}

}
