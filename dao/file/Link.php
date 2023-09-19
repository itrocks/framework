<?php
namespace ITRocks\Framework\Dao\File;

use ITRocks\Framework\Dao\Data_Link\Identifier_Map;
use ITRocks\Framework\Dao\Option;
use ITRocks\Framework\Dao\Sql\Column;
use ITRocks\Framework\Reflection\Reflection_Class;
use ITRocks\Framework\Reflection\Reflection_Property;
use ITRocks\Framework\Tools\Default_List_Data;
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
	/** The local storage path. Always has a trailing / set by __construct() */
	public string $path;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * Construct a new File link into given path
	 *
	 * @noinspection PhpTypedPropertyMightBeUninitializedInspection $this->path must be configured
	 * @param $parameters string[] ['path' => $local_storage_path]
	 */
	public function __construct(array $parameters = [])
	{
		if ($parameters) {
			foreach ($parameters as $parameter => $value) {
				$this->$parameter = $value;
			}
			Files::mkdir($this->path, 0700);
		}
		if (!str_ends_with($this->path, SL)) {
			$this->path .= SL;
		}
	}

	//----------------------------------------------------------------------------------------- count
	/**
	 * Count the number of elements that match filter
	 *
	 * @param $what       array|object|string source object, class name or properties for filter
	 * @param $class_name string|null must be set if $what is a filter array instead of an object
	 * @param $options    Option|Option[] array some options for advanced search
	 */
	public function count(
		array|object|string $what, string $class_name = null, array|Option $options = []
	) : int
	{
		// TODO: Implement count() method.
		return 0;
	}

	//--------------------------------------------------------------------------------- createStorage
	/**
	 * Create a storage space for $class_name objects
	 *
	 * @return boolean true if storage was created or updated, false if it was already up-to-date
	 */
	public function createStorage(string $class_name) : bool
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
	public function delete(object $object) : bool
	{
		// TODO: Implement delete() method.
		return false;
	}

	//--------------------------------------------------------------------------------------- getPath
	public function getPath() : string
	{
		return $this->path;
	}

	//--------------------------------------------------------------------------- getStoredProperties
	/**
	 * Returns the list of properties of class $class that are stored into data link
	 *
	 * If data link stores properties not existing into $class, they are listed too, as if they were
	 * official properties of $class, but they storage object is a Sql\Column and not a
	 * Reflection_Property.
	 *
	 * @return Reflection_Property[]|Column[]
	 */
	public function getStoredProperties(Reflection_Class $class) : array
	{
		// TODO: Implement getStoredProperties() method.
		return [];
	}

	//------------------------------------------------------------------------------ propertyFileName
	/**
	 * @param $object        object object from which to get the value of the property
	 * @param $property_name string the name of the property
	 */
	public function propertyFileName(object $object, string $property_name) : string
	{
		return $this->path
			. $this->storeNameOf($object) . SL
			. $this->getObjectIdentifier($object) . '-' . $property_name;
	}

	//------------------------------------------------------------------------------------------ read
	/**
	 * Read an object from data source
	 *
	 * @param $identifier integer|T identifier for the object, or an object to re-read
	 * @param $class_name class-string<T>|null class for read object. Useless if $identifier is an
	 *                    object
	 * @return ?T an object of class objectClass, read from data source, or null if nothing found
	 * @template T
	 */
	public function read(mixed $identifier, string $class_name = null) : ?object
	{
		// TODO: Implement read() method.
		return null;
	}

	//--------------------------------------------------------------------------------------- readAll
	/**
	 * Read all objects of a given class from data source
	 *
	 * @param $class_name class-string<T> class name of read objects
	 * @param $options    Option|Option[] some options for advanced read
	 * @return T[] a collection of read objects
	 * @template T
	 */
	public function readAll(string $class_name, array|Option $options = []) : array
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
	 * @return ?string read value for the property read from the data link, null if no value stored
	 */
	public function readProperty(object $object, string $property_name) : ?string
	{
		$file_name = $this->propertyFileName($object, $property_name);
		return (is_file($file_name)) ? file_get_contents($file_name) : null;
	}

	//----------------------------------------------------------------------------- replaceReferences
	/**
	 * Replace all references to $replaced by references to $replacement into the database.
	 * Already loaded objects will not be changed.
	 *
	 * @param $replaced    T
	 * @param $replacement T
	 * @return boolean true if replacement has been done, false if something went wrong
	 * @template T
	 */
	public function replaceReferences(object $replaced, object $replacement) : bool
	{
		// TODO: Implement replaceReferences() method.
		return false;
	}

	//---------------------------------------------------------------------------------------- search
	/**
	 * Search objects from data source
	 *
	 * It is highly recommended to instantiate the $what object using Search_Object::instantiate() in
	 * order to initialize all properties as unset and build a correct search object.
	 * If some properties are an not-loaded objects, the search will be done on the object identifier,
	 * without joins to the linked object.
	 * If some properties are loaded objects : if the object comes from a read, the search will be
	 * done on the object identifier, without join. If object is not linked to data-link, the search
	 * is done with the linked object as others search criterion.
	 *
	 * @param $what       array|T|null source object for filter, or filter array
	 *                    (need class_name) only set properties will be used for search
	 * @param $class_name class-string<T>|null must be set if is $what is a filter array instead of an
	 *                    object
	 * @param $options    Option|Option[] array some options for advanced search
	 * @return object[] a collection of read objects
	 * @template T
	 */
	public function search(
		array|object|null $what, string $class_name = null, array|Option $options = []
	) : array
	{
		// TODO: Implement search() method.
		return [];
	}

	//---------------------------------------------------------------------------------------- select
	/**
	 * Read selected columns only from data source, using optional filter
	 *
	 * @param $class         class-string<T> class for the read object
	 * @param $properties    string[]|string|Column[] the list of property paths : only those
	 *                       properties will be read.
	 * @param $filter_object array|T|null source object for filter, set properties will be used
	 *                       for search. Can be an array associating properties names to matching
	 *                       search value too.
	 * @param $options       Option|Option[] some options for advanced search
	 * @return List_Data a list of read records. Each record values (may be objects) are stored in the
	 *                   same order than columns.
	 * @template T
	 */
	public function select(
		string $class, array|string $properties, array|object $filter_object = null,
		array|Option $options = []
	) : List_Data
	{
		// TODO: Implement select() method.
		return new Default_List_Data($class, []);
	}

	//-------------------------------------------------------------------------------------- truncate
	/** Truncates the data-set storing $class_name objects. All data is deleted */
	public function truncate(string $class_name) : void
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
	 * @param $object  T object to write into data source
	 * @param $options Option|Option[] some options for advanced write
	 * @return ?T the written object
	 * @template T
	 */
	public function write(object $object, array|object $options = []) : ?object
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
	public function writeProperty(object $object, string $property_name, mixed $value = null) : void
	{
		$file_name = $this->propertyFileName($object, $property_name);
		$value     = $value ?? $object->$property_name;
		if (isset($value)) {
			Files::mkdir(lLastParse($file_name, SL), 0700);
			file_put_contents($file_name, $value);
		}
		elseif (is_file($file_name)) {
			unlink($file_name);
		}
	}

}
