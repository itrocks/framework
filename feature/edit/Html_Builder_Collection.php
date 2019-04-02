<?php
namespace ITRocks\Framework\Feature\Edit;

use ITRocks\Framework\Builder;
use ITRocks\Framework\Locale\Loc;
use ITRocks\Framework\Reflection\Annotation\Class_\Link_Annotation;
use ITRocks\Framework\Reflection\Annotation\Property\Alias_Annotation;
use ITRocks\Framework\Reflection\Annotation\Property\Tooltip_Annotation;
use ITRocks\Framework\Reflection\Annotation\Property\User_Annotation;
use ITRocks\Framework\Reflection\Annotation\Template\List_Annotation;
use ITRocks\Framework\Reflection\Annotation\Template\Method_Target_Annotation;
use ITRocks\Framework\Reflection\Reflection_Class;
use ITRocks\Framework\Reflection\Reflection_Property;
use ITRocks\Framework\Reflection\Reflection_Property_Value;
use ITRocks\Framework\Tools\Names;
use ITRocks\Framework\Tools\Namespaces;
use ITRocks\Framework\View\Html\Builder\Collection;
use ITRocks\Framework\View\Html\Dom\Input;
use ITRocks\Framework\View\Html\Dom\List_\Item;
use ITRocks\Framework\View\Html\Dom\List_\Ordered;
use ITRocks\Framework\View\Html\Dom\List_\Unordered;

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
	 * @return Unordered
	 */
	public function build()
	{
		$table = parent::build();

		/** @var $user_remove_annotations Method_Target_Annotation[] */
		$property_class          = $this->property->getType()->asReflectionClass();
		$user_remove_annotations = $property_class->getAnnotations('user_remove');
		$user_removes            = [];
		foreach ($user_remove_annotations as $user_remove_annotation) {
			$user_removes[] = $user_remove_annotation->asHtmlData(
				($this->property instanceof Reflection_Property_Value) ? $this->property->getObject() : null
			);
		}
		if ($user_removes) {
			$table->setData('on-remove', join(',', $user_removes));
		}

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
			/** @noinspection PhpUnhandledExceptionInspection class name must be valid */
			$row = new Item($this->buildRow(Builder::create($this->class_name)));
			$row->addClass('new');
			$body[] = $row;
		}
		return $body;
	}

	//------------------------------------------------------------------------------------- buildCell
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $object   object
	 * @param $property Reflection_Property
	 * @return Item
	 */
	protected function buildCell($object, Reflection_Property $property)
	{
		if (!isset($this->template)) {
			$this->template = new Html_Template();
		}
		/** @noinspection PhpUnhandledExceptionInspection property must be from object and accessible */
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
		$builder         = (new Html_Builder_Property($property, $value, $preprop . '[]'));
		$builder->object = $object;
		$input           = $builder->setTemplate($this->template)->build();
		/** @noinspection PhpUnhandledExceptionInspection $this->class_name must be valid */
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
			$property_builder->readonly = $this->readOnly();
			$property_builder->setInputAsReadOnly($id_input);
			$input = $id_input . $input;
		}
		$cell = new Item($input);
		$type = $property->getType();
		$cell->addClass(strtolower(Namespaces::shortClassName($type->asString())));
		if ($class = $type->isClassHtml()) {
			$cell->addClass($class);
		}
		if(!$property->isVisible()){
			$cell->addClass('hidden');
			$cell->setStyle('display', 'none');
		}
		$cell->setData(
			'name',
			Loc::tr(
				Names::propertyToDisplay(Alias_Annotation::of($property)->value),
				$this->class_name
			)
		);
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

	//----------------------------------------------------------------------------- isPropertyVisible
	/**
	 * @param $property Reflection_Property
	 * @return boolean
	 */
	protected function isPropertyVisible(Reflection_Property $property)
	{
		$user_annotation = $property->getListAnnotation(User_Annotation::ANNOTATION);
		return !$user_annotation->has(User_Annotation::HIDE_EDIT)
			&& !$user_annotation->has(User_Annotation::INVISIBLE)
			&& !$user_annotation->has(User_Annotation::INVISIBLE_EDIT);
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
