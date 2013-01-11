<?php
namespace SAF\Framework\Tests;
use SAF\Framework\Controller_Parameters;
use SAF\Framework\Controller_Uri;

class Controller_Uri_Test extends Unit_Test
{

	//---------------------------------------------------------------------------- testExclicitOutput
	public function testExclicitOutput()
	{
		$controller_uri = new Controller_Uri("/Test_Order/1/output", array(), "output", "list");
		$this->assume(
				__METHOD__,
				array(
						"controller_name" => $controller_uri->controller_name,
						"feature_name"    => $controller_uri->feature_name,
						"parameters"      => $controller_uri->parameters->getRawParameters()
				),
				array(
						"controller_name" => "Test_Order",
						"feature_name" => "output",
						"parameters" => (new Controller_Parameters())->set("Test_Order", 1)->getRawParameters()
				)
		);
	}

	//------------------------------------------------------------------------------ testImplicitList
	public function testImplicitList()
	{
		$controller_uri = new Controller_Uri("/Test_Orders", array(), "output", "list");
		$this->assume(
			__METHOD__,
			array(
				"controller_name" => $controller_uri->controller_name,
				"feature_name"    => $controller_uri->feature_name,
				"parameters"      => $controller_uri->parameters->getRawParameters()
			),
			array(
				"controller_name" => "Test_Orders",
				"feature_name" => "list",
				"parameters" => (new Controller_Parameters())->getRawParameters()
			)
		);
	}

	//---------------------------------------------------------------------------- testImplicitOutput
	public function testImplicitOutput()
	{
		$controller_uri = new Controller_Uri("/Test_Order/1", array(), "output", "list");
		$this->assume(
			__METHOD__,
			array(
				"controller_name" => $controller_uri->controller_name,
				"feature_name"    => $controller_uri->feature_name,
				"parameters"      => $controller_uri->parameters->getRawParameters()
			),
			array(
				"controller_name" => "Test_Order",
				"feature_name" => "output",
				"parameters" => (new Controller_Parameters())->set("Test_Order", 1)->getRawParameters()
			)
		);
	}

	//----------------------------------------------------------------------- testListRemoveParameter
	public function testListRemoveParameter()
	{
		$controller_uri = new Controller_Uri("/Test_Orders/listRemove/date");
		$this->assume(
			__METHOD__,
			array(
				"controller_name" => $controller_uri->controller_name,
				"feature_name"    => $controller_uri->feature_name,
				"parameters"      => $controller_uri->parameters->getRawParameters()
			),
			array(
				"controller_name" => "Test_Orders",
				"feature_name" => "listRemove",
				"parameters" => (new Controller_Parameters())->addValue("date")->getRawParameters()
			)
		);
	}

	//---------------------------------------------------------------------- testListRemoveParameters
	public function testListRemoveParameters()
	{
		$controller_uri = new Controller_Uri("/Test_Orders/listRemove/date/number");
		$this->assume(
			__METHOD__,
			array(
				"controller_name" => $controller_uri->controller_name,
				"feature_name"    => $controller_uri->feature_name,
				"parameters"      => $controller_uri->parameters->getRawParameters()
			),
			array(
				"controller_name" => "Test_Orders",
				"feature_name" => "listRemove",
				"parameters" => (new Controller_Parameters())->addValue("date")->addValue("number")->getRawParameters()
			)
		);
	}

	//------------------------------------------------------------------- testListRemoveWithArguments
	public function testListRemoveWithArguments()
	{
		$controller_uri = new Controller_Uri("/Test_Orders/listRemove/date/number", array("as_widget" => 1, "_" => 2));
		$this->assume(
			__METHOD__,
			array(
				"controller_name" => $controller_uri->controller_name,
				"feature_name"    => $controller_uri->feature_name,
				"parameters"      => $controller_uri->parameters->getRawParameters()
			),
			array(
				"controller_name" => "Test_Orders",
				"feature_name" => "listRemove",
				"parameters" => (new Controller_Parameters())->addValue("date")->addValue("number")->set("as_widget", 1)->set("_", 2)->getRawParameters()
			)
		);
	}

	//------------------------------------------------------------------------ testTrashcanDropOutput
	public function testTrashcanDropOutput()
	{
		$controller_uri = new Controller_Uri("/Trashcan/drop/Test_Order/1/output/date/number", array("as_widget" => 1, "_" => 2));
		$this->assume(
			__METHOD__,
			array(
				"controller_name" => $controller_uri->controller_name,
				"feature_name"    => $controller_uri->feature_name,
				"parameters"      => $controller_uri->parameters->getRawParameters()
			),
			array(
				"controller_name" => "Trashcan",
				"feature_name" => "drop",
				"parameters" => (new Controller_Parameters())->set("Test_Order", 1)->addValue("output")->addValue("date")->addValue("number")->set("as_widget", 1)->set("_", 2)->getRawParameters()
			)
		);
	}

	//-------------------------------------------------------------------- testTrashcanDropParameters
	public function testTrashcanDropParameters()
	{
		$controller_uri = new Controller_Uri("/Trashcan/drop/Test_Orders/list/date/number");
		$this->assume(
			__METHOD__,
			array(
				"controller_name" => $controller_uri->controller_name,
				"feature_name"    => $controller_uri->feature_name,
				"parameters"      => $controller_uri->parameters->getRawParameters()
			),
			array(
				"controller_name" => "Trashcan",
				"feature_name" => "drop",
				"parameters" => (new Controller_Parameters())->addValue("Test_Orders")->addValue("list")->addValue("date")->addValue("number")->getRawParameters()
			)
		);
	}

	//----------------------------------------------------------------- testTrashcanDropWithArguments
	public function testTrashcanDropWithArguments()
	{
		$controller_uri = new Controller_Uri("/Trashcan/drop/Test_Orders/list/date/number", array("as_widget" => 1, "_" => 2));
		$this->assume(
			__METHOD__,
			array(
				"controller_name" => $controller_uri->controller_name,
				"feature_name"    => $controller_uri->feature_name,
				"parameters"      => $controller_uri->parameters->getRawParameters()
			),
			array(
				"controller_name" => "Trashcan",
				"feature_name" => "drop",
				"parameters" => (new Controller_Parameters())->addValue("Test_Orders")->addValue("list")->addValue("date")->addValue("number")->set("as_widget", 1)->set("_", 2)->getRawParameters()
			)
		);
	}

}
