<?php
namespace ITRocks\Framework\Feature\Edit;

use ITRocks\Framework\Builder;
use ITRocks\Framework\Controller\Feature;
use ITRocks\Framework\Locale\Loc;
use ITRocks\Framework\Mapper\Component;
use ITRocks\Framework\Reflection\Annotation\Template\Method_Target_Annotation;
use ITRocks\Framework\Reflection\Attribute\Property\Alias;
use ITRocks\Framework\Reflection\Attribute\Property\Tooltip;
use ITRocks\Framework\Reflection\Attribute\Property\User;
use ITRocks\Framework\Reflection\Attribute\Property\Widget;
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
 * Takes a collection of objects and build an HTML edit sub-form containing their data
 *
 * @override template @var Html_Template
 * @property Html_Template template
 */
class Html_Builder_Collection extends Collection
{

	//------------------------------------------------------------------------------- HIDE_EMPTY_TEST
	const HIDE_EMPTY_TEST = false;

	//--------------------------------------------------------------------------------------- $no_add
	/** Property no add cache. Do not use this property : use noAdd() instead */
	protected bool $no_add;

	//------------------------------------------------------------------------------------ $no_delete
	/** Property no delete cache. Do not use this property : use noDelete() instead */
	protected bool $no_delete;

	//------------------------------------------------------------------------------------- $pre_path
	public string $pre_path;

	//------------------------------------------------------------------------------------ $read_only
	/** Property read only cache. Do not use this property : use readOnly() instead */
	protected bool $read_only;

	//------------------------------------------------------------------------------ $user_attributes
	/** Contains all read annotations */
	protected User $user_attributes;

	//----------------------------------------------------------------------------------- __construct
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
	 */
	public function build() : Unordered
	{
		if (!isset($this->template)) {
			$this->template = new Html_Template();
		}
		$table = parent::build();

		if ($this->readOnly() || $this->noAdd()) {
			$table->setData('no-add');
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
	protected function buildBody() : array
	{
		$body = parent::buildBody();
		if ($this->noAdd() || $this->readOnly()) {
			return $body;
		}
		/** @noinspection PhpUnhandledExceptionInspection class name must be valid */
		$add_row = Builder::create($this->class_name);
		if (isA($add_row, Component::class)) {
			/** @var $add_row Component */
			$class_name           = get_class($add_row);
			$composite_property   = $class_name::getCompositeProperty();
			$composite_class_name = $composite_property->getType()->asString();
			foreach ($this->template->objects as $object) {
				if (is_a($object, $composite_class_name)) {
					$add_row->setComposite($object);
					break;
				}
			}
			if ($this->property instanceof Reflection_Property_Value) {
				/** @var $add_row Component */
				$property = $this->property->getParentProperty();
				$add_row->setComposite($property ? $property->value() : $this->property->getObject());
			}
		}
		$row = new Item($this->buildRow($add_row));
		$row->addClass('new');
		$body[] = $row;
		return $body;
	}

	//------------------------------------------------------------------------------------- buildCell
	protected function buildCell(object $object, Reflection_Property $property, string $property_path)
		: Item
	{
		/** @noinspection PhpUnhandledExceptionInspection valid $object-$property couple */
		$property_value = new Reflection_Property_Value($object, $property_path, $object, false, true);
		$value          = $property_value->value();
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
		$content      = null;
		$origin_value = $value;
		if (
			($builder = Widget::of($property)?->class_name)
			&& is_a($builder, Property::class, true)
		) {
			$this->template->properties_prefix[] = $pre_path;
			/** @noinspection PhpParamsInspection $builder Inspector bug : $builder is a string */
			/** @noinspection PhpUnhandledExceptionInspection $builder and $property are valid */
			/** @var $builder Property */
			$builder                              = Builder::create($builder, [$property_value, $value, $this->template]);
			$builder->parameters[Feature::F_EDIT] = Feature::F_EDIT;
			if (property_exists($builder, 'pre_path')) {
				$builder->pre_path = $pre_path . '[]';
			}
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
			$value = Html_Template::ORIGIN;
		}
		if ($value === Html_Template::ORIGIN) {
			$value           = $origin_value;
			$builder         = (new Html_Builder_Property($property_value, $value, $pre_path . '[]'));
			$builder->object = $object;
			$input           = $builder->setTemplate($this->template)->build();
			if ($property->name === reset($this->properties)->name) {
				$property_builder = new Html_Builder_Property();
				$property_builder->setTemplate($this->template);
				$next_counter = $property_builder->template->nextCounter($pre_path . '[id][]');

				$id_input = new Input($pre_path . '[id][' . $next_counter . ']', $object->id ?? null);
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
		if (!$property->isVisible(static::HIDE_EMPTY_TEST)) {
			$cell->addClass('hidden');
			$cell->setStyle('display', 'none');
		}
		$cell->setData('property', $property->path);
		$cell->setData(
			'title', Loc::tr(Names::propertyToDisplay(Alias::of($property)->value), $this->class_name)
		);
		if ($component_object_html = $property->isComponentObjectHtml()) {
			$cell->addClass($component_object_html);
		}
		return $cell;
	}

	//----------------------------------------------------------------------------------- buildHeader
	protected function buildHeader() : Ordered
	{
		$header = parent::buildHeader();
		$header->addItem(new Item());
		if ($tooltip = Tooltip::of($this->property)?->callProperty($this->property)) {
			$header->setAttribute('title', $tooltip);
		}
		return $header;
	}

	//-------------------------------------------------------------------------------------- buildRow
	protected function buildRow(object $object) : Ordered
	{
		$row = parent::buildRow($object);
		if (!$this->readOnly() && !$this->noDelete()) {
			$cell = new Item('-');
			$cell->setAttribute('title', Loc::tr('remove line'));
			$cell->addClass('minus');
			$row->addItem($cell);
		}
		return $row;
	}

	//--------------------------------------------------------------------------------- getProperties
	/** @return Reflection_Property[] */
	public function getProperties(bool $link_properties) : array
	{
		$properties = parent::getProperties($link_properties);
		if ($this->readOnly()) {
			foreach ($properties as $property) {
				$user = User::of($property);
				if ($this->readOnly()) {
					// TODO Will crash if no #User
					$user->add(User::READONLY);
					$user->add(User::TOOLTIP);
				}
			}
		}
		return $properties;
	}

	//----------------------------------------------------------------------------- getUserAnnotation
	/** Read #User attributes of this->property */
	private function getUserAnnotation() : User
	{
		if (!isset($this->user_attributes)) {
			$this->user_attributes = User::of($this->property);
		}
		return $this->user_attributes;
	}

	//----------------------------------------------------------------------------- isPropertyVisible
	protected function isPropertyVisible(Reflection_Property $property) : bool
	{
		$user = User::of($property);
		return !$user->has(User::HIDE_EDIT)
			&& !$user->has(User::INVISIBLE)
			&& !$user->has(User::INVISIBLE_EDIT);
	}

	//----------------------------------------------------------------------------------------- noAdd
	protected function noAdd() : bool
	{
		if (!isset($this->no_add)) {
			$this->no_add = $this->getUserAnnotation()->has(User::NO_ADD);
		}
		return $this->no_add;
	}

	//-------------------------------------------------------------------------------------- noDelete
	protected function noDelete() : bool
	{
		if (!isset($this->no_delete)) {
			$this->no_delete = $this->getUserAnnotation()->has(User::NO_DELETE);
		}
		return $this->no_delete;
	}

	//-------------------------------------------------------------------------------------- readOnly
	protected function readOnly() : bool
	{
		if (!isset($this->read_only)) {
			$this->read_only = $this->getUserAnnotation()->has(User::READONLY);
		}
		return $this->read_only;
	}

}
