<?php
namespace SAF\Framework\Tests;
use SAF\Framework\Controller_Parameters;
use SAF\Framework\Controller_Uri;

class Controller_Uri_Test extends Unit_Test
{

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

}
