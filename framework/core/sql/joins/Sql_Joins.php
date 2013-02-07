<?php
namespace SAF\Framework;

class Sql_Joins
{

	//-------------------------------------------------------------------------------- $alias_counter
	/**
	 * alias counter for the next aliased table
	 *
	 * @var integer
	 */
	private $alias_counter;

	//-------------------------------------------------------------------------------------- $classes
	/**
	 * link property full path to their class name
	 *
	 * @var string[] indice is property full path
	 */
	private $classes = array();

	//---------------------------------------------------------------------------------------- $joins
	/**
	 * link property path to sql join
	 *
	 * @var Sql_Join[] indice is property full path
	 */
	private $joins = array();

	//-------------------------------------------------------------------------------- $linked_tables
	/**
	 * linked tables
	 *
	 * @var string[] indice is
	 */
	private $linked_tables = array();

	//----------------------------------------------------------------------------------- $properties
	/**
	 * link class names to their properties
	 *
	 * @var Reflection_Property[] indices are : class name, property name[]
	 */
	private $properties = array();

	//-------------------------------------------------------------------------- $starting_class_name
	/**
	 * Starting class name : all properties path must start from this class
	 *
	 * @var string
	 */
	private $starting_class_name;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * Construct Sql_Joins object and prepare joins for a list of property paths
	 *
	 * @param $starting_class_name string the class name for the root of property paths
	 * @param $paths array a property paths list to add at construction
	 */
	public function __construct($starting_class_name, $paths = array())
	{
		$this->alias_counter = 1;
		$this->classes[""] = $starting_class_name;
		$class = Reflection_Class::getInstanceOf($starting_class_name);
		$this->properties[$starting_class_name] = $class->getAllProperties();
		$this->starting_class_name = $starting_class_name;
		foreach ($paths as $path) {
			$this->add($path);
		}
	}

	//------------------------------------------------------------------------------------------- add
	/**
	 * Adds a property path to the joins list
	 *
	 * @param $path string  full path to desired property, starting from starting class
	 * @param $depth integer for internal use : please do not use this
	 * @return Sql_Join the added join, or null if $path does not generate any join
	 */
	public function add($path, $depth = 0)
	{
		if (array_key_exists($path, $this->joins)) {
			return $this->joins[$path];
		}
		list($master_path, $master_property_name) = Sql_Builder::splitPropertyPath($path);
		if ($master_path && !isset($this->joins[$master_path])) {
			$this->add($master_path, $depth + 1);
		}
		$join = new Sql_Join();
		$foreign_class_name = (strpos($master_property_name, "->"))
			? $this->addReverseJoin($join, $master_path, $master_property_name, $path)
			: $this->addSimpleJoin($join, $master_path, $master_property_name, $path);
		$this->joins[$path] = $join->mode
			? $this->addFinalize($join, $master_path, $foreign_class_name, $path, $depth)
			: null;
		return $this->joins[$path];
	}

	//----------------------------------------------------------------------------------- addFinalize
	/**
	 * @param $join Sql_Join
	 * @param $master_path string
	 * @param $foreign_class_name string
	 * @param $foreign_path string
	 * @param $depth integer
	 * @return Sql_Join
	 */
	private function addFinalize(
		Sql_Join $join, $master_path, $foreign_class_name, $foreign_path, $depth
	) {
		if (!$depth) {
			$join->type = Sql_Join::OBJECT;
		}
		$join->foreign_alias = "t" . $this->alias_counter++;
		if (!isset($join->foreign_table)) {
			$join->foreign_table = Dao::current()->storeNameOf($foreign_class_name);
		}
		if (!isset($join->master_alias)) {
			$join->master_alias = $master_path ? $this->getAlias($master_path) : "t0";
		}
		$this->classes[$foreign_path] = $foreign_class_name;
		$foreign_class = Reflection_Class::getInstanceOf($foreign_class_name);
		$this->properties[$foreign_class_name] = $foreign_class->getAllProperties();
		return $join;
	}

	//--------------------------------------------------------------------------------- addLinkedJoin
	/**
	 * @param $join Sql_Join
	 * @param $master_path string
	 * @param $master_property Reflection_Property
	 * @param $foreign_path string
	 * @param $foreign_class_name string
	 * @param $foreign_property_name string
	 */
	private function addLinkedJoin(
		Sql_Join $join, $master_path, Reflection_Property $master_property,
		$foreign_path, $foreign_class_name, $foreign_property_name
	) {
		$linked_join = new Sql_Join();
		$linked_join->foreign_column = "id_" . $foreign_property_name;
		if ($master_property->class < $foreign_class_name) {
			$linked_join->foreign_table = Dao::storeNameOf($master_property->class)
				. "_" . Dao::storeNameOf($foreign_class_name)
				. "_links";
		}
		else {
			$linked_join->foreign_table = Dao::storeNameOf($foreign_class_name)
			. "_" . Dao::storeNameOf($master_property->class)
			. "_links";
		}
		$linked_join->master_column = "id";
		$linked_join->mode = $join->mode;
		$this->joins[$foreign_path . "-link"] = $this->addFinalize(
			$linked_join, $master_path, $foreign_class_name, $foreign_path, 1
		);
		$join->foreign_column = "id";
		$join->master_column = "id_" . $master_property->getAnnotation("foreignlink")->value;
		$join->master_alias = $linked_join->foreign_alias;
		$this->linked_tables[$linked_join->foreign_table] = array(
			$join->master_column, $linked_join->foreign_column
		);
	}

	//----------------------------------------------------------------------------------- addMultiple
	/**
	 * Adds multiple properties paths to the joins list
	 *
	 * @param $paths_array string[]
	 * @return Sql_Joins
	 */
	public function addMultiple($paths_array)
	{
		foreach ($paths_array as $path) {
			$this->add($path);
		}
		return $this;
	}

	//-------------------------------------------------------------------------------- addReverseJoin
	/**
	 * @param $join                 Sql_Join
	 * @param $master_path          string
	 * @param $master_property_name string
	 * @param $foreign_path         string
	 * @return string
	 */
	private function addReverseJoin(Sql_Join $join, $master_path, $master_property_name, $foreign_path)
	{
		list($foreign_class_name, $foreign_property_name) = explode("->", $master_property_name);
		$foreign_class_name = Namespaces::fullClassName($foreign_class_name);
		if (strpos($foreign_property_name, "=")) {
			list($foreign_property_name, $master_property_name) = explode("=", $foreign_property_name);
			$join->master_column  = "id_" . $master_property_name;
		}
		else {
			$join->master_column = "id";
		}
		$join->foreign_column = "id_" . $foreign_property_name;
		$join->mode = Sql_Join::LEFT;
		$foreign_property = Reflection_Property::getInstanceOf(
			$foreign_class_name, $foreign_property_name
		);
		if ($foreign_property->getType()->isMultiple()) {
			if ($foreign_property->getAnnotation("component")->value && class_implements(
				$this->getProperty($master_path, $master_property_name)->class, 'SAF\Framework\Component'
			)) {
				echo "multiple component or collection<br>";
			}
			else {
				echo "multiple map<br>";
			}
		}
		return $foreign_class_name;
	}

	//--------------------------------------------------------------------------------- addSimpleJoin
	/**
	 * @param $join                 Sql_Join
	 * @param $master_path          string
	 * @param $master_property_name string
	 * @param $foreign_path         string
	 * @return string
	 */
	private function addSimpleJoin(Sql_Join $join, $master_path, $master_property_name, $foreign_path)
	{
		$foreign_class_name = null;
		$master_property = $this->getProperty($master_path, $master_property_name);
		if ($master_property) {
			$foreign_type = $master_property->getType();
			if ($foreign_type->isMultiple() && ($foreign_type->getElementTypeAsString() == "string")) {
				// TODO : string[] can have multiple implementations, depending on database engine
				// linked strings table, mysql's set.. should find a way to make this common without
				// knowing anything about the specific
				$foreign_class_name = $foreign_type->asString();
			}
			elseif (!$foreign_type->isBasic()) {
				$join->mode = $master_property->getAnnotation("mandatory")->value
					? Sql_Join::INNER
					: Sql_Join::LEFT;
				if ($foreign_type->isMultiple()) {
					$foreign_class_name = $foreign_type->getElementTypeAsString();
					$foreign_property_name = $master_property->getAnnotation("foreign")->value;
					if (property_exists($foreign_class_name, $foreign_property_name)) {
						$join->foreign_column = "id_" . $foreign_property_name;
						$join->master_column  = "id";
					}
					else {
						$this->addLinkedJoin(
							$join, $master_path, $master_property,
							$foreign_path, $foreign_class_name, $foreign_property_name
						);
					}
				}
				else {
					$foreign_class_name = $foreign_type->asString();
					$join->master_column  = "id_" . $master_property_name;
					$join->foreign_column = "id";
				}
			}
		}
		return $foreign_class_name;
	}

	//-------------------------------------------------------------------------------------- getAlias
	/**
	 * Gets foreign table alias for a given property path
	 *
	 * @param $path string
	 * @return string
	 */
	public function getAlias($path)
	{
		return $this->joins[$path]->foreign_alias;
	}

	//------------------------------------------------------------------------------------ getClasses
	/**
	 * Gets an array of used classes names
	 *
	 * Classes can be returned twice if they are used by several property paths
	 *
	 * @return string[] key is the path of the matching property
	 */
	public function getClasses()
	{
		return $this->classes;
	}

	//--------------------------------------------------------------------------------- getClassNames
	/**
	 * Gets an array of used classes names
	 *
	 * Classes are always returned once
	 *
	 * @return string[] key is an arbitrary counter
	 */
	public function getClassNames()
	{
		return array_values($this->classes);
	}

	//-------------------------------------------------------------------------------------- getJoins
	/**
	 * Gets Sql_Join object for a given property path
	 *
	 * @param $path string full property path
	 * @return Sql_Join may be null if no join have been generated with $path
	 */
	public function getJoin($path)
	{
		return isset($this->joins[$path]) ? $this->joins[$path] : null;
	}

	//-------------------------------------------------------------------------------------- getJoins
	/**
	 * Gets all joins object
	 *
	 * @return Sql_Join[] indiced by properties paths
	 */
	public function getJoins()
	{
		return $this->joins;
	}

	//------------------------------------------------------------------------------- getLinkedTables
	/**
	 * Gets the list of linked tables.
	 * There are tables which do not have any matching class
	 *
	 * @return string[] main key is the table name, contained arrays contains two fields names[]
	 */
	public function getLinkedTables()
	{
		return $this->linked_tables;
	}

	//--------------------------------------------------------------------------------- getProperties
	/**
	 * Gets the list of Reflection_Property objects for a given property path
	 *
	 * @param $master_path string
	 * @return Reflection_Property[]
	 */
	public function getProperties($master_path)
	{
		$class_name = isset($this->classes[$master_path]) ? $this->classes[$master_path] : null;
		return isset($this->properties[$class_name]) ? $this->properties[$class_name] : array();
	}

	//----------------------------------------------------------------------------------- getProperty
	/**
	 * Gets a Reflection_Property object for a given property path
	 *
	 * @param $master_path string
	 * @param $property_name string
	 * @return Reflection_Property
	 */
	private function getProperty($master_path, $property_name)
	{
		$properties = $this->getProperties($master_path);
		return isset($properties[$property_name]) ? $properties[$property_name] : null;
	}

	//----------------------------------------------------------------------------------- newInstance
	/**
	 * A new instance of Sql_Joins, for those who don't lake the new Sql_Joins() syntax
	 *
	 * Construct Sql_Joins object and prepare joins for a list of property paths.
	 *
	 * @param $starting_class_name string the class name for the root of property paths
	 * @param $paths array a property paths list to add at construction
	 * @return Sql_Joins
	 */
	public static function newInstance($starting_class_name, $paths = array())
	{
		return new Sql_Joins($starting_class_name, $paths);
	}

}
