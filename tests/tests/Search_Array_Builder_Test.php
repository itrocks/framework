<?php
namespace SAF\Tests;
use SAF\Framework\Search_Array_Builder;

class Search_Array_Builder_Test extends \SAF\Framework\Unit_Tests\Unit_Test
{

	//------------------------------------------------------------------------------------- testBuild
	public function testBuild()
	{
		$this->assume(
			__METHOD__ . ".simple",
			(new Search_Array_Builder())->build("property", "test"),
			array("property" => "test")
		);
		$this->assume(
			__METHOD__ . ".and",
			(new Search_Array_Builder())->build("property", "test what"),
			array("property" => array("AND" => array("test", "what")))
		);
		$this->assume(
			__METHOD__ . ".or",
			(new Search_Array_Builder())->build("property", "test,what"),
			array("property" => array("test", "what"))
		);
		$this->assume(
			__METHOD__ . ".mix",
			(new Search_Array_Builder())->build("property", "test,what else"),
			array("property" => array("test", "AND" => array("what", "else")))
		);
	}

	//----------------------------------------------------------------------------- testBuildMultiple
	public function testBuildMultiple()
	{
		$this->assume(
			__METHOD__ . ".simple",
			(new Search_Array_Builder())->buildMultiple(array("pro1", "pro2"), "test"),
			array("OR" => array("pro1" => "test", "pro2" => "test"))
		);
		$this->assume(
			__METHOD__ . ".and",
			(new Search_Array_Builder())->buildMultiple(array("pro1", "pro2"), "test what"),
			array(
				"AND" => array(
					array("OR" => array("pro1" => "test", "pro2" => "test")),
					array("OR" => array("pro1" => "what", "pro2" => "what"))
				)
			)
		);
		$this->assume(
			__METHOD__ . ".or",
			(new Search_Array_Builder())->buildMultiple(array("pro1", "pro2"), "test,what"),
			array(
				"OR" => array(
					"pro1" => array("test", "what"),
					"pro2" => array("test", "what")
				)
			)
		);
		$this->assume(
			__METHOD__ . ".mix",
			(new Search_Array_Builder())->buildMultiple(array("pro1", "pro2"), "test,what else"),
			array(
				"OR" => array(
					"pro1" => array("test", "AND" => array("what", "else")),
					"pro2" => array("test", "AND" => array("what", "else"))
				)
			)
		);
	}

}
