<?php
namespace ITRocks\Framework\View\Html\Dom;

use ITRocks\Framework\Locale\Loc;

/**
 * Class Content_Title
 */
class Content_Title extends Element
{

	//---------------------------------------------------------------------------------- ELEMENT_NAME
	const ELEMENT_NAME = 'content_title';

	//----------------------------------------------------------------------------------- __construct
	/**
	 * Content_Title constructor.
	 *
	 * @param $value string
	 * @param $additional_classes array
	 */
	public function __construct(string $value = '', array $additional_classes = [])
	{
		parent::__construct('h3');
		$this->setContent(Loc::tr($value));
		$this->addClass('content-title');
		foreach ($additional_classes as $class) {
			$this->addClass($class);
		}
	}

}
