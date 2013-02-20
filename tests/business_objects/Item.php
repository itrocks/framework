<?php
namespace SAF\Tests;

class Item
{

	//----------------------------------------------------------------------------------------- $code
	/**
	 * @var string
	 */
	public $code;

	//---------------------------------------------------------------------------------------- $lines
	/**
	 * @link map
	 * @var Order_Line[]
	 */
	public $lines;

	//---------------------------------------------------------------------------------------- $model
	/**
	 * @link object
	 * @var Item
	 */
	public $model;

	//-------------------------------------------------------------------------------- $main_category
	/**
	 * @link object
	 * @var Category
	 */
	public $main_category;

	//------------------------------------------------------------------------- $secondary_categories
	/**
	 * @link map
	 * @var Category[]
	 */
	public $secondary_categories;

}
