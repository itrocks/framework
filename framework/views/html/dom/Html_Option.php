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
			$string = parent::__toString();
			$this->setContent($content);
		}
		elseif ($content == ($value = $this->getAttribute("value"))) {
			$this->removeAttribute("value");
			$string = parent::__toString();
			$this->setAttribute("value", $value);
		}
		else {
			$string = parent::__toString();
		}
		return $string;
	}

	//------------------------------------------------------------------------------------ setContent
	/**
	 * @param $content string
	 * @todo This is a patch to make Html_Option::setContent work. Remove this when it will work
	 */
	public function setContent($content)
	{
		parent::setContent($content);
	}

}
