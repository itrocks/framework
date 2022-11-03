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
use ITRocks\Framework\Tools\Names;
use ITRocks\Framework\View;

/**
 * An HMI button
 */
class Button
{

	//---------------------------------------------------------------------------------------- CLASS_
	const CLASS_ = 'class';

	//----------------------------------------------------------------------------------------- COLOR
	const COLOR = 'color';

	//------------------------------------------------------------------------------------------ DATA
	const DATA = 'data';

	//------------------------------------------------------------------------------------------ HINT
	const HINT = 'hint';

	//---------------------------------------------------------------------------------------- OBJECT
	/**
	 * The button represents an object you can drag-and-drop (e.g. to trashcan)
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
	public string $cancel_label = '';

	//-------------------------------------------------------------------------------------- $caption
	/**
	 * Button caption
	 *
	 * @var string
	 */
	public string $caption = '';

	//---------------------------------------------------------------------------------------- $class
	/**
	 * More classes for the button
	 * This is css style, eg 'pressed' or 'if-edit-press'
	 *
	 * @getter
	 * @user hidden
	 * @var string
	 */
	public string $class = '';

	//----------------------------------------------------------------------------------------- $code
	/**
	 * Some natural / PHP code to apply to the object before the action is executed
	 *
	 * @link Object
	 * @multiline
	 * @output string
	 * @var ?Code
	 */
	public ?Code $code = null;

	//---------------------------------------------------------------------------------------- $color
	/**
	 * The color of the button
	 *
	 * @user invisible
	 * @var ?Tools\Color
	 */
	public ?Tools\Color $color;

	//----------------------------------------------------------------------------------- $conditions
	/**
	 * The button will be displayed only if these conditions are ok on the context object
	 *
	 * @max_length 60000
	 * @multiline
	 * @var string
	 */
	public string $conditions = '';

	//-------------------------------------------------------------------------------- $confirm_label
	/**
	 * Label of the confirm button in the confirm dialog
	 *
	 * @var string
	 */
	public string $confirm_label = '';

	//------------------------------------------------------------------------------ $confirm_message
	/**
	 * Message to display to user in the confirm dialog
	 *
	 * @var string
	 */
	public string $confirm_message = '';

	//----------------------------------------------------------------------------------------- $data
	/**
	 * @var float[]|integer[]|string[]
	 */
	public array $data = [];

	//---------------------------------------------------------------------------------- $data_object
	/**
	 * Object data linked to the button (optional)
	 *
	 * @var mixed
	 */
	public mixed $data_object = null;

	//-------------------------------------------------------------------------------------- $feature
	/**
	 * Button feature
	 *
	 * @user hidden
	 * @var string
	 */
	public string $feature = '';

	//----------------------------------------------------------------------------------------- $hint
	/**
	 * A hint for the link
	 *
	 * @var string
	 */
	public string $hint = '';

	//----------------------------------------------------------------------------------------- $link
	/**
	 * Button link
	 *
	 * @getter
	 * @user invisible
	 * @var ?string
	 */
	public ?string $link = null;

	//--------------------------------------------------------------------------------------- $object
	/**
	 * @user invisible
	 * @var ?object
	 */
	public ?object $object = null;

	//---------------------------------------------------------------------------------- $sub_buttons
	/**
	 * A button can be linked to a collection of sub-buttons
	 *
	 * @link Collection
	 * @user invisible
	 * @var Button[]
	 */
	public array $sub_buttons = [];

	//--------------------------------------------------------------------------------------- $target
	/**
	 * Target for the link
	 * Name of a targeted window / iframe
	 * If starts with '#', target is the identifier of a DOM element in the page (for ajax call)
	 *
	 * @user hidden
	 * @var string
	 */
	public string $target = '#main';

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $caption string|null Displayed caption
	 * @param $link    string|null Link URL
	 * @param $feature string|null Feature name
	 * @param $options array|string Single or multiple options
	 */
	public function __construct(
		string $caption = null, string $link = null, string $feature = null, array|string $options = []
	) {
		if (isset($caption)) $this->caption = $caption;
		if (isset($link))    $this->link    = $link;
		if (isset($feature)) $this->feature = $feature;
		if (!is_array($options)) {
			$options = [$options];
		}
		foreach ($options as $key => $option) {
			$this->addOption($option, $key);
		}

		if ($this->feature === Feature::F_CONFIRM) {
			$this->cancel_label  = $this->cancel_label  ?: Loc::tr('Cancel');
			$this->confirm_label = $this->confirm_label ?: Loc::tr('Confirm');

			$this->class .= (strlen($this->class) ? SP : '') . 'confirm';
		}

		if (!isset($this->color)) {
			$this->color = new Tools\Color(Tools\Color::BLUE);
		}
	}

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string
	 */
	public function __toString() : string
	{
		return $this->caption;
	}

	//------------------------------------------------------------------------------------- addOption
	/**
	 * Add an option to the button
	 *
	 * @param $option array|object|string Single option
	 * @param $key    string Key name of the option
	 */
	public function addOption(array|object|string $option, string $key = '')
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
			$this->sub_buttons = array_merge($this->sub_buttons, $option);
		}
		elseif (($key === self::CLASS_) || (is_numeric($key) && str_starts_with($option, DOT))) {
			$this->class .= (isset($this->class) ? SP : '') . str_replace(
				'_', '-',
				Names::methodToProperty(is_numeric($key) ? substr($option, 1) : $option)
			);
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
		elseif (($key === View::TARGET) || (is_numeric($key) && str_starts_with($option, '#'))) {
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
	public function conditionsApplyTo(object $object) : bool
	{
		return (new Code($this->conditions))->execute($object, true);
	}

	//-------------------------------------------------------------------------------------- getClass
	/**
	 * @return string
	 */
	protected function getClass() : string
	{
		return $this->class ?: str_replace('_', '-', Names::methodToProperty($this->feature));
	}

	//--------------------------------------------------------------------------------------- getLink
	/**
	 * @noinspection PhpUnused @getter
	 * @return ?string
	 */
	protected function getLink() : ?string
	{
		if (isset($this->object) && !isset($this->link)) {
			$parameters = [];
			if ($this->code && $this->code->source) {
				$parameters[] = $this->code;
			}
			$this->link = View::link($this->object, $this->feature, $parameters);
		}
		return $this->link;
	}

	//---------------------------------------------------------------------------------- insertBefore
	/**
	 * @param $buttons        Button[]
	 * @param $button         Button
	 * @param $before_feature string if empty, $button will be appended to buttons
	 * @param $feature        string if empty, $button->feature will be used
	 */
	public static function insertBefore(
		array &$buttons, Button $button, string $before_feature = '', string $feature = ''
	) {
		if (!isset($buttons[$before_feature])) {
			$buttons[$feature ?: $button->feature] = $button;
			return;
		}
		$new_buttons = [];
		foreach ($buttons as $key => $button) {
			if ($key === $before_feature) {
				$new_buttons[$feature ?: $button->feature] = $button;
			}
			$new_buttons[$key] = $button;
		}
		$buttons = $new_buttons;
	}

	//------------------------------------------------------------------------------ setObjectContext
	/**
	 * @param $object object
	 */
	public function setObjectContext(object $object)
	{
		$this->object = $object;
		// insert object identifier between the class path and the feature, if missing and if
		// object class matches the button link
		if (Dao::getObjectIdentifier($object)) {
			$uri = new Uri($this->link);
			if (
				isA($object, $uri->controller_name)
				&& !$uri->parameters->getRawParameter($uri->controller_name)
			) {
				$uri->parameters->getMainObject($object);
				$uri->parameters->shift();
				if ($uri->feature_name === Feature::F_ADD) {
					$uri->feature_name = null;
				}
				$this->link = View::link($object, $uri->feature_name, $uri->parameters->getRawParameters());
			}
		}
	}

}
