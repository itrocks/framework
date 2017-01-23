<?php
namespace ITRocks\Framework\Printer\Model;

use ITRocks\Framework\Dao\File;
use ITRocks\Framework\Mapper\Component;
use ITRocks\Framework\Printer\Model;
use ITRocks\Framework\Tools\Has_Ordering;

/**
 * A print model page : a model linked to a unique page background and design
 *
 * The page number : 1 is the first page, 3 is the last page, 2 is 'all others pages'
 *
 * @override ordering @getter getNumber @setter setNumber @signed
 * @representative model, ordering
 * @set Printer_Model_Pages
 */
class Page
{
	use Component;
	use Has_Ordering;

	//----------------------------------------------------------------------------------------- FIRST
	const FIRST = 'first';

	//------------------------------------------------------------------------------------------ LAST
	const LAST = 'last';

	//---------------------------------------------------------------------------------------- MIDDLE
	const MIDDLE = 'middle';

	//---------------------------------------------------------------------------------------- $model
	/**
	 * @composite
	 * @link Object
	 * @var Model
	 */
	public $model;

	//----------------------------------------------------------------------------------- $background
	/**
	 * @link Object
	 * @var File
	 */
	public $background;

	//--------------------------------------------------------------------------------------- $fields
	/**
	 * @link Collection
	 * @var Field[]
	 */
	public $fields;

	//------------------------------------------------------------------------------------- getNumber
	/** @noinspection PhpUnusedPrivateMethodInspection @getter */
	/**
	 * @return string @values first, middle, last
	 */
	private function getNumber()
	{
		$ordering = $this->ordering;
		switch ($ordering) {
			case 1: return self::FIRST;
			case 2: return self::MIDDLE;
			case 3: return self::LAST;
		}
		return $ordering;
	}

	//------------------------------------------------------------------------------------- setNumber
	/** @noinspection PhpUnusedPrivateMethodInspection @setter */
	/**
	 * @param $ordering integer|string @values 1, 2, 3, first, middle, last
	 */
	private function setNumber($ordering)
	{
		switch ($ordering) {
			case self::FIRST:  $ordering = 1; break;
			case self::MIDDLE: $ordering = 2; break;
			case self::LAST:   $ordering = 3; break;
		}
		$this->ordering = $ordering;
	}

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string
	 */
	public function __toString()
	{
		return strval($this->model) . SP . strval($this->ordering);
	}

}
