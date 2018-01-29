<?php
namespace ITRocks\Framework\Widget\Edit;

use ITRocks\Framework\Builder;
use ITRocks\Framework\Reflection\Annotation\Class_\Link_Annotation;
use ITRocks\Framework\Reflection\Annotation\Property\Tooltip_Annotation;
use ITRocks\Framework\Reflection\Annotation\Property\User_Annotation;
use ITRocks\Framework\Reflection\Annotation\Template\List_Annotation;
use ITRocks\Framework\Reflection\Reflection_Class;
use ITRocks\Framework\Reflection\Reflection_Property;
use ITRocks\Framework\Tools\Namespaces;
use ITRocks\Framework\View\Html\Builder\Collection;
use ITRocks\Framework\View\Html\Dom\Input;
use ITRocks\Framework\View\Html\Dom\Table;
use ITRocks\Framework\View\Html\Dom\Table\Body;
use ITRocks\Framework\View\Html\Dom\Table\Head;
use ITRocks\Framework\View\Html\Dom\Table\Header_Cell;
use ITRocks\Framework\View\Html\Dom\Table\Row;
use ITRocks\Framework\View\Html\Dom\Table\Standard_Cell;

/**
 * Takes a collection of objects and build a HTML edit sub-form containing their data
 */
class Html_Builder_Collection extends Collection
{

	//---------------------------------------------------------------------------------- $create_only
	/**
	 * Property create only
	 *
	 * @var boolean
	 */
	private $create_only;

	//--------------------------------------------------------------------------------------- $no_add
	/**
	 * Property no add cache. Do not use this property : use noAdd() instead
	 *
	 * @var boolean
	 */
	private $no_add;

	//------------------------------------------------------------------------------------ $no_delete
	/**
	 * Property no delete cache. Do not use this property : use noDelete() instead
	 *
	 * @var boolean
	 */
	private $no_delete;

	//-------------------------------------------------------------------------------------- $preprop
	/**
	 * @var string
	 */
	public $preprop = null;

	//------------------------------------------------------------------------------------ $read_only
	/**
	 * Property read only cache. Do not use this property : use readOnly() instead
	 *
	 * @var boolean
	 */
	private $read_only;

	//------------------------------------------------------------------------------------- $template
	/**
	 * @var Html_Template
	 */
	private $template = null;

	//----------------------------------------------------------------------------- $user_annotations
	/**
	 * Contains all read annotations
	 *
	 * @var List_Annotation
	 */
	private $user_annotations;

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
		if (!$this->readOnly() && !$this->noAdd()) {
			$row = $this->buildRow(Builder::create($this->class_name));
			$row->addClass('new');
			$body->addRow($row);
		}
		if ($tooltip = Tooltip_Annotation::of($this->property)->callProperty($this->property)) {
			$body->setAttribute('title', $tooltip);
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
		$input   = $builder->setTemplate($this->template)->build();
		if (
			($property->name === reset($this->properties)->name)
			&& !Link_Annotation::of(new Reflection_Class($this->class_name))->value
		) {
			$property_builder = new Html_Builder_Property();
			$property_builder->setTemplate($this->template);
			$id_input = new Input(
				$preprop . '[id][' . $property_builder->template->nextCounter($preprop . '[id][]') . ']',
				isset($object->id) ? $object->id : null
			);
			$id_input->setAttribute('type', 'hidden');
			if ($this->readOnly()) {
				$property_builder->setInputAsReadOnly($id_input);
			}
			$input = $id_input . $input;
		}
		$cell = new Standard_Cell($input);
		$type = $property->getType();
		$cell->addClass(strtolower(Namespaces::shortClassName($type->asString())));
		if ($class = $type->isClassHtml()) {
			$cell->addClass($class);
		}
		return $cell;
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
		if (!$this->readOnly() && !$this->noDelete()) {
			$cell = new Standard_Cell('-');
			$cell->setAttribute('title', '|remove line|');
			$cell->addClass('minus');
			$row->addCell($cell);
		}
		return $row;
	}

	//------------------------------------------------------------------------------------ createOnly
	/**
	 * @return boolean
	 */
	protected function createOnly()
	{
		if (!isset($this->create_only)) {
			$this->create_only = $this->getAnnotations()->has(User_Annotation::CREATE_ONLY);
		}
		return $this->create_only;
	}

	//-------------------------------------------------------------------------------- getAnnotations
	/**
	 * Read all annotations of this->property
	 *
	 * @return List_Annotation
	 */
	private function getAnnotations()
	{
		if (!$this->user_annotations) {
			$this->user_annotations = $this->property->getListAnnotation(User_Annotation::ANNOTATION);
		}
		return $this->user_annotations;
	}

	//--------------------------------------------------------------------------------- getProperties
	/**
	 * @return Reflection_Property[]
	 */
	public function getProperties()
	{
		$properties = parent::getProperties();
		if ($this->readOnly() || $this->createOnly()) {
			foreach ($properties as $property) {
				$user_annotation = $property->getListAnnotation(User_Annotation::ANNOTATION);
				if ($this->read_only
					// If collection is set then ==> read only
					// TODO Is it the best condition to test ?
					|| ($this->create_only && $this->collection)
				) {
					$user_annotation->add(User_Annotation::READONLY);
					$user_annotation->add(User_Annotation::TOOLTIP);
				}
			}
		}
		return $properties;
	}

	//----------------------------------------------------------------------------------------- noAdd
	/**
	 * @return boolean
	 */
	protected function noAdd()
	{
		if (!isset($this->no_add)) {
			$this->no_add = $this->getAnnotations()->has(User_Annotation::NO_ADD);
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
			$this->no_delete = $this->getAnnotations()->has(User_Annotation::NO_DELETE);
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
			$this->read_only = $this->getAnnotations()->has(User_Annotation::READONLY);
		}
		return $this->read_only;
	}

	//----------------------------------------------------------------------------------- setTemplate
	/**
	 * @param $template Html_Template
	 * @return Html_Builder_Collection
	 */
	public function setTemplate(Html_Template $template)
	{
		$this->template = $template;
		return $this;
	}

}
