<?php
namespace ITRocks\Framework\Layout;

use ITRocks\Framework\Layout\Generator\Property_To_Text;
use ITRocks\Framework\Layout\Structure\Page\From_Json;

/**
 * The layout generator creates a structure from a model and a source object
 *
 * Input :
 * - a layout model
 * - an object (eg a sales invoice)
 *
 * Output :
 * - a layout ready-to-print and output-format-independent data structure
 */
class Generator
{

	//---------------------------------------------------------------------------------------- $model
	/**
	 * The source layout model
	 * Set by __construct
	 *
	 * @var Model
	 */
	public $model;

	//--------------------------------------------------------------------------------------- $object
	/**
	 * The source object containing the data we want to print
	 * Set by generate() first argument
	 *
	 * @var object
	 */
	public $object;

	//------------------------------------------------------------------------------------ $structure
	/**
	 * The data structure : evolves from a raw structure read from the layout to a built structure
	 * with the whole data set inside, ready to print
	 *
	 * Generated by generate()
	 *
	 * @var Structure
	 */
	public $structure;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $model Model
	 */
	public function __construct(Model $model = null)
	{
		if (isset($model)) {
			$this->model = $model;
		}
	}

	//-------------------------------------------------------------------------------------- generate
	/**
	 * Generate the structure
	 *
	 * @param $object object
	 * @return Structure
	 */
	public function generate($object)
	{
		$this->object    = $object;
		$this->structure = new Structure();
		$this->modelToStructure();
		(new Property_To_Text($this->structure))->run($this->object);
		return $this->structure;
	}

	//------------------------------------------------------------------------------ modelToStructure
	/**
	 * Extract structure layout from $this->model->pages to generate a raw initial $this->structure
	 */
	protected function modelToStructure()
	{
		foreach ($this->model->pages as $page) {
			$this->structure->pages[] = (new From_Json)->build($page->layout);
		}
	}

}
