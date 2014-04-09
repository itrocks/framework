<?php
namespace SAF\Framework\Print_Model;

use SAF\Framework\Dao\File;
use SAF\Framework\Mapper\Component;
use SAF\Framework\Print_Model;

/**
 * A print model page : a model linked to a unique page background and design
 *
 * @representative number, background
 */
class Page
{
	use Component;

	//---------------------------------------------------------------------------------------- $model
	/**
	 * @composite
	 * @link Object
	 * @var Print_Model
	 */
	public $model;

	//----------------------------------------------------------------------------------------- $name
	/**
	 * The page number : 1 is the first page, -1 is the last page, 0 is 'all others pages'
	 *
	 * @signed
	 * @getter getNumber
	 * @setter setNumber
	 * @var integer
	 */
	public $number;

	//----------------------------------------------------------------------------------- $background
	/**
	 * @link Object
	 * @var File
	 */
	public $background;

	//--------------------------------------------------------------------------------------- $zoning
	/**
	 * @link Collection
	 * @var Zone[]
	 */
	public $zones;

	//------------------------------------------------------------------------------------- getNumber
	/**
	 * @return string
	 */
	/* @noinspection PhpUnusedPrivateMethodInspection @getter */
	private function getNumber()
	{
		$number = $this->number;
		switch ($number) {
			case 1:  return 'first';
			case 0:  return 'all';
			case -1: return 'last';
		}
		return $number;
	}

	//------------------------------------------------------------------------------------- setNumber
	/**
	 * @param string
	 */
	/* @noinspection PhpUnusedPrivateMethodInspection @setter */
	private function setNumber($number)
	{
		switch ($number) {
			case 'first': $number = 1;  break;
			case 'all':   $number = 0;  break;
			case 'last':  $number = -1; break;
		}
		$this->number = $number;
	}

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string
	 */
	public function __toString()
	{
		return strval($this->model) . SP . strval($this->number);
	}

}
