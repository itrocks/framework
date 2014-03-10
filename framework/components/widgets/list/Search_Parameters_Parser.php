<?php
namespace SAF\Framework;

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

	//----------------------------------------------------------------------------------- applyJokers
	/**
	 * @param $search_value string
	 */
	protected function applyJokers(&$search_value)
	{
		$search_value = str_replace(['*', '?'], ['%', '_'], $search_value);
	}

	//------------------------------------------------------------------------------------ applyRange
	/**
	 * @param $search_value string|Dao_Option
	 */
	protected function applyRange(&$search_value)
	{
		if (strpos($search_value, '-') !== false) {
			$range = explode('-', $search_value, 2);
			$search_value = new Dao_Range_Function($range[0], $range[1]);
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
		return in_array($type_string, [Date_Time::class, Type::INTEGER, Type::STRING]);
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
			$this->applyJokers($search_value);
			if ($this->hasRange($property)) {
				$this->applyRange($search_value);
			}
		}
		return $search;
	}

}
