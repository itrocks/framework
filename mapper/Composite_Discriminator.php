<?php
namespace ITRocks\Framework\Mapper;

use ITRocks\Framework\Reflection\Reflection_Property;

/**
 * For classes that need composite discrimination by object
 */
interface Composite_Discriminator
{

	//------------------------------------------------------------------------- discriminateComposite
	/**
	 * @param $composites Reflection_Property[] Composite properties
	 * @param $component  object                Component object
	 * @return Reflection_Property|null
	 */
	public function discriminateComposite(array $composites, $component = null);

}
