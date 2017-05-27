<?php
namespace ITRocks\Framework\Widget\Edit;

use ITRocks\Framework\Builder;
use ITRocks\Framework\Controller\Feature;
use ITRocks\Framework\Controller\Parameter;
use ITRocks\Framework\Dao;
use ITRocks\Framework\Reflection\Annotation\Property\User_Annotation;
use ITRocks\Framework\Reflection\Reflection_Property_Value;
use ITRocks\Framework\Tools\Namespaces;
use ITRocks\Framework\View\Html\Builder\Property;
use ITRocks\Framework\View\Html\Builder\Value_Widget;
use ITRocks\Framework\View\Html\Template;
use ITRocks\Framework\View\Html\Template\Loop;

/**
 * Html template that changes all properties values to form inputs
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

	//-------------------------------------------------------------------------------- parseContainer
	/**
	 * Replace code before <!--BEGIN--> and after <!--END--> by the html main container's code
	 *
	 * @param $content string
	 * @return string updated content
	 */
	protected function parseContainer($content)
	{
		$content = parent::parseContainer($content);
		$content = $this->replaceSectionByForm($content);
		return $content;
	}

	//---------------------------------------------------------------------------------- parseInclude
	/**
	 * Parses included view controller call result (must be an html view) or includes html template
	 *
	 * @param $include_uri string
	 * @return string|null included template, parsed, or null if included file was not found
	 */
	protected function parseInclude($include_uri)
	{
		$content = parent::parseInclude($include_uri);
		$content = $this->replaceSectionByForm($content);
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

	//-------------------------------------------------------------------------- replaceSectionByForm
	/**
	 * @param $content string
	 * @return string
	 */
	protected function replaceSectionByForm($content)
	{
		if (($outside_i = strpos($content, '<section')) !== false) {
			$inside_j = strrpos($content, '</section>', $outside_i);
			if (strpos(substr($content, $outside_i, $inside_j - $outside_i), '<input') !== false) {
				$short_class   = Namespaces::shortClassName(get_class(reset($this->objects)));
				$short_form_id = strtolower($short_class) . '_edit';
				$this->form_id = $short_form_id . '_' . $this->nextFormCounter();
				$action        = SL . $short_class . '/write';
				$inside_i      = strpos($content, '>', $outside_i) + 1;
				$attributes    = ' action=' . DQ . $action . DQ
					. substr($content, $outside_i + 8, $inside_i - $outside_i - 9)
					. ' enctype="multipart/form-data"'
					. ' method="post"'
					. ' name=' . DQ . $this->form_id . DQ
					. ' target="#messages"';
				$content = '<form' . $attributes . '>' . substr($content, $inside_i, $inside_j - $inside_i) . '</form>';
			}
		}
		return $content;
	}

}
