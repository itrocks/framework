<?php
namespace ITRocks\Framework\Property;

use ITRocks\Framework\Locale\Loc;
use ITRocks\Framework\Reflection;
use ITRocks\Framework\Reflection\Annotation\Property\Integrated_Annotation;
use ITRocks\Framework\Reflection\Annotation\Property\Link_Annotation;
use ITRocks\Framework\Reflection\Annotation\Property\Store_Annotation;
use ITRocks\Framework\Reflection\Type;
use ITRocks\Framework\Tools\Names;
use ReflectionException;

/**
 * A Reflection_Property into a select tree has additional methods for filtering
 */
class Reflection_Property extends Reflection\Reflection_Property
{

	//------------------------------------------------------------------------------- $all_expandable
	/**
	 * Force this to true to set all structure properties expandable
	 *
	 * @var boolean
	 */
	static bool $all_expandable = false;

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

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $class_name    string
	 * @param $property_name string
	 * @param $display       string @values name, path,
	 * @throws ReflectionException
	 */
	public function __construct($class_name, $property_name, $display = null)
	{
		parent::__construct($class_name, $property_name);
		switch ($display) {
			case 'name': $this->display = $this->name; break;
			case 'path': $this->display = $this->path; break;
		}
		if ($this->display) {
			$this->display = Loc::tr(Names::propertyToDisplay($this->display));
		}
	}

	//------------------------------------------------------------------------------------ autoExpand
	/**
	 * Check if the property should be automatically expanded
	 *
	 * @return string @values auto-expand,
	 */
	public function autoExpand()
	{
		return (
			$this->getAnnotation('component')->value
			|| $this->getAnnotation('expand')->value
			|| Integrated_Annotation::of($this)->value
			|| Link_Annotation::of($this)->isCollection()
		)
			? 'auto-expand'
			: '';
	}

	//------------------------------------------------------------------------------------- classHtml
	/**
	 * @return string
	 */
	public function classHtml()
	{
		return $this->isExpandable() ? 'class' : '';
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
	 * @return string can be dealt-with as if it is a boolean @values expandable,
	 */
	public function isExpandable()
	{
		$annotation = Store_Annotation::of($this);
		$type       = $this->getType();
		if (static::$all_expandable) {
			return $type->isBasic() ? '' : 'expandable';
		}
		return ($annotation->isString() || $type->isBasic() || $type->isStringable())
			? '' : 'expandable';
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
