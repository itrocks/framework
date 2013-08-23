<?php
namespace SAF\Framework;

/**
 * A DOM element class for HTML form select inputs option <option>...</option>
 */
class Html_Option extends Dom_Element
{

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $value   string
	 * @param $caption string
	 */
	public function __construct($value = null, $caption = null)
	{
		parent::__construct("option", true);
		if (isset($value)) {
			if (!isset($caption)) {
				$caption = $value;
			}
			elseif ($caption !== $value) {
				$this->setAttribute("value", $value);
			}
		}
		if (isset($caption)) {
			$this->setContent($caption);
		}
	}

}
