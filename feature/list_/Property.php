<?php
namespace ITRocks\Framework\Feature\List_;

use ITRocks\Framework\Feature\List_Setting;
use ITRocks\Framework\Locale;
use ITRocks\Framework\Locale\Loc;
use ITRocks\Framework\Reflection\Reflection_Property;
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

	//--------------------------------------------------------------------------------- REVERSE, SORT
	const REVERSE = 'reverse';
	const SORT    = 'sort';

	//-------------------------------------------------------------------------------------- $reverse
	/**
	 * @var boolean
	 */
	public bool $reverse = false;

	//--------------------------------------------------------------------------------------- $search
	/**
	 * @var Reflection_Property|Reflection_Property_Value
	 */
	public Reflection_Property|Reflection_Property_Value $search;

	//----------------------------------------------------------------------------------------- $sort
	/**
	 * @var integer 1..n if sort : then is the sort position, 0 if do not sort
	 */
	public int $sort = 0;

	//----------------------------------------------------------------------------------------- $type
	/**
	 * The property stored into $search may have a changed (simplified) type, to force search input
	 * Here is the original property type
	 *
	 * @var Type
	 */
	public Type $type;

	//----------------------------------------------------------------------------------- htmlReverse
	/**
	 * @noinspection PhpUnused head.html
	 * @return string @values reverse,
	 */
	public function htmlReverse() : string
	{
		return ($this->reverse ? self::REVERSE : '');
	}

	//---------------------------------------------------------------------------------- htmlSortLink
	/**
	 * Returns 'reverse' if current sort is not reverse : then a click send you to reverse.
	 * Returns 'sort' if current sort is reverse : then a click send you to non-reverse.
	 *
	 * @noinspection PhpUnused head.html
	 * @return string @values self::const
	 */
	public function htmlSortLink() : string
	{
		return ($this->sort === 1)
			? ($this->reverse ? self::SORT : self::REVERSE)
			: ($this->reverse ? self::REVERSE : self::SORT);
	}

	//-------------------------------------------------------------------------------------------- tr
	/**
	 * Translate
	 *
	 * @param $text string
	 * @return string
	 */
	protected function tr(string $text) : string
	{
		$context = $this->search->getFinalClass()->getName();
		return Locale::current() ? Loc::tr($text, $context) : $text;
	}

}
