<?php
/**
 * Cliqon Framework Registry Core
 *
 * @category   Web application framework
 * @package    Cliq
 * @author     Original Author <conkascom@gmail.com>
 * @copyright  2016 Conkas cb
 * @license    http://www.php.net/license/3_01.txt  PHP License 3.01
 * @version    Release: 4.1.0
 * @link       http://cliqon.com
 */
use Tracy\Debugger;

class Registry
{

	private static $thisclass = "Registry";

    /**
     * Our array of objects
     * @access private
     */
    private static $objects = array();
     
    /**
     * Our array of settings
     * @access private
     */
    private static $settings = array();
         
    /**
     * The instance of the registry
     * @access private
     */
    private static $instance;

    private $methods = array();
     
    /**
     * Private constructor to prevent it being created directly
     * @access protected
     */
    protected function __construct()
    {
     	
    }
         
    /**
     * singleton method used to access the object
     * @access public
     * @return 
     */
    public static function singleton()
    {
        if(!isset(self::$instance))
        {
            $obj = __CLASS__;
            self::$instance = new $obj;
        }
        return self::$instance;
    }
     
    /**
     * prevent cloning of the object: issues an E_USER_ERROR if this is attempted
     */
    public function __clone()
    {
        trigger_error('Cloning the registry is not permitted', E_USER_ERROR);
    }

    /**
    * Build an instance of the given class
    * 
    * @param string $class
    * @return mixed
    *
    * @throws Exception
    */
    public function resolve($class)
    {
        $reflector = new \ReflectionClass($class);

        if( ! $reflector->isInstantiable())
        {
            throw new \Exception("[$class] is not instantiable");
        }
       
        $constructor = $reflector->getConstructor();
        
        if(is_null($constructor)) {    
            
            /*
            $class_methods = get_class_methods($class);
            foreach($class_methods as $method_name) {
                self::$instance->$method_name = $class->$method_name;
            }   
            */
            $this->set('model', 'clean');
            return new $class;

        } else {
            
            $parameters = $constructor->getParameters();
            $dependencies = $this->getDependencies($parameters);       
            
            return $reflector->newInstanceArgs($dependencies);  
        }     
    }

    
    /**
     * Build up a list of dependencies for a given methods parameters
     *
     * @param array $parameters
     * @return array
     */
    public function getDependencies($parameters)
    {
        $dependencies = array();
        
        foreach($parameters as $parameter)
        {
            $dependency = $parameter->getClass();
            
            if(is_null($dependency))
            {
                $dependencies[] = $this->resolveNonClass($parameter);
            }
            else
            {
                $dependencies[] = $this->resolve($dependency->name);
            }
        }
        
        return $dependencies;
    }
    
    /**
     * Determine what to do with a non-class value
     *
     * @param ReflectionParameter $parameter
     * @return mixed
     *
     * @throws Exception
     */
    public function resolveNonClass(ReflectionParameter $parameter)
    {
        if($parameter->isDefaultValueAvailable())
        {
            return $parameter->getDefaultValue();
        }
        
        throw new Exception("Erm.. Cannot resolve the unkown!?");
    }

    /**
     * Register an existing Class object in the registry
     * @param String $object the name of the object
     * @param String $key the key for the array
     * @return void
     */
    public function register($object, $key, $subdir = null)
    {
    	try {

            if($subdir) {
                require_once($subdir.$object.'.php');
            }            

            if(!self::valid($key, 'string')) {
                throw new Exception("Store:Key was not a String:".$key);
            }

            if(!self::valid($object, 'string')) {
                throw new Exception("Store:Class name was not a string");
            }  
                      
            self::$objects[$key] = new $object();  

        } catch (Exception $e) {
            Debugger::log($e->getMessage());
        } 
    }   		
     
    /**
     * Stores setting in the properties registry
     * @param String $data
     * @param String $key the key for the array
     * @return void
     */
    public function set($key, $data)
    {
    	try {

	    	if(!self::valid($key, 'string')) {
	    		throw new Exception("$Key was not a String");
	    	}

	    	if(!isset($data)) {
	    		throw new Exception("$data was not set");
	    	}
    		
        	self::$settings[$key] = $data;     

        } catch (Exception $e) {
            Debugger::log($e->getMessage());
        } 
    }    
     
    /**
     * Gets an object from the registry or a value from settings
     * @param String $key the array key
     * @return object or value
     */
    public function get($key)
    {
        
    	try {

	    	if(!self::valid($key, 'string')) {
	    		throw new Exception("$Key was not a String");
	    	}

            if(array_key_exists($key, self::$objects)) {
                return self::$objects[$key];
            } else if($key == 'lcd') {
                if (class_exists('Cookie')) {
                   return Z::zget('Langcd'); 
                } 
            } else {
                return self::$settings[$key];
            }
    		
        } catch (Exception $e) {
            Debugger::log($e->getMessage());
        } 
    }

    /**
     * Does key exist in settings
     * @param String $key the key in the array
     * @return void
     */
    public function has($key)
    { 
    	try {

	    	if(!self::valid($key, 'string')) {
	    		throw new Exception("$Key was not a String");
	    	}
    		
        	if(array_key_exists($key, self::$settings)) {
	        	return self::$settings[$key];
            } else if($key == 'lcd') {
                return Z::zget('Langcd');
            } else {
	        	return false;
	        }   

        } catch (Exception $e) {
            Debugger::log($e->getMessage());
        } 
    }

    protected function valid($chk, $type)
    {
	    if(isset($chk)) {
    		switch($type) {
	    		case "string":
					return gettype($chk) === 'string' && $chk !== '';
	    		break;

	    		case "array":
	    			if(is_array($chk)) {
	    				return $chk;
	    			}
	    		break;

	    		case "int":
	    			if(is_numeric($chk)) {
	    				return $chk;
	    			}
	    		break;

	    		case "bool":
	    			if(is_bool($chk)) {
	    				return $chk;
	    			}
	    		break;
	    	}
	    }
    	return false;
    }

	// This will retrieve the "intended" request method.  Normally, this is the
	// actual method of the request.  Sometimes, though, the intended request method
	// must be hidden in the parameters of the request.  For example, when attempting to
	// delete a file using a POST request. In that case, "DELETE" will be sent along with
	// the request in a "_method" parameter.
	function get_request_method() {
	    global $HTTP_RAW_POST_DATA;

	    if(isset($HTTP_RAW_POST_DATA)) {
	        parse_str($HTTP_RAW_POST_DATA, $_POST);
	    }

	    if (isset($_POST["_method"]) && $_POST["_method"] != null) {
	        return $_POST["_method"];
	    }

	    return $_SERVER["REQUEST_METHOD"];
	}

    /**
    * pluginHook('pluginname', 'functionname', array('key' => 'value')) {}
    * 
    * @var - $plugin 'string' name of subdirectory, name of file, name of class
    * @var - $function - name of the Function within the 
    **/ 
    function pluginHook($plugin, $function, $params = null) {

        try {
            // Load the Plugin file
            loadFile(self::get('pluginpath').$plugin."/".$plugin.".class.php");

            // Create new instance of Plugin and register it, just in case ......
            // F::register($plugin, $plugin, $params, $function);
            self::register($plugin, $plugin);
            $Cliqp = new $plugin();

            // Execute function with parameters supplied
            return $Cliqp->$function($params);
            
        } catch(Exception $e) {
          echo $e->getMessage();
        }
    }
     
} // Ends Class
