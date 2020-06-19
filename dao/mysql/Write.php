<?php
namespace ITRocks\Framework\Dao\Mysql;

use ITRocks\Framework\Builder;
use ITRocks\Framework\Dao;
use ITRocks\Framework\Dao\Data_Link;
use ITRocks\Framework\Dao\Data_Link\Object_To_Write_Array;
use ITRocks\Framework\Dao\Event;
use ITRocks\Framework\Dao\Event\Property_Add;
use ITRocks\Framework\Dao\Event\Property_Remove;
use ITRocks\Framework\Dao\Option;
use ITRocks\Framework\History\Has_History;
use ITRocks\Framework\Mapper\Component;
use ITRocks\Framework\Mapper\Empty_Object;
use ITRocks\Framework\Mapper\Null_Object;
use ITRocks\Framework\Mapper\Search_Object;
use ITRocks\Framework\Reflection\Annotation\Class_;
use ITRocks\Framework\Reflection\Annotation\Property\Foreign_Annotation;
use ITRocks\Framework\Reflection\Annotation\Property\Store_Annotation;
use ITRocks\Framework\Reflection\Annotation\Property\Store_Name_Annotation;
use ITRocks\Framework\Reflection\Annotation\Sets\Replaces_Annotations;
use ITRocks\Framework\Reflection\Annotation\Template\Method_Annotation;
use ITRocks\Framework\Reflection\Link_Class;
use ITRocks\Framework\Reflection\Reflection_Property;
use ITRocks\Framework\Sql;
use ITRocks\Framework\Sql\Builder\Link_Property_Name;
use ITRocks\Framework\Sql\Builder\Map_Delete;
use ITRocks\Framework\Sql\Builder\Map_Insert;

/**
 * Write feature for Dao\Mysql link
 */
class Write extends Data_Link\Write
{

	//-------------------------------------------------------------------------------------- $exclude
	/**
	 * @var string[]
	 */
	protected $exclude;

	//------------------------------------------------------------------------------------ $force_add
	/**
	 * @var boolean
	 */
	protected $force_add;

	//---------------------------------------------------------------------------------- $id_property
	/**
	 * Identifier property name
	 *
	 * @var string
	 */
	protected $id_property;

	//----------------------------------------------------------------------------------------- $link
	/**
	 * @override
	 * @var Link
	 */
	protected $link;

	//------------------------------------------------------------------------------ $link_class_only
	/**
	 * @var boolean
	 */
	protected $link_class_only;

	//----------------------------------------------------------------------------------------- $only
	/**
	 * @var string[]
	 */
	protected $only;

	//------------------------------------------------------------------------------- $spread_options
	/**
	 * @var Option\Spreadable[]
	 */
	protected $spread_options;

	//------------------------------------------------------------------------- addImpactedProperties
	/**
	 * For each property in only : if there are impacted properties using @impact : add them
	 */
	protected function addImpactedProperties()
	{
		$class_name = get_class($this->object);
		do {
			$impacted = false;
			foreach ($this->only as $property_name) {
				/** @noinspection PhpUnhandledExceptionInspection valid class name, $only must be valid */
				$property           = new Reflection_Property($class_name, $property_name);
				$impact_annotations = $property->getListAnnotations('impacts');
				foreach ($impact_annotations as $impact_annotation) {
					foreach ($impact_annotation->values() as $impacted_property_name) {
						if (
							property_exists($class_name, $impacted_property_name)
							&& !in_array($impacted_property_name, $this->only)
						) {
							$impacted     = true;
							$this->only[] = $impacted_property_name;
						}
					}
				}
			}
		}
		while ($impacted);
	}

	//------------------------------------------------------------------------------------- callEvent
	/**
	 * Call event
	 *
	 * @param $event       Event
	 * @param $annotations Method_Annotation[]
	 * @return boolean
	 */
	protected function callEvent(Event $event, array $annotations)
	{
		return Method_Annotation::callAll($annotations, $event->object, [$event]);
	}

	//---------------------------------------------------------------------------------- parseOptions
	/**
	 * Scan options for things we need for write
	 */
	protected function parseOptions()
	{
		$this->exclude        = [];
		$this->only           = null;
		$this->spread_options = [];
		foreach ($this->options as $option) {
			if ($option instanceof Option\Add) {
				$this->force_add = true;
			}
			elseif ($option instanceof Option\Exclude) {
				$this->exclude = array_merge($this->exclude, $option->properties);
			}
			elseif ($option instanceof Option\Link_Class_Only) {
				$this->link_class_only = true;
			}
			elseif ($option instanceof Option\Only) {
				$this->only = isset($this->only)
					? array_merge($this->only, $option->properties)
					: $option->properties;
			}
			if ($option instanceof Option\Spreadable) {
				$this->spread_options[] = $option;
			}
		}
		if ($this->only) {
			$this->addImpactedProperties();
		}
	}

	//------------------------------------------------------------------------------------------- run
	/**
	 * Run the write feature
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 * @return object|null
	 */
	public function run()
	{
		$new_object = !Dao::getObjectIdentifier($this->object);
		if ($this->beforeWrite(
			$this->object,
			$this->options,
			$new_object ? self::BEFORE_CREATE : self::BEFORE_UPDATE
		)) {
			$this->link->begin();
			if (
				Null_Object::isNull($this->object, [Store_Annotation::class, 'storedPropertiesOnly'])
				&& !($this->object instanceof Has_History)
			) {
				$this->link->disconnect($this->object);
			}
			/** @noinspection PhpUnhandledExceptionInspection object */
			$class             = new Link_Class($this->object);
			$this->id_property = 'id';
			$this->parseOptions();
			do {
				$link = Class_\Link_Annotation::of($class);
				if ($link->value) {
					$link_property = $link->getLinkClass()->getLinkProperty();
					/** @noinspection PhpUnhandledExceptionInspection $link_property calculated from object */
					$link_object = $link_property->getValue($this->object);
					if (!$link_object) {
						$id_link_property                = 'id_' . $link_property->name;
						$this->object->$id_link_property = $this->link->write($link_object, $this->options);
					}
				}
				$object_to_write_array
					= (new Object_To_Write_Array($this->link, $this->object, $this->spread_options))
					->setPropertiesFilters($class, $this->only, $this->exclude)
					->build();
				$write             = $object_to_write_array->array;
				$write_collections = $object_to_write_array->collections;
				$write_maps        = $object_to_write_array->maps;
				$write_objects     = $object_to_write_array->objects;
				$write_properties  = $object_to_write_array->properties;

				$properties = $class->accessProperties();
				$properties = Replaces_Annotations::removeReplacedProperties($properties);
				if ($write) {
					$this->writeArray($write, $properties, $class);
				}
				foreach ($write_collections as $write) {
					list($property, $value) = $write;
					$this->writeCollection($property, $value);
				}
				foreach ($write_maps as $write) {
					list($property, $value) = $write;
					$this->spreadExcludeAndOnly(
						$this->spread_options, $property->name, $this->exclude, $this->only
					);
					$this->writeMap($property, $value);
				}
				foreach ($write_objects as $write) {
					list($property, $value) = $write;
					$this->spreadExcludeAndOnly(
						$this->spread_options, $property->name, $this->exclude, $this->only
					);
					$this->writeObject($property, $value);
				}
				foreach ($write_properties as $write) {
					/** @var $dao Data_Link */
					list($property, $value, $dao) = $write;
					$dao->writeProperty($this->object, $property, $value);
				}
				// if link class : write linked object too
				if ($link->value && !isset($this->link_class_only)) {
					$this->id_property = ('id_' . $class->getCompositeProperty()->name);
					/** @noinspection PhpUnhandledExceptionInspection link annotation value must be valid */
					$class = new Link_Class($link->value);
				}
				else {
					$class = $this->id_property = null;
				}
			} while (
				$class
				&& !Null_Object::isNull($this->object, function($properties) use ($class) {
					return Store_Annotation::storedPropertiesOnly(
						Reflection_Property::filter($properties, $class->name)
					);
				})
			);
			$this->link->commit();
			$this->afterWrite(
				$this->object, $this->options, $new_object ? self::AFTER_CREATE : self::AFTER_UPDATE
			);
			// TODO HIGHEST remove this 'anti-crash-on-update' patch condition
			if (method_exists($this, 'prepareAfterCommit')) {
				$this->prepareAfterCommit($this->object, $this->options);
			}
			return $this->object;
		}
		return null;
	}

	//-------------------------------------------------------------------------- spreadExcludeAndOnly
	/**
	 * Spread the Only option when it contains some $property_name.*
	 *
	 * @example 'property_name' with $only = ['property_name.thing'] will return ['thing']
	 * @param $options       Option[]
	 * @param $property_name string
	 * @param $exclude       string[]
	 * @param $only          string[]
	 */
	protected function spreadExcludeAndOnly(array &$options, $property_name, $exclude, $only)
	{
		if ($exclude) {
			$spread_only = (new Option\Only($only))->subObjectOption($property_name);
			if ($spread_only) {
				$options[] = $spread_only;
			}
		}
		if ($only) {
			$spread_only = (new Option\Only($only))->subObjectOption($property_name);
			if ($spread_only) {
				$options[] = $spread_only;
			}
		}
	}

	//------------------------------------------------------------------------------------ writeArray
	/**
	 * @param $write      array
	 * @param $properties Reflection_Property[]
	 * @param $class      Link_Class
	 */
	protected function writeArray(array $write, array $properties, Link_Class $class)
	{
		$link = Class_\Link_Annotation::of($class);
		// link class : id is the couple of composite properties values
		if ($link->value) {
			$object_identifier = Dao::getObjectIdentifier($this->object, 'id');
			$option            = [];
			$search            = [];
			foreach ($link->getLinkClass()->getUniqueProperties() as $property) {
				$property_name = $property->getName();
				$column_name   = Dao::storedAsForeign($property) ? 'id_' : '';
				$column_name  .= Store_Name_Annotation::of($properties[$property_name])->value;
				if (isset($write[$column_name])) {
					$search[$property_name] = $write[$column_name];
				}
				elseif (isset($write[$property_name])) {
					$search[$property_name] = $write[$column_name];
				}
				elseif (
					(
						in_array($property_name, $this->exclude)
						|| (!$this->only || !in_array($property_name, $this->only))
					)
					&& $this->object->$property_name
				) {
					$object_value           = $this->object->$property_name;
					$search[$property_name] = is_object($object_value)
						? $this->link->getObjectIdentifier($object_value)
						: $object_value;
				}
				else {
					trigger_error("Can't search $property_name", E_USER_ERROR);
				}
				if (
					$property->getType()->isInstanceOf($link->value)
					&& ($search[$property_name] != $object_identifier)
				) {
					$option[] = new Link_Property_Name($property_name);
				}
			}
			if ($this->link->search($search, $class->name, $option)) {
				$id = [];
				foreach ($search as $property_name => $value) {
					$property     = $properties[$property_name];
					$column_name  = Dao::storedAsForeign($property) ? 'id_' : '';
					$column_name .= Store_Name_Annotation::of($property)->value;
					if (isset($write['id_' . $column_name])) {
						$column_name = 'id_' . $column_name;
					}
					$id[$column_name] = $value;
					unset($write[$column_name]);
				}
			}
			else {
				$id = null;
			}
		}
		// standard class : get the property 'id' value
		else {
			$id = $this->link->getObjectIdentifier($this->object, $this->id_property);
		}
		if ($write) {
			$write_properties  = [];
			foreach ($properties as $property) {
				$column_name = Store_Name_Annotation::of($property)->value;
				if (array_key_exists($column_name, $write)) {
					$write_properties[$column_name] = $property;
				}
				elseif (array_key_exists('id_' . $column_name, $write)) {
					$write_properties['id_' . $column_name] = $property;
				}
			}
			$mysqli = $this->link->getConnection();
			array_push($mysqli->contexts, $class->name);
			if (empty($id) || isset($this->force_add)) {
				$this->link->disconnect($this->object);
				if (isset($this->force_add) && !empty($id)) {
					$write['id'] = $id;
				}
				$id = $this->link->query(Sql\Builder::buildInsert($class->name, $write));
				if (!empty($id)) {
					$this->link->setObjectIdentifier($this->object, $id);
				}
			}
			else {
				$this->link->query(Sql\Builder::buildUpdate($class->name, $write, $id, $write_properties));
			}
			array_pop($mysqli->contexts);
		}
	}

	//------------------------------------------------------------------------------- writeCollection
	/**
	 * Write a component collection property value
	 *
	 * Ie when you write an order, it's implicitly needed to write its lines
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $property   Reflection_Property
	 * @param $collection Component[]
	 */
	protected function writeCollection(Reflection_Property $property, array $collection)
	{
		// old collection
		$old_object = Search_Object::create(get_class($this->object));
		$this->link->setObjectIdentifier($old_object, $this->link->getObjectIdentifier($this->object));
		/** @noinspection PhpUnhandledExceptionInspection $property belongs to $old_object */
		$old_collection = $property->getValue($old_object);

		$element_class = $property->getType()->asReflectionClass();
		$element_link  = Class_\Link_Annotation::of($element_class);
		$id_set        = [];
		// collection properties : write each of them
		if ($collection) {
			$foreign_property_name = Foreign_Annotation::of($property)->value;
			foreach ($collection as $key => $element) {
				$options = $this->spread_options;
				if (!Dao::isLinkedObjectModified($element)) {
					$options[] = new Option\Link_Class_Only();
				}
				if (!is_a($element, $element_class->getName())) {
					/** @noinspection PhpUnhandledExceptionInspection $element_class always valid */
					$collection[$key] = $element = Builder::createClone(
						$element,
						$element_class->getName(),
						[$element_link->getLinkClass()->getCompositeProperty()->name => $element]
					);
				}
				$element->setComposite($this->object, $foreign_property_name);
				$id = $element_link->value
					? $this->link->getLinkObjectIdentifier($element, $element_link)
					: $this->link->getObjectIdentifier($element);
				if (!empty($id)) {
					$id_set[$id] = true;
				}
				$old_element = ($id && isset($old_collection[$id])) ? $old_collection[$id] : null;
				if (!$old_element) {
					$property_add_event = new Property_Add(
						$this->link, $this->object, $element, $options, $property
					);
					$before_add_elements = $property->getAnnotations('before_add_element');
					$before_result       = $this->callEvent($property_add_event, $before_add_elements);
				}
				else {
					$property_add_event = null;
					$before_result      = true;
				}
				if ($before_result) {
					$this->link->write($element, empty($id) ? [] : $options);
					if ($property_add_event) {
						$after_add_elements = $property->getAnnotations('after_add_element');
						$this->callEvent($property_add_event, $after_add_elements);
					}
				}
			}
		}
		// remove old unused elements
		$options = $this->spread_options;
		foreach ($old_collection as $old_element) {
			$id = $element_link->value
				? $this->link->getLinkObjectIdentifier($old_element, $element_link)
				: $this->link->getObjectIdentifier($old_element);
			if (!isset($id_set[$id])) {
				$remove_event = new Property_Remove(
					$this->link, $this->object, $old_element, $options, $property
				);
				$before_remove_elements = $property->getAnnotations('before_remove_element');
				if ($this->callEvent($remove_event, $before_remove_elements)) {
					$this->link->delete($old_element);
				}
			}
		}
	}

	//-------------------------------------------------------------------------------------- writeMap
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $property Reflection_Property
	 * @param $map      object[]
	 */
	protected function writeMap(Reflection_Property $property, array $map)
	{
		// old map
		/** @noinspection PhpUnhandledExceptionInspection object */
		$class                   = new Link_Class($this->object);
		$composite_property_name = Class_\Link_Annotation::of($class)->value
			? $class->getCompositeProperty()->name
			: null;
		$old_object = Search_Object::create(Link_Class::linkedClassNameOf($this->object));
		$this->link->setObjectIdentifier(
			$old_object, $this->link->getObjectIdentifier($this->object, $composite_property_name)
		);
		/** @noinspection PhpUnhandledExceptionInspection $property belongs to $old_object */
		$old_map = $property->getValue($old_object);
		// map properties : write each of them
		$insert_builder = new Map_Insert($property);
		$id_set         = [];
		foreach ($map as $element) {
			$id = $this->link->getObjectIdentifier($element)
				?: $this->link->getObjectIdentifier($this->link->write($element, $this->spread_options));
			if (!isset($old_map[$id]) && !isset($id_set[$id])) {
				$property_add_event = new Property_Add(
					$this->link, $this->object, $element, $this->spread_options, $property
				);
				$before_add_elements = $property->getAnnotations('before_add_element');
				if ($this->callEvent($property_add_event, $before_add_elements)) {
					$query = $insert_builder->buildQuery($this->object, $element);
					$this->link->getConnection()->query($query);
					$after_add_elements = $property->getAnnotations('after_add_element');
					$this->callEvent($property_add_event, $after_add_elements);
				}
			}
			$id_set[$id] = true;
		}
		// remove old unused elements
		$delete_builder = new Map_Delete($property);
		foreach ($old_map as $old_element) {
			$id = $this->link->getObjectIdentifier($old_element);
			if (!isset($id_set[$id])) {
				$remove_event = new Property_Remove(
					$this->link, $this->object, $old_element, $this->spread_options, $property
				);
				$before_remove_elements = $property->getAnnotations('before_remove_element');
				if ($this->callEvent($remove_event, $before_remove_elements)) {
					$query = $delete_builder->buildQuery($this->object, $old_element);
					$this->link->getConnection()->query($query);
				}
			}
		}
	}

	//----------------------------------------------------------------------------------- writeObject
	/**
	 * @param $property         Reflection_Property
	 * @param $component_object Component
	 * @todo And what if $component_object has a @link class type ?
	 * @todo working with @dao property annotation
	 */
	protected function writeObject(Reflection_Property $property, $component_object)
	{
		// notice that this work only because called after write of $this->object (see searches bellow)
		// if there is already a stored component object : there must be only one
		if (is_object($component_object)) {
			$foreign_property_name = Foreign_Annotation::of($property)->value;
			$existing = $this->link->searchOne(
				[$foreign_property_name => $this->object], get_class($component_object)
			);
			if ($existing) {
				$this->link->replace($component_object, $existing, false);
			}
		}
		// delete
		if (
			(
				$component_object
				&& Empty_Object::isEmpty($component_object)
				&& !($component_object instanceof Has_History)
			)
			|| !$component_object
		) {
			$foreign_property_name = Foreign_Annotation::of($property)->value;
			$components            = $this->link->search(
				[$foreign_property_name => $this->object],
				$property->getType()->asString()
			);
			foreach ($components as $component) {
				$this->link->delete($component);
			}
		}
		// create / update
		elseif ($component_object) {
			$component_object->setComposite($this->object);
			// for creation if a only option is given, we should always write foreign property
			if (!Dao::getObjectIdentifier($component_object)) {
				foreach ($this->spread_options as $option) {
					if ($option instanceof Option\Only) {
						$only = $option;
						break;
					}
				}
				if (isset($only) && isset($foreign_property_name)) {
					$only->add($foreign_property_name);
				}
			}
			$this->link->write($component_object, $this->spread_options);
		}
	}

}
