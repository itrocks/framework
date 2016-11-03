<?php
namespace ITRocks\Framework\Dao\Func;

/**
 * A Dao where function applies only to conditions : it changes the condition behaviour
 * This kind of function needs INNER JOIN to be added to the query
 */
interface Where_Inner extends Where
{

}
