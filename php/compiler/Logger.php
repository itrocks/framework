<?php
namespace SAF\Framework\PHP\Compiler;

use SAF\Framework;
use SAF\Framework\Builder;
use SAF\Framework\Dao;
use SAF\Framework\Dao\Func;
use SAF\Framework\PHP\Compiler;
use SAF\Framework\PHP\Reflection_Source;
use SAF\Framework\Plugin\Register;
use SAF\Framework\Plugin\Registerable;
use SAF\Framework\Tools\Date_Time;

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
	private $log_flag = false;

	//------------------------------------------------------------------------------- onCompileSource
	/**
	 * @param $source Reflection_Source
	 */
	public function onCompileSource(Reflection_Source $source)
	{
		Dao::begin();
		foreach ($source->getClasses() as $class) {
			/** @var $log Compiler_Log */
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
				/** @var $logger Compiler_Log */
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
	public function register(Register $register)
	{
		$register->aop->beforeMethod([Compiler::class, 'compileSource'], [$this, 'onCompileSource']);
		$register->aop->afterMethod([Framework\Logger::class, 'stop'], [$this, 'onLoggerStop']);
	}

}
