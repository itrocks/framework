<?php
namespace ITRocks\Framework\Feature\List_;

use ITRocks\Framework\Builder;
use ITRocks\Framework\Controller\Feature;
use ITRocks\Framework\Controller\Main;
use ITRocks\Framework\Controller\Parameters;
use ITRocks\Framework\Dao;
use ITRocks\Framework\Dao\Func;
use ITRocks\Framework\Dao\Func\Logical;
use ITRocks\Framework\Dao\Option;
use ITRocks\Framework\Dao\Option\Limit;
use ITRocks\Framework\Feature\List_Setting;
use ITRocks\Framework\Reflection\Annotation\Template\Method_Annotation;
use ITRocks\Framework\Reflection\Reflection_Class;
use ITRocks\Framework\Tools\List_Data;
use ITRocks\Framework\Tools\Set;

/**
 * Data list selection parameters decoder
 *
 * Decodes the excluded_selection, select_all and selection parameters to prepare / read data
 */
class Selection
{

	//----------------------------------------------------------------------------------- $class_name
	/**
	 * @var string
	 */
	protected $class_name;

	//--------------------------------------------------------------------------- $excluded_selection
	/**
	 * Objects identifiers excluded from selection
	 * Set only when $select_all is true
	 *
	 * @var integer[]
	 */
	protected $excluded_selection = [];

	//------------------------------------------------------------------------------ $list_controller
	/**
	 * Data list controller cache, set by getListController
	 *
	 * @see getListController
	 * @var Controller
	 */
	private $list_controller;

	//-------------------------------------------------------------------------------- $list_settings
	/**
	 * Data list settings cache, set by getListSettings
	 *
	 * @see getListSettings
	 * @var List_Setting\Set
	 */
	private $list_settings;

	//-------------------------------------------------------------------------------------- $options
	/**
	 * Read options cache, set by getSearchOptions
	 *
	 * @see getSearchOptions
	 * @var Option[]
	 */
	private $options;

	//--------------------------------------------------------------------------------------- $search
	/**
	 * Search filters cache, set by getSearchFilter
	 *
	 * @var array
	 */
	private $search;

	//----------------------------------------------------------------------------------- $select_all
	/**
	 * If true : select all elements matching the data list filters except $excluded_selection
	 * If false : select only $selection elements
	 *
	 * @var boolean
	 */
	protected $select_all = false;

	//------------------------------------------------------------------------------------ $selection
	/**
	 * Objects identifiers included into selection
	 * Set only when $select_all is false
	 *
	 * @var integer[]
	 */
	protected $selection = [];

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $object_class object|string object or class name. If Set, will be decoded as element
	 * @param $form         string[]
	 */
	public function __construct($object_class = null, array $form = null)
	{
		if (isset($form)) {
			$this->setFormData($form);
		}
		if (isset($object_class)) {
			$this->setObject($object_class);
		}
	}

	//-------------------------------------------------------------------------- allButExcludedFilter
	/**
	 * Call this only when in $select_all === true mode : returns a dao search filter for
	 * 'all results of the data list search but excluded'
	 *
	 * @return array
	 */
	protected function allButExcludedFilter()
	{
		$search = $this->getListController()->applySearchParameters($this->getListSettings());
		$search = $this->getListController()->searchObjectsToRepresentative($this->class_name, $search);
		if ($this->excluded_selection) {
			$search[]['id'] = Func::notIn($this->excluded_selection);
		}
		return $search;
	}

	//----------------------------------------------------------------------------------------- flush
	/**
	 * Reset cache
	 * Called each time we call setFormData or setObject
	 *
	 * @see setFormData, setObject
	 */
	protected function flush()
	{
		$this->list_controller = null;
		$this->list_settings   = null;
		$this->options         = null;
		$this->search          = null;
	}

	//---------------------------------------------------------------------------------- getClassName
	/**
	 * Gets the data object class name, calculated by setObject() < __construct()
	 *
	 * @return string
	 * @see setObject
	 */
	public function getClassName()
	{
		return $this->class_name;
	}

	//----------------------------------------------------------------------------- getListController
	/**
	 * Gets the Controller object associated to the object / class name
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 * @return Controller
	 */
	public function getListController()
	{
		if (!isset($this->list_controller)) {
			$list_controllers = Main::$current->getController($this->class_name, Feature::F_LIST);
			/** @noinspection PhpUnhandledExceptionInspection a controller is always a valid callable */
			$this->list_controller = Builder::create($list_controllers[0]);
		}
		return $this->list_controller;
	}

	//------------------------------------------------------------------------------- getListSettings
	/**
	 * Gets the List_Setting\Set object associated to the object / class name
	 *
	 * @return List_Setting\Set
	 */
	public function getListSettings()
	{
		if (!isset($this->list_settings)) {
			$this->list_settings = List_Setting\Set::current($this->class_name);
			$this->list_settings->cleanup();
			$this->list_settings->maximum_displayed_lines_count = null;
		}
		return $this->list_settings;
	}

	//------------------------------------------------------------------------------- getSearchFilter
	/**
	 * Gets a search filter build from :
	 * - the search criterion in the current data list for the object
	 * - the excluded_selection, select_all and selection selected elements from the form
	 *
	 * @param $search object|array Search array for filter, additional to filters get from data list
	 * @return array|Logical
	 */
	public function getSearchFilter($search = null)
	{
		if (!isset($this->search)) {
			$this->search = $this->select_all
				? $this->allButExcludedFilter()
				: $this->selectedFilter();
			$class = $this->getListSettings()->getClass();
			Method_Annotation::callAll($class->getAnnotations('on_list'), $class->name, [&$this->search]);
		}
		return $search
			? ($this->search ? [Func::andOp([$search, $this->search])] : $search)
			: $this->search;
	}

	//------------------------------------------------------------------------------ getSearchOptions
	/**
	 * Gets search option
	 *
	 * @param $options Option|Option[] options to merge with the calculated filter options
	 * @return Option[]
	 */
	public function getSearchOptions(array $options = [])
	{
		if (!is_array($options)) {
			$options = $options ? [$options] : [];
		}
		if (!isset($this->options)) {
			$this->options = [$this->getListSettings()->sort];
			if ($this->select_all && Limit::in($options)) {
				$this->options[] = Dao::doublePass();
			}
		}
		$options = array_merge($this->options, is_array($options) ? $options : [$options]);
		$this->removeSearchOptions($options);
		return $options;
	}

	//-------------------------------------------------------------------------------- readDataSelect
	/**
	 * Reads data the same way it is done by the data list controller, without limiting the number
	 * of read lines.
	 * Restrictions others thant the number of read lines limit applied to the data list controller
	 * will be applied here too.
	 *
	 * @param $properties_path string[] the list of the columns names : only those properties
	 *                         will be read. There are 'column.sub_column' to get values from linked
	 *                         objects from the same data source
	 * @param $search          object|array Search array for filter, associating properties names to
	 *                         matching search value too
	 * @param $options         Option|Option[]|string|string[] some options for advanced search
	 * @return List_Data A list of read records. Each record values (may be objects) are
	 *         stored in the same order than columns.
	 * @return List_Data[]
	 */
	public function readDataSelect(array $properties_path = null, $search = null, $options = [])
	{
		$search  = $this->getSearchFilter($search);
		$options = $this->getSearchOptions(is_array($options) ? $options : [$options]);

		if (empty($properties_path)) {
			$properties                     = array_keys($this->getListSettings()->properties);
			list($properties_path, $search) = $this->getListController()->removeInvisibleProperties(
				$this->class_name, $properties, $search
			);
		}

		$group_by = $this->getListController()->groupBy($this->getListSettings()->properties);
		if ($group_by) {
			$options[] = $group_by;
			$this->getListController()->groupConcat($properties_path, $group_by);
		}

		return $this->getListController()->readDataSelect(
			$this->class_name, $properties_path, $search, $options
		);
	}

	//----------------------------------------------------------------------------------- readObjects
	/**
	 * Reads all objects matching the filters
	 * Beware : this may consume a lot of time and memory if many objects are selected
	 * Do use only with limited sets
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $search  object|array    optional additional filters
	 * @param $options Option|Option[] optional options for advanced search
	 * @return object[]
	 */
	public function readObjects($search = null, $options = [])
	{
		$search  = $this->getSearchFilter($search);
		$options = $this->getSearchOptions(is_array($options) ? $options : [$options]);
		/** @noinspection PhpUnhandledExceptionInspection must be valid */
		$class     = new Reflection_Class($this->class_name);
		$dao_value = $class->getAnnotation('dao')->value;
		$dao       = $dao_value ? Dao::get($dao_value) : Dao::current();
		return $dao->search($search, $this->class_name, $options);
	}

	//--------------------------------------------------------------------------- removeSearchOptions
	/**
	 * @param $options Option[]|string[] remove OptionClass options when there is an '!Option_Class'
	 */
	protected function removeSearchOptions(array &$options)
	{
		foreach ($options as $key1 => $option) {
			if (is_string($option) && (substr($option, 0, 1) === '!')) {
				unset($options[$key1]);
				$remove_option_class = substr($option, 1);
				foreach ($options as $key2 => $remove_option) {
					if (is_a($remove_option, $remove_option_class)) {
						unset($options[$key2]);
					}
				}
			}
		}
	}

	//-------------------------------------------------------------------------------- selectedFilter
	/**
	 * Call this only when in $select_all === false mode : returns a dao search filter for
	 * 'selected results only'
	 *
	 * If no selected element, this search filter will not enable to get any element (empty result)
	 *
	 * @return array
	 */
	protected function selectedFilter()
	{
		return ['id' => $this->selection ? Func::in($this->selection) : 0];
	}

	//----------------------------------------------------------------------------------- setFormData
	/**
	 * Initialize properties using the following form data :
	 * - excluded_selection eg '1,2,3,4'
	 * - select_all         ie '1' | ''
	 * - selection          eg '1,2,3,4'
	 *
	 * @param $form string[]
	 */
	public function setFormData(array $form)
	{
		$this->excluded_selection = empty($form['excluded_selection'])
			? []
			: explode(',', $form['excluded_selection']);
		$this->select_all = empty($form['select_all'])
			? false
			: true;
		$this->selection = empty($form['selection'])
			? []
			: explode(',', $form['selection']);
		$this->flush();
	}

	//------------------------------------------------------------------------------------- setObject
	/**
	 * If $object is a Parameters, will get the parameters main object
	 * If $object is a Set, will be decoded as element
	 *
	 * @param $object object|string object or class name
	 */
	public function setObject($object)
	{
		if ($object instanceof Parameters) {
			$object = $object->getMainObject();
		}
		$this->class_name = ($object instanceof Set)
			? $object->element_class_name
			: (is_object($object) ? get_class($object) : $object);

		if (is_object($object) && ($identifier = Dao::getObjectIdentifier($object))) {
			$this->excluded_selection = [];
			$this->select_all         = false;
			$this->selection[]        = $identifier;
		}

		$this->flush();
	}

}
