<?php
namespace ITRocks\Framework\Feature\List_\Navigation;

use ITRocks\Framework\Dao;
use ITRocks\Framework\Feature\List_;
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
	public int $count = 100;

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
	 * @param $direction integer @values -1, 0, 1
	 * @return T
	 * @template T
	 */
	public function navigate(object $object, int $direction) : object
	{
		$current_identifier = strval(Dao::getObjectIdentifier($object));
		$list_settings      = List_Setting\Set::current($this->class_name);
		$list_settings->cleanup();
		$next_identifier = false;
		$search          = (new List_\Controller)->applySearchParameters($list_settings);
		do {
			$options = [
				Dao::groupBy(), Dao::limit($this->from, $this->count), $list_settings->sort
			];
			$data = Dao::select($this->class_name, [], $search, $options);
			$rows = $data->getRows();
			if ($direction < 0) {
				$rows = array_reverse($rows);
			}
			foreach ($rows as $row) {
				$identifier = strval($row->getObjectIdentifier());
				if ($next_identifier) {
					return Dao::read($identifier, $this->class_name);
				}
				if ($identifier === $current_identifier) {
					if ($direction === 0) {
						return $object;
					}
					$next_identifier = true;
				}
			}
			if (($direction < 0) && ($this->from > 1)) {
				$this->from = max(1, $this->from - $this->count);
			}
			elseif (($direction >= 0) && $rows) {
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
