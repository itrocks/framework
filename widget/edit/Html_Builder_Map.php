<?php
namespace SAF\Framework\Widget\Edit;

use SAF\Framework\Builder;
use SAF\Framework\Reflection\Annotation\Property\User_Annotation;
use SAF\Framework\Reflection\Reflection_Property;
use SAF\Framework\View\Html\Builder\Map;
use SAF\Framework\View\Html\Dom\Table\Body;
use SAF\Framework\View\Html\Dom\Table\Row;
use SAF\Framework\View\Html\Dom\Table\Standard_Cell;

/**
 * Takes a map of objects and build a HTML edit subform containing their data
 */
class Html_Builder_Map extends Map
{

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

	//---------------------------------------------------------------------------------------- $noAdd
	/**
	 * Property read only cache. Do not use this property : use noAdd() instead.
	 *
	 * @var boolean
	 */
	private $noAdd;

	//------------------------------------------------------------------------------------- $noDelete
	/**
	 * Property read only cache. Do not use this property : use noDelete() instead.
	 *
	 * @var boolean
	 */
	private $noDelete;

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
	public function __construct(Reflection_Property $property, $map, $preprop = null)
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

	//-------------------------------------------------------------------------------------- readOnly
	/**
	 * @return boolean
	 */
	protected function readOnly()
	{
		if (!isset($this->read_only)) {
			/** @var $user_annotation User_Annotation */
			$user_annotation = $this->property->getAnnotation(User_Annotation::ANNOTATION);
			$this->read_only = $user_annotation->has(User_Annotation::READONLY);
		}
		return $this->read_only;
	}

	//----------------------------------------------------------------------------------------- noAdd
	/**
	 * @return boolean
	 */
	protected function noAdd()
	{
		if (!isset($this->noAdd)) {
			$user_annotation = $this->property->getListAnnotation(User_Annotation::ANNOTATION);
			$this->noAdd = $user_annotation->has(User_Annotation::NO_ADD);
		}
		return $this->noAdd;
	}

	//----------------------------------------------------------------------------------------- noAdd
	/**
	 * @return boolean
	 */
	protected function noDelete()
	{
		if (!isset($this->noDelete)) {
			$user_annotation = $this->property->getListAnnotation(User_Annotation::ANNOTATION);
			$this->noDelete = $user_annotation->has(User_Annotation::NO_DELETE);
		}
		return $this->noDelete;
	}

	//----------------------------------------------------------------------------------- setTemplate
	/**
	 * @param $template Html_Template
	 * @return Html_Builder_Type
	 */
	public function setTemplate(Html_Template $template)
	{
		$this->template = $template;
		return $this;
	}

}
