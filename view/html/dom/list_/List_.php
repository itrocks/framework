<?php
namespace ITRocks\Framework\View\Html\Dom;

use ITRocks\Framework\View\Html\Dom\List_\Item;

/**
 * A DOM element class for HTML tables <table>
 */
abstract class List_ extends Element
{

	//---------------------------------------------------------------------------------------- $items
	/**
	 * @var Item[]
	 */
	public $items = [];

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $name  string
	 * @param $items Item[]
	 */
	public function __construct($name, array $items = null)
	{
		parent::__construct($name);
		if (isset($items)) $this->items = $items;
	}

	//--------------------------------------------------------------------------------------- addItem
	/**
	 * @param $item Item|string
	 */
	public function addItem($item)
	{
		$this->items[] = ($item instanceof Item) ? $item : new Item($item);
		$this->setContent(null);
	}

	//------------------------------------------------------------------------------------ getContent
	/**
	 * @return string
	 */
	public function getContent()
	{
		$content = parent::getContent();
		if (!isset($content)) {
			$items = $this->items;
			asort($items);
			$content = '';
			foreach ($items as $item) {
				$content .= $item;
			}
			$this->setContent($content);
		}
		return $content;
	}

}
