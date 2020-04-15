<?php
namespace ITRocks\Framework\Layout;

use ITRocks\Framework\Builder;
use ITRocks\Framework\Layout\Generator\Associate_Groups;
use ITRocks\Framework\Layout\Generator\Automatic_Line_Feed;
use ITRocks\Framework\Layout\Generator\Count_Pages;
use ITRocks\Framework\Layout\Generator\Dispatch_Iterations;
use ITRocks\Framework\Layout\Generator\Dispatch_Iterations_On_Pages;
use ITRocks\Framework\Layout\Generator\Generate_Groups;
use ITRocks\Framework\Layout\Generator\Link_Groups;
use ITRocks\Framework\Layout\Generator\Page_All_Elements;
use ITRocks\Framework\Layout\Generator\Property_To_Text;
use ITRocks\Framework\Layout\Generator\Text_Templating;
use ITRocks\Framework\Layout\Structure\Draw\Snap_Line;
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

	//--------------------------------------------------------------------------------------- $output
	/**
	 * This allow to ask for some methods from the final output exporter : eg real text width, etc.
	 *
	 * @var Output
	 */
	public $output;

	//---------------------------------------------------------------------------------------- $print
	/**
	 * Is it a print model ? If true, will use @print_getter to translate values for print
	 *
	 * @var boolean
	 */
	public $print = false;

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
	 * @param $model  Model
	 * @param $output Output
	 */
	public function __construct(Model $model = null, Output $output = null)
	{
		if (isset($model)) {
			$this->model = $model;
		}
		if (isset($output)) {
			$this->output = $output;
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
		$this->structure = new Structure(Builder::className($this->model->class_name));
		$this->modelToStructure();
		// associate and auto-generate groups before page all elements to avoid mixing
		(new Associate_Groups($this->structure))->run();
		(new Generate_Groups($this->structure))->run();
		$this->purgeSnapLines();
		(new Page_All_Elements($this->structure))->run();
		(new Link_Groups($this->structure))->run();
		(new Property_To_Text($this->structure, $this->print))->run($this->object);
		(new Automatic_Line_Feed($this->structure))->run($this->output);
		(new Dispatch_Iterations($this->structure))->run();
		(new Count_Pages($this->structure))->run();
		(new Dispatch_Iterations_On_Pages($this->structure))->run();
		(new Text_Templating($this->structure))->run();
		return $this->structure;
	}

	//------------------------------------------------------------------------------ modelToStructure
	/**
	 * Extract structure layout from $this->model->pages to generate a raw initial $this->structure
	 */
	protected function modelToStructure()
	{
		$this->structure->pages = [];
		foreach ($this->model->pages as $page) {
			$structure_page = (new From_Json)->build($page->layout);
			if (!$structure_page->isEmpty()) {
				$structure_page->background              = $page->background;
				$structure_page->number                  = $page->ordering;
				$this->structure->pages[$page->ordering] = $structure_page;
			}
		}
	}

	//-------------------------------------------------------------------------------- purgeSnapLines
	/**
	 * Remove snap line elements from pages
	 */
	protected function purgeSnapLines()
	{
		foreach ($this->structure->pages as $page) {
			foreach ($page->elements as $element_key => $element) {
				if ($element instanceof Snap_Line) {
					unset($page->elements[$element_key]);
				}
			}
		}
	}

}
