<?php

namespace ITRocks\Framework\Dao\Hub;

use Bappli\Hub_Client\Http\Http_Service;
use ITRocks\Framework\Builder;
use ITRocks\Framework\Dao\Func;
use ITRocks\Framework\Dao\Func\Column;
use ITRocks\Framework\Dao\Func\Dao_Function;
use ITRocks\Framework\Dao\Func\Expressions;
use ITRocks\Framework\Dao\Mysql;
use ITRocks\Framework\Dao;
use ITRocks\Framework\Dao\Option;
use ITRocks\Framework\Dao\Sql;
use ITRocks\Framework\Reflection\Reflection_Class;
use ITRocks\Framework\Reflection\Reflection_Property;
use ITRocks\Framework\Tools\Default_List_Data;
use ITRocks\Framework\Tools\List_Data;
use ITRocks\Framework;
use Psr\Http\Client\ClientExceptionInterface;
use ReflectionException;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

/**
 * Class Link
 */
class Link extends Dao\Data_Link
{

	//-------------------------------------------------------------------------------------- BASE_URL
	public const BASE_URL = 'base_url';

	//------------------------------------------------------------------------------------------ $uri
	private array $uris;

	//----------------------------------------------------------------------------------- __construct
	public function __construct(array $parameters = null)
	{
		$this->uris = $parameters[Framework\Configuration::HUB_URIS];
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
	public function count($what, $class_name = null, $options = []): int
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
	public function createStorage($class_name): bool
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
	public function delete($object): bool
	{
		// TODO: Implement delete() method.
		return false;
	}

	//------------------------------------------------------------------------------------ disconnect
	/**
	 * Disconnect an object from current data link
	 *
	 * @param $object              object object to disconnect from data source
	 * @param $load_linked_objects boolean if true, load linked objects before disconnect
	 * @see Data_Link::disconnect()
	 */
	public function disconnect($object, $load_linked_objects = false)
	{
		// TODO: Implement disconnect() method.
	}

	//-------------------------------------------------------------------------------- filterResponse
	/**
	 * @param string $response
	 */
	private function filterResponse(string &$response): void
	{
		$pattern = "/array\([0-9]\)\s\{(.*?)\}/s";
		$response = preg_replace($pattern, '', $response);
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
	public function getStoredProperties($class): array
	{
		// TODO: Implement getStoredProperties() method.
		return [];
	}

	//--------------------------------------------------------------------------------------- getUris
	/**
	 * @return mixed
	 */
	public function getUris(): mixed
	{
		return $this->uris;
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
	public function is($object1, $object2, $strict = false): bool
	{
		// TODO: Implement is() method.
		return false;
	}

	//------------------------------------------------------------------------------------------ read
	/**
	 * Read an object from data source
	 *
	 * @param $identifier mixed|object identifier for the object
	 * @param $class_name string class for read object
	 * @return object an object of class objectClass, read from data source, or null if nothing found
	 */
	public function read($identifier, $class_name = null): object
	{
		// TODO: Implement read() method.
	}

	//--------------------------------------------------------------------------------------- readAll
	/**
	 * Read all objects of a given class from data source
	 *
	 * @param $class_name string class name of read objects
	 * @param $options    Option|Option[] some options for advanced read
	 * @return object[] a collection of read objects
	 */
	public function readAll($class_name, $options = []): array
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
	public function readProperty($object, $property_name): mixed
	{
		// TODO: Implement readProperty() method.
		return null;
	}

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
	public function replace($destination, $source, $write = true): object
	{
		// TODO: Implement replace() method.
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
	public function replaceReferences($replaced, $replacement): bool
	{
		// TODO: Implement replaceReferences() method.
		return false;
	}

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
	public function search($what, $class_name = null, $options = []): array
	{
		$http_service = new Http_Service();
		$array_ids = $what['id']->values;
		$headers = ['Content-Type' => 'application/json'];
		$uri = $this->uris[$class_name]['detail'];
		$uri .= '/json';
		$base_url = $this->uris[Link::BASE_URL];
		$properties = ['search[id]' => null];
		if ($options['full']) {
			$properties = array_merge(['full' => 1], $properties);
		}

		$array_http_response = [];
		foreach ($array_ids as $id) {
			$properties['search[id]']= $id;
			$options_request = [
				'headers' => $headers,
				'query'   => $properties
			];
			$url = urldecode($base_url.$uri);
			$http_service->get($url, $options_request);
			$http_response = $http_service->getResponse();
			$http_response_content = $http_response->getContent();
			$array_http_response[] = json_decode($http_response_content);
		}
		return $array_http_response;
	}

	//------------------------------------------------------------------------------------ searchById
	/**
	 * @param mixed   $id
	 * @param null  $class_name
	 * @param array $options
	 * @return object[]
	 */
	public function searchById(mixed $id, $class_name = null, array $options = []): array
	{
		$search = ['id' => Func::in($id)];
		return $this->search($search, $class_name, $options);
	}

	//---------------------------------------------------------------------------------------- select
	/**
	 * Read selected columns only from data source, using optional filter
	 *
	 * @param $object_class  string class for the read object
	 * @param $properties    string[]|string|Column[] the list of property paths : only those
	 *                       properties will be read. You can use Dao\Func\Column sub-classes to get
	 *                       result of functions.
	 * @param $filter_object object|array source object for filter, set properties will be used for
	 *                       search. Can be an array associating properties names to corresponding
	 *                       search value too.
	 * @param $options       Option|Option[] some options for advanced search
	 * @return List_Data a list of read records. Each record values (may be objects) are stored in
	 *                   the same order than columns.
	 * @throws ClientExceptionInterface
	 * @throws TransportExceptionInterface
	 */
	public function select($object_class, $properties, $filter_object = null, $options = []): List_Data|Default_List_Data
	{
		array_walk($properties, static function(/* @noinspection PhpUnusedParameterInspection */&$val, $key) {
			$properties_str_pattern = 'properties';
			$val  = $properties_str_pattern.LBRACKET.$val.RBRACKET;
		});
		$properties = array_flip($properties);
		//$uri_parameters = http_build_query($properties, '', '&');
		$uri = $this->uris[self::BASE_URL].$this->uris[$object_class]['detail'];
		$uri .= '/json';
		//$uri .= '?'.$uri_parameters;
		$uri = urldecode($uri);

		$http_service = new Http_Service();
		$headers = ['Content-Type' => 'application/json'];
		$options_request = [
			'headers' => $headers,
			'query'   => $properties
		];

		$http_service->get($uri, $options_request);
		$http_response = $http_service->getResponse();
		$http_response_content = $http_response->getContent();
		$this->filterResponse($http_response_content);

		$http_response_decoded = json_decode($http_response_content, true);

		if (is_string($object_class)) {
			$object_class = Builder::className($object_class);
		}
		if (!is_array($options)) {
			$options = $options ? [$options] : [];
		}
		if (!is_array($properties)) {
			$properties = $properties ? [$properties] : [];
		}
		list($double_pass, $list) = $this->selectOptions($options, $properties);
		if (!isset($list)) {
			$list = $this->selectList($object_class, $properties);
		}

		foreach ($http_response_decoded as $row) {
			$this->store($row, $list, $object_class);
		}

		return $list;
	}

	//------------------------------------------------------------------------------------ selectList
	/**
	 * @param $object_class string class for the read object
	 * @param $columns      string[]|Column[] the list of the columns names : only those properties
	 *                      will be read. You can use 'column.sub_column' to get values from linked
	 *                      objects from the same data source. You can use Dao\Func\Column sub-classes
	 *                      to get result of functions.
	 * @return Default_List_Data
	 */
	private function selectList($object_class, array $columns): List_Data
	{
		$functions  = [];
		$properties = [];
		foreach ($columns as $key => $column) {
			$property_path = is_object($column) ? $key : $column;
			if (Expressions::isFunction($property_path)) {
				$expression    = Expressions::$current->cache[$property_path];
				$property_path = $expression->property_path;
			}
			try {
				$properties[$property_path] = new Reflection_Property($object_class, $property_path);
			}
			catch (ReflectionException) {
				// nothing : no property, period
			}
			$functions[$property_path]  = ($column instanceof Dao_Function) ? $column : null;
		}

		$class     = new Reflection_Class($object_class);
		$list_data = $class->getAnnotation('list_data')->value;

		$list_data = new  $list_data($object_class, $properties, $functions)
			?? new Default_List_Data($object_class, $properties, $functions);
		return $list_data;
	}

	//--------------------------------------------------------------------------------- selectOptions
	/**
	 * @param $options Option[]|callable[] some options for advanced search
	 * @param $columns string[]|Column[] the list of the columns names : only those properties will be
	 *                 read. You can use 'column.sub_column' to get values from linked objects from
	 *                 the same data source. You can use Dao\Func\Column sub-classes to get result of
	 *                 functions.
	 * @return array [boolean $double_pass, array $list]
	 */
	private function selectOptions(array $options, array $columns): array
	{
		$double_pass = false;
		$list        = null;
		foreach ($options as $option) {
			if ($option instanceof Option\Double_Pass) {
				foreach ($columns as $column_key => $column) {
					if (is_object($column) && is_string($column_key)) {
						$column = $column_key;
					}
					if (is_string($column) && strpos($column, DOT)) {
						$double_pass = true;
						break;
					}
				}
			}
			elseif (($option instanceof Option\Array_Result) || ($option === AS_ARRAY)) {
				$list = [];
			}
			elseif (is_callable($option)) {
				$list = $option;
			}
		}
		return [$double_pass, $list];
	}

	//----------------------------------------------------------------------------------------- store
	/**
	 * @param array  $row
	 * @param List_Data|array[]|object[]  $data_store
	 * @param string $class_name
	 */
	private function store(array $row, &$data_store, string $class_name): void
	{
		if ($data_store instanceof List_Data) {
			$id = array_pop($row);
			$data_store->add($data_store->newRow($class_name, $id, $row));
		}
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
		// TODO: Implement truncate() method.
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
	public function write($object, $options = []): object
	{
		// TODO: Implement write() method.
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
		// TODO: Implement writeProperty() method.
	}
}
