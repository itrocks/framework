<?php
namespace ITRocks\Framework\Feature\Edit;

use DateTime;
use ITRocks\Framework\Builder;
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
use ITRocks\Framework\View\Html\Dom\Label;
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
	/**
	 * @var string[] Additional HTML attributes for your DOM element
	 */
	public $attributes = [];

	//-------------------------------------------------------------------------------------- $classes
	/**
	 * @var string[] Additional CSS classes for your DOM element class attribute
	 */
	public $classes = [];

	//----------------------------------------------------------------------------------- $conditions
	/**
	 * The key is the name of the condition, the value is the name of the value that enables
	 * the condition
	 *
	 * @var string[]
	 */
	public $conditions;

	//----------------------------------------------------------------------------------------- $data
	/**
	 * @var string[] Additional data-key attributes for your DOM element
	 */
	public $data = [];

	//---------------------------------------------------------------------------------- $is_abstract
	/**
	 * true if the component is used for an abstract class
	 *
	 * @var boolean
	 */
	public $is_abstract = false;

	//--------------------------------------------------------------------------------------- $is_new
	/**
	 * Is it a form for a new object (true), or a modification form (false) ?
	 *
	 * @var boolean
	 */
	public $is_new = false;

	//----------------------------------------------------------------------------------------- $name
	/**
	 * @var string
	 */
	public $name;

	//----------------------------------------------------------------------------------------- $null
	/**
	 * The control may have an empty value
	 * ie checkboxes may not be limited to '0' / '1' value, and may be '' too
	 *
	 * @var boolean
	 */
	public $null = false;

	//------------------------------------------------------------------------------------ $on_change
	/**
	 * @var string[]
	 */
	public $on_change = [];

	//-------------------------------------------------------------------------------------- $preprop
	/**
	 * @var string
	 */
	public $preprop;

	//------------------------------------------------------------------------------------- $readonly
	/**
	 * The control will be read-only
	 *
	 * @var boolean
	 */
	public $readonly = false;

	//------------------------------------------------------------------------------------- $required
	/**
	 * Required / mandatory field
	 *
	 * @var boolean
	 */
	public $required = false;

	//------------------------------------------------------------------------------------- $template
	/**
	 * @var Html_Template
	 */
	public $template;

	//-------------------------------------------------------------------------------------- $tooltip
	/**
	 * @var string
	 */
	public $tooltip;

	//----------------------------------------------------------------------------------------- $type
	/**
	 * @var Type
	 */
	protected $type;

	//---------------------------------------------------------------------------------------- $value
	/**
	 * @var string
	 */
	protected $value;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $name    string
	 * @param $type    Type
	 * @param $value   mixed
	 * @param $preprop string
	 */
	public function __construct($name = null, Type $type = null, $value = null, $preprop = null)
	{
		if (isset($name))    $this->name    = $name;
		if (isset($type))    $this->type    = $type;
		if (isset($value))   $this->value   = $value;
		if (isset($preprop)) $this->preprop = $preprop;
	}

	//------------------------------------------------------------------------ addConditionsToElement
	/**
	 * Add conditions to the input 'data-conditions' attribute
	 *
	 * @param $element Element the button / input / select element
	 */
	protected function addConditionsToElement($element)
	{
		if ($this->conditions) {
			$html_conditions = [];
			$old_name        = $this->name;
			foreach ($this->conditions as $condition_name => $condition_value) {
				$this->name = $condition_name;
				$name       = $this->getFieldName('', false);
				$html_conditions[] = $name . '=' . $condition_value;
			}
			$this->name = $old_name;
			$element->setAttribute('data-conditions', join(';', $html_conditions));
		}
	}

	//----------------------------------------------------------------------------------------- build
	/**
	 * @return string
	 */
	public function build()
	{
		$this->patchSearchTypes();
		$type = $this->type;
		if (!isset($type)) {
			return $this->buildId();
		}
		else {
			switch ($type->asString()) {
				case Type::BOOLEAN:      $result = $this->buildBoolean(); break;
				case Type::FLOAT:        $result = $this->buildFloat();   break;
				case Type::INTEGER:      $result = $this->buildInteger(); break;
				case Type::STRING:       $result = $this->buildString();  break;
				case Type::STRING_ARRAY: $result = $this->buildString();  break;
			}
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
			if (isset($result) && ($result instanceof Element)) {
				$this->setOnChangeAttribute($result);
				if ($this->tooltip) {
					$result->setAttribute('title', $this->tooltip);
				}
			}
		}
		return isset($result) ? $result : $this->value;
	}

	//---------------------------------------------------------------------------------- buildBoolean
	/**
	 * @return Element|string
	 */
	protected function buildBoolean()
	{
		$value = strlen($this->value) ? $this->value : ($this->null ? null : 0);
		if ($this->null) {
			$input = new Select($this->getFieldName(), ['' => '', '0' => NO, '1' => YES], $value);
			$this->commonAttributes($input);
			return $input;
		}
		else {
			$input = new Input($this->getFieldName());
			$input->setAttribute('type', 'hidden');
			$input->setAttribute('value', $value);
			$checkbox = new Input();
			$checkbox->setAttribute('type', 'checkbox');
			$checkbox->setAttribute('value', true);
			if ($this->readonly) {
				$this->setInputAsReadOnly($input);
			}
			if ($this->value) {
				$checkbox->setAttribute('checked');
			}
			$this->commonAttributes($checkbox);
			return $input . $checkbox;
		}
	}

	//--------------------------------------------------------------------------------- buildDateTime
	/**
	 * @param $format boolean
	 * @return Element
	 */
	protected function buildDateTime($format = true)
	{
		$input = new Input(
			$this->getFieldName(),
			$format ? Loc::dateToLocale($this->value) : $this->value
		);
		$input->setAttribute('autocomplete', 'off');
		if (!$this->readonly) {
			$input->addClass('datetime');
		}
		$this->commonAttributes($input);
		return $input;
	}

	//------------------------------------------------------------------------------------- buildFile
	/**
	 * @return string
	 */
	protected function buildFile()
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
		return $id_input . $file . $span;
	}

	//------------------------------------------------------------------------------------ buildFloat
	/**
	 * @param $format boolean
	 * @return Element
	 */
	protected function buildFloat($format = true)
	{
		$input = new Input(
			$this->getFieldName(),
			$format ? Loc::floatToLocale($this->value) : $this->value
		);
		$input->addClass('auto_width');
		$input->addClass('float');
		$this->commonAttributes($input);
		return $input;
	}

	//--------------------------------------------------------------------------------------- buildId
	/**
	 * @return Element
	 */
	protected function buildId()
	{
		$input = new Input($this->getFieldName(), $this->value);
		$input->setAttribute('type', 'hidden');
		$input->addClass('id');
		$this->commonAttributes($input);
		return $input;
	}

	//---------------------------------------------------------------------------------- buildInteger
	/**
	 * @param $format boolean
	 * @return Element
	 */
	protected function buildInteger($format = true)
	{
		$input = new Input(
			$this->getFieldName(),
			$format ? Loc::integerToLocale($this->value) : $this->value
		);
		$input->addClass('auto_width');
		$input->addClass('integer');
		$this->commonAttributes($input);
		return $input;
	}

	//----------------------------------------------------------------------------------- buildObject
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $filters   string[] the key is the name of the filter, the value is the name of the form
	 *   element containing its value
	 * @param $as_string boolean true if the object should be used as a string
	 * @return string
	 */
	public function buildObject(array $filters = null, $as_string = false)
	{
		$class_name        = $this->type->asString();
		$source_class_name = Builder::current()->sourceClassName($class_name);
		// visible input ?
		$input_id = $as_string ? $this->getFieldName() : null;
		$input    = new Input($input_id, strval($this->value));
		$input->addClass('auto_width');
		$input->setAttribute('autocomplete', 'off');
		/** @noinspection PhpUnhandledExceptionInspection must be valid */
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
		}
		if ($this->readonly) {
			if (isset($id_input)) {
				$id_input->setAttribute('disabled');
			}
			$more = '';
		}
		else {
			if ($filters) {
				$html_filters = [];
				$old_name     = $this->name;
				foreach ($filters as $filter_name => $filter_value) {
					if (
						is_numeric($filter_value)
						|| (
							in_array(substr($filter_value, 0, 1), [DQ, Q])
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
				$this->name = $old_name;
				$input->setAttribute('data-combo-filters', join(',', $html_filters));
			}
			$input->addClass('combo');
			// 'more' button
			$more = new Button('more');
			$more->addClass('more');
			$more->setAttribute('tabindex', -1);
			if (isset($id_input)) {
				$this->setOnChangeAttribute($id_input);
			}
			else {
				$this->setOnChangeAttribute($input);
			}
		}
		$this->commonAttributes($input);
		return (isset($id_input) ? $id_input : '') . $input . $more;
	}

	//----------------------------------------------------------------------------------- buildString
	/**
	 * @param $multiline      boolean
	 * @param $values         string[]
	 * @param $ordered_values boolean true if values are ordered and to disable alphabetical sort
	 * @return Element
	 */
	protected function buildString($multiline = false, array $values = null, $ordered_values = false)
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
						$input->setData('ordered', 'true');
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
					$hidden = new Input(null, $this->value);
					$hidden->setAttribute('readonly');
					$hidden->setAttribute('type', 'hidden');
					$input = new Label([$hidden, Loc::tr($values[$this->value])]);
					$input->setBuildMode(Element::BUILD_MODE_RAW);
				}
			}
		}
		// case of free editable text (mono or multi line)
		else {
			$input = $this->makeTextInputOrTextarea($multiline, $this->value);
		}
		$this->commonAttributes($input);
		return $input;
	}

	//------------------------------------------------------------------------------ commonAttributes
	/**
	 * Set common attributes, classes, data to the given element
	 *
	 * @param $element Element
	 */
	protected function commonAttributes(Element $element)
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
			if ($this->preprop) {
				$element->setData('required', true);
			}
			else {
				$element->setAttribute('required', true);
			}
		}
	}

	//---------------------------------------------------------------------------------- getFieldName
	/**
	 * @param $prefix            string
	 * @param $counter_increment boolean
	 * @return string
	 */
	public function getFieldName($prefix = '', $counter_increment = true)
	{
		if (empty($this->name) && $this->preprop) {
			$prefix = '';
		}
		if (!isset($this->preprop)) {
			$field_name = $prefix . $this->name;
		}
		elseif (substr($this->preprop, -2) == '[]') {
			$field_name  = substr($this->preprop, 0, -2) . '[' . $prefix . $this->name . ']';
			$count       = $this->template->nextCounter($field_name, $counter_increment);
			$field_name .= '[' . $count . ']';
		}
		elseif (strlen($prefix . $this->name)) {
			$field_name = (strpos($this->preprop, '[]') !== false)
				? $this->getRepetitiveFieldName($prefix, $counter_increment)
				: $this->preprop . '[' . $prefix . $this->name . ']';
		}
		else {
			$count      = $this->template->nextCounter($this->preprop, $counter_increment);
			$field_name = $this->preprop . '[' . $count . ']';
		}
		return $field_name;
	}

	//------------------------------------------------------------------------ getRepetitiveFieldName
	/**
	 * @param $prefix            string
	 * @param $counter_increment boolean
	 * @return string
	 */
	private function getRepetitiveFieldName($prefix, $counter_increment)
	{
		$i                = strpos($this->preprop, '[]');
		$counter_name     = substr($this->preprop, 0, $i);
		$field_name_i     = $i + 3;
		$field_name_j     = strpos($this->preprop, ']', $field_name_i);
		$super_field_name = substr($this->preprop, $field_name_i, $field_name_j - $field_name_i);
		$counter_name    .= '[' . $super_field_name . ']' . '[' . $prefix . $this->name . ']';
		$count            = $this->template->nextCounter($counter_name, $counter_increment);
		return substr($this->preprop, 0, $i)
			. '[' . $super_field_name . ']'
			. '[' . $count . ']'
			. substr($this->preprop, $field_name_j + 1)
			. '[' . $prefix . $this->name . ']';
	}

	//----------------------------------------------------------------------- makeTextInputOrTextarea
	/**
	 * @param $multiline boolean
	 * @param $value     string
	 * @return Input
	 */
	private function makeTextInputOrTextarea($multiline, $value)
	{
		if ($multiline) {
			$input = new Textarea($this->getFieldName(), $value);
			$input->addClass('auto_height');
		}
		else {
			$input = new Input($this->getFieldName(), $value);
			$input->setAttribute('autocomplete', 'off');
		}
		$input->addClass('auto_width');
		return $input;
	}

	//------------------------------------------------------------------------------ patchSearchTypes
	/**
	 * Patch search type : eg dates should be typed as string
	 */
	private function patchSearchTypes()
	{
		if (
			(substr($this->name, 0, 7) === 'search[')
			&& $this->type->isDateTime()
		) {
			$this->type = new Type(Type::STRING);
			if ($this->value) {
				$this->value = Loc::dateToLocale($this->value);
			}
		}
	}

	//---------------------------------------------------------------------------- setInputAsReadOnly
	/**
	 * @param $input Element
	 */
	public function setInputAsReadOnly($input)
	{
		if ($this->readonly) {
			if ($input->getAttribute('name') && !$this->is_new) {
				$input->setData('name', $input->getAttribute('name')->value);
				$input->removeAttribute('name');
			}
			$input->setAttribute('readonly');
			$input->setAttribute('tabindex', -1);
		}
	}

	//-------------------------------------------------------------------------- setOnChangeAttribute
	/**
	 * @param $element Element
	 */
	private function setOnChangeAttribute(Element $element)
	{
		if ($this->on_change) {
			$on_change = join(',', $this->on_change);
			$element->setData('on-change', $on_change);
		}
	}

	//----------------------------------------------------------------------------------- setTemplate
	/**
	 * Set template : will be set only if $template is an Html_Template
	 *
	 * @param $template Template
	 * @return Html_Builder_Type
	 */
	public function setTemplate(Template $template)
	{
		if ($template instanceof Html_Template) {
			$this->template = $template;
			if (!$this->preprop) {
				$this->preprop = $this->template->getParameter(Parameter::PROPERTIES_PREFIX);
			}
		}
		return $this;
	}

}
