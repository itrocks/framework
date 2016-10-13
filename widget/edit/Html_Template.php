<?php
namespace SAF\Framework\Widget\Edit;

use SAF\Framework\Builder;
use SAF\Framework\Controller\Feature;
use SAF\Framework\Controller\Parameter;
use SAF\Framework\Dao;
use SAF\Framework\Reflection\Annotation\Property\User_Annotation;
use SAF\Framework\Reflection\Reflection_Property_Value;
use SAF\Framework\Tools\Namespaces;
use SAF\Framework\View\Html\Builder\Property;
use SAF\Framework\View\Html\Builder\Value_Widget;
use SAF\Framework\View\Html\Template;
use SAF\Framework\View\Html\Template\Loop;

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
	public function nextCounter($field_name, $increment = true)
	{
		$form = $this->getFormId();
		$counter = isset($this->cache[self::COUNTER])
			? $this->cache[self::COUNTER]
			: [];
		if (!isset($counter[$form])) {
			$counter[$form] = [];
		}
		$count = isset($counter[$form][$field_name]) ? $counter[$form][$field_name] + $increment : 0;
		if ($increment !== null) {
			$counter[$form][$field_name] = $count;
			$this->cache[self::COUNTER] = $counter;
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
		$i = strpos($content, '<!--BEGIN-->');
		if ($i !== false) {
			$i += 12;
			$j = strrpos($content, '<!--END-->', $i);
			$short_class = Namespaces::shortClassName(get_class(reset($this->objects)));
			$short_form_id = strtolower($short_class) . '_edit';
			$this->form_id = $short_form_id . '_' . $this->nextFormCounter();
			$action = SL . $short_class . '/write';
			$content = substr($content, 0, $i)
				. $this->replaceSectionByForm(substr($content, $i, $j - $i), $action)
				. substr($content, $j);
		}
		return parent::parseContainer($content);
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
		if (($property instanceof Reflection_Property_Value) && ($property_name == 'value')) {
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
					($preprop = lLastParse($property->pathAsField(), '[', 1, false))
					&& (
						!isset($this->cache[self::PARSED_ID])
						|| !isset($this->cache[self::PARSED_ID][$this->getFormId()])
						|| !isset($this->cache[self::PARSED_ID][$this->getFormId()][$preprop])
					)
				) {
					$this->cache[self::PARSED_ID][$this->getFormId()][$preprop] = true;
					if ($property instanceof Reflection_Property_Value) {
						$parent_object = $property->getObject();
						$id       = isset($parent_object) ? Dao::getObjectIdentifier($parent_object) : null;
						$html_builder_type = new Html_Builder_Type('id', null, $id, $preprop);
						$id_value = $html_builder_type->setTemplate($this)->build();
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
	 * @param $action string
	 * @return string
	 */
	protected function replaceSectionByForm($content, $action)
	{
		if (($i = strpos($content, '<section')) !== false) {
			$j = strpos($content, '>', $i) + 1;
			$attributes = ' action=' . DQ . $action . DQ
				. substr($content, $i + 8, $j - $i - 9)
				. ' enctype="multipart/form-data"'
				. ' method="post"'
				. ' name=' . DQ . $this->form_id . DQ
				. ' target="#messages"';
			$i = $j;
			$j = strrpos($content, '</section>', $i);
			$content = '<form' . $attributes . '>' . substr($content, $i, $j - $i) . '</form>';
		}
		return $content;
	}

}
