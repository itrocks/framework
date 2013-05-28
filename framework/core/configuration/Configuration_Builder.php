<?php
namespace SAF\Framework;

/**
 * A configuration builder class can build a configuration for a plugin
 */
interface Configuration_Builder
{

	//----------------------------------------------------------------------------------------- build
	/**
	 * @param $configuration array
	 * @return Configuration
	 */
	public function build($configuration);

}
