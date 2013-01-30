<?php
namespace SAF\Framework;

interface Configuration_Builder
{

	//----------------------------------------------------------------------------------------- build
	/**
	 * @param $configuration array
	 * @return Configuration
	 */
	public function build($configuration);

}
