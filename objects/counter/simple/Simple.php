<?php
namespace ITRocks\Framework\Objects\Counter;

use ITRocks\Framework\Objects\Counter;
use ITRocks\Framework\Reflection\Attribute\Class_\Store;
use ITRocks\Framework\Reflection\Attribute\Property\User_Change;

/**
 * Simplified counter, for and easier configuration
 *
 * @after_read formatToSimple
 * @before_write simpleToFormat
 * @display_order identifier, last_update, last_value, format, prefix, yearly_reset,
 *                year_with_4_characters, complete_with_zeros, documents_count
 * @feature Easy incremental counters configuration
 * @feature_menu Administration > Simple counters
 * @override format      @user  invisible
 * @override identifier  @alias document
 * @override last_update @user  invisible
 * @override last_value  @alias example @store false
 */
#[Store(false)]
class Simple extends Counter
{

	//-------------------------------------------------------------------------- $complete_with_zeros
	/**
	 * @store false
	 * @var boolean
	 */
	#[User_Change('applySimpleForm', true)]
	public bool $complete_with_zeros = true;

	//------------------------------------------------------------------------------ $documents_count
	/**
	 * @conditions complete_with_zeros=true
	 * @mandatory
	 * @store false
	 * @unit per year
	 * @var integer
	 */
	#[User_Change('applySimpleForm', true)]
	public int $documents_count = 9999;

	//--------------------------------------------------------------------------------------- $prefix
	/**
	 * @store false
	 * @var string
	 */
	#[User_Change('applySimpleForm', true)]
	public string $prefix = '';

	//----------------------------------------------------------------------- $year_with_4_characters
	/**
	 * @conditions yearly_reset=true
	 * @store false
	 * @var boolean
	 */
	#[User_Change('applySimpleForm', true)]
	public bool $year_with_4_characters = false;

	//--------------------------------------------------------------------------------- $yearly_reset
	/**
	 * @store false
	 * @var boolean
	 */
	#[User_Change('applySimpleForm', true)]
	public bool $yearly_reset = true;

	//------------------------------------------------------------------------------- formatLastValue
	/**
	 * @param $object object|null
	 * @return string
	 */
	public function formatLastValue(object $object = null) : string
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
	public function formatToSimple() : void
	{
		$this->prefix                 = lParse(lParse($this->format, '%'), '{');
		$this->yearly_reset           = str_contains($this->format, '{YEAR');
		$this->year_with_4_characters = str_contains($this->format, '{YEAR4}');
		$this->complete_with_zeros    = str_contains($this->format, '%0');
		$this->documents_count        = '1' . sprintf(
			'%0' . (intval(mParse($this->format, '%', 'd')) - 1) . 's', 0
		);
	}

	//-------------------------------------------------------------------------------- simpleToFormat
	/**
	 * Change simple assistant form fields to format
	 */
	public function simpleToFormat() : void
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
