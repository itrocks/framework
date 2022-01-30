<?php
namespace ITRocks\Framework\Component;

use ITRocks\Framework\Reflection\Interfaces\Reflection_Property;
use ITRocks\Framework\Reflection\Reflection_Property_Value;
use ITRocks\Framework\Tools\Names;

/**
 * A tab is a data organisation for multiple items $content classification into a graphical tab
 *
 * A tab :
 * - has a $title for display
 * - can contain multiple columns into $columns, each column is a content array
 * - can contain multiple content rows into $content
 * - can contain included sub-tabs into $includes
 */
class Tab
{

	//-------------------------------------------------------------------------------------- $columns
	/**
	 * Group multiple contents collections into some columns
	 *
	 * @var mixed[]
	 */
	public $columns = [];

	//-------------------------------------------------------------------------------------- $content
	/**
	 * Content can be a single entry or most often rows of multiple entries of the same class
	 *
	 * @var mixed
	 */
	public $content;

	//------------------------------------------------------------------------------------- $includes
	/**
	 * Included sub-tabs collection
	 *
	 * @link Collection
	 * @var Tab[]
	 */
	public $includes = [];

	//---------------------------------------------------------------------------------------- $title
	/**
	 * Displayable tab title
	 *
	 * @var string
	 */
	public $title;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $title string
	 * @param $content mixed
	 */
	public function __construct($title = null, $content = null)
	{
		if (isset($title)) {
			$this->title = $title;
		}
		if (isset($content)) {
			$this->content = $content;
		}
	}

	//----------------------------------------------------------------------------------------- __get
	/**
	 * @param $key string
	 * @return Tab
	 */
	public function __get($key)
	{
		return $this->includes[$key];
	}

	//--------------------------------------------------------------------------------------- __isset
	/**
	 * @param $key string
	 * @return boolean
	 */
	public function __isset($key)
	{
		return isset($this->includes[$key]);
	}

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string
	 */
	public function __toString() : string
	{
		return $this->title;
	}

	//--------------------------------------------------------------------------------------- __unset
	/**
	 * @param $key string
	 */
	public function __unset($key)
	{
		unset($this->includes[$key]);
	}

	//------------------------------------------------------------------------------------------- add
	/**
	 * Add content to the tab (eg content is an array, new elements are added)
	 *
	 * @param $content mixed
	 * @return Tab
	 */
	public function add($content)
	{
		if (is_array($this->content) && is_array($content)) {
			$this->content = array_merge($this->content, $content);
		}
		else {
			$this->content .= $content;
		}
		return $this;
	}

	//----------------------------------------------------------------------- filterVisibleProperties
	/**
	 * @param $hide_empty_test boolean If false, will be shown even if HIDE_EMPTY is set
	 */
	public function filterVisibleProperties($hide_empty_test = true)
	{
		if ($this->content) {
			foreach ($this->content as $key => $element) {
				if (($element instanceof Reflection_Property) && method_exists($element, 'isVisible')) {
					if (!$element->isVisible($hide_empty_test)) {
						unset($this->content[$key]);
					}
				}
			}
		}
		foreach ($this->includes as $include) {
			$include->filterVisibleProperties($hide_empty_test);
		}
	}

	//--------------------------------------------------------------------------------------- getName
	/**
	 * return @string
	 */
	public function getName()
	{
		return Names::displayToProperty($this->title);
	}

	//-------------------------------------------------------------------------------------------- id
	/**
	 * Return a calculated id for the tab, calculated from its title
	 */
	public function id()
	{
		return Names::displayToProperty($this->title);
	}

	//-------------------------------------------------------------------------------------- included
	/**
	 * Return included tabs, but not those which identifier begins with '_'
	 * nor those that are empty
	 *
	 * @return Tab[]
	 */
	public function included()
	{
		$list = [];
		foreach ($this->includes as $key => $tab) {
			if ((substr($key, 0, 1) != '_') && ($tab->content || $tab->columns || $tab->includes)) {
				$list[$key] = $tab;
			}
		}
		return $list;
	}

	//---------------------------------------------------------------------------- propertiesToValues
	/**
	 * Change properties or property names stored into tab into values from the object
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $object           object
	 * @param $properties_title string[] key is the property path, value is the property display (tr)
	 * @return Tab
	 */
	public function propertiesToValues($object, array $properties_title)
	{
		$class_name = get_class($object);
		foreach ($this->columns as $key => $column) {
			if ($column instanceof Reflection_Property && !($column instanceof Reflection_Property_Value)) {
				$column = $column->getName();
			}
			if (is_string($column)) {
				/** @noinspection PhpUnhandledExceptionInspection class and columns must be valid */
				$property = new Reflection_Property_Value($class_name, $column, $object, false, true);
				if (isset($properties_title[$property->name])) {
					$property->display = $properties_title[$property->name];
				}
				$property->final_class = $class_name;
				$this->columns[$key] = $property;
			}
		}
		foreach ($this->includes as $included) {
			$included->propertiesToValues($object, $properties_title);
		}
		return $this;
	}

}
