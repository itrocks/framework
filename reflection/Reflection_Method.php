<?php
namespace ITRocks\Framework\Reflection;

use ITRocks\Framework\Reflection\Annotation\Annoted;
use ITRocks\Framework\Reflection\Interfaces;
use ITRocks\Framework\Reflection\Interfaces\Has_Doc_Comment;
use ReflectionMethod;

/**
 * A rich extension of the PHP ReflectionMethod class, adding :
 * - annotations management
 */
class Reflection_Method extends ReflectionMethod
	implements Has_Doc_Comment, Interfaces\Reflection_Method
{
	use Annoted;

	//------------------------------------------------------------------------ getAnnotationCachePath
	/**
	 * @return string[]
	 */
	protected function getAnnotationCachePath() : array
	{
		return [$this->class, $this->name . '()'];
	}

	//------------------------------------------------------------------------- getDeclaringClassName
	/**
	 * Gets declaring class name
	 *
	 * @return string
	 */
	public function getDeclaringClassName() : string
	{
		return $this->class;
	}

	//--------------------------------------------------------------------------------- getDocComment
	/**
	 * TODO LOWEST parent methods read
	 *
	 * @param $flags integer[]|null
	 * @return string
	 */
	public function getDocComment(array|null $flags = []) : string
	{
		return parent::getDocComment();
	}

	//------------------------------------------------------------------------ getMandatoryParameters
	/**
	 * Get only parameters that are mandatory (ie have no default value)
	 *
	 * @param $by_name boolean
	 * @return Reflection_Parameter[]
	 */
	public function getMandatoryParameters(bool $by_name = true) : array
	{
		$parameters = [];
		foreach ($this->getParameters($by_name) as $key => $parameter) {
			if (!$parameter->isOptional()) {
				$parameters[$key] = $parameter;
			}
		}
		return $parameters;
	}

	//---------------------------------------------------------------------------------- getParameter
	/**
	 * @param $parameter_name string
	 * @return Reflection_Parameter
	 */
	public function getParameter(string $parameter_name) : Reflection_Parameter
	{
		return $this->getParameters()[$parameter_name];
	}

	//--------------------------------------------------------------------------------- getParameters
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $by_name boolean
	 * @return Reflection_Parameter[]
	 */
	public function getParameters(bool $by_name = true) : array
	{
		$parameters = [];
		foreach (parent::getParameters() as $key => $parameter) {
			/** @noinspection PhpUnhandledExceptionInspection from parent::getParameters */
			$parameters[$by_name ? $parameter->name : $key] = new Reflection_Parameter(
				[$this->class, $this->name], $parameter->name
			);
		}
		return $parameters;
	}

	//----------------------------------------------------------------------------- getParametersCall
	/**
	 * Return a calling string for parameters call
	 *
	 * @return string ie '$param1, $param2, $param3'
	 */
	public function getParametersCall() : string
	{
		$parameters_names = $this->getParametersNames();
		return $parameters_names ? ('$' . join(', $', $this->getParametersNames())) : '';
	}

	//---------------------------------------------------------------------------- getParametersNames
	/**
	 * @param $by_name boolean
	 * @return string[] key and value are both the parameter name
	 */
	public function getParametersNames(bool $by_name = true) : array
	{
		$parameter_names = array_keys($this->getParameters());
		return $by_name ? array_combine($parameter_names, $parameter_names) : $parameter_names;
	}

	//---------------------------------------------------------------------------- getPrototypeString
	/**
	 * The prototype of the function, beginning with first whitespaces before function and its doc
	 * comments, ending with { or ; followed by LF.
	 *
	 * @return string
	 */
	public function getPrototypeString() : string
	{
		$parameters = $this->getParameters();
		return ($this->isAbstract() ? 'abstract ' : '')
			. ($this->isPublic() ? 'public ' : ($this->isProtected() ? 'protected ' : 'private '))
			. ($this->isStatic() ? 'static ' : '')
			. 'function ' . $this->name
			. ($this->returnsReference() ? '& ' : '')
			. '(' . join(', ', $parameters) . ')' . LF . '{';
	}

	//---------------------------------------------------------------------------------- hasParameter
	/**
	 * Returns true if the method has a parameter named $parameter_name
	 *
	 * @param $parameter_name string
	 * @return boolean
	 */
	public function hasParameter(string $parameter_name) : bool
	{
		foreach (parent::getParameters() as $parameter) {
			if ($parameter->name === $parameter_name) {
				return true;
			}
		}
		return false;
	}

	//--------------------------------------------------------------------------------------- returns
	/**
	 * @return string
	 */
	public function returns() : string
	{
		return $this->getAnnotation('return')->value;
	}

}
