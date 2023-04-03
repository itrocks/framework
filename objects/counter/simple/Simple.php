<?php
namespace ITRocks\Framework\Objects\Counter;

use ITRocks\Framework\Objects\Counter;
use ITRocks\Framework\Reflection\Attribute\Class_;
use ITRocks\Framework\Reflection\Attribute\Class_\Display_Order;
use ITRocks\Framework\Reflection\Attribute\Class_\Override;
use ITRocks\Framework\Reflection\Attribute\Property\Alias;
use ITRocks\Framework\Reflection\Attribute\Property\Store;
use ITRocks\Framework\Reflection\Attribute\Property\Unit;
use ITRocks\Framework\Reflection\Attribute\Property\User;
use ITRocks\Framework\Reflection\Attribute\Property\User_Change;

/**
 * Simplified counter, for and easier configuration
 *
 * @after_read formatToSimple
 * @before_write simpleToFormat
 * @feature Easy incremental counters configuration
 * @feature_menu Administration > Simple counters
 */
#[
	Class_\Store(false),
	Display_Order(
		'identifier', 'last_update', 'last_value', 'format', 'prefix', 'yearly_reset',
		'year_with_4_characters', 'complete_with_zeros', 'documents_count'
	),
	Override('format',      new User(User::INVISIBLE)),
	Override('identifier',  new Alias('document')),
	Override('last_update', new User(User::INVISIBLE)),
	Override('last_value',  new Alias('example'), new Store(false))
]
class Simple extends Counter
{

	//-------------------------------------------------------------------------- $complete_with_zeros
	#[Store(false)]
	#[User_Change('applySimpleForm', true)]
	public bool $complete_with_zeros = true;

	//------------------------------------------------------------------------------ $documents_count
	/**
	 * @conditions complete_with_zeros=true
	 */
	#[Store(false)]
	#[Unit('per year')]
	#[User_Change('applySimpleForm', true)]
	public int $documents_count = 9999;

	//--------------------------------------------------------------------------------------- $prefix
	#[Store(false)]
	#[User_Change('applySimpleForm', true)]
	public string $prefix = '';

	//----------------------------------------------------------------------- $year_with_4_characters
	/**
	 * @conditions yearly_reset=true
	 */
	#[Store(false)]
	#[User_Change('applySimpleForm', true)]
	public bool $year_with_4_characters = false;

	//--------------------------------------------------------------------------------- $yearly_reset
	#[Store(false)]
	#[User_Change('applySimpleForm', true)]
	public bool $yearly_reset = true;

	//------------------------------------------------------------------------------- formatLastValue
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
