<?php
namespace ITRocks\Framework\Feature\Edit;

use ITRocks\Framework\Builder;
use ITRocks\Framework\Controller\Feature;
use ITRocks\Framework\Locale\Loc;
use ITRocks\Framework\Mapper\Component;
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

	//------------------------------------------------------------------------------- HIDE_EMPTY_TEST
	const HIDE_EMPTY_TEST = false;

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
	public string $pre_path;

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
	 * @param $property        Reflection_Property
	 * @param $collection      array
	 * @param $link_properties boolean
	 * @param $pre_path        string
	 */
	public function __construct(
		Reflection_Property $property, array $collection, bool $link_properties = false,
		string $pre_path = ''
	) {
		parent::__construct($property, $collection, $link_properties);
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
		$add_row = Builder::create($this->class_name);
		if (($this->property instanceof Reflection_Property_Value) && isA($add_row, Component::class)) {
			/** @var $add_row Component */
			$property = $this->property->getParentProperty();
			$add_row->setComposite($property ? $property->value() : $this->property->getObject());
		}
		$row = new Item($this->buildRow($add_row));
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
		$property_value = new Reflection_Property_Value($object, $property_path, $object, false, true);
		$value = $property_value->value();
		if (str_contains($this->pre_path, '[]')) {
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
			$builder         = (new Html_Builder_Property($property_value, $value, $pre_path . '[]'));
			$builder->object = $object;
			$input           = $builder->setTemplate($this->template)->build();
			if ($property->name === reset($this->properties)->name) {
				$property_builder = new Html_Builder_Property();
				$property_builder->setTemplate($this->template);
				$next_counter = $property_builder->template->nextCounter($pre_path . '[id][]');
				/** @noinspection PhpUnhandledExceptionInspection $this->class_name must be valid */
				if (!Link_Annotation::of(new Reflection_Class($this->class_name))->value) {
					$id_input = new Input(
						$pre_path . '[id][' . $next_counter . ']',
						isset($object->id) ? $object->id : null
					);
					$id_input->setAttribute('type', 'hidden');
					$property_builder->readonly = $this->readOnly();
					$property_builder->setInputAsReadOnly($id_input);
					$input = $id_input . $input;
				}
			}
			$content = $input;
		}
		$cell = new Item($content);
		$type = $property->getType();
		$cell->addClass(strtolower(Namespaces::shortClassName($type->asString())));
		if(!$property->isVisible(static::HIDE_EMPTY_TEST)){
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
		if ($component_object_html = $property->isComponentObjectHtml()) {
			$cell->addClass($component_object_html);
		}
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

	//--------------------------------------------------------------------------------- getProperties
	/**
	 * @param $link_properties boolean
	 * @return Reflection_Property[]
	 */
	public function getProperties($link_properties)
	{
		$properties = parent::getProperties($link_properties);
		if ($this->readOnly()) {
			foreach ($properties as $property) {
				$user_annotation = $property->getListAnnotation(User_Annotation::ANNOTATION);
				if ($this->read_only) {
					$user_annotation->add(User_Annotation::READONLY);
					$user_annotation->add(User_Annotation::TOOLTIP);
				}
			}
		}
		return $properties;
	}

	//----------------------------------------------------------------------------- getUserAnnotation
	/**
	 * Read @user annotations this->property
	 *
	 * @return User_Annotation
	 */
	private function getUserAnnotation()
	{
		if (!$this->user_annotations) {
			$this->user_annotations = User_Annotation::of($this->property);
		}
		return $this->user_annotations;
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
			$this->no_add = $this->getUserAnnotation()->has(User_Annotation::NO_ADD);
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
			$this->no_delete = $this->getUserAnnotation()->has(User_Annotation::NO_DELETE);
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
			$this->read_only = $this->getUserAnnotation()->has(User_Annotation::READONLY);
		}
		return $this->read_only;
	}

}
