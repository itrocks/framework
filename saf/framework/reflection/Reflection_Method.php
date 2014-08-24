<?php
namespace SAF\Framework\Reflection;

use ReflectionMethod;
use SAF\Framework\Reflection\Annotation\Annoted;
use SAF\Framework\Reflection\Interfaces;
use SAF\Framework\Reflection\Interfaces\Has_Doc_Comment;

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
	protected function getAnnotationCachePath()
	{
		return [$this->class, $this->name . '()'];
	}

	//------------------------------------------------------------------------- getDeclaringClassName
	/**
	 * Gets declaring class name
	 *
	 * @return string
	 */
	public function getDeclaringClassName()
	{
		return $this->class;
	}

	//--------------------------------------------------------------------------------- getDocComment
	/**
	 * TODO LOWEST parent methods read
	 * @param $flags integer[]
	 * @return string
	 */
	public function getDocComment($flags = [])
	{
		return parent::getDocComment();
	}

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
	 * @param $by_name boolean
	 * @return Reflection_Parameter[]
	 */
	public function getParameters($by_name = true)
	{
		$parameters = [];
		foreach (parent::getParameters() as $key => $parameter) {
			$parameters[$by_name ? $parameter->name : $key] = new Reflection_Parameter(
				[$this->class, $this->name], $parameter->name
			);
		}
		return $parameters;
	}

	//---------------------------------------------------------------------------- getParametersNames
	/**
	 * @return string[] key and value are both the parameter name
	 */
	public function getParametersNames()
	{
		$parameter_names = array_keys($this->getParameters());
		return array_combine($parameter_names, $parameter_names);
	}

	//---------------------------------------------------------------------------- getPrototypeString
	/**
	 * The prototype of the function, beginning with first whitespaces before function and its doc
	 * comments, ending with { or ; followed by LF.
	 *
	 * @return string
	 */
	public function getPrototypeString()
	{
		$parameters = $this->getParameters();
		return ($this->isAbstract() ? 'abstract ' : '')
			. ($this->isPublic() ? 'public ' : ($this->isProtected() ? 'protected ' : 'private '))
			. ($this->isStatic() ? 'static ' : '')
			. 'function ' . $this->name
			. ($this->returnsReference() ? '& ' : '')
			. '(' . join(', ', $parameters) . ')' . LF . '{';
	}

	//--------------------------------------------------------------------------------------- returns
	/**
	 * @return string
	 */
	public function returns()
	{
		return $this->getAnnotation('return')->value;
	}

}
