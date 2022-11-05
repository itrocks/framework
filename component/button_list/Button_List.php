<?php
namespace ITRocks\Framework\Component;

use ITRocks\Framework\Component\Button_List\Button;
use ITRocks\Framework\View\Html\Dom\List_\Item;
use ITRocks\Framework\View\Html\Dom\List_\Unordered;

/**
 * Class Button_List
 */
class Button_List
{

	//-------------------------------------------------------------------------------------- $buttons
	/**
	 * @var Button[]
	 */
	public array $buttons = [];

	//----------------------------------------------------------------------------------------- $list
	/**
	 * @var Unordered
	 */
	public Unordered $list;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * Button_List constructor.
	 *
	 * @param $buttons Button[]
	 */
	public function __construct(array $buttons = [])
	{
		$button_items = [];
		foreach ($buttons as $button) {
			$button_items[] = new Item(strval($button));
		}
		$this->list = new Unordered($button_items);
		$this->list->addClass('button-list');
	}

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string
	 */
	public function __toString() : string
	{
		return $this->list;
	}

	//------------------------------------------------------------------------------------ setButtons
	/**
	 * @param $buttons Button[]
	 */
	public function setButtons(array $buttons = []) : void
	{
		$button_items = [];
		foreach ($buttons as $button) {
			$button_items[] =  new Item(strval($button));
		}
		$this->list->items = $button_items;
	}

}
