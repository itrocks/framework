<?php
namespace ITRocks\Framework\Tools\Encryption\Sensitive_Data;

use ITRocks\Framework\Dao;
use ITRocks\Framework\Dao\Func;
use ITRocks\Framework\Reflection\Attribute\Class_\Display_Order;
use ITRocks\Framework\Reflection\Attribute\Property\Alias;
use ITRocks\Framework\Reflection\Attribute\Property\Mandatory;
use ITRocks\Framework\Reflection\Attribute\Property\User;
use ITRocks\Framework\Reflection\Reflection_Property;
use ITRocks\Framework;

/** @feature add, save */
#[Display_Order('sensitive_password, user, already_shared_with')]
class Share
{

	//-------------------------------------------------------------------------- $already_shared_with
	/** @var User[] */
	#[User(User::READONLY)]
	public array $already_shared_with;

	//----------------------------------------------------------------------------------------- $user
	/** @password */
	#[Alias('your_cipher_key'), Mandatory]
	public string $sensitive_password = '';

	//----------------------------------------------------------------------------------------- $user
	#[Mandatory]
	public Framework\User $user;

	//----------------------------------------------------------------------------------- __construct
	public function __construct()
	{
		$this->already_shared_with = [];
		$keys = Dao::search(['user' => Func::notEqual(Framework\User::current())], Key::class);
		foreach ($keys as $key) {
			$this->already_shared_with[] = $key->user;
		}
		if ($this->already_shared_with) {
			return;
		}
		/** @noinspection PhpUnhandledExceptionInspection object */
		$already_shared_with = new Reflection_Property($this, 'already_shared_with');
		User::of($already_shared_with)->add(User::INVISIBLE);
	}

}
