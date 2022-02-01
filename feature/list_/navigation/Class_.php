<?php
namespace ITRocks\Framework\Feature\List_\Navigation;

use ITRocks\Framework\Dao;
use ITRocks\Framework\Feature\List_Setting;

/**
 * Navigation class
 */
class Class_
{

	//----------------------------------------------------------------------------------- $class_name
	/**
	 * @var string Root class name
	 */
	public string $class_name;

	//---------------------------------------------------------------------------------------- $count
	/**
	 * @var integer
	 */
	public int $count = 5;

	//----------------------------------------------------------------------------------------- $from
	/**
	 * @var integer  
	 */
	public int $from = 1;

	//---------------------------------------------------------------------------------- $total_count
	/**
	 * @var integer
	 */
	public int $total_count;

	//-------------------------------------------------------------------------------------- navigate
	/**
	 * @param $object    T
	 * @param $direction integer @values -1, 1
	 * @return T
	 * @template T
	 */
	public function navigate(object $object, int $direction) : object
	{
		$current_identifier = strval(Dao::getObjectIdentifier($object));
		$list_settings      = List_Setting\Set::current($this->class_name);
		$list_settings->cleanup();
		$last_identifier = 0;
		$next_identifier = false;
		do {
			$options = [
				Dao::groupBy(), Dao::limit($this->from, $this->count), $list_settings->sort
			];
			$data = Dao::select($this->class_name, [], $list_settings->search, $options);
			$rows = $data->getRows();
			foreach ($rows as $row) {
				$identifier = strval($row->getObjectIdentifier());
				if ($next_identifier) {
					return Dao::read($identifier, $this->class_name);
				}
				if ($identifier === $current_identifier) {
					if ($direction < 0) {
						return Dao::read($last_identifier ?: $identifier, $this->class_name);
					}
					$next_identifier = true;
				}
				$last_identifier = $identifier;
			}
			if (($direction < 0) && ($this->from > 1)) {
				$this->from = max(1, $this->from - $this->count);
			}
			elseif (($direction > 0) && $rows) {
				$this->from += $this->count;
			}
			else {
				break;
			}
		}
		while (true);
		return $object;
	}

}
