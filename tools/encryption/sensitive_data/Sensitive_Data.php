<?php
namespace ITRocks\Framework\Tools\Encryption;

use ITRocks\Framework\Builder;
use ITRocks\Framework\Dao;
use ITRocks\Framework\Dao\Func;
use ITRocks\Framework\Reflection\Reflection_Property;
use ITRocks\Framework\Session;
use ITRocks\Framework\Tools\Encryption\Sensitive_Data\Cache;
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

	//------------------------------------------------------------------------------------- $password
	private static string $sensitive_password = '';

	//--------------------------------------------------------------------------------- allowProperty
	/**
	 * Allow access to the property data for the given user
	 * Only users that already have access to the property can give access to another user
	 */
	public function allowProperty(User $user, Reflection_Property $property) : bool
	{
		if (
			static::password()
			|| !isset($_POST['giver_password'])
			|| !isset($_POST['receiver_password'])
		) {
			return false;
		}
		if ($this->propertyKey($property, $user)) {
			return true;
		}

		static::setPassword($_POST['giver_password']);
		if (!($key = $this->propertyKey($property)) || !($secret = $key->getSecret())) {
			static::emptyPassword();
			return false;
		}

		static::setPassword($_POST['receiver_password']);
		$key                = new Key();
		$key->class_name    = $property->getFinalClassName();
		$key->property_name = $property->getName();
		$key->user          = $user;
		$key->setSecret($secret);
		Dao::write($key);
		static::emptyPassword();
		return true;
	}

	//--------------------------------------------------------------------------------------- decrypt
	public function decrypt(string $data, Reflection_Property $property) : ?string
	{
		if ($data && ($key = $this->propertyKey($property)) && ($secret = $key->getSecret())) {
			$iv = hex2bin(substr($data, 0, static::IV_SIZE * 2));
			return openssl_decrypt(substr($data, static::IV_SIZE * 2), 'AES256', $secret, 0, $iv);
		}
		return null;
	}

	//--------------------------------------------------------------------------------- emptyPassword
	public static function emptyPassword() : void
	{
		static::$sensitive_password = '';
		unset($_POST['sensitive_password']);
	}

	//--------------------------------------------------------------------------------------- encrypt
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
	public static function getter(mixed $value, Reflection_Property $property) : ?string
	{
		return (new static)->decrypt($value, $property);
	}

	//-------------------------------------------------------------------------------------- password
	/**
	 * @return string
	 */
	public static function password() : string
	{

		if (isset($_POST['sensitive_password']) && ($_POST['sensitive_password'] !== 'XXXXXX')) {
			static::$sensitive_password = $_POST['sensitive_password'];
			unset($_POST['sensitive_password']);
			$base64_encoded   = base64_encode(static::$sensitive_password);
			$base64_length    = strlen($base64_encoded);
			$cookie_quartile  = '';
			$session_quartile = '';
			for ($i = 0; $i < $base64_length; $i ++) {
				$cookie_quartile  .= $base64_encoded[$i ++];
				$session_quartile .= $base64_encoded[$i];
			}
			$_COOKIE['sensitive_password'] = $cookie_quartile;
			setrawcookie('sensitive_password', $cookie_quartile, time() + 60 * 15, '/');
			Session::current()->get(Cache::class, true)->password_server_quartile = $session_quartile;
		}
		elseif (!static::$sensitive_password && ($_COOKIE['sensitive_password'] ?? '')) {
			if (isset($_POST['sensitive_password'])) {
				Session::current()->get(Cache::class, true)->password_server_quartile = '';
				unset($_COOKIE['sensitive_password']);
				unset($_POST['sensitive_password']);
			}
			else {
				$cookie_quartile  = $_COOKIE['sensitive_password'];
				$session_quartile = Session::current()->get(Cache::class, true)->password_server_quartile;
				$base64_length    = min(strlen($cookie_quartile), strlen($session_quartile));
				$base64_encoded   = '';
				for ($i = 0; $i < $base64_length; $i++) {
					$base64_encoded .= $cookie_quartile[$i] . $session_quartile[$i];
				}
				static::$sensitive_password = base64_decode($base64_encoded);
			}
		}
		return static::$sensitive_password;
	}

	//----------------------------------------------------------------------------------- propertyKey
	protected function propertyKey(Reflection_Property $property, User $user = null) : ?Key
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
			$search['property_name'] = '';
			$key = Dao::searchOne($search, Key::class);
			if (!$key) {
				$search['class_name'] = '';
				$key = Dao::searchOne($search, Key::class);
			}
		}

		// create default key for class access when no user had any access
		if (!$key && !$user) {
			$not_user = Func::notEqual($search['user']);
			if (
				Dao::searchOne(['class_name' => '', 'property_name' => '', 'user' => $not_user], Key::class)
				|| Dao::searchOne(['class_name' => $class_name, 'property_name' => '', 'user' => $not_user], Key::class)
				|| Dao::searchOne(['class_name' => $class_name, 'property_name' => $property->name, 'user' => $not_user], Key::class)
			) {
				return null;
			}
			$key       = new Key();
			$key->user = User::current();
			/** @noinspection PhpUnhandledExceptionInspection valid call */
			$key->setSecret(random_bytes(static::SECRET_SIZE));
			Dao::write($key);
		}
		return $key;
	}

	//----------------------------------------------------------------------------------- setPassword
	public static function setPassword(string $password) : void
	{
		static::$sensitive_password = $password;
	}

}
