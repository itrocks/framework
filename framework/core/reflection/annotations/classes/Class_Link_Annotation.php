<?php
namespace SAF\Framework;

/**
 * This tells that the class is a link class
 *
 * It means that :
 * - it's data storage set naming will be appended by a "_links"
 * - there will be no data storage field creation for parent linked table into this data storage set
 *   but a link field
 *
 * @example "@link User" means that the herited class of User is linked to the parent class User
 * - data storage fields will be those from this class, and immediate parent classes if they are not "User"
 * - an additional implicit data storage field will link to the class "User"
 */
class Class_Link_Annotation extends Annotation
{

}
