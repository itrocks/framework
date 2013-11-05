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
				$this->setContent(Names::propertyToDisplay($value));
			}
			$this->setAttribute("value", $value);
		}
		if (isset($caption)) {
			if (!isset($value)) {
				$this->setAttribute("value", $value);
			}
			$this->setContent($caption);
		}
	}

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string
	 */
	public function __toString()
	{
		$content = $this->getContent();
		if (!strlen($content)) {
			$this->setContent(" ");
			$back_content = true;
		}
		elseif ($content == ($value = $this->getAttribute("value"))) {
			$this->removeAttribute($value);
			$back = true;
		}
		$string = parent::__toString();
		if (isset($back_content)) {
			$this->setContent($content);
		}
		elseif (isset($back)) {
			$this->setAttribute("value", $value);
		}
		return $string;
	}

}
