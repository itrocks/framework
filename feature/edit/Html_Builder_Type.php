<?php
namespace ITRocks\Framework\Feature\Edit;

use DateTime;
use ITRocks\Framework\Builder;
use ITRocks\Framework\Component\Combo\Fast_Add;
use ITRocks\Framework\Controller\Parameter;
use ITRocks\Framework\Dao;
use ITRocks\Framework\Dao\File;
use ITRocks\Framework\Locale\Loc;
use ITRocks\Framework\Reflection\Type;
use ITRocks\Framework\Tools\Names;
use ITRocks\Framework\View\Html;
use ITRocks\Framework\View\Html\Dom\Button;
use ITRocks\Framework\View\Html\Dom\Element;
use ITRocks\Framework\View\Html\Dom\Input;
use ITRocks\Framework\View\Html\Dom\Select;
use ITRocks\Framework\View\Html\Dom\Set;
use ITRocks\Framework\View\Html\Dom\Textarea;
use ITRocks\Framework\View\Html\Template;

/**
 * Builds a standard form input matching a given data type and value
 */
class Html_Builder_Type
{

	//----------------------------------------------------------------------------------- $attributes
	/** @var string[] Additional HTML attributes for your DOM element */
	public array $attributes = [];

	//---------------------------------------------------------------------------------- $auto_height
	public bool $auto_height = true;

	//----------------------------------------------------------------------------------- $auto_width
	public bool $auto_width = true;

	//-------------------------------------------------------------------------------------- $classes
	/** @var string[] Additional CSS classes for your DOM element class attribute */
	public array $classes = [];

	//----------------------------------------------------------------------------------- $conditions
	/**
	 * The key is the name of the condition, the value is the name of the value that enables
	 * the condition
	 *
	 * @var ?string[]
	 */
	public ?array $conditions = null;

	//----------------------------------------------------------------------------------------- $data
	/** @var string[] Additional data-key attributes for your DOM element */
	public array $data = [];

	//---------------------------------------------------------------------------------- $is_abstract
	/** true if the component is used for an abstract class */
	public bool $is_abstract = false;

	//----------------------------------------------------------------------------------------- $name
	public string $name;

	//----------------------------------------------------------------------------------------- $null
	/**
	 * The control may have an empty value
	 * ie checkboxes may not be limited to '0' / '1' value, and may be '' too
	 */
	public bool $null = false;

	//------------------------------------------------------------------------------------ $on_change
	/** @var string[] */
	public array $on_change = [];

	//------------------------------------------------------------------------- $parent_level_filters
	public bool $parent_level_filters = false;

	//------------------------------------------------------------------------------------- $pre_path
	public string $pre_path = '';

	//------------------------------------------------------------------------------------- $readonly
	/** The control will be read-only */
	public bool $readonly = false;

	//------------------------------------------------------------------------------ $realtime_change
	public bool $realtime_change = false;

	//------------------------------------------------------------------------------------- $required
	/** Required / mandatory field */
	public bool $required = false;

	//------------------------------------------------------------------------------------- $template
	public Html_Template $template;

	//-------------------------------------------------------------------------------------- $tooltip
	public string $tooltip = '';

	//----------------------------------------------------------------------------------------- $type
	protected ?Type $type = null;

	//---------------------------------------------------------------------------------------- $value
	protected mixed $value = null;

	//-------------------------------------------------------------------------------------- $with_id
	protected bool $with_id = false;

	//----------------------------------------------------------------------------------- __construct
	public function __construct(
		string $name = null, Type $type = null, mixed $value = null, string $pre_path = null
	) {
		if (isset($name))     $this->name    = $name;
		if (isset($type))     $this->type    = $type;
		if (isset($value))    $this->value   = $value;
		if (isset($pre_path)) $this->pre_path = $pre_path;
	}

	//------------------------------------------------------------------------ addConditionsToElement
	/**
	 * Add conditions to the input 'data-conditions' attribute
	 *
	 * @param $element Element the button / input / select element
	 */
	protected function addConditionsToElement(Element $element) : void
	{
		if (!$this->conditions) {
			return;
		}
		$html_conditions = [];
		$old_name        = $this->name;
		foreach ($this->conditions as $condition_name => $condition_value) {
			$this->name = $condition_name;
			$name       = $this->getFieldName('', false, $old_name);
			$operator   = strStartsWith($condition_value, ['<', '>']) ? '' : '=';
			$html_conditions[] = $name . $operator . $condition_value;
		}
		$this->name = $old_name;
		$element->setData('conditions', join(';', $html_conditions));
	}

	//----------------------------------------------------------------------------------------- build
	public function build() : string
	{
		$this->patchSearchTypes();
		$type = $this->type;
		if (!isset($type)) {
			return $this->buildId();
		}
		$result = match($type->asString()) {
			Type::BOOLEAN, Type::FALSE, Type::TRUE => $this->buildBoolean(),
			Type::FLOAT => $this->buildFloat(),
			Type::INTEGER => $this->buildInteger(),
			Type::STRING, Type::STRING_ARRAY => $this->buildString(),
			default => null
		};
		if (!isset($result) && $type->isClass()) {
			$class_name = $type->asString();
			if (is_a($class_name, DateTime::class, true)) {
				$result = $this->buildDateTime();
			}
			elseif (is_a($class_name, File::class, true)) {
				$result = $this->buildFile();
			}
			else {
				$result = $this->buildObject();
			}
		}
		// TODO SM Create a Editable_Element class to be able to add some behavior like on_change because Element may be span or other html
		if ($result instanceof Element) {
			$this->setOnChangeAttribute($result);
			if ($this->tooltip) {
				$result->setAttribute('title', $this->tooltip);
			}
		}
		return $result ?? $this->value;
	}

	//---------------------------------------------------------------------------------- buildBoolean
	protected function buildBoolean() : Element|string
	{
		$value = match($this->value) {
			null,     ''   => $this->null ? null : '0',
			false, 0, '0'  => '0',
			true,  1, '1'  => '1'
		};
		if ($this->null) {
			$input = new Select($this->getFieldName(), ['' => '', '0' => NO, '1' => YES], $value);
			$this->commonAttributes($input);
			return $input;
		}
		$input = new Input($this->getFieldName());
		$input->setAttribute('type', 'hidden');
		$input->setAttribute('value', $value);
		$checkbox = new Input();
		$checkbox->setAttribute('id', 'cb-' . uniqid());
		$checkbox->setAttribute('type', 'checkbox');
		$checkbox->setAttribute('value', true);
		if ($this->readonly) {
			$this->setInputAsReadOnly($input);
		}
		if ($this->value) {
			$checkbox->setAttribute('checked');
		}
		$this->setOnChangeAttribute($checkbox);
		$this->commonAttributes($checkbox);
		return $input . $checkbox;
	}

	//--------------------------------------------------------------------------------- buildDateTime
	protected function buildDateTime(bool $format = true) : Element
	{
		$input = new Input(
			$this->getFieldName(),
			$format ? Loc::dateToLocale($this->value) : $this->value
		);
		if (!$this->readonly) {
			$input->addClass('datetime');
		}
		$this->commonAttributes($input);
		return $input;
	}

	//------------------------------------------------------------------------------------- buildFile
	protected function buildFile() : string
	{
		$field_name = $this->getFieldName();
		if (
			($this->value instanceof File)
			&& is_numeric($counter = lParse(rLastParse($field_name, '['), ']'))
		) {
			$id_input = new Input(
				lLastParse($field_name, '[') . '[' . $counter . '][id]',
				Dao::getObjectIdentifier($this->value)
			);
			$id_input->addClass('id');
			$id_input->setAttribute('type', 'hidden');
		}
		else {
			$id_input = '';
		}
		$file = new Input($field_name);
		$file->setAttribute('type', 'file');
		$file->addClass('file');
		$span = ($this->value instanceof File)
			? (new Html\Builder\File($this->value))->build()
			: '';
		$this->commonAttributes($file);
		return $file . $span . $id_input;
	}

	//------------------------------------------------------------------------------------ buildFloat
	protected function buildFloat(bool $format = true) : Element
	{
		$input = new Input(
			$this->getFieldName(),
			$format ? Loc::floatToLocale($this->value) : $this->value
		);
		if ($this->auto_width) {
			$input->addClass('auto_width');
		}
		$input->addClass('float');
		$this->commonAttributes($input);
		return $input;
	}

	//--------------------------------------------------------------------------------------- buildId
	protected function buildId() : Element
	{
		$input = new Input($this->getFieldName(), $this->value);
		$input->setAttribute('type', 'hidden');
		$input->addClass('id');
		$this->commonAttributes($input);
		return $input;
	}

	//---------------------------------------------------------------------------------- buildInteger
	protected function buildInteger(bool $format = true) : Element
	{
		$input = new Input(
			$this->getFieldName(),
			$format ? Loc::integerToLocale($this->value) : $this->value
		);
		if ($this->auto_width) {
			$input->addClass('auto_width');
		}
		$input->addClass('integer');
		$this->commonAttributes($input);
		return $input;
	}

	//----------------------------------------------------------------------------------- buildObject
	/**
	 * @param $filters   string[] the key is the name of the filter, the value is the name of the form
	 *   element containing its value
	 * @param $as_string boolean true if the object should be used as a string
	 * @return string
	 */
	public function buildObject(array $filters = null, bool $as_string = false) : string
	{
		$this->with_id     = true;
		$class_name        = $this->type->asString();
		$source_class_name = Builder::current()->sourceClassName($class_name);
		// visible input ?
		if (!$this->name) {
			$this->pre_path .= '_';
			$re_pre_path     = true;
		}
		$input_id = (
			$as_string
			|| is_a($class_name, Fast_Add::class, true)
			|| is_a(Builder::className($source_class_name), Fast_Add::class, true)
		)
			? $this->getFieldName()
			: null;
		if (isset($re_pre_path)) {
			$this->pre_path = substr($this->pre_path, 0, -1);
		}
		$input = new Input($input_id, strval($this->value));
		if ($this->auto_width) {
			$input->addClass('auto_width');
		}
		$input->setData('combo-class', $source_class_name);
		$input->setData(
			'combo-set-class',
			($source_class_name === Type::OBJECT)
				? $source_class_name
				: Names::classToSet($source_class_name)
		);
		if ($this->tooltip) {
			$input->setAttribute('title', $this->tooltip);
		}
		// id input. Should always be output, except if as_string, cause can be used by other properties
		if (!$as_string) {
			if ($this->value) {
				$identifier = Dao::getObjectIdentifier($this->value);
				$identifier = ($identifier && $this->is_abstract)
					? (Builder::current()->sourceClassName(get_class($this->value)) . ':' . $identifier)
					: $identifier;
			}
			else {
				$identifier = '';
			}
			$id_input = new Input($this->getFieldName('id_'), $identifier);
			$id_input->addClass('id');
			$id_input->setAttribute('type', 'hidden');
			if (isset($this->data['no-empty-check'])) {
				$id_input->setData('no-empty-check', $this->data['no-empty-check']);
			}
		}
		if ($this->readonly) {
			if (isset($id_input)) {
				$id_input->setAttribute('disabled');
			}
			$more = '';
		}
		else {
			if ($filters) {
				$html_filters   = [];
				$old_name       = $this->name;
				$old_pre_path   = $this->pre_path;
				$this->pre_path = lLastParse($this->pre_path, DOT, 1, false) ?: '';
				foreach ($filters as $filter_name => $filter_value) {
					if (
						is_numeric($filter_value)
						|| (
							strStartsWith($filter_value, [DQ, Q])
							&& (substr($filter_value, 0, 1) === substr($filter_value, -1))
						)
					) {
						$html_filters[] = $filter_name . '=' . $filter_value;
					}
					else {
						$this->name     = $filter_value;
						$name           = $this->getFieldName('', false);
						$html_filters[] = $filter_name . '=' . $name;
					}
				}
				$this->name     = $old_name;
				$this->pre_path = $old_pre_path;
				$input->setData('combo-filters', join(',', $html_filters));
			}
			$input->addClass('combo');
			// 'more' button
			$more = new Button('more');
			$more->addClass('more');
			$more->setAttribute('tabindex', -1);
			if (isset($id_input)) {
				$this->setOnChangeAttribute($id_input, false);
				if ($this->realtime_change) {
					$input->setData('realtime-change');
				}
			}
			else {
				$this->setOnChangeAttribute($input);
			}
		}
		$this->commonAttributes($input);
		return ($id_input ?? '') . $input . $more;
	}

	//----------------------------------------------------------------------------------- buildString
	/**
	 * @param $multiline      boolean
	 * @param $values         string[]
	 * @param $ordered_values boolean true if values are ordered and to disable alphabetical sort
	 * @return Element
	 */
	protected function buildString(
		bool $multiline = false, array $values = null, bool $ordered_values = false
	) : Element
	{
		// case choice of values (single or multiple)
		if ($values) {
			if (!$this->readonly) {
				if ($this->type->isMultipleString()) {
					$input = new Set(
						$this->getFieldName(), $values, $this->value, null, null, $ordered_values
					);
				}
				else {
					if (!isset($values[''])) {
						$values = ['' => ''] + $values;
					}
					$input = new Select($this->getFieldName(), $values, $this->value);
					if ($ordered_values) {
						$input->ordered = true;
						$input->setData('ordered');
					}
				}
			}
			else {
				if ($this->type->isMultipleString()) {
					$input = new Set(
						$this->getFieldName(),
						$values,
						$this->value,
						null,
						$this->readonly,
						$ordered_values
					);
				}
				else {
					$hidden = new Input($this->getFieldName(), $this->value);
					$hidden->setAttribute('readonly');
					$hidden->setAttribute('type', 'hidden');
					$input = new Input(null, $this->value ? Loc::tr($this->value) : '');
					$input->prepend[] = $hidden;
				}
			}
		}
		// case of free editable text (mono or multi line)
		elseif ($this->type->isMultipleString()) {
			$input = $this->makeTextInputOrTextarea(false, $this->value);
		}
		else {
			$input = $this->makeTextInputOrTextarea($multiline, $this->value);
		}
		$this->commonAttributes($input);
		return $input;
	}

	//------------------------------------------------------------------------------ commonAttributes
	/** Set common attributes, classes, data to the given element */
	protected function commonAttributes(Element $element) : void
	{
		$this->addConditionsToElement($element);
		foreach ($this->attributes as $attribute_name => $attribute_value) {
			$element->setAttribute($attribute_name, $attribute_value);
		}
		foreach ($this->classes as $class) {
			$element->addClass($class);
		}
		foreach ($this->data as $data_name => $data_value) {
			$element->setData($data_name, $data_value);
		}
		$this->setInputAsReadOnly($element);
		if ($this->required) {
			if ($this->pre_path) {
				$element->setData('required');
			}
			else {
				$element->setAttribute('required');
			}
		}
	}

	//---------------------------------------------------------------------------------- getFieldName
	public function getFieldName(
		string $prefix = '', bool $counter_increment = true, string $counter_name = ''
	) : string
	{
		if (empty($this->name) && $this->pre_path) {
			$prefix = '';
		}
		if ($this->pre_path === '') {
			$field_name = $prefix . $this->name;
		}
		elseif (str_ends_with($this->pre_path, '[]')) {
			if ($counter_name) {
				$counter_name = substr($this->pre_path, 0, -2)
					. '[' . ($this->with_id ? 'id_' : '') . $counter_name . ']';
			}
			$field_name  = substr($this->pre_path, 0, -2) . '[' . $prefix . $this->name . ']';
			$count       = $this->template->nextCounter($counter_name ?: $field_name, $counter_increment);
			$field_name .= '[' . $count . ']';
		}
		elseif (($prefix . $this->name) !== '') {
			$field_name = str_contains($this->pre_path, '[]')
				? $this->getRepetitiveFieldName($prefix, $counter_increment)
				: ($this->pre_path . '[' . $prefix . $this->name . ']');
		}
		else {
			$count      = $this->template->nextCounter($this->pre_path, $counter_increment);
			$field_name = $this->pre_path . '[' . $count . ']';
		}
		return $field_name;
	}

	//------------------------------------------------------------------------ getRepetitiveFieldName
	private function getRepetitiveFieldName(string $prefix, bool $counter_increment) : string
	{
		$i                = strpos($this->pre_path, '[]');
		$counter_name     = substr($this->pre_path, 0, $i);
		$field_name_i     = $i + 3;
		$field_name_j     = strpos($this->pre_path, ']', $field_name_i);
		$super_field_name = substr($this->pre_path, $field_name_i, $field_name_j - $field_name_i);
		$counter_name    .= '[' . $super_field_name . ']' . '[' . $prefix . $this->name . ']';
		$count            = $this->template->nextCounter($counter_name, $counter_increment);
		return substr($this->pre_path, 0, $i)
			. '[' . $super_field_name . ']'
			. '[' . $count . ']'
			. substr($this->pre_path, $field_name_j + 1)
			. '[' . $prefix . $this->name . ']';
	}

	//----------------------------------------------------------------------- makeTextInputOrTextarea
	/**
	 * @param $multiline boolean
	 * @param $value     string|string[]|null
	 * @return Input|TextArea
	 */
	private function makeTextInputOrTextarea(bool $multiline, array|string|null $value)
		: Input|Textarea
	{
		if ($multiline) {
			if (is_array($value)) {
				$value = join(LF, $value);
			}
			$input = new Textarea($this->getFieldName(), $value);
			if ($this->auto_height) {
				$input->addClass('auto_height');
			}
		}
		else {
			if (is_array($value)) {
				$value = join(',', $value);
			}
			$input = new Input($this->getFieldName(), $value);
		}
		if ($this->auto_width) {
			$input->addClass('auto_width');
		}
		return $input;
	}

	//------------------------------------------------------------------------------ patchSearchTypes
	/** Patch search type : e.g. dates should be typed as string */
	private function patchSearchTypes() : void
	{
		if (str_starts_with($this->name, 'search[') && $this->type->isDateTime()) {
			$this->type = new Type(Type::STRING);
			if ($this->value) {
				$this->value = Loc::dateToLocale($this->value);
			}
		}
	}

	//---------------------------------------------------------------------------- setInputAsReadOnly
	public function setInputAsReadOnly(Element $input) : void
	{
		if ($this->readonly) {
			if ($input->getAttribute('name')) {
				$input->setData('name', $input->getAttribute('name')->value);
				$input->removeAttribute('name');
			}
			$input->setAttribute('readonly');
			$input->setAttribute('tabindex', -1);
		}
	}

	//-------------------------------------------------------------------------- setOnChangeAttribute
	private function setOnChangeAttribute(Element $element, bool $realtime_change = true) : void
	{
		if (!$this->on_change) {
			return;
		}
		$on_change = join(',', $this->on_change);
		$element->setData('on-change', $on_change);
		if ($realtime_change && $this->realtime_change) {
			$element->setData('realtime-change');
		}
	}

	//----------------------------------------------------------------------------------- setTemplate
	/**
	 * Set template : will be set only if $template is a Html_Template
	 *
	 * @return $this
	 */
	public function setTemplate(Template $template) : static
	{
		if ($template instanceof Html_Template) {
			$this->template = $template;
			if (!$this->pre_path) {
				$this->pre_path = $this->template->getParameter(Parameter::PROPERTIES_PREFIX) ?: '';
			}
		}
		return $this;
	}

}
