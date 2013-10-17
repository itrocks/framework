<?php
namespace SAF\Framework;

/**
 * An HMI button
 */
class Button
{

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
	 * This is css style, ie "pressed" or "ifedit press"
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

	//----------------------------------------------------------------------------------------- $link
	/**
	 * Button link
	 *
	 * @var string
	 */
	public $link;

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
	 * Name of a targetted window / iframe
	 * If starts with "#", target is the identifier of a DOM element in the page (for ajax call)
	 *
	 * @var string
	 */
	public $target;

	//---------------------------------------------------------------------------------------- $title
	/**
	 * A title for the link
	 *
	 * @var string
	 */
	public $title = "";

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $caption string Displayed caption
	 * @param $link    string Link URL
	 * @param $feature string Feature name
	 * @param $options array|string Single or multiple options
	 */
	public function __construct($caption = null, $link = null, $feature = null, $options = array())
	{
		if ($caption != null) $this->caption = $caption;
		if ($link    != null) $this->link    = $link;
		if ($feature != null) $this->feature = $feature;
		if (!is_array($options)) {
			$options = array($options);
		}
		foreach ($options as $key => $option) {
			if ($option instanceof Color) {
				$this->color = $option;
			}
			elseif ($key === "color") {
				$this->color = Color::of($option);
			}
			elseif ($option instanceof Button) {
				$this->sub_buttons[] = $option;
			}
			elseif ($key === "sub_buttons") {
				$this->sub_buttons = is_array($this->sub_buttons)
					? array_merge($this->sub_buttons, $option)
					: $option;
			}
			elseif (($key === "class") || (is_numeric($key) && (substr($option, 0, 1) == "."))) {
				$this->class .= (isset($this->class) ? " " : "") . substr($option, 1);
			}
			elseif (($key === "target") || (is_numeric($key) && substr($option, 0, 1) == "#")) {
				$this->target = $option;
			}
			elseif ($key === "title") {
				$this->title = $option;
			}
		}
		if (!isset($this->color)) {
			$this->color = Color::of("blue");
		}
	}

}
