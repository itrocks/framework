<?php
namespace ITRocks\Framework\Dao\Gaufrette;

use Gaufrette\Adapter\Local;
use ITRocks\Framework\Builder;
use ITRocks\Framework\Dao\Data_Link\Identifier_Map;
use ITRocks\Framework\Dao\Option;
use ITRocks\Framework\Dao\Sql\Column;
use ITRocks\Framework\Reflection\Reflection_Class;
use ITRocks\Framework\Reflection\Reflection_Property;
use ITRocks\Framework\Tools\List_Data;
use ReflectionProperty;

/**
 * This data link stores objects into files using knplabs/gaufrette library
 * Gaufrette provides a filesystem abstraction layer, many adapters are available.
 * (ftp, local storage, sftp, Apc, Aws ...)
 *
 * - one directory (or equivalent) per class (default is the class name's set)
 * - one file per object/property (its name is an internal integer identifier)
 *
 * The link is configurable and configuration must have this structure :
 * [
 *   self::ADAPTERS => [
 *     self::DEFAULT_ADAPTER => [
 *       'adapter_class_name'  => 'adapter_class_name',
 *       'arguments'           => [
 *         'construct_argument_name_1' => 'value'
 *         'construct_argument_name_N' => 'value'
 *       ]
 *     ],
 *     'business_class_name' => [
 *       'adapter_class_name'  => 'adapter_class_name',
 *       'arguments'           => [
 *         'construct_argument_name_1' => 'value'
 *         'construct_argument_name_N' => 'value'
 *       ]
 *     ],
 *     ...
 *   ]
 * ]
 */
class Link extends Identifier_Map
{

	//-------------------------------------------------------------------------------------- ADAPTERS
	/**
	 * Configuration key constant for adapters
	 */
	const ADAPTERS = 'adapters';

	//------------------------------------------------------------------------------- DEFAULT_ADAPTER
	/**
	 * Use to define a default adapter for all business class (if not set).
	 * see property doc of adapters.
	 */
	const DEFAULT_ADAPTER = 'default';

	//-------------------------------------------------------------------------------- $configuration
	/**
	 * Configuration of the link
	 *
	 * @var array
	 */
	private $configuration = [];

	//--------------------------------------------------------------------------------- $file_systems
	/**
	 * Instances of File_System used by the link
	 *
	 * @var File_System[] key is class name or self::DEFAULT_ADAPTER
	 */
	private $file_systems = [];

	//----------------------------------------------------------------------------------- __construct
	/**
	 * Construct a new File link into given path or adapters
	 *
	 * @param $configuration mixed[] see ::$adapters for format
	 * @throws Exception
	 */
	public function __construct($configuration = [])
	{
		if ($configuration) {
			if (empty($configuration[self::ADAPTERS][self::DEFAULT_ADAPTER])) {
				throw new Exception(
					'Configuration error for Gaufrette/Link : you should define a default_adapter'
				);
			}
			$this->configuration = array_merge($this->configuration, $configuration);
		}
	}

	//-------------------------------------------------------------------------- adapterConfiguration
	/**
	 * Get the configuration for given adapter name
	 *
	 * @param $adapter_name string
	 * @return array
	 */
	private function adapterConfiguration($adapter_name)
	{
		if (isset($this->configuration[self::ADAPTERS][$adapter_name])) {
			return $this->configuration[self::ADAPTERS][$adapter_name];
		}
		return $this->configuration[self::DEFAULT_ADAPTER];
	}

	//----------------------------------------------------------------------------------- adapterName
	/**
	 * Get the configured adapter name to use for object
	 *
	 * @param $object object
	 * @return string
	 */
	private function adapterName($object)
	{
		$class_name        = get_class($object);
		$source_class_name = Builder\Class_Builder::isBuilt($class_name)
			? Builder\Class_Builder::sourceClassName($class_name)
			: $class_name;

		if (isset($this->configuration[self::ADAPTERS][$source_class_name])) {
			return $source_class_name;
		}
		return self::DEFAULT_ADAPTER;
	}

	//----------------------------------------------------------------------------------------- count
	/**
	 * Count the number of elements that match filter
	 *
	 * @param $what       object|string|array source object, class name or properties for filter
	 * @param $class_name string must be set if is $what is a filter array instead of a filter object
	 * @return integer
	 */
	public function count($what, $class_name = null)
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

	//----------------------------------------------------------------------------------- getFilePath
	/**
	 * @noinspection PhpDocMissingThrowsInspection ReflectionException
	 * @param $object       object
	 * @param $prefix       string
	 * @param $storage_name string
	 * @return string|null
	 * @throws Exception
	 */
	private function getFilePath($object, $prefix, $storage_name)
	{
		$file_system = $this->getFileSystemFor($object);
		$adapter     = $file_system->filesystem->getAdapter();
		if ($adapter instanceof Local) {
			/** @noinspection PhpUnhandledExceptionInspection valid constants */
			$property = new ReflectionProperty(Local::class, 'directory');
			if (!$property->isPublic()) {
				$property->setAccessible(true);
			}
			$directory = $property->getValue($adapter);
			if (!$property->isPublic()) {
				$property->setAccessible(false);
			}
			if ($directory) {
				return $directory . SL . $prefix . $storage_name;
			}
		}
		return null;
	}

	//------------------------------------------------------------------------------ getFileSystemFor
	/**
	 * @param $object object
	 * @return File_System
	 * @throws Exception
	 */
	private function getFileSystemFor($object)
	{
		$adapter_name = $this->adapterName($object);
		if (!isset($this->file_systems[$adapter_name])) {
			$adapter_config                    = $this->adapterConfiguration($adapter_name);
			$this->file_systems[$adapter_name] = new File_System($adapter_name, $adapter_config);
		}
		return $this->file_systems[$adapter_name];
	}

	//------------------------------------------------------------------------------------- getPrefix
	/**
	 * Get the prefix for object and property to avoid duplicate names between classes
	 *
	 * @param $object        object object from which to get the value of the property
	 * @param $property_name string the name of the property
	 * @return string
	 */
	private function getPrefix(
		$object, /** @noinspection PhpUnusedParameterInspection */ $property_name
	) {
		return $this->storeNameOf(get_class($object));
	}

	//--------------------------------------------------------------------------- getStoredProperties
	/**
	 * Returns the list of properties of class $class that are stored into data link
	 *
	 * If data link stores properties not existing into $class,
	 * they are listed too, as if they where official properties of $class,
	 * but their storage object is a Sql\Column and not a Reflection_Property.
	 *
	 * @param $class string|Reflection_Class
	 * @return Reflection_Property[]|Column[]
	 */
	public function getStoredProperties($class)
	{
		// TODO: Implement getStoredProperties() method.
		return [];
	}

	//------------------------------------------------------------------------------------ needPrefix
	/**
	 * @param $object        object
	 * @param $property_name string
	 * @return boolean
	 */
	private function needPrefix(
		$object, /** @noinspection PhpUnusedParameterInspection */
		$property_name
	) {
		return ($this->adapterName($object) == self::DEFAULT_ADAPTER);
	}

	//------------------------------------------------------------------------------ propertyFileName
	/**
	 * @param $object        object object from which to get the value of the property
	 * @param $property_name string the name of the property
	 * @param $full_path     boolean if false, returns only the path relative to File_System
	 * @return string
	 * @throws Exception
	 */
	public function propertyFileName($object, $property_name, $full_path = true)
	{
		$prefix = $this->needPrefix($object, $property_name)
			? $this->getPrefix($object, $property_name) . SL
			: '';
		if (isA($object, Has_File::class)) {
			/** @var $object Has_File */
			if ($storage_name = $object->storage_name) {
				if ($full_path) {
					if ($file_path = $this->getFilePath($object, $prefix, $storage_name)) {
						return $file_path;
					}
				}
				return $prefix . $storage_name;
			}
		}
		if ($full_path) {
			if (
			$file_path = $this->getFilePath(
				$object, $prefix, $this->getObjectIdentifier($object) . '-' . $property_name
			)
			) {
				return $file_path;
			}
		}
		$file_name = $prefix . $this->getObjectIdentifier($object) . '-' . $property_name;
		return $file_name;
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
	 * @throws Exception
	 */
	public function readProperty($object, $property_name)
	{
		if ($file_system = $this->getFileSystemFor($object)) {
			$file_name = $this->propertyFileName($object, $property_name, false);
			if ($file_system->filesystem->has($file_name)) {
				return $file_system->filesystem->read($file_name);
			}
		}
		return null;
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
	 * It is highly recommended to instantiate the $what object using Search_Object::instantiate() in
	 * order to initialize all properties as unset and build a correct search object. If some
	 * properties are an not-loaded objects, the search will be done on the object identifier, without
	 * joins to the linked object.
	 * If some properties are loaded objects : if the object comes from a read, the search will be
	 * done on the object identifier, without join.
	 * If object is not linked to data-link, the search is done with the linked object as others
	 * search criteria.
	 *
	 * @param $what       object|array source object for filter, or filter array (need class_name)
	 *                    only set properties will be used for search
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
	 *                       properties will be read.
	 * @param $filter_object object|array source object for filter, set properties will be used for
	 *                       search. Can be an array associating properties names to corresponding
	 *                       search value too.
	 * @param $options       Option|Option[] some options for advanced search
	 * @return List_Data a list of read records. Each record values (may be objects) are stored in the
	 *                   same order than columns.
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
	 * @throws Exception
	 */
	public function writeProperty($object, $property_name, $value = null)
	{
		if ($file_system = $this->getFileSystemFor($object)) {
			$file_name = $this->propertyFileName($object, $property_name, false);
			$value     = isset($value) ? $value : $object->$property_name;
			if (isset($value)) {
				$file_system->filesystem->write($file_name, $value, true);
			}
			elseif ($file_system->filesystem->has($file_name)) {
				$file_system->filesystem->delete($file_name);
			}
		}
	}

}
