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
		if (!$this->readOnly()) {
			$row = $this->buildRow(Builder::create($this->class_name));
			$row->addClass('new');
			$body->addRow($row);
		}
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
		if (strpos($this->preprop, '[]')) {
			$property_builder = new Html_Builder_Property();
			$property_builder->setTemplate($this->template);
			$preprop_to_count = lParse($this->preprop, '[]');
			$counter = $property_builder->template->nextCounter($preprop_to_count . '[id][]', false);
			$preprop = $preprop_to_count . '[' . $this->property->name . '][' . $counter . ']';
		}
		else {
			$preprop = $this->preprop
				? ($this->preprop . '[' . $this->property->name . ']')
				: $this->property->name;
		}
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
		if (!$this->readOnly()) {
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
		if ($this->readOnly()) {
			foreach ($properties as $property) {
				/** @var $user_annotation User_Annotation */
				$user_annotation = $property->getAnnotation(User_Annotation::ANNOTATION);
				$user_annotation->add(User_Annotation::READONLY);
			}
		}
		return $properties;
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
