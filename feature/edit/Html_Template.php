<?php
namespace ITRocks\Framework\Feature\Edit;

use ITRocks\Framework\Builder;
use ITRocks\Framework\Controller\Feature;
use ITRocks\Framework\Controller\Parameter;
use ITRocks\Framework\Dao;
use ITRocks\Framework\Html\Parser;
use ITRocks\Framework\Reflection\Attribute\Property\Mandatory;
use ITRocks\Framework\Reflection\Attribute\Property\User;
use ITRocks\Framework\Reflection\Attribute\Property\Widget;
use ITRocks\Framework\Reflection\Reflection_Property;
use ITRocks\Framework\Reflection\Reflection_Property_Value;
use ITRocks\Framework\Tools\Names;
use ITRocks\Framework\Tools\Namespaces;
use ITRocks\Framework\View\Html\Builder\Property;
use ITRocks\Framework\View\Html\Builder\Value_Widget;
use ITRocks\Framework\View\Html\Template;
use ITRocks\Framework\View\Html\Template\Loop;

/**
 * HTML template that changes all properties values to form inputs
 */
class Html_Template extends Template
{

	//------------------------------------------------------------------------------- cache constants
	const COUNTER   = 'counter';
	const PARSED_ID = 'parsed_id';

	//------------------------------------------------------------------------------------- PROPAGATE
	const PROPAGATE = false;

	//---------------------------------------------------------------------------------------- $cache
	/**
	 * Some caches ie to know if id where already defined, to increment some counters.
	 *
	 * @var array
	 */
	protected array $cache = [];

	//-------------------------------------------------------------------------------------- $form_id
	/**
	 * @var string
	 */
	protected string $form_id = '';

	//--------------------------------------------------------------------------------- $link_objects
	/**
	 * @var boolean
	 */
	public bool $link_objects = false;

	//------------------------------------------------------------------------------------- getFormId
	/**
	 * @return string
	 */
	public function getFormId() : string
	{
		if ($this->form_id) {
			return $this->form_id;
		}
		// search reference object for the form
		reset($this->objects);
		$count = count($this->objects) ?: 1;
		while (--$count && !is_object(current($this->objects))) {
			next($this->objects);
		}
		if (is_object(current($this->objects))) {
			$class_name  = get_class(current($this->objects));
			$short_class = Namespaces::shortClassName($class_name);
		}
		// can use the first var name, or 'unknown' if no object nor var name found
		else {
			$short_class = reset($this->var_names) ?: 'unknown';
		}
		$short_form_id = strtolower($short_class) . '_edit';
		$this->form_id = $short_form_id . '_' . $this->nextFormCounter();
		return $this->form_id;
	}

	//---------------------------------------------------------------------------------- newFunctions
	/**
	 * @noinspection PhpDocMissingThrowsInspection class constant is valid
	 * @return Html_Template_Functions
	 */
	protected function newFunctions() : Html_Template_Functions
	{
		/** @noinspection PhpUnhandledExceptionInspection class constant is valid */
		return Builder::create(Html_Template_Functions::class);
	}

	//----------------------------------------------------------------------------------- nextCounter
	/**
	 * Returns next counter for field name into current form context
	 *
	 * @param $field_name string
	 * @param $increment  boolean
	 * @return integer
	 */
	public function nextCounter(string $field_name, bool $increment = true) : int
	{
		$form    = $this->getFormId();
		$counter = $this->cache[self::COUNTER] ?? [];
		if (!isset($counter[$form])) {
			$counter[$form] = [];
		}
		$count = isset($counter[$form][$field_name]) ? ($counter[$form][$field_name] + $increment) : 0;
		if ($increment) {
			$counter[$form][$field_name] = $count;
			$this->cache[self::COUNTER]  = $counter;
		}
		return $count;
	}

	//------------------------------------------------------------------------------- nextFormCounter
	/**
	 * @return string
	 */
	private function nextFormCounter() : string
	{
		return uniqid();
	}

	//----------------------------------------------------------------------------- originValueAddDiv
	/**
	 * @param $value    mixed
	 * @param $property Reflection_Property
	 * @return mixed
	 */
	protected function originValueAddDiv(mixed $value, Reflection_Property $property) : mixed
	{
		return $value;
	}

	//----------------------------------------------------------------------------------------- parse
	/**
	 * After the whole page has been parsed, replace all <section class="edit window"> by <form>
	 *
	 * @return string updated content
	 */
	public function parse() : string
	{
		$this->getFormId();
		return $this->replaceEditWindowsByForm(parent::parse());
	}

	//------------------------------------------------------------------------------ parseLoopElement
	/**
	 * @param $loop Loop
	 * @param $else boolean
	 * @return string
	 */
	protected function parseLoopElement(Loop $loop, bool $else = false) : string
	{
		if ($loop->has_id && $loop->counter) {
			if (
				($expand_property_path = ($this->parameters[Parameter::EXPAND_PROPERTY_PATH] ?? ''))
				&& isset($this->cache[self::PARSED_ID][$this->getFormId()])
			) {
				if (str_ends_with($expand_property_path, DOT)) {
					$expand_property_path = substr($expand_property_path, 0, -1) . '[]';
				}
				$form_id = $this->getFormId();
				foreach ($this->cache[self::PARSED_ID][$form_id] as $key => $value) {
					if (str_starts_with($key, $expand_property_path)) {
						unset($this->cache[self::PARSED_ID][$form_id][$key]);
					}
				}
			}
		}
		return parent::parseLoopElement($loop, $else);
	}

	//------------------------------------------------------------------------------ parseSingleValue
	/**
	 * Parse a variable / function / include and returns its return value
	 *
	 * @noinspection PhpDocMissingThrowsInspection Builder::create with valid parameters
	 * @param $property_name string can be a unique var or path.of.vars
	 * @param $format_value  boolean
	 * @return mixed var value after reading value / executing specs (can be an object)
	 */
	protected function parseSingleValue(string $property_name, bool $format_value = true) : mixed
	{
		$property = $source_object = reset($this->objects);
		if (
			($property instanceof Reflection_Property_Value)
			&& ($property_name === 'value')
			&& !User::of($property)->has(User::STRICT_READ_ONLY)
		) {
			if (
				($builder = Widget::of($property)?->class_name)
				&& is_a($builder, Property::class, true)
			) {
				/** @noinspection PhpParamsInspection Inspector bug : $builder is a string */
				/** @noinspection PhpUnhandledExceptionInspection $builder and $property are valid */
				/** @var $builder Property */
				$builder = Builder::create($builder, [$property, $property->value(), $this]);
				$builder->parameters[Feature::F_EDIT] = Feature::F_EDIT;
				$value = $builder->buildHtml();
				if ($builder instanceof Value_Widget) {
					$value = (new Html_Builder_Property($property, $value))->setTemplate($this)->build();
				}
			}
			else {
				$value = static::ORIGIN;
			}
			if ($value === static::ORIGIN) {
				$value = $property->getType()->isBoolean()
					? $property->value(null, true)
					: parent::parseSingleValue($property_name, false);
				if (
					($prefix = lLastParse($property->pathAsField(), '[', 1, false))
					&& (
						!isset($this->cache[self::PARSED_ID])
						|| !isset($this->cache[self::PARSED_ID][$this->getFormId()])
						|| !isset($this->cache[self::PARSED_ID][$this->getFormId()][$prefix])
					)
				) {
					$this->cache[self::PARSED_ID][$this->getFormId()][$prefix] = true;
					$parent_object   = $property->getObject();
					$id              = $parent_object ? Dao::getObjectIdentifier($parent_object) : null;
					$property_prefix = $this->properties_prefix
						? $this->functions->getPropertyPrefix($this)
						: '';
					$property_prefix = $property_prefix
						? (
							$property_prefix . (
								str_contains($prefix, '[')
									? ('[' . lParse($prefix, '[') . '][' . rParse($prefix, '['))
									: ('[' . $prefix . ']')
							)
						)
						: $prefix;
					$html_builder_type = new Html_Builder_Type('id', null, $id, $property_prefix);
					$parent_property   = $property->getParentProperty();
					if ($parent_property) {
						// TODO HIGHER properties via widgets must transmit their context (property path)
						$html_builder_type->required = Mandatory::of($parent_property)->value;
					}
					$id_value = $html_builder_type->setTemplate($this)->build();
				}
				else {
					$id_value = '';
				}
				if ($property->getAnnotation('output')->value === 'string') {
					$property->setAnnotationLocal('var')->value = 'string';
					$value    = isset($value) ? strval($value) : null;
					$id_value = '';
				}
				if (User::of($property)->has(User::READONLY)) {
					$id_value = '';
				}
				$prefix = $this->properties_prefix ? $this->functions->getPropertyPrefix($this) : null;
				$html_builder_property         = new Html_Builder_Property($property, $value, $prefix);
				$html_builder_property->object = ($source_object instanceof Reflection_Property_Value)
					? $source_object->getObject()
					: $source_object;
				$value = $id_value . $html_builder_property->setTemplate($this)->build();
			}
		}
		else {
			$value = parent::parseSingleValue($property_name);
		}
		return $value;
	}

	//---------------------------------------------------------------------- replaceEditWindowsByForm
	/**
	 * Replace all <section class="edit window"> into the content by <form>
	 *
	 * @param $content string
	 * @return string
	 */
	protected function replaceEditWindowsByForm(string $content) : string
	{
		$parser         = new Parser();
		$parser->buffer =& $content;
		$position       = 0;
		while (($outside_i = $parser->tagPos('article', $position)) !== false) {
			$inside_i = strpos($content, '>', $outside_i) + 1;

			$article_class_i = strpos($content, 'class=', $outside_i);
			if ($article_class_i && ($article_class_i < $inside_i)) {
				$article_class_i += 7;
				$content = substr($content, 0, $article_class_i) . 'form' . SP
					. substr($content, $article_class_i);
				$inside_i += 5;
			}

			$inside   = substr($content, $outside_i, $inside_i - $outside_i);
			$position = $outside_i + 1;
			if (str_contains($inside, 'data-class=')) {
				$classes = array_flip(explode(SP, mParse($inside, 'class=' . DQ, DQ)));
				if (isset($classes['edit'])) {
					$identifier    = Dao::getObjectIdentifier(reset($this->objects));
					$inside_j      = $parser->closingTag('article', $inside_i, Parser::BEFORE);
					$class_name    = get_class(reset($this->objects));
					$action        = $this->replaceLink(SL . Names::classToUri($class_name)
						. ($identifier ? (SL . $identifier) : '') . SL . Feature::F_SAVE);
					$attributes = ' action=' . DQ . $action . DQ
						. 'autocomplete="off"'
						. ' enctype="multipart/form-data"'
						. ' method="post"'
						. ' name=' . DQ . $this->getFormId() . DQ
						. ' target="#responses"';
					$form = LF . '<form' . $attributes . '>'
						. substr($content, $inside_i, $inside_j - $inside_i)
						. '</form>' . LF;
					$content        = substr($content, 0, $inside_i) . $form . substr($content, $inside_j);
					$position       = $outside_i + strlen($form);
					$parser->buffer = $content;
				}
			}

		}
		return $content;
	}

}
