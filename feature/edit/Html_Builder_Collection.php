<?php
namespace ITRocks\Framework\Feature\Edit;

use ITRocks\Framework\Builder;
use ITRocks\Framework\Controller\Feature;
use ITRocks\Framework\Locale\Loc;
use ITRocks\Framework\Reflection\Annotation\Class_\Link_Annotation;
use ITRocks\Framework\Reflection\Annotation\Property\Alias_Annotation;
use ITRocks\Framework\Reflection\Annotation\Property\Tooltip_Annotation;
use ITRocks\Framework\Reflection\Annotation\Property\User_Annotation;
use ITRocks\Framework\Reflection\Annotation\Property\Widget_Annotation;
use ITRocks\Framework\Reflection\Annotation\Template\List_Annotation;
use ITRocks\Framework\Reflection\Annotation\Template\Method_Target_Annotation;
use ITRocks\Framework\Reflection\Reflection_Class;
use ITRocks\Framework\Reflection\Reflection_Property;
use ITRocks\Framework\Reflection\Reflection_Property_Value;
use ITRocks\Framework\Tools\Names;
use ITRocks\Framework\Tools\Namespaces;
use ITRocks\Framework\View\Html\Builder\Collection;
use ITRocks\Framework\View\Html\Builder\Property;
use ITRocks\Framework\View\Html\Builder\Value_Widget;
use ITRocks\Framework\View\Html\Dom\Input;
use ITRocks\Framework\View\Html\Dom\List_;
use ITRocks\Framework\View\Html\Dom\List_\Item;
use ITRocks\Framework\View\Html\Dom\List_\Ordered;
use ITRocks\Framework\View\Html\Dom\List_\Unordered;

/**
 * Takes a collection of objects and build a HTML edit sub-form containing their data
 *
 * @override template @var Html_Template
 * @property Html_Template template
 */
class Html_Builder_Collection extends Collection
{

	//---------------------------------------------------------------------------------- $create_only
	/**
	 * Property create only
	 *
	 * @var boolean
	 */
	protected $create_only;

	//--------------------------------------------------------------------------------------- $no_add
	/**
	 * Property no add cache. Do not use this property : use noAdd() instead
	 *
	 * @var boolean
	 */
	protected $no_add;

	//------------------------------------------------------------------------------------ $no_delete
	/**
	 * Property no delete cache. Do not use this property : use noDelete() instead
	 *
	 * @var boolean
	 */
	protected $no_delete;

	//------------------------------------------------------------------------------------- $pre_path
	/**
	 * @var string
	 */
	public $pre_path;

	//------------------------------------------------------------------------------------ $read_only
	/**
	 * Property read only cache. Do not use this property : use readOnly() instead
	 *
	 * @var boolean
	 */
	protected $read_only;

	//----------------------------------------------------------------------------- $user_annotations
	/**
	 * Contains all read annotations
	 *
	 * @var List_Annotation
	 */
	protected $user_annotations;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $property   Reflection_Property
	 * @param $collection array
	 * @param $pre_path   string
	 */
	public function __construct(Reflection_Property $property, array $collection, $pre_path = null)
	{
		parent::__construct($property, $collection);
		$this->pre_path = $pre_path;
	}

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

		if ($this->readOnly() || $this->noAdd()) {
			$table->setData('no-add', true);
		}

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
	 * @return Item[]|List_[][]
	 */
	protected function buildBody()
	{
		$body = parent::buildBody();
		/** @noinspection PhpUnhandledExceptionInspection class name must be valid */
		$row = new Item($this->buildRow(Builder::create($this->class_name)));
		$row->addClass('new');
		$body[] = $row;
		return $body;
	}

	//------------------------------------------------------------------------------------- buildCell
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $object        object
	 * @param $property      Reflection_Property
	 * @param $property_path string
	 * @return Item
	 */
	protected function buildCell($object, Reflection_Property $property, $property_path = null)
	{
		if (!isset($this->template)) {
			$this->template = new Html_Template();
		}
		/** @noinspection PhpUnhandledExceptionInspection valid $object-$property couple */
		$property_value = strpos($property_path, DOT)
			? new Reflection_Property_Value($object, $property_path, $object)
			: null;
		/** @noinspection PhpUnhandledExceptionInspection valid $object-$property couple */
		$value = ($property_value ?: $property)->getValue($object);
		if (strpos($this->pre_path, '[]')) {
			$property_builder = new Html_Builder_Property();
			$property_builder->setTemplate($this->template);
			$pre_path_to_count = lParse($this->pre_path, '[]');
			$counter  = $property_builder->template->nextCounter($pre_path_to_count . '[id][]', false);
			$pre_path = $pre_path_to_count . '[' . $this->property->name . '][' . $counter . ']';
		}
		else {
			$pre_path = $this->pre_path
				? ($this->pre_path . '[' . $this->property->name . ']')
				: $this->property->name;
		}
		if (
			($builder = Widget_Annotation::of($property)->value)
			&& is_a($builder, Property::class, true)
		) {
			if (!$property_value) {
				/** @noinspection PhpUnhandledExceptionInspection from valid property */
				$property_value = new Reflection_Property_Value($object, $property_path, $object);
			}
			array_push($this->template->properties_prefix, $pre_path);
			/** @noinspection PhpUnhandledExceptionInspection $builder and $property are valid */
			/** @var $builder Property */
			$builder = Builder::create($builder, [$property_value, $value, $this->template]);
			$builder->parameters[Feature::F_EDIT] = Feature::F_EDIT;
			$builder->pre_path                    = $pre_path . '[]';
			$value = $builder->buildHtml();
			if ($builder instanceof Value_Widget) {
				$value = (new Html_Builder_Property($property_value, $value, $pre_path . '[]'))
					->setTemplate($this->template)
					->build();
			}
			array_pop($this->template->properties_prefix);
			$content = $value;
		}
		else {
			$builder         = (new Html_Builder_Property($property, $value, $pre_path . '[]'));
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
					$pre_path . '[id][' . $property_builder->template->nextCounter($pre_path . '[id][]') . ']',
					isset($object->id) ? $object->id : null
				);
				$id_input->setAttribute('type', 'hidden');
				$property_builder->readonly = $this->readOnly();
				$property_builder->setInputAsReadOnly($id_input);
				$input = $id_input . $input;
			}
			$content = $input;
		}
		$cell = new Item($content);
		$type = $property->getType();
		$cell->addClass(strtolower(Namespaces::shortClassName($type->asString())));
		if(!$property->isVisible()){
			$cell->addClass('hidden');
			$cell->setStyle('display', 'none');
		}
		$cell->setData('property', $property->path);
		$cell->setData(
			'title',
			Loc::tr(
				Names::propertyToDisplay(Alias_Annotation::of($property)->value),
				$this->class_name
			)
		);
		return $cell;
	}

	//----------------------------------------------------------------------------------- buildHeader
	/**
	 * @return Ordered
	 */
	protected function buildHeader()
	{
		$header = parent::buildHeader();
		$header->addItem(new Item());
		if ($tooltip = Tooltip_Annotation::of($this->property)->callProperty($this->property)) {
			$header->setAttribute('title', $tooltip);
		}
		return $header;
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

}
