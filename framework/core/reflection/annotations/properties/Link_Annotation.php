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
		if (!in_array($value, array("All", "Collection", "DateTime", "Map", "Object"))) {
			trigger_error(
				"@link $value is a bad value, only All, Collection, DateTime, Map and Object can be used",
				E_USER_ERROR
			);
			$value = "";
		}
		parent::__construct($value);
	}

}
