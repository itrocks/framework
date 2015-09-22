<?php
namespace SAF\Framework\Widget;

use SAF\Framework\Builder;
use SAF\Framework\Controller\Feature;
use SAF\Framework\Controller\Target;
use SAF\Framework\Controller\Uri;
use SAF\Framework\Dao;
use SAF\Framework\Tools\Color;
use SAF\Framework\View;
use SAF\Framework\Widget\Button\Code;

/**
 * An HMI button
 */
class Button
{

	//------------------------------------------------------------------- Button option key constants
	const COLOR       = 'color';
	const HINT        = 'hint';
	const SUB_BUTTONS = 'sub_buttons';

	//-------------------------------------------------------------------------------------- $caption
	/**
	 * Button caption
	 *
	 * @var string
	 */
	public $caption;

	//---------------------------------------------------------------------------------------- $class
	/**
	 * More classes for the button
	 * This is css style, eg 'pressed' or 'if-edit-press'
	 *
	 * @user hidden
	 * @var string
	 */
	public $class;

	//----------------------------------------------------------------------------------------- $code
	/**
	 * Some natural / PHP code to apply to the object before the action is executed
	 *
	 * @integrated block
	 * @link Object
	 * @var Code
	 */
	public $code;

	//---------------------------------------------------------------------------------------- $color
	/**
	 * The color of the button
	 *
	 * @user invisible
	 * @var Color
	 */
	public $color;

	//-------------------------------------------------------------------------------------- $feature
	/**
	 * Button feature
	 *
	 * @user hidden
	 * @var string
	 */
	public $feature;

	//----------------------------------------------------------------------------------------- $hint
	/**
	 * A hint for the link
	 *
	 * @var string
	 */
	public $hint;

	//----------------------------------------------------------------------------------------- $link
	/**
	 * Button link
	 *
	 * @getter
	 * @user invisible
	 * @var string
	 */
	public $link;

	//--------------------------------------------------------------------------------------- $object
	/**
	 * @user invisible
	 * @var object
	 */
	public $object;

	//---------------------------------------------------------------------------------- $sub_buttons
	/**
	 * A button can be linked to a collection of sub-buttons
	 *
	 * @link Collection
	 * @user invisible
	 * @var Button[]
	 */
	public $sub_buttons;

	//--------------------------------------------------------------------------------------- $target
	/**
	 * Target for the link
	 * Name of a targeted window / iframe
	 * If starts with '#', target is the identifier of a DOM element in the page (for ajax call)
	 *
	 * @user hidden
	 * @var string
	 */
	public $target = '#main';

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $caption string Displayed caption
	 * @param $link    string Link URL
	 * @param $feature string Feature name
	 * @param $options array|string Single or multiple options
	 */
	public function __construct($caption = null, $link = null, $feature = null, $options = [])
	{
		if ($caption != null) $this->caption = $caption;
		if ($link    != null) $this->link    = $link;
		if ($feature != null) $this->feature = $feature;
		if (!is_array($options)) {
			$options = [$options];
		}
		foreach ($options as $key => $option) {
			if ($option instanceof Color) {
				$this->color = $option;
			}
			elseif ($key === self::COLOR) {
				$this->color = new Color($option);
			}
			elseif ($option instanceof Button) {
				$this->sub_buttons[] = $option;
			}
			elseif ($key === self::SUB_BUTTONS) {
				$this->sub_buttons = is_array($this->sub_buttons)
					? array_merge($this->sub_buttons, $option)
					: $option;
			}
			elseif (($key === self::CLASS) || (is_numeric($key) && (substr($option, 0, 1) == DOT))) {
				$this->class .= (isset($this->class) ? SP : '') . substr($option, 1);
			}
			elseif ($key === self::HINT) {
				$this->hint = $option;
			}
			elseif (($key === View::TARGET) || (is_numeric($key) && substr($option, 0, 1) == '#')) {
				$this->target = ($option === Target::NONE) ? null : $option;
			}
		}
		if (!isset($this->color)) {
			$this->color = new Color(Color::BLUE);
		}
	}

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string
	 */
	public function __toString()
	{
		return strval($this->caption);
	}

	//--------------------------------------------------------------------------------------- getLink
	/** @noinspection PhpUnusedPrivateMethodInspection @getter */
	/**
	 * @return string
	 */
	private function getLink()
	{
		if (!isset($this->link)) {
			$parameters = [];
			if ($this->code->source) {
				$parameters[] = $this->code;
			}
			$this->link = View::link($this->object, $this->feature, $parameters);
		}
		return $this->link;
	}

	//------------------------------------------------------------------------------ setObjectContext
	/**
	 * @param $object object
	 */
	public function setObjectContext($object)
	{
		$this->object = $object;
		// insert object identifier between the class path and the feature, if missing and if
		// object class matches the button link
		if ($identifier = Dao::getObjectIdentifier($object)) {
			$uri = new Uri($this->link);
			if (
				isA($object, $uri->controller_name)
				&& !$uri->parameters->getRawParameter($uri->controller_name)
			) {
				$uri->parameters->getMainObject($object);
				$uri->parameters->shift();
				if ($uri->feature_name == Feature::F_ADD) {
					$uri->feature_name = null;
				}
				$this->link = View::link($object, $uri->feature_name, $uri->parameters->getRawParameters());
			}
		}
	}

}
