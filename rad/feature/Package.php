<?php
namespace ITRocks\Framework\RAD\Feature;

use ITRocks\Framework\RAD\Feature;
use ITRocks\Framework\Traits\Has_Name;

/**
 * @display_order name, super_packages, sub_packages, features
 * @displays feature packages
 * @feature
 * @store_name rad_packages
 */
class Package
{
	use Has_Name;

	//------------------------------------------------------------------------------------- $features
	/**
	 * @link Map
	 * @var Feature[]
	 */
	public array $features;

	//---------------------------------------------------------------------------------- $included_in
	/**
	 * @foreign includes
	 * @link Map
	 * @var Package[]
	 */
	public array $included_in;

	//------------------------------------------------------------------------------------- $includes
	/**
	 * @foreign included_in
	 * @link Map
	 * @var Package[]
	 */
	public array $includes;

}
