<?php
namespace SAF\Framework\Widget;

use SAF\Framework\Tools\Color;
use SAF\Framework\View;

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
	 * @var string
	 */
	public $class;

	//---------------------------------------------------------------------------------------- $color
	/**
	 * The color of the button
	 *
	 * @var Color
	 */
	public $color;

	//-------------------------------------------------------------------------------------- $feature
	/**
	 * Button feature
	 *
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
	 * @var string
	 */
	public $link;

	//---------------------------------------------------------------------------------- $sub_buttons
	/**
	 * A button can be linked to a collection of sub-buttons
	 *
	 * @link Collection
	 * @var Button[]
	 */
	public $sub_buttons;

	//--------------------------------------------------------------------------------------- $target
	/**
	 * Target for the link
	 * Name of a targeted window / iframe
	 * If starts with '#', target is the identifier of a DOM element in the page (for ajax call)
	 *
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
				$this->target = $option;
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

}
