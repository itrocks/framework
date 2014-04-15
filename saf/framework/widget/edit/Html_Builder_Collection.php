<?php
namespace SAF\Framework\Widget\Edit;

use SAF\Framework\Builder;
use SAF\Framework\Reflection\Reflection_Property;
use SAF\Framework\Reflection\Reflection_Property_View;
use SAF\Framework\View\Html\Builder\Collection;
use SAF\Framework\View\Html\Dom\Input;
use SAF\Framework\View\Html\Dom\Table\Body;
use SAF\Framework\View\Html\Dom\Table\Head;
use SAF\Framework\View\Html\Dom\Table\Header_Cell;
use SAF\Framework\View\Html\Dom\Table\Row;
use SAF\Framework\View\Html\Dom\Table\Standard_Cell;

/**
 * Takes a collection of objects and build a HTML edit subform containing their data
 */
class Html_Builder_Collection extends Collection
{

	//------------------------------------------------------------------------------------- $template
	/**
	 * @var Html_Template
	 */
	private $template = null;

	//------------------------------------------------------------------------------------- buildBody
	/**
	 * @return Body
	 */
	protected function buildBody()
	{
		$body = parent::buildBody();
		$row = $this->buildRow(Builder::create($this->class_name));
		$row->addClass('new');
		$body->addRow($row);
		return $body;
	}

	//------------------------------------------------------------------------------------- buildCell
	/**
	 * @param $object   object
	 * @param $property Reflection_Property
	 * @return Standard_Cell
	 */
	protected function buildCell($object, Reflection_Property $property)
	{
		if (!isset($this->template)) {
			$this->template = new Html_Template();
		}
		$type = $property->getType();
		$value = (
			$type->isBoolean()
			|| ($type->isString() && $property->getListAnnotation('values')->values())
		)
			? $property->getValue($object)
			: (new Reflection_Property_View($property))->getFormattedValue($object);
		$builder = (new Html_Builder_Property($property, $value, $this->property->name . '[]'));
		$input = $builder->setTemplate($this->template)->build();
		if ($property->name == reset($this->properties)->name) {
			$property_builder = new Html_Builder_Property();
			$property_builder->setTemplate($this->template);
			$id_input = new Input(
				$this->property->name . '[id]['
				. $property_builder->nextCounter($this->property->name . '[id][]')
				. ']',
				isset($object->id) ? $object->id : null
			);
			$id_input->setAttribute('type', 'hidden');
			$input = $id_input . $input;
		}
		return new Standard_Cell($input);
	}

	//------------------------------------------------------------------------------------- buildHead
	/**
	 * @return Head
	 */
	protected function buildHead()
	{
		$head = parent::buildHead();
		foreach ($head->rows as $row) {
			$row->addCell(new Header_Cell());
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
		$cell = new Standard_Cell('-');
		$cell->setAttribute('title', '|remove line|');
		$cell->addClass('minus');
		$row->addCell($cell);
		return $row;
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
