<?php
namespace SAF\Framework\Reflection\Annotation\Template;

use SAF\Framework\Builder;
use SAF\Framework\Reflection\Annotation;
use SAF\Framework\Reflection\Type;

/**
 * Types annotation : apply this trait to annotations that can contain type name or type names list
 *
 * applyNamespace() will be automatically called by the annotation parser in order to change every
 * class names into full class names, using the namespace and namespaces uses
 *
 * Notice for annotation classes that use this trait : if it calculates a default value for not set
 * annotation, the default value must include namespace, because applyNamespace will not be called.
 *
 * @extends Annotation
 */
trait Types_Annotation
{

	//-------------------------------------------------------------------------------- applyNamespace
	/**
	 * Apply namespace and use entries to the type name (if class)
	 *
	 * Return the full element class name, used to modify the type (multiple stays multiple)
	 *
	 * @param $namespace string
	 * @param $use       string[]
	 * @return string
	 */
	public function applyNamespace($namespace, $use = [])
	{
		/** @var $this Annotation|Types_Annotation */
		/** @var $values string[] */
		$values = is_array($this->value) ? $this->value : [$this->value];

		foreach ($values as $key => $class_name) {
			if (ctype_upper($class_name[0])) {
				if (substr($class_name, -2) == '[]') {
					$class_name = substr($class_name, 0, -2);
					$multiple = '[]';
				}
				else {
					$multiple = '';
				}
				$values[$key] = Builder::className(
					(new Type($class_name))->applyNamespace($namespace, $use)
				) . $multiple;
			}
			elseif ($class_name[0] === BS) {
				$values[$key] = substr($class_name, 1);
			}
		}

		$this->value = is_array($this->value) ? $values : reset($values);
	}

}
