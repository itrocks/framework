<?php
namespace ITRocks\Framework\Sql\Builder;

use ITRocks\Framework\Dao\Mysql\View;

/**
 * SQL create view queries builder
 */
class Create_View
{

	//----------------------------------------------------------------------------------------- $view
	/**
	 * @var View
	 */
	private View $view;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $view View
	 */
	public function __construct(View $view)
	{
		$this->view = $view;
	}

	//----------------------------------------------------------------------------------------- build
	/**
	 * To create a view with foreign keys, we need multiple queries.
	 * This method Returns all necessary queries : CREATE VIEW, then ALTER VIEW ... ADD CONSTRAINT.
	 *
	 * @return string[]
	 */
	public function build() : array
	{
		$queries[] = 'CREATE VIEW' . LF . BQ . $this->view->getName() . BQ . LF
			. 'AS' . SP
			. join(LF . 'UNION ALL ', $this->view->select_queries);
		return $queries;
	}

}
