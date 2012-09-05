<?php
namespace SAF\Framework;

class Aop_Tracer
{

	//-------------------------------------------------------------------------------------- register
	/**
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
