<?php
namespace ITRocks\Framework\Tools;

/**
 * Value lists works
 */
class Value_Lists
{

	//---------------------------------------------------------------------------------------- $lists
	/**
	 * @var array string[][]
	 */
	public $lists;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $lists array string[][]
	 */
	public function __construct(array $lists)
	{
		$this->lists = $lists;
	}

	//-------------------------------------------------------------------------------------- assembly
	/**
	 * Intelligent before/after assembly algorithm
	 */
	public function assembly()
	{
		$values = reset($this->lists) ?: [];
		foreach (array_slice($this->lists, 1) as $list) {
			// first pass : insert before first value found into the previous list
			$insert_position = false;
			foreach ($list as $position => $value) {
				if (in_array($value, $values)) {
					$insert_position = array_search($value, $values);
					if ($position) {
						$values = array_merge(
							array_slice($values, 0, $insert_position),
							array_slice($list, 0, $position),
							array_slice($values, $insert_position)
						);
					}
					$insert_position ++;
					break;
				}
			}
			// if no value found into the previous list : simply append new values and continue
			if ($insert_position === false) {
				$values = array_merge($values, $list);
				continue;
			}
			// next values are inserted immediately after the insert position
			// the insert position moves each time a value already present into the previous list is found
			$value    = next($list);
			$position = key($list);
			while ($value !== false) {
				if (in_array($value, $values)) {
					$to_position = key($list);
					if ($position !== $to_position) {
						$values = array_merge(
							array_slice($values, 0, $insert_position),
							array_slice($list, $position, $to_position - $position),
							array_slice($values, $insert_position)
						);
					}
					$insert_position = array_search($value, $values) + 1;
					$value           = next($list);
					$position        = key($list);
					continue;
				}
				$value = next($list);
			}
			// finally, last values are inserted after the last matching value position
			if ($position && ($position < count($list))) {
				$values = array_merge(
					array_slice($values, 0, $insert_position),
					array_slice($list, $position),
					array_slice($values, $insert_position)
				);
			}
		}
		return $values;
	}

}
