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
 *                year_with_4_characters, monthly_reset, complete_with_zeros, documents_count,
 *                suffix
 * @feature Easy incremental counters configuration
 * @feature_menu Administration > Simple counters
 * @override format     @user readonly
 * @override last_value @alias example @store false
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
	 * @user_change applySimpleForm
	 * @var integer
	 */
	public $documents_count = 9999;

	//-------------------------------------------------------------------------------- $monthly_reset
	/**
	 * @store false
	 * @user_change applySimpleForm
	 * @var boolean
	 */
	public $monthly_reset = false;

	//--------------------------------------------------------------------------------------- $prefix
	/**
	 * @store false
	 * @user_change applySimpleForm
	 * @user_change_realtime
	 * @var string
	 */
	public $prefix = '';

	//--------------------------------------------------------------------------------------- $suffix
	/**
	 * @store false
	 * @user_change applySimpleForm
	 * @user_change_realtime
	 * @var string
	 */
	public $suffix = '';

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
		$this->monthly_reset          = (strpos($this->format, '{MONTH}') !== false);
		$this->complete_with_zeros    = (strpos($this->format, '%0') !== false);
		$this->documents_count        = max(1, intval(mParse($this->format, '%', 'd')));
		$this->suffix                 = rLastParse($this->format, 'd');
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
		if ($this->monthly_reset) {
			$format .= '{MONTH}';
		}
		$length  = max(strlen($this->documents_count), 1);
		$format .= '%' . ($this->complete_with_zeros ? ('0' . $length) : '') . 'd';
		$format .= $this->suffix;
		$this->format = $format;
	}

}
