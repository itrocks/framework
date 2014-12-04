<?php
namespace SAF\Framework\Widget\Edit;

use SAF\Framework\Builder;
use SAF\Framework\Dao;
use SAF\Framework\Reflection\Reflection_Property_Value;
use SAF\Framework\Tools\Namespaces;
use SAF\Framework\View\Html\Builder\Property;
use SAF\Framework\View\Html\Template;

/**
 * Html template that changes all properties values to form inputs
 */
class Html_Template extends Template
{

	//---------------------------------------------------------------------------------------- $cache
	/**
	 * Some caches ie to know if id where already defined, to increment some counters.
	 *
	 * @var array
	 */
	public $cache = [];

	//-------------------------------------------------------------------------------------- $form_id
	/**
	 * @var string
	 */
	private $form_id;

	//------------------------------------------------------------------------------------- getFormId
	/**
	 * @return string
	 */
	public function getFormId()
	{
		return strval($this->form_id);
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
				. $this->replaceSectionByForm(substr($content, $i, $j), $action)
				. substr($content, $j);
		}
		return parent::parseContainer($content);
	}

	//------------------------------------------------------------------------------ parseSingleValue
	/**
	 * Parse a variable / function / include and returns its return value
	 *
	 * @param $var_name  string can be an unique var or path.of.vars
	 * @return string var value after reading value / executing specs (can be an object)
	 */
	protected function parseSingleValue($var_name)
	{
		$property = reset($this->objects);
		if (($property instanceof Reflection_Property_Value) && ($var_name == 'value')) {
			$value = $property->getType()->isBoolean()
				? $property->value()
				: parent::parseSingleValue($var_name, false);
			if (
				($preprop = lLastParse($property->pathAsField(), '[', 1, false))
				&& (
					!isset($this->cache['parsed_id'])
					|| !isset($this->cache['parsed_id'][$this->getFormId()])
					|| !isset($this->cache['parsed_id'][$this->getFormId()][$preprop])
				)
			) {
				$this->cache['parsed_id'][$this->getFormId()][$preprop] = true;
				if ($property instanceof Reflection_Property_Value) {
					$parent_object = $property->getObject();
					$id = isset($parent_object) ? Dao::getObjectIdentifier($parent_object) : null;
					$id_value = (new Html_Builder_Type('id', null, $id, $preprop))->build();
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
				$value = isset($value) ? strval($value) : null;
				$id_value = '';
			}
			if (
				($builder = $property->getAnnotation('widget')->value)
				&& is_a($builder, Property::class, true)
			) {
				$builder = Builder::create($builder, [$property, $value, $this]);
				/** @var $builder Property */
				$builder->parameters['edit'] = ' edit';
				$value = $builder->buildHtml();
			}
			else {
				$value = $id_value
					. (new Html_Builder_Property($property, $value))->setTemplate($this)->build();
			}
		}
		else {
			$value = parent::parseSingleValue($var_name);
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
		$i = strpos($content, '<section');
		$j = strpos($content, '>', $i) + 1;
		$attributes = ' action=' . DQ . $action . DQ
			. substr($content, $i + 8, $j - $i - 9)
			. ' enctype="multipart/form-data"'
			. ' method="post"'
			. ' name=' . DQ . $this->form_id . DQ
			. ' target="#messages"';
		$i = $j;
		$j = strrpos($content, '</section>', $i);
		return '<form' . $attributes . '>'
			. substr($content, $i, $j - $i)
			. '</form>';
	}

}
