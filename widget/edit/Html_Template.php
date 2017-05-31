<?php
namespace ITRocks\Framework\Widget\Edit;

use ITRocks\Framework\Builder;
use ITRocks\Framework\Controller\Feature;
use ITRocks\Framework\Controller\Parameter;
use ITRocks\Framework\Dao;
use ITRocks\Framework\Html\Parser;
use ITRocks\Framework\Reflection\Annotation\Property\User_Annotation;
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

	//--------------------------------------------------------------------------------------- COUNTER
	const COUNTER   = 'counter';

	//------------------------------------------------------------------------------------- PARSED_ID
	const PARSED_ID = 'parsed_id';

	//----------------------------------------------------------------------------- options constants

	//------------------------------------------------------------------------------------- PROPAGATE
	const PROPAGATE = false;

	//---------------------------------------------------------------------------------------- $cache
	/**
	 * Some caches ie to know if id where already defined, to increment some counters.
	 *
	 * @var array
	 */
	protected $cache = [];

	//-------------------------------------------------------------------------------------- $form_id
	/**
	 * @var string
	 */
	protected $form_id;

	//------------------------------------------------------------------------------------- getFormId
	/**
	 * @return string
	 */
	public function getFormId()
	{
		return strval($this->form_id);
	}

	//---------------------------------------------------------------------------------- newFunctions
	/**
	 * @return Html_Template_Functions
	 */
	protected function newFunctions()
	{
		/** @var $functions Html_Template_Functions */
		$functions = Builder::create(Html_Template_Functions::class);
		return $functions;
	}

	//----------------------------------------------------------------------------------- nextCounter
	/**
	 * Returns next counter for field name into current form context
	 *
	 * @param $field_name string
	 * @param $increment  boolean
	 * @return integer
	 */
	public function nextCounter($field_name, $increment = true)
	{
		$form    = $this->getFormId();
		$counter = isset($this->cache[self::COUNTER]) ? $this->cache[self::COUNTER] : [];
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
	 * @return integer
	 */
	private function nextFormCounter()
	{
		return uniqid();
	}

	//----------------------------------------------------------------------------------------- parse
	/**
	 * After the whole page has been parsed, replace all <section class="edit window"> by <form>
	 *
	 * @return string updated content
	 */
	public function parse()
	{
		$content = parent::parse();
		$content = $this->replaceEditWindowsByForm($content);
		return $content;
	}

	//------------------------------------------------------------------------------ parseLoopElement
	/**
	 * @param $loop Loop
	 * @return string
	 */
	protected function parseLoopElement(Loop $loop)
	{
		if ($loop->has_id && $loop->counter) {
			if (
				($expand_property_path = $this->parameters[Parameter::EXPAND_PROPERTY_PATH])
				&& isset($this->cache[self::PARSED_ID][$this->getFormId()])
			) {
				if (substr($expand_property_path, -1) === DOT) {
					$expand_property_path = substr($expand_property_path, 0, -1) . '[]';
				}
				$form_id = $this->getFormId();
				foreach ($this->cache[self::PARSED_ID][$form_id] as $key => $value) {
					if (substr($key, 0, strlen($expand_property_path)) === $expand_property_path) {
						unset($this->cache[self::PARSED_ID][$form_id][$key]);
					}
				}
			}
		}
		return parent::parseLoopElement($loop);
	}

	//------------------------------------------------------------------------------ parseSingleValue
	/**
	 * Parse a variable / function / include and returns its return value
	 *
	 * @param $property_name string can be an unique var or path.of.vars
	 * @param $format_value  boolean
	 * @return string var value after reading value / executing specs (can be an object)
	 */
	protected function parseSingleValue($property_name, $format_value = true)
	{
		$property = $source_object = reset($this->objects);
		if (
			($property instanceof Reflection_Property_Value)
			&& ($property_name == 'value')
			&& !User_Annotation::of($property)->has(User_Annotation::STRICT_READ_ONLY)
		) {
			if (
				($builder = $property->getAnnotation('widget')->value)
				&& is_a($builder, Property::class, true)
			) {
				$builder = Builder::create($builder, [$property, $property->value(), $this]);
				/** @var $builder Property */
				$builder->parameters[Feature::F_EDIT] = Feature::F_EDIT;
				$value = $builder->buildHtml();
				if ($builder instanceof Value_Widget) {
					$value = (new Html_Builder_Property($property, $value))->setTemplate($this)->build();
				}
			}
			else {
				$value = $property->getType()->isBoolean()
					? $property->value()
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
					if ($property instanceof Reflection_Property_Value) {
						$parent_object     = $property->getObject();
						$id                = $parent_object ? Dao::getObjectIdentifier($parent_object) : null;
						$html_builder_type = new Html_Builder_Type('id', null, $id, $prefix);
						$id_value          = $html_builder_type->setTemplate($this)->build();
					}
					else {
						$id_value = '';
					}
				}
				else {
					$id_value = '';
				}
				if ($property->getAnnotation('output')->value == 'string') {
					$property->setAnnotationLocal('var')->value = 'string';
					$value    = isset($value) ? strval($value) : null;
					$id_value = '';
				}
				if (
					$property->getListAnnotation(User_Annotation::ANNOTATION)->has(User_Annotation::READONLY)
				) {
					$id_value = '';
				}
				$value = $id_value
					. (new Html_Builder_Property($property, $value))->setTemplate($this)->build();
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
	protected function replaceEditWindowsByForm($content)
	{
		$parser   = new Parser($content);
		$position = 0;
		while (($outside_i = $parser->tagPos('section', $position)) !== false) {
			$inside_i = strpos($content, '>', $outside_i) + 1;
			$inside   = substr($content, $outside_i, $inside_i - $outside_i);
			$position = $outside_i + 1;
			if (strpos($inside, 'data-class=') && strpos($inside, 'class=')) {
				$classes = array_flip(explode(SP, mParse($inside, 'class=' . DQ, DQ)));
				if (isset($classes['edit']) && isset($classes['window'])) {
					$inside_j      = $parser->closingTag('section', $inside_i, 'start');
					$outside_j     = $inside_j + 10;
					$class_name    = get_class(reset($this->objects));
					$short_class   = Namespaces::shortClassName($class_name);
					$short_form_id = strtolower($short_class) . '_edit';
					$this->form_id = $short_form_id . '_' . $this->nextFormCounter();
					$action        = $this->replaceLink(SL . Names::classToUri($class_name) . '/write');
					$attributes    = substr($content, $outside_i + 8, $inside_i - $outside_i - 9);
					$attributes    = ' action=' . DQ . $action . DQ
						. $attributes
						. ' enctype="multipart/form-data"'
						. ' method="post"'
						. ' name=' . DQ . $this->form_id . DQ
						. ' target="#messages"';
					$form = '<form' . $attributes . '>'
						. substr($content, $inside_i, $inside_j - $inside_i)
						. '</form>';
					$content        = substr($content, 0, $outside_i) . $form . substr($content, $outside_j);
					$position       = $outside_i + strlen($form);
					$parser->buffer = $content;
				}
			}
		}
		return $content;
	}

}
