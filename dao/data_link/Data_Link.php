<?php
namespace ITRocks\Framework\Dao;

use ITRocks\Framework\Builder;
use ITRocks\Framework\Dao;
use ITRocks\Framework\Dao\Data_Link\After_Action;
use ITRocks\Framework\Dao\Data_Link\Write;
use ITRocks\Framework\Dao\Func\Column;
use ITRocks\Framework\Dao\Option\Key;
use ITRocks\Framework\PHP\Dependency;
use ITRocks\Framework\Reflection\Annotation\Template\Method_Annotation;
use ITRocks\Framework\Reflection\Attribute\Class_\Representative;
use ITRocks\Framework\Reflection\Attribute\Class_\Store;
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
	public array $after_commit = [];

	//----------------------------------------------------------------------------------- afterCommit
	/**
	 * This is called after a commit, for objects that have some @after_commit :
	 * - in case of non-transactional data-link : after each call to write
	 * - in case of transactional data-link but outside a transaction : after each call to write
	 * - in case of transactional data-link, inside a transaction : after call of commit()
	 */
	public function afterCommit() : void
	{
		if (!$this->after_commit) {
			return;
		}
		After_Action::callAll($this->after_commit, $this);
		$this->after_commit = [];
	}

	//------------------------------------------------------------------------------------- afterRead
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $object object
	 */
	public function afterRead(object $object) : void
	{
		$options = [];
		/** @noinspection PhpUnhandledExceptionInspection Class of an object is always valid */
		/** @var $after_read_annotations Method_Annotation[] */
		$after_read_annotations = (new Reflection_Class($object))->getAnnotations('after_read');
		Method_Annotation::callAll($after_read_annotations, $object, [$this, &$options]);
	}

	//----------------------------------------------------------------------------- afterReadMultiple
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $objects object[]
	 * @param $options Option[]
	 */
	public function afterReadMultiple(array $objects, array &$options = []) : void
	{
		if ($objects) {
			/** @noinspection PhpUnhandledExceptionInspection Class of an object is always valid */
			/** @var $after_reads Method_Annotation[] */
			$after_reads = (new Reflection_Class(reset($objects)))->getAnnotations('after_read');
			foreach ($objects as $object) {
				Method_Annotation::callAll($after_reads, $object, [$this, &$options]);
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
	public function classNamesOf(string $store_name) : array
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
			if (!str_contains($class_name, BS)) {
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
	 * @param $what       array|object|string source object, class name or properties for filter
	 * @param $class_name string|null must be set if $what is a filter array instead of an object
	 * @param $options    Option|Option[] array some options for advanced search
	 * @return integer
	 */
	abstract public function count(
		array|object|string $what, string $class_name = null, array|Option $options = []
	) : int;

	//--------------------------------------------------------------------------------- createStorage
	/**
	 * Create a storage space for $class_name objects
	 *
	 * @param $class_name string
	 * @return boolean true if storage was created or updated, false if it was already up-to-date
	 */
	abstract public function createStorage(string $class_name) : bool;

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
	abstract public function delete(object $object) : bool;

	//------------------------------------------------------------------------------------ disconnect
	/**
	 * Disconnect an object from current data link
	 *
	 * @param $object              object object to disconnect from data source
	 * @param $load_linked_objects boolean if true, load linked objects before disconnect
	 * @see Data_Link::disconnect()
	 */
	abstract public function disconnect(object $object, bool $load_linked_objects = false) : void;

	//---------------------------------------------------------------------------------- escapeString
	/**
	 * Escape string into string or binary values
	 *
	 * @param $value string
	 * @return string
	 */
	public function escapeString(string $value) : string
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
	protected function getKeyPropertyName(string $class_name, array $options = [])
		: array|callable|string
	{
		foreach ($options as $option) if ($option instanceof Key) {
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
	 * If data link stores properties not existing into $class, they are listed too, as if they were
	 * official properties of $class, but they storage object is a Sql\Column and not a
	 * Reflection_Property.
	 *
	 * @param $class Reflection_Class
	 * @return Reflection_Property[]|Sql\Column[]
	 */
	abstract public function getStoredProperties(Reflection_Class $class) : array;

	//-------------------------------------------------------------------------------------- getWrite
	/**
	 * Get a new Write object matching the data link
	 *
	 * @param $object  object|null
	 * @param $options Option[]
	 * @return Write
	 */
	public function getWrite(object $object = null, array $options = []) : Write
	{
		$write_class = Namespaces::of($this) . BS . 'Write';
		return new $write_class($this, $object, $options);
	}

	//-------------------------------------------------------------------------------------------- is
	/**
	 * Returns true if object1 and object2 match the same stored object
	 *
	 * @param $object1 ?object
	 * @param $object2 ?object
	 * @param $strict  boolean if true, will consider @link object and non-@link object different
	 * @return boolean
	 */
	abstract public function is(?object $object1, ?object $object2, bool $strict = false) : bool;

	//------------------------------------------------------------------------------------------ read
	/**
	 * Read an object from data source
	 *
	 * @param $identifier mixed|T identifier for the object
	 * @param $class_name class-string<T>|null class for read object
	 * @return ?T an object of class objectClass, read from data source, or null if nothing found
	 * @template T
	 */
	abstract public function read(mixed $identifier, string $class_name = null) : ?object;

	//--------------------------------------------------------------------------------------- readAll
	/**
	 * Read all objects of a given class from data source
	 *
	 * @param $class_name class-string<T> class name of read objects
	 * @param $options    Option|Option[] some options for advanced read
	 * @return T[] a collection of read objects
	 * @template T
	 */
	abstract public function readAll(string $class_name, array|Option $options = []) : array;

	//---------------------------------------------------------------------------------- readProperty
	/**
	 * Reads the value of a property from the data store
	 * Used only when @dao dao_name is used on a property which is not a @var @link (simple values)
	 *
	 * @param $object        object object from which to read the value of the property
	 * @param $property_name string the name of the property
	 * @return mixed the read value for the property read from the data link. null if no value stored
	 */
	abstract public function readProperty(object $object, string $property_name) : mixed;

	//--------------------------------------------------------------------------------------- replace
	/**
	 * Replace a destination object with the source object into the data source
	 *
	 * The source object overwrites the destination object into the data source, even if the
	 * source object was not originally read from the data source.
	 * Warning: as destination object will stay independent of source object but also linked to the
	 * same data source identifier. You will still be able to write() either source or destination
	 * after call to replace().
	 *
	 * @param $destination T Destination object
	 * @param $source      T Source object
	 * @param $write       boolean true if the destination object must be immediately written
	 * @return T the resulting $destination object
	 * @template T
	 */
	abstract public function replace(object $destination, object $source, bool $write = true)
		: object;

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
	abstract public function replaceReferences(object $replaced, object $replacement) : bool;

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
	 * @param $what       array|T|null source object for filter, or filter array
	 *                    (need class_name) only set properties will be used for search
	 * @param $class_name class-string<T>|null must be set if is $what is a filter array instead of
	 *                    an object
	 * @param $options    Option|Option[] array some options for advanced search
	 * @return T[] a collection of read objects
	 * @template T
	 */
	abstract public function search(
		array|object|null $what, string $class_name = null, array|Option $options = []
	) : array;

	//------------------------------------------------------------------------------------- searchOne
	/**
	 * Search one object from data source
	 *
	 * Same as search(), but expected result is one object only.
	 * It is highly recommended to use this search with primary keys properties values searches.
	 * If several result exist, only one will be taken, the first on the list (may be random).
	 *
	 * @param $what       array|T source object for filter, only set properties will be used for
	 *        search
	 * @param $class_name class-string<T>|null must be set if is not a filter array
	 * @param $options    Option|Option[] some options for advanced search
	 * @return ?T the found object, or null if no object was found
	 * @template T
	 */
	public function searchOne(
		array|object $what, string $class_name = null, array|Option $options = []
	) : ?object
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
	 * @param $class         class-string<T> class for the read object
	 * @param $properties    string[]|string|Column[] the list of property paths : only those
	 *                       properties will be read.
	 * @param $filter_object array|T|null source object for filter, set properties will be used
	 *                       for search. Can be an array associating properties names to matching
	 *                       search value too.
	 * @param $options       Option|Option[] some options for advanced search
	 * @return List_Data a list of read records. Each record values (may be objects) are stored in
	 *                   the same order than columns.
	 * @template T
	 */
	abstract public function select(
		string $class, array|string $properties, array|object $filter_object = null,
		array|Option $options = []
	) : List_Data;

	//----------------------------------------------------------------------------------- storeNameOf
	/**
	 * Gets the store name for records typed as $class_name
	 */
	public function storeNameOf(object|string $class) : string
	{
		/** @noinspection PhpUnhandledExceptionInspection $class_name must always be valid */
		$class = ($class instanceof Reflection_Class) ? $class : new Reflection_Class($class);
		$value = Store::of($class)->storeName();
		if (!$value) {
			// TODO should never happen
			trigger_error('Missing #Store for class ' . $class->getName(), E_USER_ERROR);
		}
		return $value;
	}

	//-------------------------------------------------------------------------------------- truncate
	/**
	 * Truncates the data-set storing $class_name objects
	 * All data is deleted
	 *
	 * @param $class_name string
	 */
	abstract public function truncate(string $class_name) : void;

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
	protected function valueChanged(object $element, string $property_name, mixed $default_value)
		: bool
	{
		if (!isset($element->$property_name)) {
			return false;
		}
		$element_value = $element->$property_name;
		if (is_object($element_value)) {
			/** @noinspection PhpUnhandledExceptionInspection Class of an object is always valid */
			$class    = new Reflection_Class($element_value);
			$defaults = $class->getDefaultProperties([T_EXTENDS]);
			foreach (Representative::of($class)->values as $property_name) {
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
				&& (strval($element_value) !== '')
				&& (strval($element_value) !== strval($default_value));
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
	 * @param $object  T object to write into data source
	 * @param $options Option|Option[] some options for advanced write
	 * @return ?T the written object
	 * @template T
	 */
	abstract public function write(object $object, array|Option $options = []) : ?object;

	//--------------------------------------------------------------------------------- writeProperty
	/**
	 * Writes the value of a property into the data store
	 * Used only when @dao dao_name is used on a property which is not a @var @link (simple values)
	 *
	 * @param $object        object object from which to get the value of the property
	 * @param $property_name string the name of the property
	 * @param $value         mixed if set (recommended), the value to be stored. default in $object
	 */
	abstract public function writeProperty(
		object $object, string $property_name, mixed $value = null
	) : void;

}
