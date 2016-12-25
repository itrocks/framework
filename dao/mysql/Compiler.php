<?php
namespace ITRocks\Framework\Dao\Mysql;

use ITRocks\Framework\Builder;
use ITRocks\Framework\Dao;
use ITRocks\Framework\Dao\Func;
use ITRocks\Framework\Mapper\Search_Object;
use ITRocks\Framework\PHP;
use ITRocks\Framework\PHP\Dependency;
use ITRocks\Framework\PHP\ICompiler;
use ITRocks\Framework\PHP\Reflection_Class;
use ITRocks\Framework\PHP\Reflection_Source;
use ITRocks\Framework\Reflection\Annotation\Class_\Link_Annotation;

/**
 * Mysql compiler updates table structure once a PHP script was changed
 *
 * Update is done only if the table already exists. If not, Maintainer will create the table with
 * the right structure once it is accessed
 *
 * This compiler must be into the last compilation wave, as it uses internal reflection and then
 * loads compiled classes.
 *
 * It does not modify PHP sources files.
 */
class Compiler implements ICompiler
{

	//--------------------------------------------------------------------------------------- compile
	/**
	 * @param $source   Reflection_Source the PHP source file object
	 * @param $compiler PHP\Compiler the main compiler
	 * @return boolean false as compilation do never change source
	 */
	public function compile(Reflection_Source $source, PHP\Compiler $compiler = null)
	{
		$dao = Dao::current();
		if ($dao instanceof Link) {
			//$dao->begin();
			//$tables = [];

			/*
			// drop empty tables
			foreach ($dao->getConnection()->getTables() as $table_name) {
				if ($dao->query('SELECT COUNT(*) FROM ' . BQ . $table_name . BQ)->fetch_row()[0]) {
					$tables[$table_name] = true;
				}
				else {
					@$dao->query('DROP TABLE IF EXISTS ' . BQ . $table_name . BQ);
				}
			}
			*/

			/*
			// update tables structures
			foreach ($source->getClasses() as $class) {
				if (isset($tables[$dao->storeNameOf($class->name)])) {
					$dao->createStorage($class->name);
				}
			}
			*/

			//$dao->commit();
		}
		return false;
	}

	//-------------------------------------------------------------------------- moreSourcesToCompile
	/**
	 * Extends the list of sources to compile
	 *
	 * When you modify a file, all these classes may have their matching mysql structure changed :
	 * - the class itself
	 * - all classes that extend the class or use the trait
	 *
	 * @param &$sources Reflection_Source[]
	 * @return Reflection_Source[] added sources list
	 */
	public function moreSourcesToCompile(array &$sources)
	{
		$added = [];
		// Builder is disabled during the listing as we want to get the original linked class name when
		// reading class annotation @link
		Builder::current()->enabled = false;

		/** @var $search Dependency */
		$search = Search_Object::create(Dependency::class);
		$search->file_name = Func::notLike('cache/%');
		$search->type      = Func::orOp([Dependency::T_EXTENDS, Dependency::T_USE]);

		foreach ($sources as $source) {
			foreach ($source->getClasses() as $class) {
				while ($linked_class = Link_Annotation::of($class)->value) {
					$source = Reflection_Class::of($linked_class)->source;
					if (!isset($sources[$source->file_name])) {
						$sources[$source->file_name] = $source;
						$added[$source->getFirstClassName() ?: $source->file_name] = $source;
					}
					$class = $source->getClass($linked_class);
				}
				$search->dependency_name = $class->name;
				foreach (Dao::search($search, Dependency::class) as $dependency) {
					/** @var $dependency Dependency */
					if (!isset($sources[$dependency->file_name])) {
						$source = Reflection_Source::ofFile($dependency->file_name, $dependency->class_name);
						$sources[$dependency->file_name] = $source;
						$added[$source->getFirstClassName() ?: $dependency->file_name] = $source;
					}
				}
			}
		}

		Builder::current()->enabled = true;
		return $added;
	}

}
