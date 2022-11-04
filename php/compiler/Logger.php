<?php
namespace ITRocks\Framework\PHP\Compiler;

use ITRocks\Framework;
use ITRocks\Framework\Builder;
use ITRocks\Framework\Dao;
use ITRocks\Framework\Dao\Func;
use ITRocks\Framework\PHP\Compiler;
use ITRocks\Framework\PHP\Reflection_Source;
use ITRocks\Framework\Plugin\Register;
use ITRocks\Framework\Plugin\Registerable;
use ITRocks\Framework\Tools\Date_Time;

/**
 * This plugin will log every file compiling
 */
class Logger implements Registerable
{

	//------------------------------------------------------------------------------------- $log_flag
	/**
	 * This is for optimization purpose : we need to search logs only when there are logs
	 *
	 * @var boolean
	 */
	private bool $log_flag = false;

	//------------------------------------------------------------------------------- onCompileSource
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $source Reflection_Source
	 */
	public function onCompileSource(Reflection_Source $source)
	{
		Dao::begin();
		foreach ($source->getClasses() as $class) {
			/** @noinspection PhpUnhandledExceptionInspection constant */
			$log = Builder::create(Compiler_Log::class);
			$log->class_name = $class->getName();
			$log->date_time  = Date_Time::now();
			Dao::write($log);
			$this->log_flag = true;
		}
		Dao::commit();
	}

	//---------------------------------------------------------------------------------- onLoggerStop
	/**
	 * @param $object Framework\Logger
	 */
	public function onLoggerStop(Framework\Logger $object)
	{
		if ($this->log_flag) {
			Dao::begin();
			foreach (Dao::search(['log' => Func::isNull()], Compiler_Log::class) as $logger) {
				$logger->log = $object->log_entry;
				Dao::write($logger, Dao::only('log'));
			}
			$this->log_flag = false;
			Dao::commit();
		}
	}

	//-------------------------------------------------------------------------------------- register
	/**
	 * @param $register Register
	 */
	public function register(Register $register) : void
	{
		$register->aop->beforeMethod([Compiler::class, 'compileSource'], [$this, 'onCompileSource']);
		$register->aop->afterMethod([Framework\Logger::class, 'stop'], [$this, 'onLoggerStop']);
	}

}
