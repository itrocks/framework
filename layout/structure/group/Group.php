<?php
namespace ITRocks\Framework\Layout\Structure;

use ITRocks\Framework\Layout\Structure\Field\Property;
use ITRocks\Framework\Layout\Structure\Group\Iteration;

/**
 * A group manages repeated fields
 *
 * Groups can be configured by dropping them strictly around fields that may be repeated.
 * If a field is a property.path with multiple repetitive steps, you must use multiple groups.
 * If a property.path contains repetitive properties but has no group, an auto-group will be added.
 * When the structure is built, groups are not immediately linked to elements inside :
 * Generator\Associate_Groups does this job.
 */
class Group extends Element
{

	//------------------------------------------------------------------------ ALL_ELEMENT_PROPERTIES
	const ALL_ELEMENT_PROPERTIES = ['elements', 'groups', 'iterations', 'properties'];

	//----------------------------------------------------------------------------------- DUMP_SYMBOL
	const DUMP_SYMBOL = '>';

	//---------------------------------------------------------------------------- $direction @values
	const HORIZONTAL = 'horizontal';
	const VERTICAL   = 'vertical';

	//------------------------------------------------------------------------------------ $direction
	/**
	 * @values self::const local
	 * @var string
	 */
	public $direction = self::VERTICAL;

	//------------------------------------------------------------------------------------- $elements
	/**
	 * Raw elements that are not $groups, $iterations nor $properties
	 *
	 * When iterations are generated, this is empty
	 *
	 * @var Element[]
	 */
	public $elements = [];

	//--------------------------------------------------------------------------------------- $groups
	/**
	 * Sub-group elements
	 *
	 * @var Group[]
	 */
	public $groups = [];

	//---------------------------------------------------------------------------- $iteration_spacing
	/**
	 * Space between iterations (for margins)
	 *
	 * @var float
	 */
	public $iteration_spacing = .5;

	//----------------------------------------------------------------------------------- $iterations
	/**
	 * @var Iteration[]
	 */
	public $iterations = [];

	//---------------------------------------------------------------------------------------- $links
	/**
	 * Set by Link_Groups::run : key is the structure page number, value is the same group in the page
	 *
	 * All $linked_groups are the same group in multiple pages
	 * They are all linked by reference : modify $linked_groups in a group and all the others will be
	 *
	 * @var Group[] Group[string $page_number] page number must always be a string
	 */
	public $links = [];

	//----------------------------------------------------------------------------------- $properties
	/**
	 * @var Property[]
	 */
	public $properties = [];

	//-------------------------------------------------------------------------------- $property_path
	/**
	 * The path of the property, starting from the layout model context class
	 *
	 * The final property has always a multiple type (eg Class[], string[])
	 *
	 * @var string
	 */
	public $property_path;

	//----------------------------------------------------------------------------------- allElements
	/**
	 * @return Element[]
	 */
	public function allElements()
	{
		return array_merge($this->elements, $this->groups, $this->iterations, $this->properties);
	}

	//------------------------------------------------------------------------------ cloneWithContext
	/**
	 * @param $page      Page
	 * @param $group     Group|null
	 * @param $iteration Iteration|null
	 * @return static
	 */
	public function cloneWithContext(Page $page, Group $group = null, Iteration $iteration = null)
	{
		/** @var $group static PhpStorm bug */
		$group = parent::cloneWithContext($page, $group, $iteration);
		$this->links[strval($group->page->number)] = $group;

		foreach (static::ALL_ELEMENT_PROPERTIES as $elements_property_name) {
			$elements = [];
			foreach ($this->$elements_property_name as $element) {
				/** @var $element Element */
				if ($element instanceof Iteration) {
					foreach ($element->elements as $iteration_element) {
						$elements[] = $iteration_element->cloneWithContext($page, $group);
					}
				}
				else {
					$elements[] = $element->cloneWithContext($page, $group);
				}
			}
			$this->$elements_property_name = $elements;
		}

		return $group;
	}

	//------------------------------------------------------------------------------------------ dump
	/**
	 * @param $level integer
	 * @param $detail boolean
	 * @return string
	 */
	public function dump($level = 0, $detail = true)
	{
		if ($detail) {
			$dump = parent::dump($level) . LF;
			foreach ($this->allElements() as $element) {
				$dump .= $element->dump($level + 1) . LF;
			}
			foreach ($this->links as $link) {
				$dump .= str_repeat(SP, $level * 2 + 2) . $link->page->number
					. SP . $link->dump(0, false) . LF;
			}
			return $dump;
		}
		return parent::dump($level);
	}

	//---------------------------------------------------------------------------------- heightOnPage
	/**
	 * Gets the height of the linked group into this page
	 * If the group is not stored into this page, will return 0 because we can't output it here
	 *
	 * @param $page Page
	 * @return float
	 */
	public function heightOnPage(Page $page)
	{
		return isset($this->links[strval($page->number)])
			? $this->links[strval($page->number)]->height
			: 0;
	}

	//------------------------------------------------------------------------------------ linkOnPage
	/**
	 * Gets the linked group into this page, if exist
	 *
	 * @param $page Page
	 * @return Group|null
	 */
	public function linkOnPage(Page $page)
	{
		return isset($this->links[strval($page->number)])
			? $this->links[strval($page->number)]
			: null;
	}

}
