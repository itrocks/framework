<?php
namespace ITRocks\Framework\Reflection\Annotation;

use ITRocks\Framework\Reflection\Annotation\Template\Boolean_Annotation;
use ITRocks\Framework\Reflection\Annotation\Template\Constant_Or_Method_Annotation;
use ITRocks\Framework\Reflection\Annotation\Template\Default_True_Boolean_Annotation;
use ITRocks\Framework\Reflection\Annotation\Template\Documented_Type_Annotation;
use ITRocks\Framework\Reflection\Annotation\Template\List_Annotation;
use ITRocks\Framework\Reflection\Annotation;
use ITRocks\Framework\Reflection\Annotation\Template\Method_Annotation;
use ITRocks\Framework\Reflection\Annotation\Template\Method_Target_Annotation;

//-------------------------------------------------------------------- Parser::$default_annotations
/**
 * Known annotations that do not need a specific class
 */
Parser::$default_annotations = [

	/**
	 * @after_commit afterCommit
	 * This is a Multiple_Annotation
	 * Declare one or several methods to call after the written object are committed (transaction end)
	 * using any (even non-transactional) data link
	 * - These methods may accept a Data_Link as first argument, if needed
	 * - These methods may accept a Dao\Option[] as second argument, if needed
	 */
	Parser::T_CLASS . '@after_commit' => Method_Annotation::class,

	/**
	 * @after_create afterCreate
	 * This is a Multiple_Annotation
	 * Declare one or several methods to call after the object is created using a data link
	 * - These methods may accept a Dao\Data_Link as first argument and Dao\Option[] as second,
	 * if needed
	 */
	Parser::T_CLASS . '@after_create' => Method_Annotation::class,

	/**
	 * @after_read afterRead
	 * This is a Multiple_Annotation
	 * Declare one or several methods to call after the object is read using a data link
	 * - These methods may accept the resulting object and Dao\Option[] as arguments, if needed
	 */
	Parser::T_CLASS . '@after_read' => Method_Annotation::class,

	/**
	 * @after_transform afterTransform
	 * This is a Multiple_Annotation
	 * Declare one or several methods to call after the object is being created from a transformation
	 * - These methods may accept the source object, if needed
	 */
	Parser::T_CLASS . '@after_transform' => Method_Annotation::class,

	/**
	 * @after_update afterUpdate
	 * This is a Multiple_Annotation
	 * Declare one or several methods to call after the object is updated using a data link
	 * - These methods may accept a Dao\Data_Link as first argument and Dao\Option[] as second,
	 * if needed
	 */
	Parser::T_CLASS . '@after_update' => Method_Annotation::class,

	/**
	 * @after_write afterWrite
	 * This is a Multiple_Annotation
	 * Declare one or several methods to call after the object is written using a data link
	 * - These methods may accept a Dao\Data_Link as first argument and Dao\Option[] as second,
	 * if needed
	 */
	Parser::T_CLASS . '@after_write' => Method_Annotation::class,

	/**
	 * @before_build_array beforeBuildArray
	 * This is a Multiple_Annotation
	 * Declare one or several methods to call before the object is built from an array representation
	 * - These methods may accept an array as first reference argument, if needed
	 */
	Parser::T_CLASS . '@before_build_array' => Annotation::class,

	/**
	 * @before_create beforeCreate
	 * This is a Multiple_Annotation
	 * Declare one or several methods to call before the object is created using a data link
	 * - These methods may accept a Dao\Data_Link as first argument and Dao\Option[] as second,
	 * if needed
	 * - These methods may return false to cancel the writing of the object
	 */
	Parser::T_CLASS . '@before_create' => Method_Annotation::class,

	/**
	 * @before_delete beforeDelete
	 * This is a Multiple_Annotation
	 * Declare one or several methods to call before the object is deleted using a data link
	 * - These methods may return false to cancel the deletion of the object
	 */
	Parser::T_CLASS . '@before_delete' => Method_Annotation::class,

	/**
	 * @before_update beforeUpdate
	 * This is a Multiple_Annotation
	 * Declare one or several methods to call before the object is updated using a data link
	 * - These methods may accept a Dao\Data_Link as first argument and Dao\Option[] as second,
	 * if needed
	 * - These methods may return false to cancel the writing of the object
	 */
	Parser::T_CLASS . '@before_update' => Method_Annotation::class,

	/**
	 * @before_write beforeWrite
	 * This is a Multiple_Annotation
	 * Declare one or several methods to call before the object is written using a data link
	 * - These methods may accept a Dao\Data_Link as first argument and Dao\Option[] as second,
	 * if needed
	 * - These methods may return false to cancel the writing of the object
	 */
	Parser::T_CLASS . '@before_write' => Method_Annotation::class,

	/**
	 * @before_writes beforeWrites
	 * This is a Multiple_Annotation
	 * Declare one or several methods to call before the objects is written using a data link
	 * by the write controller
	 * - These methods may accept a Dao\Option[] as first argument, if needed
	 * - These methods may return false to cancel the writing of the objects by the controller
	 */
	Parser::T_CLASS . '@before_writes' => Method_Annotation::class,

	/**
	 * @business
	 * This defines a class or a trait used to describe business objects
	 */
	Parser::T_CLASS . '@business' => Boolean_Annotation::class,

	/**
	 * @default_class_feature add
	 */
	Parser::T_CLASS . '@default_class_feature' => Annotation::class,

	/**
	 * @default_feature output
	 * @deprecated replace by @default_object_feature
	 */
	Parser::T_CLASS . '@default_feature' => Annotation::class,

	/**
	 * @default_object_feature output
	 */
	Parser::T_CLASS . '@default_object_feature' => Annotation::class,

	/**
	 * @default_set_feature list
	 */
	Parser::T_CLASS . '@default_set_feature' => Annotation::class,

	/**
	 * @deprecated [false]
	 * Identifies a deprecated class
	 */
	Parser::T_CLASS . '@deprecated' => Boolean_Annotation::class,

	/**
	 * @duplicate duplicateMethod
	 * This is a Multiple_Annotation
	 * Declare one or several methods to call after the object has been duplicated
	 */
	Parser::T_CLASS . '@duplicate' => Method_Annotation::class,

	/**
	 * @feature [[[Class/Path/]feature] Human-readable atomic end-user feature name]
	 * This is a Multiple_Annotation
	 * Marks the class as an atomic end-user feature
	 * Implicit end-user features will be enabled for this class if there are no yaml files
	 */
	Parser::T_CLASS . '@feature' => Annotation::class,

	/**
	 * @groups_order Group1, Group2, ...
	 * This is a Multiple_Annotation
	 *
	 * Declares what is the "from the most important to the less important" order for groups
	 * Group1 and so on are the identifiers of the groups existing for the class
	 * groups that are not into @groups_order will be the least important, sorted alphabetically
	 */
	Parser::T_CLASS . '@groups_order' => List_Annotation::class,

	/**
	 * @on_list onList
	 * This is a Multiple_Annotation
	 * Declare one or several methods to call on data list Dao::select() call
	 */
	Parser::T_CLASS . '@on_list' => Method_Annotation::class,

	/**
	 * @stored [false]
	 * Identifies a class that may be stored using data links
	 * When this annotation is set, this enables simplified / implicit use of @link
	 * ie "@link Object" and @link DateTime" will be implicit (you won't need it)
	 * ie "@link All", "@link Collection", "@link Map"
	 *   will be replaced by "@var Object[] All", "@var Object[] Collection" and "@var Object[] Map"
	 */
	Parser::T_CLASS . '@stored' => Boolean_Annotation::class,

	/**
	 * @unique property1, property2, ...
	 * Identifies a list of property that are the unique tuple of data that identify a record.
	 * Used with @link classes to allow the same object multiple times with different link property
	 * values (ie a client can have the same contract several times, with different dates)
	 */
	Parser::T_CLASS . '@unique' => List_Annotation::class,

	/**
	 * @user_remove [[\Vendor\Module\]Class_Name::]featureName] [target_selector]
	 * Associates a feature controller to call each time a sub / linked object is removed by the final
	 * user to an input form (target of a @link Collection or link @Map).
	 * a target selector can be used to define where the result is loaded (#messages as default)
	 */
	Parser::T_CLASS . '@user_remove' => Method_Target_Annotation::class,

	/**
	 * @advice
	 * This tells everybody the method is an AOP advice
	 */
	Parser::T_METHOD . '@advice' => Boolean_Annotation::class,

	/**
	 * @deprecated [false]
	 * Identifies a deprecated method
	 */
	Parser::T_METHOD . '@deprecated' => Boolean_Annotation::class,

	/**
	 * @return string
	 * Gets the type of the returned value (as a string) and the associated documentation
	 */
	Parser::T_METHOD . '@return' => Documented_Type_Annotation::class,

	/**
	 * @after_add_element afterAddElement
	 * This is a Multiple_Annotation
	 * Declare one or several methods to call after the Collection/Map property element is added
	 * using a data link
	 * The called method accepts a Dao\Event\Property_Write event object as a unique argument
	 */
	Parser::T_PROPERTY . '@after_add_element' => Method_Annotation::class,

	/**
	 * @before_add_element beforeAddElement
	 * This is a Multiple_Annotation
	 * Declare one or several methods to call before the Collection/Map property element is added
	 * using a data link
	 * The called method accepts a Dao\Event\Property_Write event object as a unique argument
	 */
	Parser::T_PROPERTY . '@before_add_element' => Method_Annotation::class,

	/**
	 * @before_remove_element beforeRemoveElement
	 * This is a Multiple_Annotation
	 * Declare one or several methods to call before the Collection/Map property element is removed
	 * using a data link
	 * The called method accepts a Dao\Event\Property_Delete event object as a unique argument
	 */
	Parser::T_PROPERTY . '@before_remove_element' => Method_Annotation::class,

	/**
	 * @binary
	 * Set this boolean annotation to tell that binary data will be stored into the property value
	 * The property should be of type 'string'
	 */
	Parser::T_PROPERTY . '@binary' => Boolean_Annotation::class,

	/**
	 * @block The block display
	 * The Block annotation for a property enables grouping properties into common blocks
	 * TODO LOW explain why a property could have multiple blocks
	 */
	Parser::T_PROPERTY . '@block' => List_Annotation::class,

	/**
	 * @calculated
	 * The value of this property is calculated : it will not be stored into DAO data links
	 */
	Parser::T_PROPERTY . '@calculated' => Boolean_Annotation::class,

	/**
	 * @component
	 * The object referenced by the property is a component of the main object.
	 * It should not exist without its container.
	 */
	Parser::T_PROPERTY . '@component' => Boolean_Annotation::class,

	/**
	 * @composite
	 * Identifies a property to link to the composite object.
	 * To be used into a component class only, when multiple properties link to composite class(es)
	 */
	Parser::T_PROPERTY . '@composite' => Boolean_Annotation::class,

	/**
	 * @dao ITRocks\Framework\Dao\Mysql\Link
	 * This annotation stores the name of the Dao that should always used for a linked object,
	 * map or collection property. Use it in conjunction with @link and @var annotations.
	 */
	Parser::T_PROPERTY . '@dao' => Annotation::class,

	/**
	 * @default [[\Class\Namespace\]Class_Name::]methodName
	 * Identifies a method that gets the default value for the property
	 * The Property will be sent as an argument to this callable
	 */
	Parser::T_PROPERTY . '@default' => Method_Annotation::class,

	/**
	 * @deprecated [false]
	 * Identifies a deprecated property
	 */
	Parser::T_PROPERTY . '@deprecated' => Boolean_Annotation::class,

	/**
	 * @display_order property_name, another_property
	 * Declares property names' display order
	 */
	Parser::T_PROPERTY . '@display_order' => List_Annotation::class,

	/**
	 * @editor editor_name
	 * Enables online text editor (ckeditor)
	 */
	Parser::T_PROPERTY . '@editor' => Annotation::class,

	/**
	 * @empty_check [false]
	 * This property value is checked into Empty_Object and Null_Object only if true (default)
	 */
	Parser::T_PROPERTY . '@empty_check' => Default_True_Boolean_Annotation::class,

	/**
	 * @immutable [false]
	 * This property value is checked into Is_Immutable : property is not part of immutable if false
	 */
	Parser::T_PROPERTY . '@immutable' => Default_True_Boolean_Annotation::class,

	/**
	 * @filters property_name, another_property
	 * Declares other property names that are used to filter possible values of the property
	 */
	Parser::T_PROPERTY . '@filters' => List_Annotation::class,

	/**
	 * @length 5
	 * Tells what is the wished count of characters for the value of the property
	 */
	Parser::T_PROPERTY . '@length' => Annotation::class,

	/**
	 * @link_composite
	 * Identifies a property to link to the composite object for a @link Class
	 * This is useful to declare it explicitely only if the @link Class links two identical classes
	 */
	Parser::T_PROPERTY . '@link_composite' => Boolean_Annotation::class,

	/**
	 * @mandatory [false]
	 * Set this annotation to tell the data controllers that the property value is mandatory
	 */
	Parser::T_PROPERTY . '@mandatory' => Boolean_Annotation::class,

	/**
	 * @max_length 100
	 * Tells what maximal count of characters can have the value of the property
	 */
	Parser::T_PROPERTY . '@max_length' => Annotation::class,

	/**
	 * @max_value 40
	 * Tells what is the maximal allowed value for the property
	 */
	Parser::T_PROPERTY . '@max_value' => Annotation::class,

	/**
	 * @min_length 10
	 * Tells what is the minimal count of characters for the value of the property
	 */
	Parser::T_PROPERTY . '@min_length' => Annotation::class,

	/**
	 * @min_value -5
	 * Tells what is the minimal allowed value for the property
	 */
	Parser::T_PROPERTY . '@min_value' => Annotation::class,

	/**
	 * @multiline [false]
	 * This tells that the string property can store multiple lines of text (default is false)
	 */
	Parser::T_PROPERTY . '@multiline' => Boolean_Annotation::class,

	/**
	 * @ordered_values [false]
	 * This tells that the property values must not be ordered (default is true)
	 */
	Parser::T_PROPERTY . '@ordered_values' => Boolean_Annotation::class,

	/**
	 * @output serialized
	 * The serialized value of the property value will be displayed and edited
	 */
	Parser::T_PROPERTY . '@output' => Annotation::class,

	/**
	 * @override [false]
	 * This tells that the property overrides a parent public/protected property having the same name
	 */
	Parser::T_PROPERTY . '@override' => Boolean_Annotation::class,

	/**
	 * @precision 4
	 * Tells how many decimals are stored/displayed on a float number, ie 0.5513 in ok in this case
	 */
	Parser::T_PROPERTY . '@precision' => Annotation::class,

	/**
	 * @read_only [false]
	 * Set this annotation to set the property in read-only mode : it can't be set
	 * Not to be confused with @user readonly, which enable the application to set a value
	 * into this property, but does not allow the final user to alter it through the HMI
	 */
	Parser::T_PROPERTY . '@read_only' => Boolean_Annotation::class,

	/**
	 * @replace_filter [false]
	 * The property must be used as a filter when replacing the object referenced by another object
	 */
	Parser::T_PROPERTY . '@replace_filter' => Boolean_Annotation::class,

	/**
	 * @replaces property_name
	 * This tells the framework the property replaces an existing parent property name, so the parent
	 * property and this property will point on the same reference and have a common value.
	 */
	Parser::T_PROPERTY . '@replaces' => Annotation::class,

	/**
	 * @search_range false
	 * Search range using "from-to" with - as separator is accepted by default. Set this to false to
	 * disable ie for properties which values often contain the - character.
	 */
	Parser::T_PROPERTY . '@search_range' => Default_True_Boolean_Annotation::class,

	/**
	 * @set_store_name store_name
	 * Defines the name of the automatically generated link table.
	 */
	Parser::T_PROPERTY . '@set_store_name' => Annotation::class,

	/**
	 * @setter [[[\Vendor\Module\]Class_Name::]methodName]
	 * This is a Multiple_Annotation
	 * Tells a method name that is the setter for that property.
	 * The setter will be called each time the program changes the value of the property.
	 */
	Parser::T_PROPERTY . '@setter' => Method_Annotation::class,

	/**
	 * @show_seconds
	 * Tells that for a Date_Time we must show seconds to the user.
	 * If not (default), seconds are always hidden by Loc::dateToLocale()
	 */
	Parser::T_PROPERTY . '@show_seconds' => Boolean_Annotation::class,

	/**
	 * @show_time
	 * Tells that for a Date_Time how we must show time to the user.
	 * default / false is the same as 'auto' : time will be shown if not 00:00:00.
	 * Others values are 'always' and 'never', 'auto' can also be set.
	 */
	Parser::T_PROPERTY . '@show_time' => Annotation::class,

	/**
	 * @signed
	 * Tells that the numeric value can be negative.
	 */
	Parser::T_PROPERTY . '@signed' => Boolean_Annotation::class,

	/**
	 * @textile
	 * This tells that the property should be rendered using textile parsing
	 */
	Parser::T_PROPERTY . '@textile' => Boolean_Annotation::class,

	/**
	 * @translate [common]
	 * This property is translated : original in 'default language', use user language to translate
	 * If common : the common application translation classes / data is used
	 * If not common (true) : a specific Data_Translation class is used for data-type translations
	 */
	Parser::T_PROPERTY . '@translate' => Annotation::class,

	/**
	 * @unit [[\Class\Namespace\]Class_Name::]methodName|constant
	 * This tells that the property should be rendered using textile parsing
	 */
	Parser::T_PROPERTY . '@unit' => Constant_Or_Method_Annotation::class,

	/**
	 * @user_change [[\Vendor\Module\]Class_Name::]featureName] [target_selector]
	 * Associates a feature controller to call each time a property value is changed by the final user
	 * to an input form.
	 * a target selector can be used to define where the result is loaded (#messages as default)
	 */
	Parser::T_PROPERTY . '@user_change' => Method_Target_Annotation::class,

	/**
	 * @user_default [[\Class\Namespace\]Class_Name::]methodName
	 * Identifies a method that gets the default value for the property into forms only
	 * The Property will be sent as an argument to this callable
	 */
	Parser::T_PROPERTY . '@user_default' => Method_Annotation::class,

	/**
	 * @user_getter [Vendor\Module\Class_Name::]methodName
	 */
	Parser::T_PROPERTY . '@user_getter' => Annotation::class,

];
