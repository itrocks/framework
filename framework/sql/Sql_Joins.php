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
	 * link field full path to their class name
	 *
	 * @var multitype:string indice is field full path
	 */
	private $classes = array();

	//---------------------------------------------------------------------------------------- $joins
	/**
	 * link field path to sql join
	 *
	 * @var multitype:Sql_Join indice is field full path
	 */
	private $joins = array();

	//----------------------------------------------------------------------------------- $properties
	/**
	 * link class names to their properties
	 *
	 * @var multitype:multitype:Reflection_Property indices are : class name, property name 
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
	 * @param string $starting_class_name
	 */
	public function __construct($starting_class_name, $paths = array())
	{
		$this->alias_counter       = 1;
		$this->classes[""]         = $starting_class_name;
		$this->properties[$starting_class_name] = Class_Fields::fields($starting_class_name);
		$this->starting_class_name = $starting_class_name;
		foreach ($paths as $path) {
			$this->add($path);
		}
	}

	//------------------------------------------------------------------------------------------- add
	/**
	 * @param string $path full path to desired property, starting from starting class
	 */
	public function add($path, $depth = 0)
	{
		$foreign_path = $path;
		$master_path = lLastParse($foreign_path, ".", 1, false);
		if (strpos($foreign_path, ".")) {
			if (!$this->joins[$master_path]) {
				$this->add($master_path, $depth + 1);
			}
		}
		$join = new Sql_Join();
		$master_property_name = rLastParse($foreign_path, ".", 1, true);
		if (strpos($master_property_name, "->")) {
			list($foreign_class_name, $sql_field) = split("->", $master_property_name);
			list($join->foreign_field, $join->master_field) = split("=", $sql_field);
			if (!$join->master_field) {
				$join->master_field = "id";
			}
			$join->foreign_field = "id_" . $join->foreign_field;
			$join->mode = "LEFT";
		} else {
			$master_property = $this->getProperty($master_path, $master_property_name);
			if ($master_property) {
				$foreign_class_name = $master_property->getType();
				if (!Type::isBasic($foreign_class_name)) {
					$join->mode = $master_property->isMandatory() ? Sql_Join::INNER : Sql_Join::LEFT;
					if (strpos($foreign_class_name, "[]")) {
						$foreign_class_name = substr($foreign_class_name, 0, -2);
						$join->master_field   = "id";
						$join->foreign_field  = "id_" . $master_property->getForeignName();
					} else {
						$join->master_field  = "id_" . $master_property_name;
						$join->foreign_field = "id";
					}
				}
			}
		}
		if ($join->mode) {
			if (!$depth) {
				$join->type = Sql_Join::OBJECT;
			}
			$join->master_alias  = $master_path ? $this->getAlias($master_path) : "t0";
			$join->foreign_table = Sql_Table::classToTableName($foreign_class_name);
			$join->foreign_alias = "t" . $this->alias_counter ++;
			$this->joins[$foreign_path]            = $join;
			$this->classes[$foreign_path]          = $foreign_class_name;
			$this->properties[$foreign_class_name] = Class_Fields::fields($foreign_class_name);
		}
		return $this;
	}

	//-------------------------------------------------------------------------------------- getAlias
	public function getAlias($path)
	{
		return $this->joins[$path]->foreign_alias;
	}
 
	//-------------------------------------------------------------------------------------- getJoins
	/**
	 * @param  string $path full property path
	 * @return Sql_Join may be null if no join have been generated with $path
	 */
	public function getJoin($path)
	{
		return $this->joins[$path];
	}

	//-------------------------------------------------------------------------------------- getJoins
	/**
	 * @return Sql_Join[] indiced by field path 
	 */
	public function getJoins()
	{
		return $this->joins;
	}

	//----------------------------------------------------------------------------------- getProperty
	/**
	 * @param  string $master_path
	 * @param  string $property_name
	 * @return Reflection_Property
	 */
	private function getProperty($master_path, $property_name)
	{
		$properties = $this->getProperties($master_path);
		return $properties[$property_name];
	}

	//--------------------------------------------------------------------------------- getProperties
	/**
	 * @param  string $master_path
	 * @return multitype:Reflection_Property
	 */
	public function getProperties($master_path)
	{
		$class_name = $this->classes[$master_path];
		return $this->properties[$class_name];
	}

	//----------------------------------------------------------------------------------- newInstance
	/**
	 * @param  string $starting_class_name
	 * @return Sql_Joins
	 */
	public static function newInstance($starting_class_name)
	{
		return new Sql_Joins($starting_class_name);
	}

}
