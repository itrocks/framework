<?php
namespace ITRocks\Framework\Feature\Edit;

use ITRocks\Framework\Builder;
use ITRocks\Framework\Reflection\Annotation\Property\Filters_Annotation;
use ITRocks\Framework\Reflection\Annotation\Property\User_Annotation;
use ITRocks\Framework\Reflection\Reflection_Property;
use ITRocks\Framework\Reflection\Type;
use ITRocks\Framework\Tools\Namespaces;
use ITRocks\Framework\Tools\String_Class;
use ITRocks\Framework\View\Html\Builder\Map;
use ITRocks\Framework\View\Html\Dom\Button;
use ITRocks\Framework\View\Html\Dom\Div;
use ITRocks\Framework\View\Html\Dom\List_\Item;

/**
 * Takes a map of objects and build a HTML edit subform containing their data
 */
class Html_Builder_Map extends Map
{

	//--------------------------------------------------------------------------------------- $no_add
	/**
	 * Property read only cache. Do not use this property : use noAdd() instead.
	 *
	 * @var boolean
	 */
	private $no_add;

	//------------------------------------------------------------------------------------ $no_delete
	/**
	 * Property read only cache. Do not use this property : use noDelete() instead.
	 *
	 * @var boolean
	 */
	private $no_delete;

	//------------------------------------------------------------------------------------- $pre_path
	/**
	 * Property name prefix
	 *
	 * @var string
	 */
	public string $pre_path;

	//------------------------------------------------------------------------------------ $read_only
	/**
	 * Property read only cache. Do not use this property : use readOnly() instead.
	 *
	 * @var boolean
	 */
	private $read_only;

	//------------------------------------------------------------------------------------- $template
	/**
	 * @var Html_Template
	 */
	private $template = null;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $property Reflection_Property
	 * @param $map      object[]
	 * @param $pre_path string
	 */
	public function __construct(Reflection_Property $property, array $map, string $pre_path = '')
	{
		parent::__construct($property, $map);
		$this->pre_path = $pre_path;
	}

	//------------------------------------------------------------------------------------- buildBody
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @return Item[]
	 */
	protected function buildBody()
	{
		$body = parent::buildBody();
		if (!$this->readOnly() && !$this->noAdd()) {
			$is_abstract = (new Type($this->class_name))->isAbstractClass();
			/** @noinspection PhpUnhandledExceptionInspection class name must be valid */
			$object = $is_abstract ? new String_Class : Builder::create($this->class_name);
			$row    = $this->buildCell($object);
			$row->addClass('new');
			$body[] = $row;
		}
		return $body;
	}

	//------------------------------------------------------------------------------------- buildCell
	/**
	 * @param $object object
	 * @return Item
	 */
	protected function buildCell($object)
	{
		$property  = $this->property;
		$pre_path  = $this->pre_path ?: $property->name;
		$value     = $object;

		$builder = new Html_Builder_Type('', $property->getType()->getElementType(), $value, $pre_path);
		$builder->is_abstract = $this->is_abstract;
		$builder->readonly    = $this->readOnly();
		$builder->setTemplate($this->template);

		$filters = Filters_Annotation::of($this->property)->parse($object);
		$builder->parent_level_filters = boolval($filters);
		$input = new Div($filters ? $builder->buildObject($filters) : $builder->build());
		if (!$this->readOnly() && !$this->noDelete()) {
			$minus = new Button('-');
			$minus->addClass('minus');
			$minus->setAttribute('tabindex', -1);
			$cell = new Item($input . $minus);
		}
		else {
			$cell = $input;
		}
		$type  = $property->getType();
		$cell->addClass(strtolower(Namespaces::shortClassName($type->asString())));
		return $cell;
	}

	//----------------------------------------------------------------------------------------- noAdd
	/**
	 * @return boolean
	 */
	protected function noAdd()
	{
		if (!isset($this->no_add)) {
			$user_annotation = $this->property->getListAnnotation(User_Annotation::ANNOTATION);
			$this->no_add    = $user_annotation->has(User_Annotation::NO_ADD);
		}
		return $this->no_add;
	}

	//-------------------------------------------------------------------------------------- noDelete
	/**
	 * @return boolean
	 */
	protected function noDelete()
	{
		if (!isset($this->no_delete)) {
			$user_annotation = $this->property->getListAnnotation(User_Annotation::ANNOTATION);
			$this->no_delete = $user_annotation->has(User_Annotation::NO_DELETE);
		}
		return $this->no_delete;
	}

	//-------------------------------------------------------------------------------------- readOnly
	/**
	 * @return boolean
	 */
	protected function readOnly()
	{
		if (!isset($this->read_only)) {
			$user_annotation = $this->property->getListAnnotation(User_Annotation::ANNOTATION);
			$this->read_only = $user_annotation->has(User_Annotation::READONLY);
		}
		return $this->read_only;
	}

	//----------------------------------------------------------------------------------- setTemplate
	/**
	 * @param $template Html_Template
	 * @return Html_Builder_Map
	 */
	public function setTemplate(Html_Template $template)
	{
		$this->template = $template;
		return $this;
	}

}
