<?php
namespace ITRocks\Framework\User;

use Bappli\Company\Employee;
use Bappli\Company\Employee\Has_Dates;
use Bappli\Company\Employee\User\Has_User;
use Exception;
use ITRocks\Framework\Controller;
use ITRocks\Framework\Dao;
use ITRocks\Framework\Tools\Date_Time;

/**
 * Disconnect current user if is not active
 */
class Banned_Controller implements Controller
{

	//------------------------------------------------------------------------------------------- run
	/**
	 * @return mixed|void
	 * @throws Exception
	 */
	public function run()
	{
		/** @var $employees Employee|Has_Dates[]|Has_User[] */
		$employees = Dao::search(
			['date_of_exit' => [Dao\Func::notNull(), Dao\Func::lessOrEqual(Date_Time::now())]],
			Employee::class
		);

		foreach ($employees as $employee) {
			/** @var $user Has_Active */
			$user = $employee->user;

			if (isA($user, Has_Active::class)) {
				$user->active = false;
				Dao::write($user, Dao::only('active'));
			}
		}
		return 'OK';
	}

}
