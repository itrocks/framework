<?php
namespace ITRocks\Framework\Objects;

use ITRocks\Framework\Builder;
use ITRocks\Framework\Dao;
use ITRocks\Framework\Dao\Func;
use ITRocks\Framework\Reflection\Attribute\Class_\Store;
use ITRocks\Framework\Tools\Date_Time;
use ITRocks\Framework\Tools\Names;
use ITRocks\Framework\User;

/**
 * Opened documents log
 */
#[Store]
class Opened_Document
{

	//----------------------------------------------------------------------------------------- DELAY
	/**
	 * Delay after which we consider the document is not opened anymore (in seconds)
	 * Must be at least twice the 'document is opened' rate
	 */
	const DELAY = 10;

	//----------------------------------------------------------------------------------- $class_name
	public string $class_name;

	//----------------------------------------------------------------------------------- $identifier
	public int $identifier;

	//----------------------------------------------------------------------------------------- $ping
	public Date_Time|string $ping;

	//----------------------------------------------------------------------------------------- $user
	public User $user;

	//------------------------------------------------------------------------------------ __toString
	public function __toString() : string
	{
		return $this->class_name
			? (Names::classToDisplay($this->class_name) . SP . $this->identifier)
			: Names::classToDisplay(static::class);
	}

	//----------------------------------------------------------------------------------- closeObject
	/**
	 * Closes an opened document
	 *
	 * @param $object object
	 * @return boolean true if the object has correctly been closed, false if was not existing before
	 */
	public static function closeObject(object $object) : bool
	{
		return ($opened_document = self::openedObject($object)) && Dao::delete($opened_document);
	}

	//------------------------------------------------------------------------------ keepObjectOpened
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $object object
	 * @return true
	 */
	public static function keepObjectOpened(object $object) : bool
	{
		if ($opened_document = static::openedObject($object)) {
			/** @noinspection PhpUnhandledExceptionInspection valid */
			$opened_document->ping = new Date_Time();
			Dao::write($opened_document, Dao::only('ping'));
		}
		else {
			static::openObject($object);
		}
		return true;
	}

	//------------------------------------------------------------------------------------ openObject
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $object object
	 * @return boolean true if the object can be opened, false if an object was already opened before
	 */
	public static function openObject(object $object) : bool
	{
		if (!self::openedObject($object)) {
			/** @noinspection PhpUnhandledExceptionInspection constant */
			$opened_document             = Builder::create(Opened_Document::class);
			$opened_document->class_name = get_class($object);
			$opened_document->identifier = Dao::getObjectIdentifier($object);
			$opened_document->ping       = Date_Time::now();
			$opened_document->user       = User::current();
			Dao::write($opened_document);
			return true;
		}
		return false;
	}

	//---------------------------------------------------------------------------------- openedObject
	/**
	 * Returns an Opened_Document object if the object is opened
	 */
	public static function openedObject(object $object) : ?static
	{
		$since = Date_Time::now()->sub(static::DELAY, Date_Time::SECOND);
		return Dao::searchOne(
			[
				'class_name' => get_class($object),
				'identifier' => Dao::getObjectIdentifier($object),
				'ping'       => Func::greater($since)
			],
			static::class
		);
	}

	//----------------------------------------------------------------------------------------- purge
	/**
	 * Purge old opened documents :
	 * documents are considered as closed when they did not receive any ping since DELAY seconds
	 */
	public static function purge() : void
	{
		Dao::begin();
		$since = Date_Time::now()->sub(static::DELAY, Date_Time::SECOND);
		$opened_documents = Dao::search(['ping' => Dao\Func::less($since)], static::class);
		foreach ($opened_documents as $opened_document) {
			Dao::delete($opened_document);
		}
		Dao::commit();
	}

}
