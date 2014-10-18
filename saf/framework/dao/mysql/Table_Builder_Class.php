<?php
namespace SAF\Framework\Dao\Mysql;

use SAF\Framework\Dao;
use SAF\Framework\Reflection\Annotation\Property\Link_Annotation;
use SAF\Framework\Reflection\Reflection_Class;
use SAF\Framework\Tools\Namespaces;

/**
 * Builds Table object with a structure matching the structure of a PHP class
 */
class Table_Builder_Class
{

	//------------------------------------------------------------------------- $dependencies_context
	/**
	 * dependencies class names : all properties linked to an object have this object class set here
	 *
	 * @var string[]
	 */
	public $dependencies_context;

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
	 * Builds Table objects using a Php class definition
	 *
	 * A Php class becomes a Table
	 * Non-static properties of the class will become Column objects
	 *
	 * @param $class_name string
	 * @return Table[]
	 */
	public function build($class_name)
	{
		$this->dependencies_context = [];
		$this->excluded_properties = [];
		return $this->buildInternal($class_name, null);
	}

	//------------------------------------------------------------------------------- buildClassTable
	/**
	 * Builds a Table object using a Php class definition
	 *
	 * This takes care of excluded properties, so buildLinkTable() should be called
	 * before buildClassTable().
	 *
	 * @param $class      Reflection_Class
	 * @param $more_field Column
	 * @return Table
	 */
	private function buildClassTable(Reflection_Class $class, $more_field)
	{
		$table_name = Dao::current()->storeNameOf($class->name);
		$table = new Table($table_name);
		if (!in_array('id', $this->excluded_properties)) {
			$table->addColumn(Column::buildId());
		}
		if ($more_field) {
			$table->addColumn($more_field);
		}
		foreach ($class->accessProperties() as $property) {
			if (!in_array($property->name, $this->excluded_properties)) {
				$type = $property->getType();
				if (($type->isMultipleString() || !$type->isMultiple()) && !$property->isStatic()) {
					$table->addColumn(Column::buildProperty($property));
					if ($property->getAnnotation('link')->value == Link_Annotation::OBJECT) {
						$class_name = $property->getType()->asString();
						$this->dependencies_context[$class_name] = $class_name;
						$table->addForeignKey(Foreign_Key::buildProperty($table_name, $property));
						$table->addIndex(Index::buildLink($property->getAnnotation('storage')->value));
					}
				}
			}
		}
		return $table;
	}

	//--------------------------------------------------------------------------------- buildInternal
	/**
	 * The internal build method builds Table objects using a Php class definition
	 *
	 * It is the same than build(), but enables to add an additional field
	 * (link field for @link classes)
	 *
	 * @param $class_name string
	 * @param $more_field Column
	 * @return Table[]
	 */
	private function buildInternal($class_name, $more_field)
	{
		$class = new Reflection_Class($class_name);
		$link = $class->getAnnotation('link')->value;
		$tables = $link ? $this->buildLinkTable($link, $class_name) : [];
		$tables[] = $this->buildClassTable($class, $more_field);
		return $tables;
	}

	//-------------------------------------------------------------------------------- buildLinkTable
	/**
	 * @param $link
	 * @param $class_name
	 * @return Table[]
	 */
	private function buildLinkTable($link, $class_name)
	{
		$link_class_name = Namespaces::defaultFullClassName($link, $class_name);
		$tables = (new Table_Builder_Class)->build($link_class_name);
		$this->excluded_properties = array_keys(
			(new Reflection_Class($link_class_name))->getProperties([T_EXTENDS, T_USE])
		);
		return $tables;
	}

}
