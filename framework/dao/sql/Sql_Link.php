<?php
namespace Framework;

abstract class Sql_Link extends Identifier_Map_Data_Link
{

	/**
	 * Connection to a SQL driver
	 */
	protected $connection;

	/**
	 * @var boolean
	 */
	private $transactional;

	//----------------------------------------------------------------------------------------- begin
	public function begin()
	{
	}

	//---------------------------------------------------------------------------------------- commit
	public function commit()
	{
	}

	//---------------------------------------------------------------------------------- executeQuery
	protected abstract function executeQuery($query);

	//--------------------------------------------------------------------------------- getColumnName
	protected abstract function getColumnName($result_set, $index);

	//-------------------------------------------------------------------------------- getColumnsCount
	protected abstract function getColumnsCount($result_set);

	//------------------------------------------------------------------------------- isTransactional
	/**
	 * @return bool
	 */
	protected function isTransactional()
	{
		return $this->transactional;
	}

	//----------------------------------------------------------------------------------------- fetch
	protected abstract function fetch($result_set, $class_name);

	//----------------------------------------------------------------------------------------- query
	/**
	 * @param  string $query
	 * @return int
	 */
	public abstract function query($query);

	//---------------------------------------------------------------------------------------- select
	public function select($object_class, $columns, $filter_object = null)
	{
		if ($filter_object) {
			$filter_map["id_" . strtolower(get_class($filter_object))]
				= $this->getObjectIdentifier($filter_object);
		}
		$query = Sql_Builder::buildSelect($object_class, $columns, $this);
		if ($filter_object) {
			$query .= Sql_Builder::builderWhere($filter_map);
		}
		return $this->selectCore($query, count($columns));
	}

	//---------------------------------------------------------------------------------------- select
	/**
	 * @todo factorize
	 * @param  string $query
	 * @param  string $list_length
	 * @return string 
	 */
	private function selectCore($query, $list_length)
	{
		$list = array();
		$result_set = $this->executeQuery($query);
		$column_count = $this->getColumnCount($result_set);
		$classes_index = array();
		$j = 0;
		for ($i = 0; $i < $column_count; $i++) {
			$column_names[$i] = $this->getColumnName($result_set, $i);
			if (strpos($column_names[$i], ":") == false) {
				$itoj[$i] = $j++;
			} else {
				$split = split("\\:", $column_names[$i]);
				$column_names[$i] = $split[1];
				$object_class = $split[0];
				$hisj = $classes_index[$object_class];
				if (!$hisj) {
					$hisj = $j;
					$classes_index[$object_class] = $j;
					$create_object[$j] = true;
					$itoj[$i] = $j++;
				} else {
					$itoj[$i] = $hisj;
				}
				$classes[$hisj] = $object_class;
			}
			if ((count($column_names[$i]) > 3) && substr($column_names[$i], 0, 3) === "id_") {
				$column_names[$i] = substr($column_names[$i], 3);
			}
		}
		$first = true;
		while ($result = $this->nextRow($result_set)) {
			for ($i = 0; $i < $column_count; $i++) {
				$j = $itoj[$i];
				if (!is_object($classes[$j])) {
					$row[$j] = $result[$i];
				} else {
					if (!is_object($row[$j])) {
						// TODO try to get the object from an object map (avoid several instances of the same)
						$row[$j] = Instantiator::newInstance($classes[$j]);
						if ($first) {
							$fields[$classes[$j]] = Class_Fields::accessFields($classes[$j]);
						}
					}
					if ($column_names[$i] === "id") {
						$this->setObjectIdentifier($row[$j], $result[$i]);
					} else {
						$object->$column_names[$i] = $result[$i];
					}
				}
			}
			$list[] = row;
			$first = false;
		}
		foreach (array_keys($fields) as $object_class) if ($object_class) {
			Class_Fields::accessFieldsDone($object_class);
		}
		return $list;
	}

	//------------------------------------------------------------------------------ setTransactional
	/**
	 * @param  bool $transactional
	 * @return Sql_Link
	 */
	protected function setTransactional($transactional)
	{
		$this->transactional = $transactional;
		return $this;
	}

}
