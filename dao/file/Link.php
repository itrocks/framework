<?php
namespace ITRocks\Framework\Dao\File;

use ITRocks\Framework\Dao\Data_Link\Identifier_Map;
use ITRocks\Framework\Dao\Option;
use ITRocks\Framework\Dao\Sql\Column;
use ITRocks\Framework\Reflection\Reflection_Class;
use ITRocks\Framework\Reflection\Reflection_Property;
use ITRocks\Framework\Tools\Files;
use ITRocks\Framework\Tools\List_Data;

/**
 * This data link stores objects into files
 * - one directory per class (its path is the full class name)
 * - one file per object (its name is an internal integer identifier)
 */
class Link extends Identifier_Map
{

	//------------------------------------------------------------------------------------------ PATH
	/**
	 * File link configuration array keys constants
	 */
	const PATH = 'path';

	//----------------------------------------------------------------------------------------- $path
	/**
	 * The local storage path. Always has a trailing / set by __construct().
	 *
	 * @var string
	 */
	private $path;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * Construct a new File link into given path
	 *
	 * @param $parameters string[] ['path' => $local_storage_path]
	 */
	public function __construct($parameters = [])
	{
		if ($parameters) {
			foreach ($parameters as $parameter => $value) {
				$this->$parameter = $value;
			}
			Files::mkdir($this->path, 0700);
		}
		if (substr($this->path, -1) !== SL) {
			$this->path .= SL;
		}
	}

	//----------------------------------------------------------------------------------------- count
	/**
	 * Count the number of elements that match filter
	 *
	 * @param $what       object|string|array source object, class name or properties for filter
	 * @param $class_name string must be set if is $what is a filter array instead of a filter object
	 * @param $options    Option|Option[] array some options for advanced search
	 * @return integer
	 */
	public function count($what, $class_name = null, $options = [])
	{
		// TODO: Implement count() method.
		return 0;
	}

	//--------------------------------------------------------------------------------- createStorage
	/**
	 * Create a storage space for $class_name objects
	 *
	 * @param $class_name string
	 * @return boolean true if storage was created or updated, false if it was already up to date
	 */
	public function createStorage($class_name)
	{
		// TODO: Implement createStorage() method.
		return false;
	}

	//---------------------------------------------------------------------------------------- delete
	/**
	 * Delete an object from data source
	 *
	 * If object was originally read from data source, corresponding data will be overwritten.
	 * If object was not originally read from data source, nothing is done and returns false.
	 *
	 * @param $object object object to delete from data source
	 * @return boolean true if deleted
	 */
	public function delete($object)
	{
		// TODO: Implement delete() method.
		return false;
	}

	//--------------------------------------------------------------------------------------- getPath
	/**
	 * @return string
	 */
	public function getPath()
	{
		return $this->path;
	}

	//--------------------------------------------------------------------------- getStoredProperties
	/**
	 * Returns the list of properties of class $class that are stored into data link
	 *
	 * If data link stores properties not existing into $class, they are listed too, as if they where official properties of $class, but they storage object is a Sql\Column and not a Reflection_Property.
	 *
	 * @param $class string|Reflection_Class
	 * @return Reflection_Property[]|Column[]
	 */
	public function getStoredProperties($class)
	{
		// TODO: Implement getStoredProperties() method.
		return [];
	}

	//------------------------------------------------------------------------------ propertyFileName
	/**
	 * @param $object        object object from which to get the value of the property
	 * @param $property_name string the name of the property
	 * @return string
	 */
	public function propertyFileName($object, $property_name)
	{
		return $this->path
			. $this->storeNameOf(get_class($object)) . SL
			. $this->getObjectIdentifier($object) . '-' . $property_name;
	}

	//------------------------------------------------------------------------------------------ read
	/**
	 * Read an object from data source
	 *
	 * @param $identifier integer|object identifier for the object, or an object to re-read
	 * @param $class_name string class for read object. Useless if $identifier is an object
	 * @return object an object of class objectClass, read from data source, or null if nothing found
	 */
	public function read($identifier, $class_name = null)
	{
		// TODO: Implement read() method.
		return null;
	}

	//--------------------------------------------------------------------------------------- readAll
	/**
	 * Read all objects of a given class from data source
	 *
	 * @param $class_name string class name of read objects
	 * @param $options    Option|Option[] some options for advanced read
	 * @return object[] a collection of read objects
	 */
	public function readAll($class_name, $options = [])
	{
		// TODO: Implement readAll() method.
		return [];
	}

	//---------------------------------------------------------------------------------- readProperty
	/**
	 * Reads the value of a property from the data store
	 * Used only when @dao dao_name is used on a property which is not a @var @link (simple values)
	 *
	 * @param $object        object object from which to read the value of the property
	 * @param $property_name string the name of the property
	 * @return mixed the read value for the property read from the data link. null if no value stored
	 */
	public function readProperty($object, $property_name)
	{
		$file_name = $this->propertyFileName($object, $property_name);
		return (is_file($file_name)) ? file_get_contents($file_name) : null;
	}

	//----------------------------------------------------------------------------- replaceReferences
	/**
	 * Replace all references to $replaced by references to $replacement into the database.
	 * Already loaded objects will not be changed.
	 *
	 * @param $replaced    object
	 * @param $replacement object
	 * @return boolean true if replacement has been done, false if something went wrong
	 */
	public function replaceReferences($replaced, $replacement)
	{
		// TODO: Implement replaceReferences() method.
		return false;
	}

	//---------------------------------------------------------------------------------------- search
	/**
	 * Search objects from data source
	 *
	 * It is highly recommended to instantiate the $what object using Search_Object::instantiate() in order to initialize all properties as unset and build a correct search object.
	 * If some properties are an not-loaded objects, the search will be done on the object identifier, without joins to the linked object.
	 * If some properties are loaded objects : if the object comes from a read, the search will be done on the object identifier, without join. If object is not linked to data-link, the search is done with the linked object as others search criterion.
	 *
	 * @param $what       object|array source object for filter, or filter array (need class_name) only set properties will be used for search
	 * @param $class_name string must be set if is $what is a filter array instead of a filter object
	 * @param $options    Option|Option[] array some options for advanced search
	 * @return object[] a collection of read objects
	 */
	public function search($what, $class_name = null, $options = [])
	{
		// TODO: Implement search() method.
		return [];
	}

	//---------------------------------------------------------------------------------------- select
	/**
	 * Read selected columns only from data source, using optional filter
	 *
	 * @param $class         string class for the read object
	 * @param $properties    string[]|string|Column[] the list of property paths : only those
	 *        properties will be read.
	 * @param $filter_object object|array source object for filter, set properties will be used for search. Can be an array associating properties names to corresponding search value too.
	 * @param $options       Option|Option[] some options for advanced search
	 * @return List_Data a list of read records. Each record values (may be objects) are stored in the same order than columns.
	 */
	public function select($class, $properties, $filter_object = null, $options = [])
	{
		// TODO: Implement select() method.
		return null;
	}

	//-------------------------------------------------------------------------------------- truncate
	/**
	 * Truncates the data-set storing $class_name objects
	 * All data is deleted
	 *
	 * @param $class_name string
	 */
	public function truncate($class_name)
	{
		// TODO: Implement truncate() method
	}

	//----------------------------------------------------------------------------------------- write
	/**
	 * Write an object into data source
	 *
	 * If object was originally read from data source, corresponding data will be overwritten
	 * If object was not originally read from data source nor linked to it using replace(), a new
	 * record will be written into data source using this object's data.
	 *
	 * @param $object  object object to write into data source
	 * @param $options Option|Option[] some options for advanced write
	 * @return object the written object
	 */
	public function write($object, $options = [])
	{
		// TODO: Implement write() method.
		return null;
	}

	//--------------------------------------------------------------------------------- writeProperty
	/**
	 * Writes the value of a property into the data store
	 * Used only when @dao dao_name is used on a property which is not a @var @link (simple values)
	 *
	 * @param $object        object object from which to get the value of the property
	 * @param $property_name string the name of the property
	 * @param $value         mixed if set (recommended), the value to be stored. default in $object
	 */
	public function writeProperty($object, $property_name, $value = null)
	{
		$file_name = $this->propertyFileName($object, $property_name);
		$value = isset($value) ? $value : $object->$property_name;
		if (isset($value)) {
			Files::mkdir(lLastParse($file_name, SL), 0700);
			file_put_contents($file_name, $value);
		}
		elseif (is_file($file_name)) {
			unlink($file_name);
		}
	}

}
