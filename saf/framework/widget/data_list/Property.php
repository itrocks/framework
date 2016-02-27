<?php
namespace SAF\Framework\Widget\Data_List;

use SAF\Framework\Locale;
use SAF\Framework\Locale\Loc;
use SAF\Framework\Reflection\Reflection_Property_Value;
use SAF\Framework\Tools\String;
use SAF\Framework\Widget\Data_List_Setting;

/**
 * Data list property (ie visible column)
 *
 * All data concerning a property=column is here
 */
class Property extends Data_List_Setting\Property
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
		return (($this->sort == 1) && !$this->reverse) ? 'reverse' : 'sort';
	}

	//------------------------------------------------------------------------------------ shortTitle
	/**
	 * @return string
	 */
	public function shortTitle()
	{
		return (new String($this->title()))->twoLast();
	}

	//----------------------------------------------------------------------------------------- title
	/**
	 * @return string
	 */
	public function title()
	{
		if (empty($this->display)) {
			$display = str_replace(
				'_', SP, ($locale = Locale::current()) ? Loc::tr($this->path) : $this->path
			);
		}
		else {
			$display = $this->display;
		}
		return $display;
	}

}
