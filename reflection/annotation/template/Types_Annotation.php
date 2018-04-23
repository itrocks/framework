<?php
namespace ITRocks\Framework\Reflection\Annotation\Template;

use ITRocks\Framework\Builder;
use ITRocks\Framework\Reflection\Annotation;
use ITRocks\Framework\Reflection\Type;

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

	//------------------------------------------------------------------------- $declared_class_names
	/**
	 * The declared class names
	 *
	 * $value will contain built class name(s) (after replacement by Builder::className)
	 * These are the names of the classes as they were declared into the annotation
	 *
	 * @var string[]
	 */
	public $declared_class_names = [];

	//-------------------------------------------------------------------------------- applyNamespace
	/**
	 * Apply namespace and use entries to the type name (if class)
	 *
	 * Return the full element class name, used to modify the type (multiple stays multiple)
	 *
	 * @param $namespace string
	 * @param $use       string[]
	 */
	public function applyNamespace($namespace, array $use = [])
	{
		/** @var $this Annotation|Types_Annotation */

		/** @var $declared_class_names string[] */
		$declared_class_names = [];
		/** @var $values string[] */
		$values = is_array($this->value) ? $this->value : [$this->value];

		foreach ($values as $key => $class_name) {
			if (ctype_upper($class_name[0])) {
				if (substr($class_name, -2) == '[]') {
					$class_name = substr($class_name, 0, -2);
					$multiple   = '[]';
				}
				else {
					$multiple = '';
				}
				$declared_class_name        = (new Type($class_name))->applyNamespace($namespace, $use);
				$declared_class_names[$key] = $declared_class_name  . $multiple;
				$values[$key]               = Builder::className($declared_class_name) . $multiple;
			}
			elseif ($class_name[0] === BS) {
				$declared_class_names[$key] = substr($class_name, 1);
				$values[$key]               = substr($class_name, 1);
			}
		}

		$this->declared_class_names = $declared_class_names;
		$this->value                = is_array($this->value) ? $values : reset($values);
	}

}
