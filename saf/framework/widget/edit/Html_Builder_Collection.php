<?php
namespace SAF\Framework\Widget\Edit;

use SAF\Framework\Builder;
use SAF\Framework\Reflection\Annotation\Property\User_Annotation;
use SAF\Framework\Reflection\Reflection_Class;
use SAF\Framework\Reflection\Reflection_Property;
use SAF\Framework\View\Html\Builder\Collection;
use SAF\Framework\View\Html\Dom\Input;
use SAF\Framework\View\Html\Dom\Table\Body;
use SAF\Framework\View\Html\Dom\Table\Head;
use SAF\Framework\View\Html\Dom\Table\Header_Cell;
use SAF\Framework\View\Html\Dom\Table\Row;
use SAF\Framework\View\Html\Dom\Table\Standard_Cell;
use SAF\Framework\View\Html\Dom\Table;

/**
 * Takes a collection of objects and build a HTML edit subform containing their data
 */
class Html_Builder_Collection extends Collection
{

	//-------------------------------------------------------------------------------------- $preprop
	/**
	 * @var string
	 */
	public $preprop = null;

	//------------------------------------------------------------------------------------- $template
	/**
	 * @var Html_Template
	 */
	private $template = null;

	//----------------------------------------------------------------------------------------- build
	/**
	 * TODO remove this patch will crash AOP because AOP on parent method does not work
	 * + AOP should create a build_() method that calls parent::build()
	 * + AOP should complete parameters like Table to give full path as they may not be in use clause
	 *
	 * @return Table
	 */
	public function build()
	{
		return parent::build();
	}

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
		$value = $property->getValue($object);
		$preprop = $this->preprop
			? ($this->preprop . '[' . $this->property->name . ']')
			: $this->property->name;
		$builder = (new Html_Builder_Property($property, $value, $preprop . '[]'));
		$input = $builder->setTemplate($this->template)->build();
		if (
			($property->name == reset($this->properties)->name)
			&& !(new Reflection_Class($this->class_name))->getAnnotation('link')->value
		) {
			$property_builder = new Html_Builder_Property();
			$property_builder->setTemplate($this->template);
			$id_input = new Input(
				$preprop . '[id][' . $property_builder->template->nextCounter($preprop . '[id][]') . ']',
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
		/** @var $user_annotation User_Annotation */
		$user_annotation = $this->property->getAnnotation(User_Annotation::ANNOTATION);
		if (!$user_annotation->has(User_Annotation::READONLY)) {
			$cell = new Standard_Cell('-');
			$cell->setAttribute('title', '|remove line|');
			$cell->addClass('minus');
			$row->addCell($cell);
		}
		return $row;
	}

	//--------------------------------------------------------------------------------- getProperties
	/**
	 * @return Reflection_Property[]
	 */
	public function getProperties()
	{
		$properties = parent::getProperties();
		/** @var $user_annotation User_Annotation */
		$user_annotation = $this->property->getAnnotation(User_Annotation::ANNOTATION);
		if ($user_annotation->has(User_Annotation::READONLY)) {
			foreach ($properties as $property) {
				$user_annotation = $property->getAnnotation(User_Annotation::ANNOTATION);
				$user_annotation->add(User_Annotation::READONLY);
			}
		}
		return $properties;
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
