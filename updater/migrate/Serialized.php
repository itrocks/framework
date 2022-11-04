<?php
namespace ITRocks\Framework\Updater\Migrate;

/**
 * Tools to migrate serialized data
 */
trait Serialized
{

	//--------------------------------------------------------------------------------------- replace
	/**
	 * @param $value   string serialized data
	 * @param $search  string value to search
	 * @param $replace string replacement value
	 * @param $prefix  string serialized prefix @example O for object, s for string
	 * @return boolean true if something changed
	 */
	public function replace(string &$value, string $search, string $replace, string $prefix = 'O')
		: bool
	{
		if (!str_contains($value, $search)) {
			return false;
		}
		$value = str_replace($search, $replace, $value);

		if (!($diff = (strlen($replace) - strlen($search)))) {
			return true;
		}

		$position = strpos($value, $replace);
		while ($position !== false) {
			$size_position = strrpos(substr($value, 0, $position), $prefix . ':') + 2;
			$size          = intval(lParse(substr($value, $size_position), ':'));
			$new_size      = $size + $diff;
			$size_diff     = strlen($new_size) - strlen($size);

			$value = substr($value, 0, $size_position)
				. $new_size
				. substr($value, $size_position + strlen($size));

			$position = strpos($value, $replace, $position + $size_diff + strlen($replace));
		}

		return true;
	}

	//------------------------------------------------------------------------------- replaceMultiple
	/**
	 * @param $value           string
	 * @param $search_replaces array string[][] [string $search => string $replace]
	 * @return boolean true if something changed
	 */
	public function replaceMultiple(string &$value, array $search_replaces) : bool
	{
		$changed = false;
		foreach ($search_replaces as $search => $replace) {
			if ($this->replace($value, $search, $replace)) {
				$changed = true;
			}
		}
		return $changed;
	}

}
