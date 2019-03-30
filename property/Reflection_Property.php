<?php
namespace ITRocks\Framework\Property;

use ITRocks\Framework\Reflection;
use ITRocks\Framework\Reflection\Annotation\Property\Store_Annotation;
use ITRocks\Framework\Reflection\Type;

/**
 * A Reflection_Property into a select tree has additional methods for filtering
 */
class Reflection_Property extends Reflection\Reflection_Property
{

	//-------------------------------------------------------------------------------------- $display
	/**
	 * Translated display for the property
	 *
	 * @example 'property'
	 * @example 'class (property)'
	 * @var string
	 */
	public $display = null;

	//----------------------------------------------------------------------------------- $link_class
	/**
	 * Class for the link : always the root class name
	 *
	 * @var string
	 */
	public $link_class = null;

	//------------------------------------------------------------------------------------ $link_path
	/**
	 * Path to send for the link
	 *
	 * @example 'Class\Name(property)'
	 * @example 'main.class.property.Class\Name(property).path'
	 * @var string
	 */
	public $link_path = null;

	//------------------------------------------------------------------------------------- classHtml
	/**
	 * @return string
	 */
	public function classHtml()
	{
		return $this->isExpandable() ? 'class' : '';
	}

	//-------------------------------------------------------------------------------------- expandId
	/**
	 * An unique identifier for the expand target zone
	 *
	 * @return string
	 */
	public function expandId()
	{
		return str_replace(DOT, '-', strUri($this->link_path));
	}

	//----------------------------------------------------------------------------------- isBasicHtml
	/**
	 * Tells if the property type is a basic type or not, with an HTML result if yes
	 *
	 * @param $include_multiple_string boolean if false, string[] is not considered as a basic type
	 * @return string|null 'basic'
	 * @see Type::isBasic
	 */
	public function isBasicHtml($include_multiple_string = true)
	{
		return $this->getType()->isBasic($include_multiple_string) ? 'basic' : null;
	}

	//---------------------------------------------------------------------------------- isExpandable
	/**
	 * Returns true if the property is expandable into the select properties tree
	 *
	 * Expandable properties :
	 * - have no @store false : we do not know how to read these objects sub-properties data for lists
	 * - have not basic or stringable types
	 *
	 * TODO NORMAL should deal with @store and stringable : we miss them
	 *
	 * @return boolean
	 */
	public function isExpandable()
	{
		$annotation = Store_Annotation::of($this);
		$type       = $this->getType();
		return !($annotation->isFalse() || $type->isBasic() || $type->isStringable());
	}

	//------------------------------------------------------------------------------ isStringableHtml
	/**
	 * @return string|null 'stringable' if stringable
	 */
	public function isStringableHtml()
	{
		return $this->getType()->isStringable() ? 'stringable' : null;
	}

}
