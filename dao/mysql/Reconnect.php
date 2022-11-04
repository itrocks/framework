<?php
namespace ITRocks\Framework\Dao\Mysql;

use ITRocks\Framework\AOP\Joinpoint\Before_Method;
use ITRocks\Framework\Dao;
use ITRocks\Framework\Plugin\Register;
use ITRocks\Framework\Plugin\Registerable;
use ITRocks\Framework\Tools\Contextual_Mysqli;
use ITRocks\Framework\User;

/**
 * Mysql\Reconnect plugin allow auto-reconnect when a server disconnected error is thrown
 */
class Reconnect implements Registerable
{

	//---------------------------------------------------------------------------- onMysqliQueryError
	/**
	 * This is called after each mysql query error in order to reconnect lost connexion to server
	 *
	 * @output $joinpoint->result mysqli_result|boolean
	 * @param $object    Contextual_Mysqli
	 * @param $query     string
	 * @param $joinpoint Before_Method
	 */
	public function onMysqliQueryError(
		Contextual_Mysqli &$object, string $query, Before_Method $joinpoint
	) {
		$mysqli =& $object;
		if (
			in_array($mysqli->last_errno, [Errors::CR_SERVER_GONE_ERROR, Errors::CR_SERVER_LOST], true)
		) {
			// wait 1 second an try to reconnect
			sleep(1);
			if (!$mysqli->ping()) {
				if (!$mysqli->reconnect()) {
					trigger_error(
						'$mysqli->ping() and reconnect() failed after a server gone error', E_USER_ERROR
					);
				}
			}
			$joinpoint->result = $mysqli->query($query);
			if (!$mysqli->last_errno && !$mysqli->last_error) {
				$joinpoint->stop = true;
			}
		}
	}

	//-------------------------------------------------------------------------------------- register
	/**
	 * @param $register Register
	 */
	public function register(Register $register) : void
	{
		$aop = $register->aop;
		$aop->beforeMethod([Contextual_Mysqli::class, 'queryError'], [$this, 'onMysqliQueryError']);
	}

	//------------------------------------------------------------------------------------------ test
	/**
	 * A functional test for reconnect :
	 * Executes a query once per seconds during one minute.
	 * Try to kill the running mysql thread during the test : the connexion should come back.
	 */
	public function test()
	{
		$time = time();
		for ($i = 1; $i <= 15; $i ++) {
			/** @var $dao Link */
			$dao   = Dao::current();
			$users = Dao::readAll(User::class);
			/** @var $user User PhpStorm bug */
			$user = reset($users);
			if (
				!is_a($user, User::class)
				|| $dao->getConnection()->last_errno
				|| $dao->getConnection()->last_error
			) {
				return $i . ' : query error '
					. $dao->getConnection()->last_errno . SP . $dao->getConnection()->last_error;
			}
			if ($i < 15) {
				sleep(1);
			}
		}
		return 'OK. Took ' . (time() - $time) . ' seconds.';
	}

}
