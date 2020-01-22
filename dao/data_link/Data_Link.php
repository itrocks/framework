<?php
namespace ITRocks\Framework\Dao;

use ITRocks\Framework\Builder;
use ITRocks\Framework\Dao;
use ITRocks\Framework\Dao\Data_Link\After_Action;
use ITRocks\Framework\Dao\Data_Link\Write;
use ITRocks\Framework\Dao\Func\Column;
use ITRocks\Framework\Dao\Option\Key;
use ITRocks\Framework\PHP\Dependency;
use ITRocks\Framework\Reflection\Annotation\Class_\Representative_Annotation;
use ITRocks\Framework\Reflection\Annotation\Class_\Store_Name_Annotation;
use ITRocks\Framework\Reflection\Annotation\Template\Method_Annotation;
use ITRocks\Framework\Reflection\Link_Class;
use ITRocks\Framework\Reflection\Reflection_Class;
use ITRocks\Framework\Reflection\Reflection_Property;
use ITRocks\Framework\Tools\List_Data;
use ITRocks\Framework\Tools\Names;
use ITRocks\Framework\Tools\Namespaces;

/**
 * This class stores methods common to all data links classes,
 * and defines mandatory methods prototypes to be implemented by descendants
 */
abstract class Data_Link
{

	//--------------------------------------------------------------------------------- $after_commit
	/**
	 * @var After_Action[]
	 */
	public $after_commit;

	//----------------------------------------------------------------------------------- afterCommit
	/**
	 * This is called after a commit, for objects that have some @after_commit :
	 * - in case of non-transactional data-link : after each call to write
	 * - in case of transactional data-link but outside a transaction : after each call to write
	 * - in case of transactional data-link, inside a transaction : after call of commit()
	 */
	public function afterCommit()
	{
		if ($this->after_commit) {
			foreach ($this->after_commit as $after_commit) {
				if ($after_commit->call($this) === false) {
					break;
				}
			}
			$this->after_commit = [];
		}
	}

	//------------------------------------------------------------------------------------- afterRead
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $object object
	 */
	public function afterRead($object)
	{
		/** @noinspection PhpUnhandledExceptionInspection Class of an object is always valid */
		foreach ((new Reflection_Class($object))->getAnnotations('after_read') as $after_read) {
			/** @var $after_read Method_Annotation */
			$options = [];
			if ($after_read->call($object, [$this, &$options]) === false) {
				break;
			}
		}
	}

	//----------------------------------------------------------------------------- afterReadMultiple
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $objects object[]
	 * @param $options Option[]
	 */
	public function afterReadMultiple(array $objects, array &$options = [])
	{
		if ($objects) {
			/** @noinspection PhpUnhandledExceptionInspection Class of an object is always valid */
			/** @var $after_reads Method_Annotation[] */
			$after_reads = (new Reflection_Class(reset($objects)))->getAnnotations('after_read');
			foreach ($objects as $object) {
				foreach ($after_reads as $after_read) {
					if ($after_read->call($object, [$this, &$options]) === false) {
						break;
					}
				}
			}
		}
	}

	//---------------------------------------------------------------------------------- classNamesOf
	/**
	 * Gets the names of the classes associated to a store set name
	 *
	 * Several classes can match a single store set name
	 *
	 * @example 'my_addresses' will become 'A\Namespace\My\Address'
	 * @param $store_name string
	 * @return string[] Full class names with namespace
	 */
	public function classNamesOf($store_name)
	{
		$dependencies = Dao::search(
			['dependency_name' => $store_name, 'type' => Dependency::T_STORE], Dependency::class
		);
		if ($dependencies) {
			$class_names = [];
			foreach ($dependencies as $dependency) {
				$class_names[] = Builder::className($dependency->class_name);
			}
		}
		else {
			$class_name = Namespaces::fullClassName(
				Names::setToClass(str_replace(SP, '_', ucwords(str_replace('_', SP, $store_name))), false),
				false
			);
			if (strpos($class_name, BS) === false) {
				$class_name = explode('_', $class_name);
				foreach ($class_name as $key => $class_name_part) {
					$class_name[$key] = Names::setToClass($class_name_part, false);
				}
				$class_names = [
					Builder::className(Namespaces::fullClassName(join('_', $class_name), false))
				];
			}
			else {
				$class_names = [$class_name];
			}
		}
		return $class_names;
	}

	//----------------------------------------------------------------------------------------- count
	/**
	 * Count the number of elements that match filter
	 *
	 * @param $what       object|string|array source object, class name or properties for filter
	 * @param $class_name string must be set if is $what is a filter array instead of a filter object
	 * @return integer
	 */
	abstract public function count($what, $class_name = null);

	//--------------------------------------------------------------------------------- createStorage
	/**
	 * Create a storage space for $class_name objects
	 *
	 * @param $class_name string
	 * @return boolean true if storage was created or updated, false if it was already up to date
	 */
	abstract public function createStorage($class_name);

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
	abstract public function delete($object);

	//------------------------------------------------------------------------------------ disconnect
	/**
	 * Disconnect an object from current data link
	 *
	 * @param $object              object object to disconnect from data source
	 * @param $load_linked_objects boolean if true, load linked objects before disconnect
	 * @see Data_Link::disconnect()
	 */
	abstract public function disconnect($object, $load_linked_objects = false);

	//---------------------------------------------------------------------------------- escapeString
	/**
	 * Escape string into string or binary values
	 *
	 * @param $value string
	 * @return string
	 */
	public function escapeString($value)
	{
		return str_replace([Q, DQ], [BS . Q, BS . DQ], $value);
	}

	//---------------------------------------------------------------------------- getKeyPropertyName
	/**
	 * Gets the key property name taken from any set Sql\Key_Option
	 * Default will be 'id'
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $class_name string
	 * @param $options    Option[]
	 * @return callable|string|string[]
	 */
	protected function getKeyPropertyName($class_name, array $options = null)
	{
		if ($options) foreach ($options as $option) if ($option instanceof Key) {
			return $option->property_name;
		}
		/** @noinspection PhpUnhandledExceptionInspection */
		if ((new Reflection_Class($class_name))->isAbstract()) {
			return ['id', 'class_name'];
		}
		/** @noinspection PhpUnhandledExceptionInspection You must call it with a valid class */
		$class = new Link_Class($class_name);
		if ($class->getLinkedClassName()) {
			$key = [];
			foreach ($class->getUniqueProperties() as $property) {
				$key[] = $property->name;
			}
			return $key;
		}
		return 'id';
	}

	//--------------------------------------------------------------------------- getStoredProperties
	/**
	 * Returns the list of properties of class $class that are stored into data link
	 *
	 * If data link stores properties not existing into $class, they are listed too, as if they where
	 * official properties of $class, but they storage object is a Sql\Column and not
	 * a Reflection_Property.
	 *
	 * @param $class string|Reflection_Class
	 * @return Reflection_Property[]|Sql\Column[]
	 */
	abstract public function getStoredProperties($class);

	//-------------------------------------------------------------------------------------- getWrite
	/**
	 * Get a new Write object matching the data link
	 *
	 * @param $object  object
	 * @param $options Option[]
	 * @return Write
	 */
	public function getWrite($object = null, array $options = [])
	{
		$write_class = Namespaces::of(get_class($this)) . BS . 'Write';
		return new $write_class($this, $object, $options);
	}

	//-------------------------------------------------------------------------------------------- is
	/**
	 * Returns true if object1 and object2 match the same stored object
	 *
	 * @param $object1 object
	 * @param $object2 object
	 * @param $strict  boolean if true, will consider @link object and non-@link object as different
	 * @return boolean
	 */
	abstract public function is($object1, $object2, $strict = false);

	//------------------------------------------------------------------------------------------ read
	/**
	 * Read an object from data source
	 *
	 * @param $identifier mixed|object identifier for the object
	 * @param $class_name string class for read object
	 * @return object an object of class objectClass, read from data source, or null if nothing found
	 */
	abstract public function read($identifier, $class_name);

	//--------------------------------------------------------------------------------------- readAll
	/**
	 * Read all objects of a given class from data source
	 *
	 * @param $class_name string class name of read objects
	 * @param $options    Option|Option[] some options for advanced read
	 * @return object[] a collection of read objects
	 */
	abstract public function readAll($class_name, $options = []);

	//---------------------------------------------------------------------------------- readProperty
	/**
	 * Reads the value of a property from the data store
	 * Used only when @dao dao_name is used on a property which is not a @var @link (simple values)
	 *
	 * @param $object        object object from which to read the value of the property
	 * @param $property_name string the name of the property
	 * @return mixed the read value for the property read from the data link. null if no value stored
	 */
	abstract public function readProperty($object, $property_name);

	//--------------------------------------------------------------------------------------- replace
	/**
	 * Replace a destination object with the source object into the data source
	 *
	 * The source object overwrites the destination object into the data source, even if the
	 * source object was not originally read from the data source.
	 * Warning: as destination object will stay independent from source object but also linked to the
	 * same data source identifier. You will still be able to write() either source or destination
	 * after call to replace().
	 *
	 * @param $destination object destination object
	 * @param $source      object source object
	 * @param $write       boolean true if the destination object must be immediately written
	 * @return object the resulting $destination object
	 */
	abstract public function replace($destination, $source, $write = true);

	//----------------------------------------------------------------------------- replaceReferences
	/**
	 * Replace all references to $replaced by references to $replacement into the database.
	 * Already loaded objects will not be changed.
	 *
	 * @param $replaced    object
	 * @param $replacement object
	 * @return boolean true if replacement has been done, false if something went wrong
	 */
	abstract public function replaceReferences($replaced, $replacement);

	//---------------------------------------------------------------------------------------- search
	/**
	 * Search objects from data source
	 *
	 * It is highly recommended to instantiate the $what object using Search_Object::instantiate()
	 * in order to initialize all properties as unset and build a correct search object.
	 * If some properties are an not-loaded objects, the search will be done on the object identifier,
	 * without joins to the linked object.
	 * If some properties are loaded objects : if the object comes from a read, the search will
	 * be done on the object identifier, without join. If object is not linked to data-link,
	 * the search is done with the linked object as others search criterion.
	 *
	 * @param $what       object|array source object for filter, or filter array (need class_name)
	 *                    only set properties will be used for search
	 * @param $class_name string must be set if is $what is a filter array instead of a filter object
	 * @param $options    Option|Option[] array some options for advanced search
	 * @return object[] a collection of read objects
	 */
	abstract public function search($what, $class_name = null, $options = []);

	//------------------------------------------------------------------------------------- searchOne
	/**
	 * Search one object from data source
	 *
	 * Same as search(), but expected result is one object only.
	 * It is highly recommended to use this search with primary keys properties values searches.
	 * If several result exist, only one will be taken, the first on the list (may be random).
	 *
	 * @param $what       object|array source object for filter, only set properties will be used for
	 *        search
	 * @param $class_name string must be set if is not a filter array
	 * @param $options    Option|Option[] some options for advanced search
	 * @return object|null the found object, or null if no object was found
	 */
	public function searchOne($what, $class_name = null, $options = [])
	{
		if (!is_array($options)) {
			$options = $options ? [$options] : [];
		}
		$options[] = Dao::limit(1);
		$result    = $this->search($what, $class_name, $options);
		return $result ? reset($result) : null;
	}

	//---------------------------------------------------------------------------------------- select
	/**
	 * Read selected columns only from data source, using optional filter
	 *
	 * @param $class         string class for the read object
	 * @param $properties    string[]|string|Column[] the list of property paths : only those
	 *        properties will be read.
	 * @param $filter_object object|array source object for filter, set properties will be used for
	 *        search. Can be an array associating properties names to corresponding search value too.
	 * @param $options Option|Option[] some options for advanced search
	 * @return List_Data a list of read records. Each record values (may be objects) are stored in
	 *         the same order than columns.
	 */
	abstract public function select($class, $properties, $filter_object = null, $options = []);

	//----------------------------------------------------------------------------------- storeNameOf
	/**
	 * Gets the store name for records typed as $class_name
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $class_name string
	 * @return string
	 */
	public function storeNameOf($class_name)
	{
		/** @noinspection PhpUnhandledExceptionInspection $class_name must always be valid */
		return Store_Name_Annotation::of(new Reflection_Class($class_name))->value;
	}

	//-------------------------------------------------------------------------------------- truncate
	/**
	 * Truncates the data-set storing $class_name objects
	 * All data is deleted
	 *
	 * @param $class_name string
	 */
	abstract public function truncate($class_name);

	//---------------------------------------------------------------------------------- valueChanged
	/**
	 * Returns true if the element's property value changed since previous value
	 * and if it is not empty
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $element       object
	 * @param $property_name string
	 * @param $default_value mixed
	 * @return boolean
	 */
	protected function valueChanged($element, $property_name, $default_value)
	{
		$id_property_name = 'id_' . $property_name;
		if (!isset($element->$property_name) && empty($id_property_name)) {
			return false;
		}
		$element_value = $element->$property_name;
		if (is_object($element_value)) {
			/** @noinspection PhpUnhandledExceptionInspection Class of an object is always valid */
			$class    = new Reflection_Class($element_value);
			$defaults = $class->getDefaultProperties([T_EXTENDS]);
			foreach (Representative_Annotation::of($class)->values() as $property_name) {
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
				&& (strval($element_value) != '')
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
	 * @param $object  object object to write into data source
	 * @param $options Option|Option[] some options for advanced write
	 * @return object the written object
	 */
	abstract public function write($object, $options = []);

	//--------------------------------------------------------------------------------- writeProperty
	/**
	 * Writes the value of a property into the data store
	 * Used only when @dao dao_name is used on a property which is not a @var @link (simple values)
	 *
	 * @param $object        object object from which to get the value of the property
	 * @param $property_name string the name of the property
	 * @param $value         mixed if set (recommended), the value to be stored. default in $object
	 */
	abstract public function writeProperty($object, $property_name, $value = null);

}
