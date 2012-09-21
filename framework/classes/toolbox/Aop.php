<?php
namespace SAF\Framework;

abstract class Aop
{

	//--------------------------------------------------------------------------------- registerAfter
	/**
	 * Register a call_back advice, called after the execution of a function or before
	 * the read/write of a property
	 *
	 * @param string $function ie "Class_Name->functionName()" or "Class_Name->property_name"
	 * @param string $call_back valid callback function descriptor : "Class_Name::functionName()
	 */
	public static function registerAfter($function, $call_back)
	{
		aop_add_after($function, $call_back);
	}

	//-------------------------------------------------------------------------------- registerAround
	/**
	 * Register a call_back advice, called around the execution of a function or before
	 * the read/write of a property
	 *
	 * @param string $function ie "Class_Name->functionName()" or "Class_Name->property_name"
	 * @param string $call_back valid callback function descriptor : "Class_Name::functionName()
	 */
	public static function registerAround($function, $call_back)
	{
		aop_add_around($function, $call_back);
	}

	//-------------------------------------------------------------------------------- registerBefore
	/**
	 * Register a call_back advice, called before the execution of a function or before
	 * the read/write of a property
	 *
	 * @param string $function ie "Class_Name->functionName()" or "Class_Name->property_name"
	 * @param string $call_back valid callback function descriptor : "Class_Name::functionName()
	 */
	public static function registerBefore($function, $call_back)
	{
		aop_add_before($function, $call_back);
	}

}
