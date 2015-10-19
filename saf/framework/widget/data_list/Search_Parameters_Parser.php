<?php
namespace SAF\Framework\Widget\Data_List;

use SAF\Framework\Dao\Func;
use SAF\Framework\Dao\Func\Range;
use SAF\Framework\Dao\Option;
use SAF\Framework\Locale\Loc;
use SAF\Framework\Reflection\Reflection_Class;
use SAF\Framework\Reflection\Reflection_Property;
use SAF\Framework\Reflection\Type;
use SAF\Framework\Tools\Date_Time;

/**
 * Search parameters parser
 *
 * Parse user-input search strings to get valid search arrays in return
 */
class Search_Parameters_Parser
{

	//---------------------------------------------------------------------------------------- $class
	/**
	 * @var Reflection_Class
	 */
	public $class;

	//--------------------------------------------------------------------------------------- $search
	/**
	 * @var array
	 */
	public $search;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $class_name string
	 * @param $search     array user-input search string
	 */
	public function __construct($class_name, $search)
	{
		$this->class  = new Reflection_Class($class_name);
		$this->search = $search;
	}

	//-------------------------------------------------------------------------------------- applyAnd
	/**
	 * @param $search_value string
	 * @param $property     Reflection_Property
	 */
	protected function applyAnd(&$search_value, Reflection_Property $property)
	{
		if (is_string($search_value) && (strpos($search_value, '&') !== false)) {
			$and = [];
			foreach (explode('&', $search_value) as $search) {
				$this->applySingleValue($search, $property);
				$and[] = $search;
			}
			$search_value = Func::andOp($and);
		}
		else {
			$this->applySingleValue($search_value, $property);
		}
	}

	//------------------------------------------------------------------------------------ applyEmpty
	/**
	 * @param $search_value string|Func
	 * @param $type         Type
	 */
	protected function applyEmpty(&$search_value, Type $type)
	{
		if (
			is_string($search_value)
			&& in_array(Loc::rtr($search_value), ['empty', 'none', 'null', '!', '='])
		) {
			$value = in_array($type->asString(), [Type::BOOLEAN, Type::FLOAT, Type::INTEGER]) ? 0 : '';
			$search_value = Func::orOp([$value, Func::isNull()]);
		}
	}

	//----------------------------------------------------------------------------------- applyJokers
	/**
	 * @param $search_value string
	 */
	protected function applyJokers(&$search_value)
	{
		if (is_string($search_value)) {
			$search_value = str_replace(['*', '?'], ['%', '_'], $search_value);
		}
	}

	//-------------------------------------------------------------------------------------- applyNot
	/**
	 * @param $search_value string
	 * @param $property     Reflection_Property
	 */
	protected function applyNot(&$search_value, Reflection_Property $property)
	{
		if (is_string($search_value) && (substr($search_value, 0, 1) === '!')) {
			$search_value = substr($search_value, 1);
			$this->applySingleValue($search_value, $property);
			$search_value = Func::notEqual($search_value);
		}
	}

	//--------------------------------------------------------------------------------------- applyOr
	/**
	 * @param $search_value string
	 * @param $property     Reflection_Property
	 */
	protected function applyOr(&$search_value, Reflection_Property $property)
	{
		if (is_string($search_value) && (strpos($search_value, ',') !== false)) {
			$or = [];
			foreach (explode(',', $search_value) as $search) {
				$this->applyAnd($search, $property);
				$or[] = $search;
			}
			$search_value = Func::orOp($or);
		}
		else {
			$this->applySingleValue($search_value, $property);
		}
	}

	//------------------------------------------------------------------------------------ applyRange
	/**
	 * @param $search_value string|Option
	 */
	protected function applyRange(&$search_value)
	{
		if (is_string($search_value) && (strpos($search_value, '-') !== false)) {
			$range = explode('-', $search_value, 2);
			$search_value = new Range($range[0], $range[1]);
		}
	}

	//------------------------------------------------------------------------------ applySingleValue
	/**
	 * @param $search_value string
	 * @param $property     Reflection_Property
	 */
	protected function applySingleValue(&$search_value, Reflection_Property $property)
	{
		$this->applyNot($search_value, $property);
		$this->applyEmpty($search_value, $property->getType());
		$this->applyJokers($search_value);
		if ($this->hasRange($property)) {
			$this->applyRange($search_value);
		}
	}

	//-------------------------------------------------------------------------------------- hasRange
	/**
	 * @param $property Reflection_Property
	 * @return boolean
	 */
	public function hasRange(Reflection_Property $property)
	{
		$type_string = $property->getType()->asString();
		return ($property->getAnnotation('search_range')->value !== false)
			&& in_array($type_string, [Date_Time::class, Type::FLOAT, Type::INTEGER, Type::STRING]);
	}

	//----------------------------------------------------------------------------------------- parse
	/**
	 * @return array search-compatible search array
	 */
	public function parse()
	{
		$search = $this->search;
		foreach ($search as $property_path => &$search_value) {
			$property = new Reflection_Property($this->class->name, $property_path);
			$this->applyOr($search_value, $property);
		}
		return $search;
	}

}
