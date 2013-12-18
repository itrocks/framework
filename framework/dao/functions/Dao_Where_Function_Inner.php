<?php
namespace SAF\Framework;

/**
 * A Dao where function applies only to conditions : it changes the condition behavior
 * This kind of function needs INNER JOIN to be added to the query
 */
interface Dao_Where_Function_Inner extends Dao_Where_Function
{

}
