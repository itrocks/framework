<?php
namespace ITRocks\Framework\Tools\Encryption;

use ITRocks\Framework\Builder;
use ITRocks\Framework\Dao;
use ITRocks\Framework\Reflection\Reflection_Property;
use ITRocks\Framework\Tools\Encryption\Sensitive_Data\Key;
use ITRocks\Framework\Tools\Password;
use ITRocks\Framework\User;

/**
 * Sensitive data encryption algorithm
 */
class Sensitive_Data
{

	//--------------------------------------------------------------------------------------- IV_SIZE
	const IV_SIZE = 16;

	//---------------------------------------------------------------------------------------- METHOD
	const METHOD = 'AES256';

	//----------------------------------------------------------------------------------- SECRET_SIZE
	const SECRET_SIZE = 128;

	//--------------------------------------------------------------------------------- allowProperty
	/**
	 * Allow access to the property data for the given user
	 *
	 * Only users that already have access to the property can give access for another user
	 *
	 * @param $user     User
	 * @param $property Reflection_Property
	 * @return boolean true if the property has been allowed or was already allowed
	 */
	public function allowProperty(User $user, Reflection_Property $property) : bool
	{
		if (
			isset($_POST['password'])
			|| !isset($_POST['giver_password'])
			|| !isset($_POST['receiver_password'])
		) {
			return false;
		}
		if ($this->propertyKey($property, $user)) {
			return true;
		}

		$_POST['password'] = $_POST['giver_password'];
		if (!($key = $this->propertyKey($property)) || !($secret = $key->getSecret())) {
			unset($_POST['password']);
			return false;
		}

		$_POST['password']  = $_POST['received_password'];
		$key                = new Key();
		$key->class_name    = $property->getFinalClassName();
		$key->property_name = $property->getName();
		$key->user          = $user;
		$key->setSecret($secret);
		Dao::write($key);
		unset($_POST['password']);
		return true;
	}

	//--------------------------------------------------------------------------------------- decrypt
	/**
	 * @param $data     string
	 * @param $property Reflection_Property
	 * @return ?string
	 */
	public function decrypt(string $data, Reflection_Property $property) : ?string
	{
		if (($key = $this->propertyKey($property)) && ($secret = $key->getSecret())) {
			$iv = hex2bin(substr($data, static::IV_SIZE * 2));
			return openssl_decrypt(substr($data, static::IV_SIZE * 2), 'AES256', $secret, 0, $iv);
		}
		return null;
	}

	//--------------------------------------------------------------------------------------- encrypt
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $data     string
	 * @param $property Reflection_Property
	 * @return string
	 */
	public function encrypt(string $data, Reflection_Property $property) : string
	{
		if (($key = $this->propertyKey($property)) && ($secret = $key->getSecret())) {
			/** @noinspection PhpUnhandledExceptionInspection valid call */
			$iv = random_bytes(static::IV_SIZE);
			return bin2hex($iv) . openssl_encrypt($data, 'AES256', $secret, 0, $iv);
		}
		return Password::UNCHANGED;
	}

	//---------------------------------------------------------------------------------------- getter
	/**
	 * @param $value    string
	 * @param $property Reflection_Property
	 * @return ?string
	 */
	public static function getter(string $value, Reflection_Property $property) : ?string
	{
		return (new static)->decrypt($value, $property);
	}

	//----------------------------------------------------------------------------------- propertyKey
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $property Reflection_Property
	 * @param $user     User|null
	 * @return Key
	 */
	protected function propertyKey(Reflection_Property $property, User $user = null) : Key
	{
		$class_name = Builder::current()->sourceClassName($property->final_class);

		// search property restricted access
		$search = [
			'class_name'    => $class_name,
			'property_name' => $property->name,
			'user'          => $user ?: User::current()
		];
		/** @var $key Key */
		$key = Dao::searchOne($search, Key::class);

		// search class restricted access
		if (!$key) {
			$search['property_name'] = Dao\Func::isNull();
			$key = Dao::searchOne($search, Key::class);
		}

		// create default key for class access when no user had any access
		if (!$key && !$user) {
			unset($search['property_name']);
			if (!Dao::searchOne($search, Key::class)) {
				$key             = new Key();
				$key->class_name = $class_name;
				$key->user       = User::current();
				/** @noinspection PhpUnhandledExceptionInspection valid call */
				$key->setSecret(random_bytes(static::SECRET_SIZE));
				Dao::write($key);
			}
		}
		return $key;
	}

}
