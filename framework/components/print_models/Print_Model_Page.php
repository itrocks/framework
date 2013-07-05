<?php
namespace SAF\Framework;

/**
 * A print model page : a model linked to a unique page background and design
 */
class Print_Model_Page
{
	use Component;

	//---------------------------------------------------------------------------------------- $model
	/**
	 * @link Object
	 * @var Print_Model
	 */
	public $model;

	//----------------------------------------------------------------------------------------- $name
	/**
	 * The page number : 1 is the first page, -1 is the last page, 0 is "all others pages"
	 *
	 * @signed
	 * @getter getNumber
	 * @setter setNumber
	 * @var integer
	 */
	public $number;

	//----------------------------------------------------------------------------------- $background
	/**
	 * @dao file
	 * @link Object
	 * @var File
	 */
	public $background;

	//--------------------------------------------------------------------------------------- $zoning
	/**
	 * @link Collection
	 * @var Print_Model_Zone[]
	 */
	public $zones;

	//------------------------------------------------------------------------------------- getNumber
	/**
	 * @return string
	 */
	public function getNumber()
	{
		$number = $this->number;
		switch ($number) {
			case 1:  return "first";
			case 0:  return "all";
			case -1: return "last";
		}
		return $number;
	}

	//------------------------------------------------------------------------------------- setNumber
	/**
	 * @param string
	 */
	public function setNumber($number)
	{
		switch ($number) {
			case "first": $number = 1;  break;
			case "all":   $number = 0;  break;
			case "last":  $number = -1; break;
		}
		$this->number = $number;
	}

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string
	 */
	public function __toString()
	{
		return strval($this->model) . " " . strval($this->number);
	}

}
