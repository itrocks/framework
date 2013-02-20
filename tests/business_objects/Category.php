<?php
namespace SAF\Tests;

class Category
{

	//----------------------------------------------------------------------------------------- $name
	/**
	 * @var string
	 */
	public $name;

	//-------------------------------------------------------------------------- $main_super_category
	/**
	 * @link object
	 * @var Category
	 */
	public $main_super_category;

	//----------------------------------------------------------------------------- $super_categories
	/**
	 * @link map
	 * @var Category[]
	 */
	public $super_categories;

	//------------------------------------------------------------------------------- $sub_categories
	/**
	 * @link map
	 * @var Category[]
	 */
	public $sub_categories;

	//---------------------------------------------------------------------------------------- $shops
	/**
	 * @link collection
	 * @var Shop[]
	 */
	public $shops;

}
