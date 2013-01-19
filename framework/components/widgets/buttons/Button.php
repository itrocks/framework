<?php
namespace SAF\Framework;

class Button
{

	//-------------------------------------------------------------------------------------- $caption
	/**
	 * Button caption
	 * @var string
	 */
	public $caption;

	//---------------------------------------------------------------------------------------- $class
	/**
	 * More classes for the button
	 * This is css style, ie "pressed" or "ifedit press"
	 * @var string
	 */
	public $class;

	//-------------------------------------------------------------------------------------- $feature
	/**
	 * Button feature
	 * @var string
	 */
	public $feature;

	//----------------------------------------------------------------------------------------- $link
	/**
	 * Button link
	 * @var string
	 */
	public $link;

	//--------------------------------------------------------------------------------------- $target
	/**
	 * Target for the link
	 * Name of a targetted window / iframe
	 * If starts with "#", target is the identifier of a DOM element in the page (for ajax call)
	 * @var string
	 */
	public $target;

	//----------------------------------------------------------------------------------- __construct
	public function __construct($caption = null, $link = null, $feature = null, $options = array())
	{
		if ($caption != null) $this->caption = $caption;
		if ($link    != null) $this->link    = $link;
		if ($feature != null) $this->feature = $feature;
		if (!is_array($options)) {
			$options = array($options);
		}
		foreach ($options as $key => $option) {
			if (($key === "class") || is_numeric($key) && substr($option, 0, 1) == ".") {
				$this->class .= (isset($this->class) ? " " : "") . substr($option, 1);
			}
			if (($key === "target") || is_numeric($key) && substr($option, 0, 1) == "#") {
				$this->target = $option;
			}
		}
	}

}
