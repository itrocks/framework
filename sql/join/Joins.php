<?php
namespace ITRocks\Framework\Sql\Join;

use ITRocks\Framework\Builder;
use ITRocks\Framework\Dao;
use ITRocks\Framework\Reflection\Annotation\Class_;
use ITRocks\Framework\Reflection\Annotation\Class_\Link_Same_Annotation;
use ITRocks\Framework\Reflection\Annotation\Property\Foreign_Annotation;
use ITRocks\Framework\Reflection\Annotation\Property\Link_Annotation;
use ITRocks\Framework\Reflection\Annotation\Property\Store_Annotation;
use ITRocks\Framework\Reflection\Annotation\Property\Store_Name_Annotation;
use ITRocks\Framework\Reflection\Link_Class;
use ITRocks\Framework\Reflection\Reflection_Class;
use ITRocks\Framework\Reflection\Reflection_Property;
use ITRocks\Framework\Sql;
use ITRocks\Framework\Sql\Join;
use ITRocks\Framework\Sql\Link_Table;
use ITRocks\Framework\Tools\Namespaces;

/**
 * This builds and stores SQL tables joins in order to make easy automatic joins generation
 * knowing only a source business object and property paths.
 */
class Joins
{

	//-------------------------------------------------------------------------------- $alias_counter
	/**
	 * alias counter for the next aliased table
	 *
	 * @var integer
	 */
	private $alias_counter;

	//--------------------------------------------------------------------------------- $alias_prefix
	/**
	 * Alias prefix for all aliased tables
	 *
	 * @var string
	 */
	public $alias_prefix = '';

	//-------------------------------------------------------------------------------------- $classes
	/**
	 * link property full path to their class name
	 *
	 * @var string[] key is property full path
	 */
	private $classes = [];

	//-------------------------------------------------------------------------------- $id_link_joins
	/**
	 * link joins that work with id of the join master table
	 *
	 * @var Join[] key is property full path
	 */
	private $id_link_joins = [];

	//---------------------------------------------------------------------------------------- $joins
	/**
	 * link property path to sql join
	 *
	 * @var Join[] key is property full path
	 */
	private $joins = [];

	//----------------------------------------------------------------------------------- $link_joins
	/**
	 * joins for properties coming from classes having the 'link' annotation
	 *
	 * @var Join[] key is property full path
	 */
	private $link_joins = [];

	//--------------------------------------------------------------------------- $link_property_name
	/**
	 * If set : force the name of the property for the linked object
	 *
	 * @var string|null
	 */
	private $link_property_name;

	//-------------------------------------------------------------------------------- $linked_tables
	/**
	 * linked tables
	 *
	 * Each key is the linked table name
	 * Each value is a string[] : element 0 is the master column name, 1 is the foreign column name
	 *
	 * @var string[]
	 */
	private $linked_tables = [];

	//----------------------------------------------------------------------------------- $properties
	/**
	 * link class names to their properties
	 *
	 * @var array The 2 keys are class and property name, value is Reflection_Property
	 */
	private $properties = [];

	//------------------------------------------------------------------------------- $starting_class
	/**
	 * The starting class reflection object
	 *
	 * @var Reflection_Class
	 */
	private $starting_class;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * Construct Joins object and prepare joins for a list of property paths
	 *
	 * @param $starting_class_name string the class name for the root of property paths
	 * @param $paths               array a property paths list to add at construction
	 * @param $link_property_name  string if set : force the name of the property for linked object
	 */
	public function __construct($starting_class_name, array $paths = [], $link_property_name = null)
	{
		$this->alias_counter      = 1;
		$this->classes['']        = $starting_class_name;
		$this->link_property_name = $link_property_name;
		$this->addProperties('', $starting_class_name);
		foreach ($paths as $path) {
			$this->add($path);
		}
	}

	//------------------------------------------------------------------------------------------- add
	/**
	 * Adds a property path to the joins list
	 *
	 * @param $path  string full path to desired property, starting from starting class
	 * @param $depth integer for internal use : please do not use this
	 * @return Join the added join, or null if $path does not generate any join
	 */
	public function add($path, $depth = 0)
	{
		if (isset($this->joins[$path]) || array_key_exists($path, $this->joins)) {
			return $this->joins[$path];
		}
		list($master_path, $master_property_name) = Sql\Builder::splitPropertyPath($path);
		if ($master_path && !isset($this->joins[$master_path])) {
			$this->add($master_path, $depth + 1);
		}
		if (isset($this->link_joins[$path])) {
			$property_type = $this->getProperties($master_path)[$master_property_name]->getType();
			if ($property_type->isClass() && !$property_type->isBasic()) {
				$linked_master_alias = $this->link_joins[$path]->foreign_alias;
			}
			else {
				return $this->link_joins[$path];
			}
		}
		$join = new Join();
		if (
			// new Class_Name(property_name)
			(substr($master_property_name, -1) === ')')
			// @deprecated Class_Name->property_name
			|| strpos($master_property_name, '->')
		) {
			$foreign_class_name = $this->addReverseJoin(
				$join, $master_path, $master_property_name, $path
			);
		}
		else {
			$foreign_class_name = $this->addSimpleJoin(
				$join, $master_path, $master_property_name, $path
			);
		}
		$this->joins[$path] = $join->mode
			? $this->addFinalize($join, $master_path, $foreign_class_name, $path, $depth)
			: null;
		if (isset($linked_master_alias) && !$join->linked_join) {
			$join->master_alias = $linked_master_alias;
		}
		return $this->joins[$path];
	}

	//----------------------------------------------------------------------------------- addFinalize
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $join               Join
	 * @param $master_path        string
	 * @param $foreign_class_name string
	 * @param $foreign_path       string
	 * @param $depth              integer
	 * @return Join
	 */
	private function addFinalize(
		Join $join, $master_path, $foreign_class_name, $foreign_path, $depth
	) {
		if (!$depth) {
			$join->type = Join::OBJECT;
		}
		$join->foreign_alias = $this->alias_prefix . 't' . $this->alias_counter++;
		if (!isset($join->foreign_table)) {
			$join->foreign_class = Builder::className($foreign_class_name);
			$join->foreign_table = Dao::storeNameOf($join->foreign_class);
		}
		if (!isset($join->master_alias)) {
			$join->master_alias = $master_path ? $this->getAlias($master_path) : 't0';
		}
		$this->classes[$foreign_path] = $foreign_class_name;
		$this->addProperties($foreign_path, $foreign_class_name, $join->mode);
		/** @noinspection PhpUnhandledExceptionInspection */
		if (
			(substr($join->foreign_table, -5) === '_view')
			&& (new Reflection_Class($join->foreign_class))->isAbstract()
		) {
			$join->secondary['class'] = $join->master_column . '_class';
		}
		return $join;
	}

	//--------------------------------------------------------------------------------------- addJoin
	/**
	 * Adds a join and automatically set its foreign alias to the next one (if not already set)
	 *
	 * @param $join Join
	 */
	public function addJoin(Join $join)
	{
		if (!isset($join->foreign_alias)) {
			$join->foreign_alias = $this->alias_prefix . 't' . $this->alias_counter++;
		}
		$this->joins[] = $join;
	}

	//-------------------------------------------------------------------------------- addLinkedClass
	/**
	 * Add a link class (using the 'link' class annotation) to joins
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $path               string     the property path
	 * @param $class              Link_Class the link class itself (which contains the @link)
	 * @param $linked_class_name  string     the linked class name (the value of @link)
	 * @param $join_mode          string
	 * @return Reflection_Property[] the properties that come from the linked class,
	 *                               for further exclusion
	 */
	private function addLinkedClass($path, Link_Class $class, $linked_class_name, $join_mode)
	{
		/** @noinspection PhpUnhandledExceptionInspection linked class name must be valid */
		$linked_class    = new Reflection_Class($linked_class_name);
		$link_same       = Link_Same_Annotation::of($class)->getLinkClass() ?: $class;
		$master_property = $link_same->getCompositeProperty($linked_class_name);

		$join                  = new Join();
		$join->foreign_alias   = $this->alias_prefix . 't' . $this->alias_counter;
		$join->foreign_column  = 'id';
		$join->foreign_class   = Builder::className($linked_class_name);
		$join->foreign_table   = Dao::storeNameOf($join->foreign_class);
		$join->master_alias    = $this->alias_prefix . 't' . ($this->alias_counter - 1);
		$join->master_column   = 'id_' . Store_Name_Annotation::of($master_property)->value;
		$join->master_property = $master_property;
		$join->mode            = ($join_mode == Join::LEFT) ? Join::LEFT : Join::INNER;
		$join->type            = Join::LINK;

		$this->alias_counter ++;
		// this ensures that the main path is set before the linked path
		if (!isset($this->joins[$path])) {
			$this->joins[$path] = null;
		}
		$this->joins[($path ? ($path . '-') : '') . $join->foreign_table . '-@link'] = $join;
		$this->id_link_joins[$path] = $join;
		$this->link_joins[$path]    = $join;
		$more_linked_class_name     = Class_\Link_Annotation::of($linked_class)->value;
		$exclude_properties         = $more_linked_class_name
			? $this->addLinkedClass($path, $class, $more_linked_class_name, $join_mode)
			: [];

		foreach ($linked_class->getProperties([T_EXTENDS, T_USE]) as $property) {
			if (!$property->isStatic() && !isset($exclude_properties[$property->name])) {
				$this->properties[$linked_class_name][$property->name] = $property;
				$property_path = ($path ? $path . DOT : '') . $property->name;
				$type          = $property->getType();
				if ($type->isClass()) {
					$this->classes[$property_path] = $property->getType()->getElementTypeAsString();
				}
				$this->link_joins[$property_path]    = $join;
				$exclude_properties[$property->name] = true;
			}
		}

		return $exclude_properties;
	}

	//--------------------------------------------------------------------------------- addLinkedJoin
	/**
	 * @param $join               Join
	 * @param $master_path        string
	 * @param $foreign_path       string
	 * @param $foreign_class_name string
	 * @param $property           Reflection_Property
	 * @param $reverse            boolean
	 */
	private function addLinkedJoin(
		Join $join, $master_path, $foreign_path, $foreign_class_name,
		Reflection_Property $property, $reverse = false
	) {
		$link_table                  = new Link_Table($property);
		$linked_join                 = new Join();
		$linked_join->foreign_column = $reverse
			? $link_table->foreignColumn()
			: $link_table->masterColumn();
		$linked_join->foreign_table           = $link_table->table();
		$linked_join->master_column           = 'id';
		$linked_join->mode                    = $join->mode;
		$this->joins[$foreign_path . '-link'] = $this->addFinalize(
			$linked_join, $master_path ? $master_path : 'id', $foreign_class_name, $foreign_path, 1
		);
		$join->foreign_column = 'id';
		$join->linked_join    = $linked_join;
		$join->master_alias   = $linked_join->foreign_alias;
		$join->master_column  = $reverse
			? $link_table->masterColumn()
			: $link_table->foreignColumn();
		$join->master_property                            = $property;
		$this->linked_tables[$linked_join->foreign_table] = [
			$join->master_column, $linked_join->foreign_column
		];

		// fix linked join master_alias when master_column is an id : if point on a link class,
		// then replace the master_alias with the link class.
		if ($linked_join->master_column === 'id') {
			$master_join = $this->byAlias($linked_join->master_alias);
			if ($master_join) {
				$master_linked_join = $this->getLinkedJoin($master_join);
				if ($master_linked_join) {
					$linked_join->master_alias = $master_linked_join->foreign_alias;
				}
			}
		}
	}

	//----------------------------------------------------------------------------------- addMultiple
	/**
	 * Adds multiple properties paths to the joins list
	 *
	 * @param $paths_array string[]
	 * @return Joins
	 */
	public function addMultiple(array $paths_array)
	{
		foreach ($paths_array as $path) {
			$this->add($path);
		}
		return $this;
	}

	//--------------------------------------------------------------------------------- addProperties
	/**
	 * Adds properties of the class name into $properties
	 *
	 * Please always call this instead of adding properties manually : it manages 'link'
	 * class annotations.
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $path       string
	 * @param $class_name string
	 * @param $join_mode  string
	 */
	private function addProperties($path, $class_name, $join_mode = null)
	{
		/** @noinspection PhpUnhandledExceptionInspection class name must be valid */
		$class                         = new Link_Class($class_name);
		$this->properties[$class_name] = $class->getProperties([T_EXTENDS, T_USE]);
		$linked_class_name             = Class_\Link_Annotation::of($class)->value;
		if ($linked_class_name) {
			$class->link_property_name = $this->link_property_name;
			$this->addLinkedClass($path, $class, $linked_class_name, $join_mode);
		}
	}

	//-------------------------------------------------------------------------------- addReverseJoin
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $join                 Join
	 * @param $master_path          string
	 * @param $master_property_name string @example From an Order context : 'Order_Line(order)'
	 * @param $foreign_path         string
	 * @return string the foreign class name
	 * @todo use @store_name to get correct master and foreign columns name
	 */
	private function addReverseJoin(
		Join $join, $master_path, $master_property_name, $foreign_path
	) {
		// new Class_Name(property_name)
		if (strpos($master_property_name, ')')) {
			list($foreign_class_name, $foreign_property_name) = explode('(', $master_property_name);
			$foreign_property_name = substr($foreign_property_name, 0, -1);
		}
		// @deprecated Class_Name->property_name
		else {
			list($foreign_class_name, $foreign_property_name) = explode('->', $master_property_name);
		}
		$master_class_name  = $this->classes[$master_path];
		$foreign_class_name = Namespaces::defaultFullClassName(
			Builder::className($foreign_class_name), $master_class_name
		);
		if (strpos($foreign_property_name, ',')) {
			foreach (explode(',', rParse($foreign_property_name, ',')) as $secondary_link) {
				list($secondary_foreign, $secondary_master) = explode('=', $secondary_link);
				$join->secondary[$secondary_foreign] = $secondary_master;
			}
			$foreign_property_name = lParse($foreign_property_name, ',');
		}
		if (strpos($foreign_property_name, '=')) {
			list($foreign_property_name, $master_property_name) = explode('=', $foreign_property_name);
			/** @noinspection PhpUnhandledExceptionInspection master property must be valid in class */
			$master_property = new Reflection_Property($master_class_name, $master_property_name);
			if (strpos($master_property_name, DOT)) {
				list($sub_master, $master_property_name) = Sql\Builder::splitPropertyPath(
					$master_property_name
				);
				if (!isset($this->joins[$sub_master])) {
					$sub_join           = $this->add($sub_master);
					$join->master_alias = $sub_join->foreign_alias;
				}
			}
			$join->master_column = $master_property->getType()->isClass()
				? ('id_' . $master_property_name)
				: $master_property_name;
		}
		else {
			$join->master_column = 'id';
		}
		/** @noinspection PhpUnhandledExceptionInspection foreign property must be valid in class */
		$foreign_property      = new Reflection_Property($foreign_class_name, $foreign_property_name);
		$foreign_property_type = $foreign_property->getType();
		$join->foreign_column  = $foreign_property_type->isClass()
			? ('id_' . $foreign_property_name)
			: $foreign_property_name;
		$join->mode = Join::LEFT;
		if ($foreign_property_type->isMultiple()) {
			$this->addLinkedJoin(
				$join, $master_path, $foreign_path, $foreign_class_name, $foreign_property, true
			);
		}
		else {
			$join->foreign_property = $foreign_property;
		}
		return $foreign_class_name;
	}

	//--------------------------------------------------------------------------------- addSimpleJoin
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $join                 Join
	 * @param $master_path          string
	 * @param $master_property_name string
	 * @param $foreign_path         string
	 * @return string the foreign class name
	 */
	private function addSimpleJoin(Join $join, $master_path, $master_property_name, $foreign_path)
	{
		$foreign_class_name = null;
		$master_property    = $this->getProperty($master_path, $master_property_name);
		if ($master_property) {
			$foreign_type = $master_property->getType();
			if ($foreign_type->isMultiple() && ($foreign_type->getElementTypeAsString() == 'string')) {
				// TODO : string[] can have multiple implementations, depending on database engine
				// linked strings table, mysql set.. should find a way to make this common without
				// knowing anything about the specific
				$foreign_class_name = $foreign_type->asString();
			}
			elseif (
				!$foreign_type->isBasic()
				&& !$master_property->getAnnotation(Store_Annotation::ANNOTATION)->value
			) {
				$join->mode = $master_property->getAnnotation('mandatory')->value
					? Join::INNER
					: Join::LEFT;
				// force LEFT if any of the properties in the master property path is not mandatory
				if (($join->mode === Join::INNER) && $master_path) {
					/** @noinspection PhpUnhandledExceptionInspection classes are valid */
					$root_class    = new Reflection_Class($this->classes['']);
					$property_path = '';
					foreach (explode(DOT, $master_path . DOT . $master_property_name) as $property_name) {
						$property_path .= ($property_path ? DOT : '') . $property_name;
						$property       = $root_class->getProperty($property_path);
						if (!$property || !$property->getAnnotation('mandatory')->value) {
							$join->mode = Join::LEFT;
							break;
						}
					}
				}
				if ($foreign_type->isMultiple() || $master_property->getAnnotation('component')->value) {
					$foreign_class_name    = $foreign_type->getElementTypeAsString();
					$foreign_property_name = Foreign_Annotation::of($master_property)->value;
					if (
						property_exists($foreign_class_name, $foreign_property_name)
						&& !Link_Annotation::of($master_property)->isMap()
					) {
						/** @noinspection PhpUnhandledExceptionInspection property must be valid in class */
						$foreign_property = new Reflection_Property(
							$foreign_class_name, $foreign_property_name
						);
						$join->foreign_column   = 'id_' . Store_Name_Annotation::of($foreign_property)->value;
						$join->foreign_property = $foreign_property;
						$join->master_column    = 'id';
					}
					else {
						$this->addLinkedJoin(
							$join, $master_path, $foreign_path, $foreign_class_name, $master_property
						);
					}
				}
				else {
					$foreign_class_name    = Builder::className($foreign_type->asString());
					$join->foreign_column  = 'id';
					$join->master_column   = 'id_' . Store_Name_Annotation::of($master_property)->value;
					$join->master_property = $master_property;
				}
			}
		}
		return $foreign_class_name;
	}

	//--------------------------------------------------------------------------------------- byAlias
	/**
	 * Gets the join that matches this foreign_alias
	 *
	 * @param $alias string
	 * @return Join
	 */
	private function byAlias($alias)
	{
		foreach ($this->joins as $join) {
			if ($join && ($join->foreign_alias === $alias)) {
				return $join;
			}
		}
		return null;
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
		return isset($this->joins[$path])
			? $this->joins[$path]->foreign_alias
			: ($this->alias_prefix . 't0');
	}

	//-------------------------------------------------------------------------------------- getClass
	/**
	 * Gets the class (property type) associated to the $column name, if set
	 *
	 * @param $column_name string
	 * @return string|null
	 */
	public function getClass($column_name)
	{
		return isset($this->classes[$column_name]) ? $this->classes[$column_name] : null;
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

	//---------------------------------------------------------------------------- getClassProperties
	/**
	 * @param $class_name string
	 * @return Reflection_Property[]
	 */
	public function getClassProperties($class_name)
	{
		return $this->properties[$class_name];
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

	//--------------------------------------------------------------------------------- getIdLinkJoin
	/**
	 * Returns id link join, if set for given path
	 *
	 * @param $path string
	 * @return Join
	 */
	public function getIdLinkJoin($path)
	{
		return isset($this->id_link_joins[$path]) ? $this->id_link_joins[$path] : null;
	}

	//--------------------------------------------------------------------------------------- getJoin
	/**
	 * Gets Join object for a given property path
	 *
	 * @param $path string full property path
	 * @return Join|null may be null if no join have been generated with $path
	 */
	public function getJoin($path)
	{
		return isset($this->joins[$path]) ? $this->joins[$path] : null;
	}

	//-------------------------------------------------------------------------------------- getJoins
	/**
	 * Gets all joins object
	 *
	 * @return Join[] keys are properties paths
	 */
	public function getJoins()
	{
		return $this->joins;
	}

	//--------------------------------------------------------------------------------- getLinkedJoin
	/**
	 * Gets the linked join matching a direct join
	 *
	 * @param $join Join
	 * @return Join the linked join
	 */
	public function getLinkedJoin(Join $join)
	{
		foreach ($this->getLinkedJoins() as $linked_join) {
			if ($linked_join->master_alias === $join->foreign_alias) {
				return $linked_join;
			}
		}
		return null;
	}

	//-------------------------------------------------------------------------------- getLinkedJoins
	/**
	 * Gets the list of joins that come from a 'link' class annotation
	 *
	 * @return Join[]
	 */
	public function getLinkedJoins()
	{
		$joins = [];
		foreach ($this->joins as $key => $join) {
			if (is_object($join) && (($key === 'id') || (substr($key, -6) === '-@link'))) {
				$joins[$key] = $join;
			}
		}
		return $joins;
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
		return isset($this->properties[$class_name]) ? $this->properties[$class_name] : [];
	}

	//----------------------------------------------------------------------------------- getProperty
	/**
	 * Gets a Reflection_Property object for a given property path
	 *
	 * @param $master_path   string
	 * @param $property_name string If null, $master path contains the full path for the property
	 * @return Reflection_Property
	 */
	public function getProperty($master_path, $property_name = null)
	{
		if (!$property_name) {
			list($master_path, $property_name) = Sql\Builder::splitPropertyPath($master_path);
		}
		$properties = $this->getProperties($master_path);
		return isset($properties[$property_name]) ? $properties[$property_name] : null;
	}

	//------------------------------------------------------------------------------ getStartingClass
	/**
	 * Gets starting class as a Reflection_Class object
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 * @return Reflection_Class
	 */
	public function getStartingClass()
	{
		if (!$this->starting_class) {
			$class_name = $this->getStartingClassName();
			/** @noinspection PhpUnhandledExceptionInspection starting class name is always valid */
			$this->starting_class = new Reflection_Class($class_name);
		}
		return $this->starting_class;
	}

	//-------------------------------------------------------------------------- getStartingClassName
	/**
	 * Gets starting class name as defined in constructor
	 *
	 * @return string
	 */
	public function getStartingClassName()
	{
		return Builder::className($this->classes['']);
	}

	//----------------------------------------------------------------------------------- newInstance
	/**
	 * A new instance of Joins, for those who don't lake the new Joins() syntax
	 *
	 * Construct Joins object and prepare joins for a list of property paths.
	 *
	 * @param $starting_class_name string the class name for the root of property paths
	 * @param $paths               array a property paths list to add at construction
	 * @return Joins
	 */
	public static function newInstance($starting_class_name, array $paths = [])
	{
		return new Joins($starting_class_name, $paths);
	}

	//------------------------------------------------------------------------------------- rootAlias
	/**
	 * @return string
	 */
	public function rootAlias()
	{
		return $this->alias_prefix . 't0';
	}

}
