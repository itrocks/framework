<?php
namespace Bappli\Company\Employee\Tests;

use Bappli\Company\Employee;
use Bappli\Company\Employee\Has_Dates;
use Bappli\Company\Employee\User\Has_User;
use ITRocks\Framework\User\Has_Active;

/**
 * Class Dummy
 * Only test
 *
 * @store false
 */
class Dummy extends Employee
{
	use Has_Active;
	use Has_Dates;
	use Has_User;

}
