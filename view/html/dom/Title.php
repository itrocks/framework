<?php
namespace ITRocks\Framework\View\Html\Dom;

/**
 * Class Title
 */
class Title extends Element
{

	//----------------------------------------------------------------------------------- __construct
	/**
	 * Title constructor.
	 *
	 * @param $value   string
	 * @param $level   integer
	 * @param $end_tag boolean
	 */
	public function __construct(string $value = '', int $level = 1, bool $end_tag = true)
	{
		parent::__construct('h' . ($level >= 1 && $level <= 6 ? $level : 1), $end_tag);
		$this->setContent($value);
	}

}
