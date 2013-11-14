<?php
namespace SAF\Framework;

/**
 * Link annotation defines which kind of link is defined for an object or array of objects property
 *
 * Value can be "All", "Collection", "DateTime", "Map", "Object"
 */
class Link_Annotation extends Annotation
{

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param string $value
	 */
	public function __construct($value)
	{
		if (
			!empty($value)
			&& !in_array($value, array("All", "Collection", "DateTime", "Map", "Object"))
		) {
			trigger_error(
				"@link $value is a bad value : only All, Collection, DateTime, Map and Object can be used",
				E_USER_ERROR
			);
			$value = "";
		}
		parent::__construct($value);
	}

	//----------------------------------------------------------------------------------------- isAll
	/**
	 * @return boolean
	 */
	public function isAll()
	{
		return ($this->value === "All");
	}

	//---------------------------------------------------------------------------------- isCollection
	/**
	 * @return boolean
	 */
	public function isCollection()
	{
		return $this->value === "Collection";
	}

	//------------------------------------------------------------------------------------ isDateTime
	/**
	 * @return boolean
	 */
	public function isDateTime()
	{
		return ($this->value === "DateTime");
	}

	//----------------------------------------------------------------------------------------- isMap
	/**
	 * @return boolean
	 */
	public function isMap()
	{
		return ($this->value === "Map");
	}

	//------------------------------------------------------------------------------------ isMultiple
	/**
	 * @return boolean
	 */
	public function isMultiple()
	{
		return (in_array($this->value, array("All", "Collection", "Map")));
	}

	//-------------------------------------------------------------------------------------- isObject
	/**
	 * @return boolean
	 */
	public function isObject()
	{
		return ($this->value === "Object");
	}

}
