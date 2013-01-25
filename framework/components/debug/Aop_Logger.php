<?php
namespace SAF\Framework;
use AopJoinpoint;

abstract class Aop_Logger implements Plugin
{

	public static $active;

	public static $inside;

	//------------------------------------------------------------------------------------------- log
	/**
	 * @param $joinpoint AopJoinpoint
	 */
	public static function log(AopJoinpoint $joinpoint)
	{
		if (self::$active) {
			if ($joinpoint->getKindOfAdvice() & AOP_KIND_BEFORE) {
				if (isset(self::$inside)) {
					echo "<div>Essaie de faire de l'aop depuis l'aop ! " . print_r(self::$inside, true)
						. "</div>";
					die();
				}
				self::$inside = $joinpoint;
			}
			elseif ($joinpoint->getKindOfAdvice() & AOP_KIND_AFTER) {
				if (!isset(self::$inside)) {
					echo "<div>Là c'est n'importe quoi !</div>";
				}
				self::$inside = null;
			}
			$arguments = $joinpoint->getArguments();
			$side = ($joinpoint->getKindOfAdvice() & AOP_KIND_BEFORE)
				? "before"
				: (($joinpoint->getKindOfAdvice() & AOP_KIND_AFTER) ? "after" : "");
			if ($side == "after") {
				echo "<div class=\"Aop logger " . $joinpoint->getFunctionName() . "\">"
					. "<b>" . $joinpoint->getFunctionName() . "</b> "
					. print_r($arguments[0], true) . " -&gt; " . print_r($arguments[1], true)
					. "</div>";
			}
		}
	}

	//----------------------------------------------------------------------------------- logRegister
	/**
	 * @param $joinpoint AopJoinpoint
	 */
	public static function logRegister(AopJoinpoint $joinpoint)
	{
		$arguments = $joinpoint->getArguments();
		echo "<div class=\"Aop logger register\">"
			. "Register " . $arguments[0] . " " . $arguments[1] . " " . $arguments[2]
			. "</div>";
	}

	//-------------------------------------------------------------------------------------- register
	public static function register()
	{
		self::$active = false;
		Aop::add("After",  "aop_add_after()",  array(__CLASS__, "log"));
		Aop::add("after",  "aop_add_around()", array(__CLASS__, "log"));
		Aop::add("after",  "aop_add_before()", array(__CLASS__, "log"));
		Aop::add("before", "aop_add_after()",  array(__CLASS__, "log"));
		Aop::add("before", "aop_add_around()", array(__CLASS__, "log"));
		Aop::add("before", "aop_add_before()", array(__CLASS__, "log"));
		self::$active = true;
		Aop::add("before",
			__NAMESPACE__ . "\\Aop->registerProperties()",
			array(__CLASS__, "logRegister")
		);
	}

}
