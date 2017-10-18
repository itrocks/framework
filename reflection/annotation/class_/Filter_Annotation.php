<?php
namespace ITRocks\Framework\Reflection\Annotation\Class_;

use ITRocks\Framework\Dao\Func;
use ITRocks\Framework\Reflection\Annotation\Template\Method_Annotation;
use ITRocks\Framework\Reflection\Interfaces;
use ITRocks\Framework\Reflection\Reflection_Class;

/**
 * The @filter annotation allow to link a method for filtering to a class
 */
class Filter_Annotation extends Method_Annotation
{

	//------------------------------------------------------------------------------------ ANNOTATION
	const ANNOTATION = 'filter';

	//--------------------------------------------------------------------------------- For constants
	const FOR_USE      = 'for_use';
	const FOR_VIEW     = 'for_view';
	const NONE_FOR_ALL = 'none_for_all';

	//-------------------------------------------------------------------------------------- $for_use
	/**
	 * Filter is used for use only
	 *
	 * @var boolean
	 */
	public $for_use;

	//------------------------------------------------------------------------------------- $for_view
	/**
	 * Filter is used for view only
	 *
	 * @var boolean
	 */
	public $for_view;

	//--------------------------------------------------------------------------------- $none_for_all
	/**
	 * If true, an object with no restriction link will not be filtered
	 * ('no restriction value means no restriction')
	 *
	 * @var boolean
	 */
	public $none_for_all;

	//----------------------------------------------------------------------------------- $properties
	/**
	 * @var string
	 */
	public $properties = [];

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $value           string
	 * @param $class           Interfaces\Reflection_Class|Interfaces\Reflection
	 * @param $annotation_name string
	 */
	public function __construct($value, Interfaces\Reflection $class, $annotation_name = null)
	{
		if (strpos($value, SP)) {
			list($value, $options) = explode(SP, $value, 2);
		}
		parent::__construct($value, $class, $annotation_name ?: static::ANNOTATION);
		// set options
		$default_options = true;
		if (isset($options)) {
			foreach (explode(',', $options) as $options2) {
				$options2 = trim($options2);
				foreach (explode(SP, $options2) as $option) {
					$option = trim($option);
					if (property_exists($this, $option)) {
						$this->$option = true;
						if (in_array($option, [self::FOR_USE, self::FOR_VIEW])) {
							$default_options = false;
						}
					}
					else {
						$this->properties[] = $option;
					}
				}
			}
		}
		if ($default_options) {
			$this->for_use  = $this->properties ? false : true;
			$this->for_view = true;
		}
	}

	//----------------------------------------------------------------------------------------- apply
	/**
	 * Apply all @filter annotations of the class
	 *
	 * @param $class string|Reflection_Class
	 * @param $for   string null means 'for any' @values for_use, for_view
	 * @return object
	 */
	public static function apply($class, $for = null)
	{
		if (is_string($class)) {
			$class = new Reflection_Class($class);
		}
		$filters = static::allOf($class);
		if ($filters) {
			$search = [];
			foreach ($filters as $filter) {
				if (!$for || $filter->$for) {
					$element = $filter->call($filter->value);
					if ($element) {
						if ($filter->properties) {
							$elements = [];
							foreach ($filter->properties as $property_path) {
								if (($property_path === DOT) || !$property_path) {
									$elements[] = $element;
								}
								else {
									$elements[$property_path] = $element;
								}
							}
							if ($filter->none_for_all) {
								$null_elements = [];
								foreach ($filter->properties as $property_path) {
									$null_elements[$property_path] = Func::isNull();
								}
								$elements[] = (count($null_elements) > 1)
									? Func::andOp($null_elements)
									: $null_elements;
							}
							$element = (count($elements) > 1)
								? Func::orOp($elements)
								: $elements;
						}
						$search[] = $element;
					}
				}
			}
			$search = (count($search) > 1)
				? Func::andOp($search)
				: ($search ? reset($search) : null);
			return $search;
		}
		return null;
	}

}
