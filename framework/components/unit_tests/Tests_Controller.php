<?php
namespace SAF\Framework\Unit_Tests;
use SAF\Framework\Controller_Parameters;
use SAF\Framework\Feature_Controller;
use SAF\Framework\Reflection_Class;
use SAF\Framework\Reflection_Method;

class Tests_Controller implements Feature_Controller
{

	//------------------------------------------------------------------------------------------ test
	public function run(Controller_Parameters $parameters, $form, $files)
	{
		(new Tests())->run();
	}

}
