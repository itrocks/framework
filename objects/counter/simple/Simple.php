<?php
namespace ITRocks\Framework\Objects\Counter;

use ITRocks\Framework\Objects\Counter;

/**
 * Simplified counter, for and easier configuration
 *
 * @after_read formatToSimple
 * @before_write simpleToFormat
 * @business false
 * @display_order identifier, last_update, last_value, format, prefix, yearly_reset,
 *                year_with_4_characters, complete_with_zeros, documents_count
 * @feature Easy incremental counters configuration
 * @feature_menu Administration > Simple counters
 * @override format      @user  invisible
 * @override identifier  @alias document
 * @override last_update @user  invisible
 * @override last_value  @alias example @store false
 * @store_name counters
 */
class Simple extends Counter
{

	//-------------------------------------------------------------------------- $complete_with_zeros
	/**
	 * @store false
	 * @user_change applySimpleForm
	 * @var boolean
	 */
	public $complete_with_zeros = true;

	//------------------------------------------------------------------------------ $documents_count
	/**
	 * @conditions complete_with_zeros=true
	 * @mandatory
	 * @realtime_change
	 * @store false
	 * @unit per year
	 * @user_change applySimpleForm
	 * @var integer
	 */
	public $documents_count = 9999;

	//--------------------------------------------------------------------------------------- $prefix
	/**
	 * @store false
	 * @user_change applySimpleForm
	 * @user_change_realtime
	 * @var string
	 */
	public $prefix = '';

	//----------------------------------------------------------------------- $year_with_4_characters
	/**
	 * @conditions yearly_reset=true
	 * @store false
	 * @user_change applySimpleForm
	 * @var boolean
	 */
	public $year_with_4_characters = false;

	//--------------------------------------------------------------------------------- $yearly_reset
	/**
	 * @store false
	 * @user_change applySimpleForm
	 * @var boolean
	 */
	public $yearly_reset = true;

	//------------------------------------------------------------------------------- formatLastValue
	/**
	 * @param $object object|null
	 * @return string
	 */
	public function formatLastValue($object = null)
	{
		if (!$this->last_value) {
			$this->last_value = 1;
		}
		return parent::formatLastValue($object);
	}

	//-------------------------------------------------------------------------------- formatToSimple
	/**
	 * Change format to simple assistant form fields
	 */
	public function formatToSimple()
	{
		$this->prefix                 = lParse(lParse($this->format, '%'), '{');
		$this->yearly_reset           = (strpos($this->format, '{YEAR') !== false);
		$this->year_with_4_characters = (strpos($this->format, '{YEAR4}') !== false);
		$this->complete_with_zeros    = (strpos($this->format, '%0') !== false);
		$this->documents_count        = '1' . sprintf(
			'%0' . (intval(mParse($this->format, '%', 'd')) - 1) . 's', 0
		);
	}

	//-------------------------------------------------------------------------------- simpleToFormat
	/**
	 * Change simple assistant form fields to format
	 */
	public function simpleToFormat()
	{
		$format = $this->prefix;
		if ($this->yearly_reset) {
			$format .= '{YEAR' . ($this->year_with_4_characters ? '4' : '') . '}';
		}
		$length  = max(strlen($this->documents_count), 1);
		$format .= '%' . ($this->complete_with_zeros ? ('0' . $length) : '') . 'd';
		$this->format = $format;
	}

}
