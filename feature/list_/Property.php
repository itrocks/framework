<?php
namespace ITRocks\Framework\Feature\List_;

use ITRocks\Framework\Feature\List_Setting;
use ITRocks\Framework\Locale;
use ITRocks\Framework\Locale\Loc;
use ITRocks\Framework\Reflection\Reflection_Property_Value;
use ITRocks\Framework\Reflection\Type;

/**
 * List property (ie visible column)
 *
 * All data concerning a property=column is here
 *
 * @feature Controller
 */
class Property extends List_Setting\Property
{

	//-------------------------------------------------------------------------------------- $reverse
	/**
	 * @var boolean
	 */
	public $reverse = false;

	//--------------------------------------------------------------------------------------- $search
	/**
	 * @var Reflection_Property_Value
	 */
	public $search;

	//----------------------------------------------------------------------------------------- $sort
	/**
	 * @var integer 1..n if sort : then is the sort position, null if do not sort
	 */
	public $sort;

	//----------------------------------------------------------------------------------------- $type
	/**
	 * The property stored into $search may have a changed (simplified) type, to force search input
	 * Here is the original property type
	 *
	 * @var Type
	 */
	public $type;

	//----------------------------------------------------------------------------------- htmlReverse
	/**
	 * @return string @values reverse, sort
	 */
	public function htmlReverse()
	{
		return ($this->reverse ? 'reverse' : '');
	}

	//---------------------------------------------------------------------------------- htmlSortLink
	/**
	 * Returns 'reverse' if current sort is not reverse : then a click send you to reverse.
	 * Returns 'sort' if current sort is reverse : then a click send you to non-reverse.
	 *
	 * @return string @values reverse, sort
	 */
	public function htmlSortLink()
	{
		return ($this->sort == 1)
			? ($this->reverse ? 'sort' : 'reverse')
			: ($this->reverse ? 'reverse' : 'sort');
	}

	//-------------------------------------------------------------------------------------------- tr
	/**
	 * Translate
	 *
	 * @param $text string
	 * @return string
	 */
	protected function tr($text)
	{
		$context = $this->search->getFinalClass()->getName();
		return Locale::current() ? Loc::tr($text, $context) : $text;
	}

}
