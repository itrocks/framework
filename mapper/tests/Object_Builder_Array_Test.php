<?php
namespace ITRocks\Framework\Mapper\Tests;

use ITRocks\Framework\Dao;
use ITRocks\Framework\Dao\Cache;
use ITRocks\Framework\Mapper\Built_Object;
use ITRocks\Framework\Mapper\Object_Builder_Array;
use ITRocks\Framework\Session;
use ITRocks\Framework\Tests\Objects\Component;
use ITRocks\Framework\Tests\Objects\Composite;
use ITRocks\Framework\Tests\Objects\Resource;
use ITRocks\Framework\Tests\Objects\Salesman;
use ITRocks\Framework\Tests\Test;
use ITRocks\Framework\View\User_Error_Exception;

/**
 * Object builder from array unit tests
 *
 * These tests simulate arrays coming from view's forms (ie HTML)
 *
 * TODO may test 'component.name' => 'Component object' format
 * TODO should add tests for all compositions
 *
 * @group functional
 */
class Object_Builder_Array_Test extends Test
{

	//------------------------------------------------------------------------------------ flushCache
	/**
	 * Flush DAO cache (if the plugin is enabled)
	 */
	protected function flushCache() : void
	{
		/** @var $cache Cache::class */
		$cache = Session::current()->plugins->get(Cache::class);
		if ($cache) {
			$cache->flush();
		}
	}

	//---------------------------------------------------------------- testExistingComponentSubObject
	/**
	 * What if we build an existing composite with its component sub-object
	 *
	 * @throws User_Error_Exception
	 */
	public function testExistingComponentSubObject() : void
	{
		$composite            = new Composite('Composite object');
		$composite->component = new Component('Component object');
		Dao::write($composite);
		$this->flushCache();

		$builder = new Object_Builder_Array(Composite::class);
		$builder->build([
			'id'        => Dao::getObjectIdentifier($composite),
			'name'      => $composite->name,
			'component' => ['name' => 'Component object']
		]);

		// only the main object : Dao::write() will always write the #Composite property
		$assume = [new Built_Object($composite)];

		static::assertEquals($assume, $builder->getBuiltObjects());

		Dao::delete($composite);
	}

	//------------------------------------------------------------------------- testExistingSubObject
	/**
	 * What if we build an existing object and existing sub-objects
	 *
	 * @throws User_Error_Exception
	 */
	public function testExistingSubObject() : void
	{
		$object                   = new Resource('Hello world');
		$object->mandatory_object = new Salesman('Mandatory object');
		$object->optional_object  = new Salesman('Optional object');
		Dao::write($object);
		$this->flushCache();

		$builder = new Object_Builder_Array(Resource::class);
		$builder->build([
			'id'                  => Dao::getObjectIdentifier($object),
			'id_mandatory_object' => Dao::getObjectIdentifier($object->mandatory_object),
			'id_optional_object'  => Dao::getObjectIdentifier($object->optional_object),
			'name'                => $object->name
		]);

		// only the main object : sub-objects that are not #Composite are not explicitly built objects,
		// but they are inside the object that use them
		// (the default write controller will not write them even if data has changed)
		$assume = [new Built_Object(Dao::searchOne($object))];

		static::assertEquals($assume, $builder->getBuiltObjects());

		Dao::delete($object);
		Dao::delete($object->mandatory_object);
		Dao::delete($object->optional_object);
	}

	//--------------------------------------------------------------------- testExistingSubObjectData
	/**
	 * What if we build an existing object and data from sub-objects
	 *
	 * @throws User_Error_Exception
	 */
	public function testExistingSubObjectData() : void
	{
		$object                   = new Resource('Hello world');
		$object->mandatory_object = new Salesman('Mandatory object');
		$object->optional_object  = new Salesman('Optional object');
		Dao::write($object);
		$this->flushCache();

		$builder = new Object_Builder_Array(Resource::class);
		$builder->build([
			'id'               => Dao::getObjectIdentifier($object),
			'name'             => $object->name,
			'mandatory_object' => ['name' => $object->mandatory_object->name],
			'optional_object'  => ['name' => $object->optional_object->name]
		]);

		$assume = [
			new Built_Object($object->mandatory_object),
			new Built_Object($object->optional_object),
			new Built_Object($object)
		];

		// we have explicitly changed data from sub-objects, so they are explicitly built object
		// (the default write controller will write them before the object that use them)
		static::assertEquals($assume, $builder->getBuiltObjects());

		Dao::delete($object);
		Dao::delete($object->mandatory_object);
		Dao::delete($object->optional_object);
	}

	//------------------------------------------------------------------------------ testNewSubObject
	/**
	 * The form contains data for a new mandatory or optional sub-object
	 *
	 * @throws User_Error_Exception
	 */
	public function testNewSubObject() : void
	{
		$object                   = new Resource('Hello world');
		$object->mandatory_object = new Salesman('Mandatory object');
		$object->optional_object  = new Salesman('Optional object');

		$builder = new Object_Builder_Array(Resource::class);
		$builder->build(
			[
				'name'             => $object->name,
				'mandatory_object' => $object->mandatory_object,
				'optional_object'  => $object->optional_object
			]
		);

		// only the main object, that contains the new sub-objects, will be generated
		// if Dao::write() is called, the new sub-objects will be written because they are new
		$assume = [new Built_Object($object)];

		static::assertEquals($assume, $builder->getBuiltObjects());
	}

	//-------------------------------------------------------------------------- testNewSubObjectData
	/**
	 * The form contains data for a new mandatory or optional sub-object
	 *
	 * @throws User_Error_Exception
	 */
	public function testNewSubObjectData() : void
	{
		$object                   = new Resource('Hello world');
		$object->mandatory_object = new Salesman('Mandatory object');
		$object->optional_object  = new Salesman('Optional object');

		$builder = new Object_Builder_Array(Resource::class);
		$builder->build([
			'name'             => $object->name,
			'mandatory_object' => ['name' => $object->mandatory_object->name],
			'optional_object'  => ['name' => $object->optional_object->name]
		]);

		// when sub-object data is set, the built objects will always contain those built sub-objects
		// The default write controller will write the objects, then Dao::write() will not do the job
		// again, as they will already exist in database at this step
		$assume = [
			new Built_Object($object->mandatory_object),
			new Built_Object($object->optional_object),
			new Built_Object($object)
		];

		static::assertEquals($assume, $builder->getBuiltObjects());
	}

	//------------------------------------------------------------------------------------ testSimple
	/**
	 * A simple test : will a form build a salesman ?
	 *
	 * @throws User_Error_Exception
	 */
	public function testSimple() : void
	{
		$salesman       = new Salesman();
		$salesman->name = 'Hello World';

		$builder = new Object_Builder_Array(Salesman::class);
		$builder->build(['name' => $salesman->name]);

		$assume = [new Built_Object($salesman)];

		static::assertEquals($assume, $builder->getBuiltObjects());
	}

}
