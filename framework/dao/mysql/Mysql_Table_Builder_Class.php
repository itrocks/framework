<?php
namespace SAF\Framework;

/**
 * Builds Mysql_Table object with a structure matching the structure of a PHP class
 */
class Mysql_Table_Builder_Class
{

	//-------------------------------------------------------------------------- $excluded_properties
	/**
	 * Excluded properties names
	 *
	 * For classes with a link annotation, all properties names from the linked parent class
	 * and it's own parents are excluded.
	 *
	 * @var string[]
	 */
	private $excluded_properties;

	//----------------------------------------------------------------------------------------- build
	/**
	 * Builds Mysql_Table objects using a Php class definition
	 *
	 * A Php class becomes a Mysql_Table
	 * Non-static properties of the class will become Mysql_Column objects
	 *
	 * @param $class_name string
	 * @return Mysql_Table[]
	 */
	public function build($class_name)
	{
		$this->excluded_properties = array();
		return $this->buildInternal($class_name, null);
	}

	//------------------------------------------------------------------------------- buildClassTable
	/**
	 * Builds a Mysql_Table object using a Php class definition
	 *
	 * This takes care of excluded properties, so buildLinkTable() should be called
	 * before buildClassTable().
	 *
	 * @param $class      Reflection_Class
	 * @param $more_field Mysql_Column
	 * @return Mysql_Table
	 */
	private function buildClassTable(Reflection_Class $class, $more_field)
	{
		$table_name = Dao::current()->storeNameOf($class->name);
		$table = new Mysql_Table($table_name);
		if (!in_array('id', $this->excluded_properties)) {
			$table->addColumn(Mysql_Column::buildId());
		}
		if ($more_field) {
			$table->addColumn($more_field);
		}
		foreach ($class->accessProperties() as $property) {
			if (!in_array($property->name, $this->excluded_properties)) {
				$type = $property->getType();
				if (($type->isMultipleString() || !$type->isMultiple()) && !$property->isStatic()) {
					$table->addColumn(Mysql_Column::buildProperty($property));
					if ($property->getAnnotation('link')->value == 'Object') {
						$table->addForeignKey(Mysql_Foreign_Key::buildProperty($table_name, $property));
						$table->addIndex(Mysql_Index::buildLink($property->name));
					}
				}
			}
		}
		return $table;
	}

	//--------------------------------------------------------------------------------- buildInternal
	/**
	 * The internal build method builds Mysql_Table objects using a Php class definition
	 *
	 * It is the same than build(), but enables to add an additional field
	 * (link field for @link classes)
	 *
	 * @param $class_name string
	 * @param $more_field Mysql_Column
	 * @return Mysql_Table[]
	 */
	private function buildInternal($class_name, $more_field)
	{
		$class = new Reflection_Class($class_name);
		$link = $class->getAnnotation('link')->value;
		$tables = $link ? $this->buildLinkTable($link, $class_name) : array();
		$tables[] = $this->buildClassTable($class, $more_field);
		return $tables;
	}

	//-------------------------------------------------------------------------------- buildLinkTable
	/**
	 * @param $link
	 * @param $class_name
	 * @return Mysql_Table[]
	 */
	private function buildLinkTable($link, $class_name)
	{
		$link_class_name = Namespaces::defaultFullClassName($link, $class_name);
		$tables = (new Mysql_Table_Builder_Class)->build($link_class_name);
		$this->excluded_properties = array_keys(
			(new Reflection_Class($link_class_name))->getAllProperties()
		);
		return $tables;
	}

}
