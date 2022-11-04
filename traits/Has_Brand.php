<?php
namespace ITRocks\Framework\Traits;

use ITRocks\Framework\Objects\Brand;

/**
 * For things that want to link to a brand
 */
trait Has_Brand
{

	//---------------------------------------------------------------------------------------- $brand
	/**
	 * @link Object
	 * @var ?Brand
	 */
	public ?Brand $brand;

}
