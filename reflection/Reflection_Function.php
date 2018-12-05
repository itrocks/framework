<?php
namespace ITRocks\Framework\Reflection;

use ReflectionFunction;

/**
 * A transposition of the PHP ReflectionMethod class for functions
 *
 * TODO add annotations management
 */
class Reflection_Function extends ReflectionFunction
{

	//---------------------------------------------------------------------------------- getParameter
	/**
	 * @param $parameter_name string
	 * @return Reflection_Parameter
	 */
	public function getParameter($parameter_name)
	{
		return $this->getParameters()[$parameter_name];
	}

	//--------------------------------------------------------------------------------- getParameters
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $by_name boolean
	 * @return Reflection_Parameter[]
	 */
	public function getParameters($by_name = true)
	{
		$parameters = [];
		foreach (parent::getParameters() as $key => $parameter) {
			/** @noinspection PhpUnhandledExceptionInspection from parent::getParameters */
			$parameters[$by_name ? $parameter->name : $key] = new Reflection_Parameter(
				$this->name, $parameter->name
			);
		}
		return $parameters;
	}

	//---------------------------------------------------------------------------------- hasParameter
	/**
	 * @param $parameter_name string
	 * @return boolean
	 */
	public function hasParameter($parameter_name)
	{
		foreach (parent::getParameters() as $parameter) {
			if ($parameter->name === $parameter_name) {
				return true;
			}
		}
		return false;
	}

}
