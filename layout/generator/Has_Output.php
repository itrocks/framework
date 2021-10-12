<?php
namespace ITRocks\Framework\Layout\Generator;

use ITRocks\Framework\Layout\Output;

trait Has_Output
{

	//--------------------------------------------------------------------------------------- $output
	/**
	 * @var Output
	 */
	protected Output $output;

	//------------------------------------------------------------------------------------------- run
	/**
	 * Must be called before run for all generators that use this trait
	 *
	 * @param $output Output
	 * @return static
	 */
	public function setOutput(Output $output) : static
	{
		$this->output = $output;
		return $this;
	}

}
