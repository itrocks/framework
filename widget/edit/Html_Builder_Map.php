<?php
namespace ITRocks\Framework\Widget\Edit;

use ITRocks\Framework\Builder;
use ITRocks\Framework\Reflection\Annotation\Property\User_Annotation;
use ITRocks\Framework\Reflection\Reflection_Property;
use ITRocks\Framework\View\Html\Builder\Map;
use ITRocks\Framework\View\Html\Dom\Table\Body;
use ITRocks\Framework\View\Html\Dom\Table\Row;
use ITRocks\Framework\View\Html\Dom\Table\Standard_Cell;

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

	//------------------------------------------------------------------------------------- buildBody
	/**
	 * @return Body
	 */
	protected function buildBody()
	{
		$body = parent::buildBody();
		if (!$this->readOnly() && !$this->noAdd()) {
			$row = $this->buildRow(Builder::create($this->class_name));
			$row->addClass('new');
			$body->addRow($row);
		}
		return $body;
	}

	//------------------------------------------------------------------------------------- buildCell
	/**
	 * @param $object object
	 * @return Standard_Cell
	 */
	protected function buildCell($object)
	{
		$property = $this->property;
		$value = $object;
		$preprop = $this->preprop ?: $property->name;
		$builder = new Html_Builder_Type('', $property->getType()->getElementType(), $value, $preprop);
		$builder->readonly = $this->readOnly();
		$input = $builder->setTemplate($this->template)->build();
		return new Standard_Cell($input);
	}

	//------------------------------------------------------------------------------------- buildHead
	/**
	 * @return string
	 */
	protected function buildHead()
	{
		$head = parent::buildHead();
		foreach ($head->rows as $row) {
			$row->addCell(new Standard_Cell(''));
		}
		return $head;
	}

	//-------------------------------------------------------------------------------------- buildRow
	/**
	 * @param $object object
	 * @return Row
	 */
	protected function buildRow($object)
	{
		$row = parent::buildRow($object);
		if (!$this->readOnly() && !$this->noDelete()) {
			$cell = new Standard_Cell('-');
			$cell->setAttribute('title', '|remove line|');
			$cell->addClass('minus');
			$row->addCell($cell);
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
			$this->no_add = $user_annotation->has(User_Annotation::NO_ADD);
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
