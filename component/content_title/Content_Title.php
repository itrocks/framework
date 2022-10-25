<?php
namespace ITRocks\Framework\Component;

use ITRocks\Framework\Locale\Loc;
use ITRocks\Framework\View\Html\Dom\Title;

/**
 * Class Content_Title
 */
class Content_Title
{

	//-------------------------------------------------------------------------------------- $element
	/**
	 * @var Title
	 */
	private Title $element;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * Content_Title constructor.
	 *
	 * @param $value              string
	 * @param $additional_classes array
	 * @param $translate_value    boolean
	 */
	public function __construct(
		string $value = '', array $additional_classes = [], bool $translate_value = true
	) {
		$this->element = new Title($translate_value ? Loc::tr($value) : $value, 3);
		$this->element->addClass('content-title');
		foreach ($additional_classes as $class) {
			$this->element->addClass($class);
		}
	}

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string
	 */
	public function __toString() : string
	{
		return strval($this->element);
	}

}
