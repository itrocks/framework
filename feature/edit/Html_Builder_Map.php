<?php
namespace ITRocks\Framework\Feature\Edit;

use ITRocks\Framework\Builder;
use ITRocks\Framework\Reflection\Annotation\Property\Tooltip_Annotation;
use ITRocks\Framework\Reflection\Annotation\Property\User_Annotation;
use ITRocks\Framework\Reflection\Reflection_Property;
use ITRocks\Framework\Reflection\Type;
use ITRocks\Framework\Tools\Namespaces;
use ITRocks\Framework\Tools\String_Class;
use ITRocks\Framework\View\Html\Builder\Map;
use ITRocks\Framework\View\Html\Dom\List_\Item;
use ITRocks\Framework\View\Html\Dom\List_\Ordered;
use ITRocks\Framework\View\Html\Dom\List_\Unordered;

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

	//-------------------------------------------------------------------------------------- $preprop
	/**
	 * Property name prefix
	 *
	 * @var string
	 */
	public $preprop;

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
	 * @param $preprop  string
	 */
	public function __construct(Reflection_Property $property, array $map, $preprop = null)
	{
		parent::__construct($property, $map);
		$this->preprop = $preprop;
	}

	//----------------------------------------------------------------------------------------- build
	/**
	 * @return Unordered
	 */
	public function build()
	{
		$table = parent::build();
		return $table;
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
			$row    = $this->buildRow($object);
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
		$property = $this->property;
		$value    = $object;
		$preprop  = $this->preprop ?: $property->name;

		$builder = new Html_Builder_Type('', $property->getType()->getElementType(), $value, $preprop);
		$builder->is_abstract = $this->is_abstract;
		$builder->readonly    = $this->readOnly();

		$input = $builder->setTemplate($this->template)->build();
		$cell  = new Item($input);
		$type  = $property->getType();
		$cell->addClass(strtolower(Namespaces::shortClassName($type->asString())));
		if ($class = $type->isClassHtml()) {
			$cell->addClass($class);
		}
		return $cell;
	}

	//------------------------------------------------------------------------------------- buildHead
	/**
	 * @return Ordered
	 */
	protected function buildHead()
	{
		$head = parent::buildHead();
		$head->addItem(new Item());
		if ($tooltip = Tooltip_Annotation::of($this->property)->callProperty($this->property)) {
			$head->setAttribute('title', $tooltip);
		}
		return $head;
	}

	//-------------------------------------------------------------------------------------- buildRow
	/**
	 * @param $object object
	 * @return Ordered
	 */
	protected function buildRow($object)
	{
		$row = parent::buildRow($object);
		if (!$this->readOnly() && !$this->noDelete()) {
			$cell = new Item('-');
			$cell->setAttribute('title', '|remove line|');
			$cell->addClass('minus');
			$row->addItem($cell);
		}
		return $row;
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
