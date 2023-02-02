<?php
namespace ITRocks\Framework\RAD\Feature;

use ITRocks\Framework\RAD\Feature;
use ITRocks\Framework\Reflection\Attribute\Class_\Displays;
use ITRocks\Framework\Reflection\Attribute\Class_\Store;
use ITRocks\Framework\Traits\Has_Name;

/**
 * @display_order name, super_packages, sub_packages, features
 * @feature
 */
#[Displays('feature packages')]
#[Store('rad_packages')]
class Package
{
	use Has_Name;

	//------------------------------------------------------------------------------------- $features
	/**
	 * @var Feature[]
	 */
	public array $features;

	//---------------------------------------------------------------------------------- $included_in
	/**
	 * @foreign includes
	 * @var Package[]
	 */
	public array $included_in;

	//------------------------------------------------------------------------------------- $includes
	/**
	 * @foreign included_in
	 * @var Package[]
	 */
	public array $includes;

}
