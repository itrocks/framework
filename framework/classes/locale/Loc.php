<?php
namespace SAF\Framework;

abstract class Loc
{

	//------------------------------------------------------------------------------------------- rtr
	public static function rtr($translation, $context = "")
	{
		return Locale::current()->translations->reverse($translation, $context);
	}

	//-------------------------------------------------------------------------------------------- tr
	public static function tr($text, $context = "")
	{
		return Locale::current()->translations->translate($text, $context);
	}

}
