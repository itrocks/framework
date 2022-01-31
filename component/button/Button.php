<?php
namespace ITRocks\Framework\Component;

use ITRocks\Framework\Component\Button\Align;
use ITRocks\Framework\Component\Button\Code;
use ITRocks\Framework\Controller\Feature;
use ITRocks\Framework\Controller\Target;
use ITRocks\Framework\Controller\Uri;
use ITRocks\Framework\Dao;
use ITRocks\Framework\Feature\Confirm\Confirm;
use ITRocks\Framework\Locale\Loc;
use ITRocks\Framework\Tools;
use ITRocks\Framework\View;

/**
 * An HMI button
 */
class Button
{

	//----------------------------------------------------------------------------------------- COLOR
	const COLOR = 'color';

	//------------------------------------------------------------------------------------------ DATA
	const DATA = 'data';

	//------------------------------------------------------------------------------------------ HINT
	const HINT = 'hint';

	//---------------------------------------------------------------------------------------- OBJECT
	/**
	 * The button represents an object you can drag'n'drop (eg to trashcan)
	 */
	const OBJECT = 'object';

	//----------------------------------------------------------------------------------- SUB_BUTTONS
	const SUB_BUTTONS = 'sub_buttons';

	//---------------------------------------------------------------------------------------- $align
	/**
	 * @see Align
	 * @values Align::const
	 * @var string
	 */
	public string $align = '';
	
	//--------------------------------------------------------------------------------- $cancel_label
	/**
	 * Label of the cancel button in the confirm dialog
	 *
	 * @var string
	 */
	public $cancel_label;

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
	 * @link Object
	 * @multiline
	 * @output string
	 * @var Code
	 */
	public $code;

	//---------------------------------------------------------------------------------------- $color
	/**
	 * The color of the button
	 *
	 * @user invisible
	 * @var Tools\Color
	 */
	public $color;

	//----------------------------------------------------------------------------------- $conditions
	/**
	 * The button will be displayed only if these conditions are ok on the context object
	 *
	 * @max_length 60000
	 * @multiline
	 * @var string
	 */
	public $conditions;

	//-------------------------------------------------------------------------------- $confirm_label
	/**
	 * Label of the confirm button in the confirm dialog
	 *
	 * @var string
	 */
	public $confirm_label;

	//------------------------------------------------------------------------------ $confirm_message
	/**
	 * Message to display to user in the confirm dialog
	 *
	 * @var string
	 */
	public $confirm_message;

	//----------------------------------------------------------------------------------------- $data
	/**
	 * @var float[]|integer[]|string[]
	 */
	public $data = [];

	//---------------------------------------------------------------------------------- $data_object
	/**
	 * Object data linked to the button (optional)
	 *
	 * @var integer
	 */
	public $data_object;

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
			$this->addOption($option, $key);
		}

		if ($this->feature == Feature::F_CONFIRM) {
			$this->cancel_label  = $this->cancel_label  ?: Loc::tr('Cancel');
			$this->confirm_label = $this->confirm_label ?: Loc::tr('Confirm');

			$this->class .= (isset($this->class) ? SP : '') . 'confirm';
		}

		if (!isset($this->color)) {
			$this->color = new Tools\Color(Tools\Color::BLUE);
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

	//------------------------------------------------------------------------------------- addOption
	/**
	 * Add an option to the button
	 *
	 * @param $option array|string Single option
	 * @param $key    string Key name of the option
	 */
	public function addOption($option, $key = '')
	{
		if ($option instanceof Tools\Color) {
			$this->color = $option;
		}
		elseif (in_array($option, [Align::CENTER, Align::LEFT, Align::RIGHT])) {
			$this->align = $option;
		}
		elseif ($key === self::COLOR) {
			$this->color = new Tools\Color($option);
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
			$this->class .= (isset($this->class) ? SP : '')
				. (is_numeric($key) ? substr($option, 1) : $option);
		}
		elseif ($key === self::DATA) {
			$this->data = array_merge($this->data, $option);
		}
		elseif ($key === self::HINT) {
			$this->hint = $option;
		}
		elseif ($key === self::OBJECT) {
			$this->data_object = $option;
		}
		elseif ($key === Confirm::CANCEL_LABEL) {
			$this->cancel_label = $option;
		}
		elseif ($key === Confirm::CONFIRM_LABEL) {
			$this->confirm_label = $option;
		}
		elseif ($key === Confirm::MESSAGE) {
			$this->confirm_message = $option;
		}
		elseif (($key === View::TARGET) || (is_numeric($key) && substr($option, 0, 1) == '#')) {
			$this->target = ($option === Target::NONE) ? null : $option;
		}
	}

	//----------------------------------------------------------------------------- conditionsApplyTo
	/**
	 * Returns true if the conditions apply to the context object
	 *
	 * @param $object object
	 * @return boolean
	 */
	public function conditionsApplyTo($object)
	{
		return (new Code($this->conditions))->execute($object, true);
	}

	//--------------------------------------------------------------------------------------- getLink
	/**
	 * @return string
	 */
	protected function getLink()
	{
		if (!isset($this->link)) {
			$parameters = [];
			if ($this->code && $this->code->source) {
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
