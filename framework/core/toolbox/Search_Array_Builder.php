<?php
namespace SAF\Framework;

/**
 * The search array builder builds search arrays from properties paths and search phrases
 */
class Search_Array_Builder
{

	//------------------------------------------------------------------------------------------ $and
	public $and = " ";

	//------------------------------------------------------------------------------------------- $or
	public $or = ",";

	//----------------------------------------------------------------------------------------- build
	/**
	 * @param $property_name string
	 * @param $search_phrase string
	 * @param $append string
	 * @return array
	 */
	public function build($property_name, $search_phrase, $append = "")
	{
		$search_phrase = trim($search_phrase);
		// search phrase contains OR
		if (strpos($search_phrase, $this->or) !== false) {
			$result = array();
			foreach (explode($this->or, $search_phrase) as $search) {
				$sub_result = $this->build("", $search, $append);
				if ((!is_array($sub_result)) || (count($sub_result) > 1)) {
					$result[$property_name][] = $sub_result;
				}
				elseif (isset($result[$property_name])) {
					$result[$property_name] = array_merge($result[$property_name], $sub_result);
				}
				else {
					$result[$property_name] = $sub_result;
				}
			}
			return $property_name ? $result : reset($result);
		}
		// search phrase contains AND
		elseif (strpos($search_phrase, $this->and) !== false) {
			$result = array();
			foreach (explode($this->and, $search_phrase) as $search) {
				$result[$property_name]["AND"][] = $this->build("", $search, $append);
			}
			return $property_name ? $result : reset($result);
		}
		// simple search phrase
		else {
			return $property_name
				? array($property_name => $search_phrase . $append)
				: ($search_phrase . $append);
		}
	}

	//--------------------------------------------------------------------------------- buildMultiple
	/**
	 * @param $property_names_or_class string[]|Reflection_Class
	 * @param $search_phrase string
	 * @param $append string
	 * @return array
	 */
	public function buildMultiple($property_names_or_class, $search_phrase, $append = "")
	{
		$property_names = ($property_names_or_class instanceof Reflection_Class)
			? $this->classRepresentativeProperties($property_names_or_class)
			: $property_names_or_class;
		$result = array();
		// search phrase contains OR
		if (strpos($search_phrase, $this->or) !== false) {
			foreach ($property_names as $property_name) {
				$result["OR"][$property_name] = $this->build("", $search_phrase, $append);
			}
		}
		// search phrase contains AND
		elseif (strpos($search_phrase, $this->and) !== false) {
			foreach (explode($this->and, $search_phrase) as $search) {
				$result["AND"][] = $this->buildMultiple($property_names, $search, $append);
			}
		}
		// simple search phrase
		else {
			foreach ($property_names as $property_name) {
				$result["OR"][$property_name] = $search_phrase . $append;
			}
		}
		return $result;
	}

	//----------------------------------------------------------------- classRepresentativeProperties
	/**
	 * @param $class Reflection_Class
	 * @return string[]
	 */
	private function classRepresentativeProperties($class)
	{
		/** @var $property_names List_Annotation */
		$property_names = $class->getListAnnotation("representative")->values();
		foreach ($property_names as $key => $property_name) {
			$property_class = $class;
			$i = strpos($property_name, ".");
			while ($i !== false) {
				$property = $property_class->getProperty(substr($property_name, 0, $i));
				$property_class = Reflection_Class::getInstanceOf($property->getType());
				$property_name = substr($property_name, $i + 1);
				$i = strpos($property_name, ".");
			}
			$property = $property_class->getProperty($property_name);
			$type = $property->getType();
			if (!$type->isBasic()) {
				unset($property_names[$key]);
				$sub_class = Reflection_Class::getInstanceOf($type);
				foreach ($this->classRepresentativeProperties($sub_class) as $sub_property_name) {
					$property_names[] = $property_name . "." . $sub_property_name;
				}
			}
		}
		return $property_names;
	}

}
