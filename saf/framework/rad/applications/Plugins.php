<?php
namespace SAF\Framework\RAD\Applications;

use SAF\Framework\View\Html\Builder\Property;

/**
 * Application instance plugins widget
 */
class Plugins extends Property
{

	//------------------------------------------------------------------------------------- buildHtml
	/**
	 * @return string
	 */
	public function buildHtml()
	{
		return 'nothing';
	}

	//------------------------------------------------------------------------------------ buildValue
	/**
	 * @param $object        object
	 * @param $null_if_empty boolean
	 * @return mixed
	 */
	public function buildValue($object, $null_if_empty)
	{
		echo 'build value for ' . print_r($object, true) . ' and ' . $null_if_empty . '<br>';
		return [];
	}

}
