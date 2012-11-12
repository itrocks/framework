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

	//----------------------------------------------------------------------------------- __construct
	public function __construct($caption, $link, $feature = null, $options = array())
	{
		$this->caption = $caption;
		$this->feature = $feature;
		$this->link    = $link;
		if (!is_array($options)) {
			$options = array($options);
		}
		foreach ($options as $option) {
			if (substr($option, 0, 1) == ".") {
				$this->class .= (isset($this->class) ? " " : "") . substr($option, 1);
			}
		}
	}

}
