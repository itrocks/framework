<?php
namespace ITRocks\Framework\Widget\Data_List;

use ITRocks\Framework\Builder;
use ITRocks\Framework\Controller\Feature;
use ITRocks\Framework\Controller\Main;
use ITRocks\Framework\Controller\Parameters;
use ITRocks\Framework\Dao;
use ITRocks\Framework\Dao\Func;
use ITRocks\Framework\Dao\Func\Logical;
use ITRocks\Framework\Dao\Option;
use ITRocks\Framework\Dao\Option\Limit;
use ITRocks\Framework\Reflection\Annotation\Template\Method_Annotation;
use ITRocks\Framework\Tools\List_Data;
use ITRocks\Framework\Tools\Set;
use ITRocks\Framework\Widget\Data_List_Setting\Data_List_Settings;

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

	//------------------------------------------------------------------------- $data_list_controller
	/**
	 * Data list controller cache, set by getDataListController
	 *
	 * @see getDataListController
	 * @var Data_List_Controller
	 */
	private $data_list_controller;

	//--------------------------------------------------------------------------- $data_list_settings
	/**
	 * Data list settings cache, set by getDataListSettings
	 *
	 * @see getDataListSettings
	 * @var Data_List_Settings
	 */
	private $data_list_settings;

	//--------------------------------------------------------------------------- $excluded_selection
	/**
	 * Objects identifiers excluded from selection
	 * Set only when $select_all is true
	 *
	 * @var integer[]
	 */
	protected $excluded_selection = [];

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
		$search = $this->getDataListController()->applySearchParameters($this->getDataListSettings());
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
		$this->data_list_controller = null;
		$this->data_list_settings   = null;
		$this->options              = null;
		$this->search               = null;
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

	//------------------------------------------------------------------------- getDataListController
	/**
	 * Gets the Data_List_Controller object associated to the object / class name
	 *
	 * @return Data_List_Controller
	 */
	public function getDataListController()
	{
		if (!isset($this->data_list_controller)) {
			$data_list_controllers = Main::$current->getController($this->class_name, Feature::F_LIST);
			$this->data_list_controller = Builder::create($data_list_controllers[0]);
		}
		return $this->data_list_controller;
	}

	//--------------------------------------------------------------------------- getDataListSettings
	/**
	 * Gets the Data_List_Settings object associated to the object / class name
	 *
	 * @return Data_List_Settings
	 */
	public function getDataListSettings()
	{
		if (!isset($this->data_list_settings)) {
			$this->data_list_settings = Data_List_Settings::current($this->class_name);
			$this->data_list_settings->maximum_displayed_lines_count = null;
		}
		return $this->data_list_settings;
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
			$this->options = [$this->getDataListSettings()->sort];
			if ($this->select_all && Limit::in($options)) {
				$this->options[] = Dao::doublePass();
			}
		}
		$options = array_merge($this->options, is_array($options) ? $options : [$options]);
		$this->removeSearchOptions($options);
		return $options;
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
			$class = $this->getDataListSettings()->getClass();
			foreach ($class->getAnnotations('on_data_list') as $execute) {
				/** @var $execute Method_Annotation */
				if ($execute->call($class->name, [&$this->search]) === false) {
					break;
				}
			}
		}
		return empty($search)
			? $this->search
			: (empty($this->search) ? $search : [Func::andOp([$search, $this->search])]);
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
		$options = $this->getSearchOptions($options);

		if (empty($properties_path)) {
			$properties = array_keys($this->getDataListSettings()->properties);
			list($properties_path, $search) = $this->getDataListController()->removeInvisibleProperties(
				$this->class_name, $properties, $search
			);
		}

		$group_by = $this->getDataListController()->groupBy($this->getDataListSettings()->properties);
		if ($group_by) {
			$options[] = $group_by;
			$this->getDataListController()->groupConcat($properties_path, $group_by);
		}

		return $this->getDataListController()->readDataSelect(
			$this->class_name, $properties_path, $search, $options
		);
	}

	//----------------------------------------------------------------------------------- readObjects
	/**
	 * Reads all objects matching the filters
	 * Beware : this may consume a lot of time and memory if many objects are selected
	 * Do use only with limited sets
	 *
	 * @param $search  object|array    optional additional filters
	 * @param $options Option|Option[] optional options for advanced search
	 * @return object[]
	 */
	public function readObjects($search = null, $options = [])
	{
		$search  = $this->getSearchFilter($search);
		$options = $this->getSearchOptions($options);
		return Dao::search($search, $this->class_name, $options);
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

}
