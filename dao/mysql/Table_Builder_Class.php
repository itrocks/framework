<?php
namespace ITRocks\Framework\Dao\Mysql;

use ITRocks\Framework\Dao;
use ITRocks\Framework\Reflection\Annotation\Class_;
use ITRocks\Framework\Reflection\Annotation\Property\Link_Annotation;
use ITRocks\Framework\Reflection\Annotation\Property\Store_Annotation;
use ITRocks\Framework\Reflection\Annotation\Property\Store_Name_Annotation;
use ITRocks\Framework\Reflection\Annotation\Sets\Replaces_Annotations;
use ITRocks\Framework\Reflection\Reflection_Class;
use ITRocks\Framework\Reflection\Reflection_Property;
use ITRocks\Framework\Tools\Namespaces;

/**
 * Builds Table object with a structure matching the structure of a PHP class
 */
class Table_Builder_Class
{
	use Property_Filter;

	//------------------------------------------------------------------------- $dependencies_context
	/**
	 * dependencies class names : all properties linked to an object have this object class set here
	 *
	 * @var string[]
	 */
	public $dependencies_context;

	//-------------------------------------------------------------------------- $exclude_class_names
	/**
	 * @var string[]
	 */
	public $exclude_class_names = [];

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
		$this->excluded_properties  = [];
		return $this->buildInternal($class_name, null);
	}

	//------------------------------------------------------------------------------- buildClassTable
	/**
	 * Builds a Table object using a Php class definition
	 *
	 * This takes care of excluded properties, so buildLinkTable() should be called
	 * before buildClassTable().
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $class      Reflection_Class
	 * @param $more_field Column
	 * @return Table
	 */
	private function buildClassTable(Reflection_Class $class, $more_field)
	{
		$table_name = Dao::current()->storeNameOf($class->name);
		$table      = new Table($table_name);
		if (!in_array('id', $this->excluded_properties)) {
			$table->addColumn(Column::buildId());
		}
		if ($more_field) {
			$table->addColumn($more_field);
		}
		if ($class->isAbstract()) {
			$table->addColumn(
				new Column('class', 'varchar(255)' . SP . Database::characterSetCollateSql())
			);
		}
		else {
			/** @var $properties Reflection_Property[] */
			$properties = Replaces_Annotations::removeReplacedProperties($class->accessProperties());
			foreach ($properties as $property) {
				if ($this->filterProperty($property)) {
					$table->addColumn(Column::buildProperty($property));
					if (
						Link_Annotation::of($property)->isObject()
						&& !Store_Annotation::of($property)->value
					) {
						$class_name                              = $property->getType()->asString();
						$this->dependencies_context[$class_name] = $class_name;
						/** @noinspection PhpUnhandledExceptionInspection valid class name */
						if (!(new Reflection_Class($class_name))->isAbstract()) {
							$table->addForeignKey(Foreign_Key::buildProperty($table_name, $property));
						}
						$table->addIndex(Index::buildLink(Store_Name_Annotation::of($property)->value));
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
	 * (link field for link classes)
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $class_name string
	 * @param $more_field Column
	 * @return Table[]
	 */
	private function buildInternal($class_name, $more_field)
	{
		/** @noinspection PhpUnhandledExceptionInspection class name must be valid */
		$class  = new Reflection_Class($class_name);
		$link   = Class_\Link_Annotation::of($class)->value;
		$tables = $link ? $this->buildLinkTables($link, $class_name) : [];
		if (!in_array($class_name, $this->exclude_class_names)) {
			$tables[] = $this->buildClassTable($class, $more_field);
		}
		return $tables;
	}

	//------------------------------------------------------------------------------- buildLinkTables
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $link       string
	 * @param $class_name string
	 * @return Table[]
	 */
	private function buildLinkTables($link, $class_name)
	{
		$table_builder_class                      = new Table_Builder_Class();
		$table_builder_class->exclude_class_names = $this->exclude_class_names;

		$link_class_name = Namespaces::defaultFullClassName($link, $class_name);
		$tables          = $table_builder_class->build($link_class_name);

		/** @noinspection PhpUnhandledExceptionInspection link class name is always valid */
		$this->excluded_properties = array_keys(
			(new Reflection_Class($link_class_name))->getProperties([T_EXTENDS, T_USE])
		);
		$this->excluded_properties[] = 'id';

		return $tables;
	}

}
