<?php
namespace ITRocks\Framework\Property;

use ITRocks\Framework\Locale\Loc;
use ITRocks\Framework\Reflection;
use ITRocks\Framework\Reflection\Annotation\Property\Integrated_Annotation;
use ITRocks\Framework\Reflection\Annotation\Property\Link_Annotation;
use ITRocks\Framework\Reflection\Attribute\Property\Component;
use ITRocks\Framework\Reflection\Attribute\Property\Store;
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
	 * @var ?string
	 */
	public ?string $display = null;

	//----------------------------------------------------------------------------------- $link_class
	/**
	 * Class for the link : always the root class name
	 *
	 * @var ?string
	 */
	public ?string $link_class = null;

	//------------------------------------------------------------------------------------ $link_path
	/**
	 * Path to send for the link
	 *
	 * @example 'Class\Name(property)'
	 * @example 'main.class.property.Class\Name(property).path'
	 * @var ?string
	 */
	public ?string $link_path = null;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $class_name    object|string
	 * @param $property_name string
	 * @param $display       string|null @values name, path,
	 * @throws ReflectionException
	 */
	public function __construct(
		object|string $class_name, string $property_name, string $display = null
	) {
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
	 * @noinspection PhpUnused select.html
	 * @return string @values auto-expand,
	 */
	public function autoExpand() : string
	{
		return (
			Component::of($this)?->value
			|| $this->getAnnotation('expand')->value
			|| Integrated_Annotation::of($this)->value
			|| Link_Annotation::of($this)->isCollection()
		)
			? 'auto-expand'
			: '';
	}

	//------------------------------------------------------------------------------------- classHtml
	/**
	 * @noinspection PhpUnused select.html
	 * @return string
	 */
	public function classHtml() : string
	{
		return $this->isExpandable() ? 'class' : '';
	}

	//----------------------------------------------------------------------------------- isBasicHtml
	/**
	 * Tells if the property type is a basic type or not, with an HTML result if yes
	 *
	 * @noinspection PhpUnused select.html
	 * @param $include_multiple_string boolean if false, string[] is not considered as a basic type
	 * @return ?string 'basic'
	 * @see Type::isBasic
	 */
	public function isBasicHtml(bool $include_multiple_string = true) : ?string
	{
		return $this->getType()->isBasic($include_multiple_string) ? 'basic' : null;
	}

	//---------------------------------------------------------------------------------- isExpandable
	/**
	 * Returns true if the property is expandable into the select properties tree
	 *
	 * Expandable properties :
	 * - have no #Store(false) : we do not know how to read these objects sub-properties data for lists
	 * - have not basic or stringable types
	 *
	 * TODO NORMAL should deal with #Store and stringable : we miss them
	 *
	 * @return string can be dealt-with as if it is a boolean @values expandable,
	 */
	public function isExpandable() : string
	{
		$type = $this->getType();
		if (static::$all_expandable) {
			return $type->isBasic() ? '' : 'expandable';
		}
		return (Store::of($this)->isString() || $type->isBasic() || $type->isStringable())
			? '' : 'expandable';
	}

	//------------------------------------------------------------------------------ isStringableHtml
	/**
	 * @noinspection PhpUnused select.html
	 * @return ?string 'stringable' if stringable
	 */
	public function isStringableHtml() : ?string
	{
		return $this->getType()->isStringable() ? 'stringable' : null;
	}

}
