<?php
namespace SAF\Framework;

require_once "framework/classes/toolbox/Aop.php";

abstract class Aop_Tracer
{

	//-------------------------------------------------------------------------------------- register
	/**
	 * Register this advice to trace calls to a given method
	 *
	 * @example Aop::registerBefore("Class_Name->methodName()", __NAMESPACE__ . "\\Aop_Tracer::method")
	 * @param AopJoinpoint $joinpoint
	 */
	public static function method($joinpoint)
	{
		if ($joinpoint->getKindOfAdvice() | AOP_KIND_BEFORE) {
			echo ".. before ";
		}
		if ($joinpoint->getKindOfAdvice() | AOP_KIND_AFTER) {
			echo ".. after ";
		}
		echo "<pre>" . print_r($joinpoint, true) . "</pre>";
		echo $joinpoint->getMethodName();
		$first = true;
		echo "(<br>";
		foreach ($joinpoint->getArguments() as $argument) {
			if (!$first) echo ","; else $first = false;
			echo " &nbsp; &nbsp; - " . print_r($argument, true) . "<br>";
		}
		echo ")<br>";
	}

}
