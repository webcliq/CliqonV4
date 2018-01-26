 <?php
 // Ctrl K3 to fold
 *
 * @category   Web application framework
 * @package    Cliq
 * @author     Original Author <conkascom@gmail.com>
 * @copyright  2017 Webcliq
 * @license    http://www.php.net/license/3_01.txt  PHP License 3.01
 * @version    Release: 4.1.0
 * @link       http://cliqon.com
 */
class MyAdmin extends Admin
{
	
	static function MyuStr($ref)
	{
		return "My version of uStr()";
	}

}
# alias +f+ class
if(!class_exists("E")){ class_alias('Myadmin', 'E'); };

/*
See Classkit

    classkit_import — Import new class method definitions from a file
    classkit_method_add — Dynamically adds a new method to a given class
    classkit_method_copy — Copies a method from class to another
    classkit_method_redefine — Dynamically changes the code of the given method
    classkit_method_remove — Dynamically removes the given method
    classkit_method_rename — Dynamically changes the name of the given method

*/

