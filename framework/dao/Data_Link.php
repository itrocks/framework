<?php
namespace SAF\Framework;

abstract class Data_Link implements Configurable
{

	//---------------------------------------------------------------------------------------- $limit
	/**
	 * Limits the maximum count of objects that can be read at each call
	 * null or zero : no limit
	 *
	 * @var integer
	 */
	private $limit;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * Construct a new data link using parameters
	 *
	 * The $parameters array keys are : "limit".
	 *
	 * @param $parameters array
	 */
	public function __construct($parameters = null)
	{
		if (isset($parameters) && isset($parameters["limit"])) {
			$this->limit($parameters["limit"]);
		}
	}

	//---------------------------------------------------------------------------------------- delete
	/**
	 * Delete an object from data source
	 *
	 * If object was originally read from data source, corresponding data will be overwritten.
	 * If object was not originally read from data source, nothing is done and returns false.
	 *
	 * @param $object object object to delete from data source
	 * @return bool true if deleted
	 */
	abstract public function delete($object);

	//--------------------------------------------------------------------------- getStoredProperties
	/**
	 * Returns the list of properties of class $class that are stored into data link
	 *
	 * If data link stores properties not existing into $class, they are listed too, as if they where official properties of $class, but they storage object is a Dao_Column and not a Reflection_Property.
	 *
	 * @param $class string|Reflection_Class
	 * @return Reflection_Property[]|Dao_Column[]
	 */
	abstract public function getStoredProperties($class);

	//----------------------------------------------------------------------------------------- limit
	/**
	 * Sets/gets the count of read objects limit
	 *
	 * @param $length integer
	 * @return integer
	 */
	public function limit($length = null)
	{
		if (isset($length)) {
			$this->limit = $length;
		}
		return $this->limit;
	}

	//------------------------------------------------------------------------------------------ read
	/**
	 * Read an object from data source
	 *
	 * @param $identifier mixed identifier for the object
	 * @param $class      string class for read object
	 * @return object an object of class objectClass, read from data source, or null if nothing found
	 */
	abstract public function read($identifier, $class);

	//--------------------------------------------------------------------------------------- readAll
	/**
	 * Read all objects of a given class from data source
	 *
	 * @param $class string class for read objects
	 * @return object[] a collection of read objects
	 */
	abstract public function readAll($class);

	//--------------------------------------------------------------------------------------- replace
	/**
	 * Replace a destination object with the source object into the data source
	 *
	 * The source object overwrites the destination object into the data source, even if the source object was not originally read from the data source.
	 * Warning: as destination object will stay independent from source object but also linked to the same data source identifier. You will still be able to write() either source or destination after call to replace().
	 *
	 * @param $destination object destination object
	 * @param $source      object source object
	 * @return object the resulting $destination object
	 */
	abstract public function replace($destination, $source);

	//---------------------------------------------------------------------------------------- search
	/**
	 * Search objects from data source
	 *
	 * It is highly recommended to instantiate the $what object using Search_Object::instantiate() in order to initialize all properties as unset and build a correct search object.
	 * If some properties are an not-loaded objects, the search will be done on the object identifier, without joins to the linked object.
	 * If some properties are loaded objects : if the object comes from a read, the search will be done on the object identifier, without join. If object is not linked to data-link, the search is done with the linked object as others search criterion.
	 *
	 * @param $what       mixed source object for filter, or filter array (need class_name) only set properties will be used for search
	 * @param $class_name string must be set if is not a filter array
	 * @return object[] a collection of read objects
	 */
	abstract public function search($what, $class_name = null);

	//------------------------------------------------------------------------------------- searchOne
	/**
	 * Search one object from data source
	 *
	 * Same as search(), but expected result is one object only.
	 * It is highly recommended to use this search with primary keys properties values searches.
	 * If several result exist, only one will be taked, the first on the list (may be random).
	 *
	 * @param $what       object source object for filter, only set properties will be used for search
	 * @param $class_name string must be set if is not a filter array
	 * @return object|null the found object, or null if no object was found
	 */
	public function searchOne($what, $class_name = null)
	{
		$limit = isset($this->limit) ? $this->limit : null;
		$this->limit = 1;
		$result = $this->search($what, $class_name);
		$this->limit = $limit;
		return $result ? reset($result) : null;
	}

	//---------------------------------------------------------------------------------------- select
	/**
	 * Read selected columns only from data source, using optional filter
	 *
	 * @param $class         string class for the read object
	 * @param $columns       array  the list of the columns names : only those properties will be read. You can use "column.sub_column" to get values from linked objects from the same data source.
	 * @param $filter_object mixed source object for filter, set properties will be used for search. Can be an array associating properties names to corresponding search value too.
	 * @return List_Data a list of read records. Each record values (may be objects) are stored in the same order than columns.
	 */
	abstract public function select($class, $columns, $filter_object = null);

	//----------------------------------------------------------------------------------- storeNameOf
	/**
	 * Gets the store name for records typed as $class_name
	 *
	 * @param $class_name string
	 * @return string
	 */
	public function storeNameOf($class_name)
	{
		return strtolower(Namespaces::shortClassName(Names::classToSet($class_name)));
	}

	//---------------------------------------------------------------------------------- valueChanged
	/**
	 * Returns true if the element's property value changed since previous value and if it is not empty
	 *
	 * @param $element       object
	 * @param $property_name string
	 * @param $default_value mixed
	 * @return boolean
	 */
	protected function valueChanged($element, $property_name, $default_value)
	{
		$id_property_name = "id_" . $property_name;
		if (!isset($element->$property_name) && empty($id_property_name)) {
			return false;
		}
		$element_value = $element->$property_name;
		if (is_object($element_value)) {
			$class = Reflection_Class::getInstanceOf(get_class($element_value));
			$defaults = $class->getDefaultProperties();
			foreach ($class->getListAnnotation("representative")->values() as $property_name) {
				if (
					isset($defaults[$property_name])
					&& $this->valueChanged($element_value, $property_name, $defaults[$property_name])
				) {
					return true;
				}
			}
			return false;
		}
		else {
			return isset($element_value)
				&& (strval($element_value) != "")
				&& (strval($element_value) != strval($default_value));
		}
	}

	//----------------------------------------------------------------------------------------- write
	/**
	 * Write an object into data source
	 *
	 * If object was originally read from data source, corresponding data will be overwritten
	 * If object was not originally read from data source nor linked to it using replace(), a new
	 * record will be written into data source using this object's data.
	 *
	 * @param $object object object to write into data source
	 * @return object the written object
	 */
	abstract public function write($object);

}
