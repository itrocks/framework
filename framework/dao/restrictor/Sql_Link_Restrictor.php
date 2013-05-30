<?php
namespace SAF\Framework;

use AopJoinpoint;

/**
 * This configuration plugin enables to restrict read data
 *
 * TODO this has not been tested nor used yet, please finish and test this !
 */
class Sql_Link_Restrictor implements Configurable, Plugin
{
	use Current { current as private pCurrent; }

	//--------------------------------------------------------------------------------------- CURRENT
	/**
	 * Current callback restrict objects to their Class_Name::current() value
	 * (if no current value, restriction is full and nothing could be read)
	 */
	const CURRENT = "current";

	//------------------------------------------------------------------------- $current_restrictions
	/**
	 * Stores current restrictions queries : set by restrict() and used by applyCurrentRestrictions()
	 *
	 * Each where starts with " WHERE "
	 *
	 * @var string[]
	 */
	private $current_restrictions;

	//--------------------------------------------------------------------------------- $restrictions
	/**
	 * Associate classes names and multiple restriction callbacks
	 *
	 * This is given by configuration
	 *
	 * @var array
	 */
	private $restrictions = array();

	//--------------------------------------------------------------------------------- $restrictions
	/**
	 * Associate classes names and multiple restriction callbacks
	 *
	 * This is an internal cache to avoid repetitive searches into parent classes
	 *
	 * @var array associate classes and callbacks
	 */
	private $final_restrictions = array();

	//----------------------------------------------------------------------------------- __construct
	/**
	 * Constructs a Sql_link restrictor object
	 *
	 * $restrictions are a list of class names as keys, associated to callback restriction functions
	 * Those callbacks must be static public methods accepting two arguments :
	 * - the class name which the callback is associated to into Sql_Link_Restrictor
	 * - a Sql_Joins object giving the restrictor full paths for searches and where it can add joins
	 *
	 * @param $restrictions array
	 */
	public function __construct($restrictions = null)
	{
		if (isset($restrictions)) $this->restrictions = $restrictions;
	}

	//---------------------------------------------------------------------- applyCurrentRestrictions
	/**
	 * Applies current restrictions set by last call to restrict() to a SQL "WHERE" clause
	 *
	 * @param $where string empty or begins where " WHERE "
	 * @return string full SQL "WHERE" clause, including $where and added restrictions
	 */
	private function applyCurrentRestrictions($where)
	{
		$sql = join(") AND (", $this->current_restrictions);
		return $sql
			? (" WHERE " . ($where ? "(" . substr($where, 7) . ") AND " : "") . "(" . $sql . ")")
			: $where;
	}

	//------------------------------------------------------------------------------ applyRestriction
	/**
	 * Apply a restriction to the builder
	 *
	 * @param $builder     Sql_Select_Builder
	 * @param $class_name  string
	 * @param $restriction string|array a restriction callback
	 */
	private function applyRestriction(Sql_Select_Builder $builder, $class_name, $restriction)
	{
		if ($restriction == self::CURRENT) {
			$restriction = array($class_name, "current");
		}
		$where_array = call_user_func_array($restriction, array($class_name, $builder->getJoins()));
		if ($where_array) {
			$where_builder = new Sql_Where_Builder(
				$builder->getJoins()->getStartingClassName(),
				$where_array,
				$builder->getSqlLink(),
				$builder->getJoins()
			);
			$this->current_restrictions[] = $where_builder->build();
		}
	}

	//---------------------------------------------------------------- beforeSqlSelectBuilderFinalize
	/**
	 * @param $joinpoint AopJoinpoint
	 */
	public static function beforeSqlSelectBuilderFinalize(AopJoinpoint $joinpoint)
	{
		/** @var $arguments string[] */
		$arguments = $joinpoint->getArguments();
		/** @var $where string */
		$where = $arguments[1];
		/** @var $builder Sql_Select_Builder */
		$arguments[1] = self::current()->applyCurrentRestrictions($where);
		if ($arguments[1] !== $where) {
			$joinpoint->setArguments($arguments);
		}
	}
	//------------------------------------------------------------- beforeSqlSelectBuilderBuildTables
	/**
	 * @param $joinpoint AopJoinpoint
	 */
	public static function beforeSqlSelectBuilderBuildTables(AopJoinpoint $joinpoint)
	{
		/** @var $builder Sql_Select_Builder */
		$builder = $joinpoint->getObject();
		self::current()->restrict($builder);
	}

	//--------------------------------------------------------------------------------------- current
	/**
	 * @param $set_current Sql_Link_Restrictor
	 * @return Sql_Link_Restrictor
	 */
	public static function current($set_current = null)
	{
		return self::pCurrent($set_current);
	}

	//------------------------------------------------------------------------------- getRestrictions
	/**
	 * Gets restrictions list for a given class name
	 *
	 * @param $class_name
	 * @return array
	 */
	private function getRestrictions($class_name)
	{
		if (isset($this->final_restrictions[$class_name])) {
			$restrictions = $this->final_restrictions[$class_name];
		}
		else {
			$restrictions = array();
			foreach (class_tree($class_name) as $tree_class_name) {
				if (isset($this->restrictions[$tree_class_name])) {
					$restrictions = array_merge($restrictions, $this->restrictions[$tree_class_name]);
				}
			}
			$this->final_restrictions[$class_name] = $restrictions;
		}
		return $restrictions;
	}

	//-------------------------------------------------------------------------------------- register
	/**
	 * Registers SQL link restrictor plugin
	 */
	public static function register()
	{
		Aop::add(Aop::BEFORE, 'SAF\Framework\Sql_Select_Builder->buildTables()',
			array(__CLASS__, "beforeSqlSelectBuilderBuildTables")
		);
		Aop::add(Aop::BEFORE, 'SAF\Framework\Sql_Select_Builder->finalize()',
			array(__CLASS__, "beforeSqlSelectBuilderFinalize")
		);
	}

	//-------------------------------------------------------------------------------------- restrict
	/**
	 * Sets current restriction using $builder's joins foreign classes
	 *
	 * If current restrictions exist before call of restrict(), they are reset by this call.
	 *
	 * @param $builder Sql_Select_Builder
	 */
	private function restrict(Sql_Select_Builder $builder)
	{
		$this->current_restrictions = array();
		if ($this->restrictions) {
			foreach ($builder->getJoins()->getJoins() as $join) {
				if (isset($join->foreign_class)) {
					$restrictions = $this->getRestrictions($join->foreign_class);
					foreach ($restrictions as $restriction) {
						$this->applyRestriction($builder, $join->foreign_class, $restriction);
					}
				}
			}
		}
	}

}
