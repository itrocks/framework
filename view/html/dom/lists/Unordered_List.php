<?php
namespace ITRocks\Framework\View\Html\Dom\Lists;

use ITRocks\Framework\View\Html\Dom\Element;

/**
 * A DOM element class for HTML tables <table>
 */
class Unordered_List extends Element
{

	//---------------------------------------------------------------------------------------- $items
	/**
	 * @var List_Item[]
	 */
	public $items = [];

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $items List_Item[]
	 */
	public function __construct(array $items = null)
	{
		parent::__construct('ul');
		if (isset($items)) $this->items = $items;
	}

	//--------------------------------------------------------------------------------------- addItem
	/**
	 * @param $item string
	 */
	public function addItem($item)
	{
		$this->items[] = $item;
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
				$content .= new List_Item($item);
			}
			$this->setContent($content);
		}
		return $content;
	}

}
