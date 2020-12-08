<?php
namespace ITRocks\Framework\Layout\Display_Model;

use ITRocks\Framework\Builder;
use ITRocks\Framework\Layout\Generator;
use ITRocks\Framework\Layout\Generator\Generate_Groups;
use ITRocks\Framework\Layout\Structure;

/**
 * The layout model generator for output display models
 */
class Output_Generator extends Generator
{

	//-------------------------------------------------------------------------------------- generate
	/**
	 * Generate the structure
	 *
	 * @param $object object
	 * @return Structure
	 */
	public function generate($object) : Structure
	{
		$this->object    = $object;
		$this->structure = new Structure(Builder::className($this->model->class_name));
		$this->modelToStructure();
		(new Generate_Groups($this->structure))->run();
		$this->purgeSnapLines();
		return $this->structure;
	}

}
