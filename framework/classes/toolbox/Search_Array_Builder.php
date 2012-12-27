<?php
namespace SAF\Framework;

class Search_Array_Builder
{

	//------------------------------------------------------------------------------------------ $and
	public $and = " ";

	//------------------------------------------------------------------------------------------- $or
	public $or = ",";

	//----------------------------------------------------------------------------------------- build
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
	public function buildMultiple($property_names, $search_phrase, $append = "")
	{
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

}
