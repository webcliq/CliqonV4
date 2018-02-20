<?php

spl_autoload_register(function ($class) 
{
    //class directories
    $dirs = array(
        'models/',
        'controllers/',
        'plugins/',
        'framework/core/',
        'framework/app/',
        'framework/filter/',
        'framework/template/',
        'framework/fluentpdo/',
        'framework/oauth/',
        'framework/curl/',
        'framework/utils/'
    );
   
    //for each directory
    foreach($dirs as $dir)
    {
        //see if the file exsists
        if(existsFile($dir.$class.'.php'))
        {
            loadFile($dir.$class.'.php');
            return;
        }           
    }
});

function loadFile($file)
{
	try {
		global $basedir;
    $fp = $basedir.$file;
		if(!is_readable($fp)) {
			 throw new Exception("File does not exist at: ".$fp);
		}
		require_once $fp;
		return true;
  } catch(Exception $e) {
      return false;
      Debugger::log($e->getMessage());
  }
}

function existsFile($file)
{
	try {
    global $basedir;
		$fp = $basedir.$file;
		if(!is_readable($fp)) {
			 throw new Exception("File does not exist at: ".$fp);
		}
		return true;
    } catch(Exception $e) {
        return false;
        Debugger::log($e->getMessage());
    }
}

/** 
* Recursively computes the intersection of arrays using keys for comparison.
* 
* @param   array $array1 The array with master keys to check.
* @param   array $array2 An array to compare keys against.
* @return  array associative array containing all the entries of array1 which have keys that are present in array2.
**/
function array_intersect_key_recursive(array $array1, array $array2) {
    $array1 = array_intersect_key($array1, $array2);
    foreach ($array1 as $key => &$value) {
        if (is_array($value) && is_array($array2[$key])) {
            $value = array_intersect_key_recursive($value, $array2[$key]);
        }
    }
    return $array1;
}

function serialize_array_values($arr){
    foreach($arr as $key=>$val){
        sort($val);
        $arr[$key]=serialize($val);
    }
    return $arr;
}

function array_intersect_other(array $array1, array $array2) {
  foreach ($array1 as $key=>$value){
      if (!in_array($value,$array2)){
          unset($array1[$key]);
      }
  }
  return $array1;
}

function array_intersect_value($array1, $array2) {
    $result = array();
    foreach ($array1 as $val) {
      if (($key = array_search($val, $array2, TRUE))!==false) {
         $result[] = $val;
         unset($array2[$key]);
      }
    }
    return $result;
} 

function strtobool($val) {
	if($val === 'false') {
		return false;
	} else if($val === 'true') {
		return true;
	} else {
		return $val;
	}
}

function array_map_deep($callback, $array) {
    
    $new = [];
    if(is_array($array)) {
      foreach ($array as $key => $val) {
          if (is_array($val)) {
              $new[$key] = array_map_deep($callback, $val);
          } else {
              $new[$key] = call_user_func($callback, $val);
          }
      }
    }
  
    return $new;
}

function startsWith($haystack, $needle)
{
     $length = strlen($needle);
     return (substr($haystack, 0, $length) === $needle);
}

function endsWith($haystack, $needle)
{
    $length = strlen($needle);
    if ($length == 0) {
        return true;
    }

    return (substr($haystack, -$length) === $needle);
}

function n($n)
{
    $n = str_replace(',','.',$n);
    $n = floatval($n);
    return $n; 
}

function getSum(float $a, float $b)
{
    return $a + $b;
}


function object_encode($a) {
    return json_encode(Q::object_to_array($a), JSON_FORCE_OBJECT);
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


/*
 * Inserts a new key/value before the key in the array.
 *
 * @param $key
 *   The key to insert before.
 * @param $array
 *   An array to insert in to.
 * @param $new_key
 *   The key to insert.
 * @param $new_value
 *   An value to insert.
 *
 * @return
 *   The new array if the key exists, FALSE otherwise.
 *
 * @see array_insert_after()
 */
function array_insert_before($key, array &$array, $new_key, $new_value) {
  if (array_key_exists($key, $array)) {
    $new = array();
    foreach ($array as $k => $value) {
      if ($k === $key) {
        $new[$new_key] = $new_value;
      }
      $new[$k] = $value;
    }
    return $new;
  }
  return FALSE;
}

/*
 * Inserts a new key/value after the key in the array.
 *
 * @param $key
 *   The key to insert after.
 * @param $array
 *   An array to insert in to.
 * @param $new_key
 *   The key to insert.
 * @param $new_value
 *   An value to insert.
 *
 * @return
 *   The new array if the key exists, FALSE otherwise.
 *
 * @see array_insert_before()
 */
function array_insert_after($key, array &$array, $new_key, $new_value) {
  if (array_key_exists($key, $array)) {
    $new = array();
    foreach ($array as $k => $value) {
      $new[$k] = $value;
      if ($k === $key) {
        $new[$new_key] = $new_value;
      }
    }
    return $new;
  }
  return FALSE;
}

function is_json($string)
{
    $result = json_decode($string, true);
    switch (json_last_error()) {
        case JSON_ERROR_NONE:
           return $result; 
        break;
        case JSON_ERROR_DEPTH:
        case JSON_ERROR_STATE_MISMATCH:
        case JSON_ERROR_CTRL_CHAR:
        case JSON_ERROR_SYNTAX:
        case JSON_ERROR_UTF8:
        default:
            return $string;
        break;
    }
}


// Does not support flag GLOB_BRACE        
function glob_recursive($pattern, $flags = 0)
{
  $files = glob($pattern, $flags);
  foreach (glob(dirname($pattern).'/*', GLOB_ONLYDIR|GLOB_NOSORT) as $dir) {
    $files = array_merge($files, glob_recursive($dir.'/'.basename($pattern), $flags));
  }
  return $files;
}


function normalize_files_array($files = []) 
{
    $normalized_array = [];
    foreach($files as $index => $file) {

        if (!is_array($file['name'])) {
            $normalized_array[$index][] = $file;
            continue;
        }

        foreach($file['name'] as $idx => $name) {
            $normalized_array[$index][$idx] = [
                'name' => $name,
                'type' => $file['type'][$idx],
                'tmp_name' => $file['tmp_name'][$idx],
                'error' => $file['error'][$idx],
                'size' => $file['size'][$idx]
            ];
        }

    }
    return $normalized_array;
}

function is_true($key, $array)
{
  if(array_key_exists($key, $array) and $array[$key] == 'true') {
    return true;
  } else if (array_key_exists($key, $array) and $array[$key] == true) {
    return true;
  } else {
    return false;
  }
}

function is_set($key, $array)
{
  if(array_key_exists($key, $array) and $array[$key] != "") {
    return true;
  } else {
    return false;
  }
}

/**
 * All of the Defines for the classes below.
 * @author S.C. Chen <me578022@gmail.com>
 */
define('HDOM_TYPE_ELEMENT', 1);
define('HDOM_TYPE_COMMENT', 2);
define('HDOM_TYPE_TEXT',  3);
define('HDOM_TYPE_ENDTAG',  4);
define('HDOM_TYPE_ROOT',  5);
define('HDOM_TYPE_UNKNOWN', 6);
define('HDOM_QUOTE_DOUBLE', 0);
define('HDOM_QUOTE_SINGLE', 1);
define('HDOM_QUOTE_NO',  3);
define('HDOM_INFO_BEGIN',   0);
define('HDOM_INFO_END',  1);
define('HDOM_INFO_QUOTE',   2);
define('HDOM_INFO_SPACE',   3);
define('HDOM_INFO_TEXT',  4);
define('HDOM_INFO_INNER',   5);
define('HDOM_INFO_OUTER',   6);
define('HDOM_INFO_ENDSPACE',7);
define('DEFAULT_TARGET_CHARSET', 'UTF-8');
define('DEFAULT_BR_TEXT', "\r\n");
define('DEFAULT_SPAN_TEXT', " ");
define('MAX_FILE_SIZE', 600000);
// helper functions
// -----------------------------------------------------------------------------
// get html dom from file
// $maxlen is defined in the code as PHP_STREAM_COPY_ALL which is defined as -1.
function file_get_html(
    $url, 
    $use_include_path = false, 
    $context = null, 
    $offset = -1, 
    $maxLen = -1, 
    $lowercase = true, 
    $forceTagsClosed = true, 
    $target_charset = DEFAULT_TARGET_CHARSET, 
    $stripRN = true, 
    $defaultBRText = DEFAULT_BR_TEXT, 
    $defaultSpanText=DEFAULT_SPAN_TEXT
)
{
    // We DO force the tags to be terminated.
    $dom = new Htmldom(null, $lowercase, $forceTagsClosed, $target_charset, $stripRN, $defaultBRText, $defaultSpanText);
    // For sourceforge users: uncomment the next line and comment the retreive_url_contents line 2 lines down if it is not already done.
    $contents = file_get_contents($url, $use_include_path, $context, $offset);
    // Paperg - use our own mechanism for getting the contents as we want to control the timeout.
    //$contents = retrieve_url_contents($url);
    if (empty($contents) || strlen($contents) > MAX_FILE_SIZE)
    {
      return false;
    }
    // The second parameter can force the selectors to all be lowercase.
    $dom->load($contents, $lowercase, $stripRN);
    return $dom;
}

// get html dom from string
function str_get_html(
    $str, 
    $lowercase=true, 
    $forceTagsClosed=true, 
    $target_charset = DEFAULT_TARGET_CHARSET, 
    $stripRN=true, 
    $defaultBRText=DEFAULT_BR_TEXT, 
    $defaultSpanText=DEFAULT_SPAN_TEXT
)
{
  $dom = new Htmldom(null, $lowercase, $forceTagsClosed, $target_charset, $stripRN, $defaultBRText, $defaultSpanText);
  if (empty($str) || strlen($str) > MAX_FILE_SIZE)
  {
    $dom->clear();
    return false;
  }
  $dom->load($str, $lowercase, $stripRN);
  return $dom;
}

// dump html dom tree
function dump_html_tree($node, $show_attr=true, $deep=0)
{
  $node->dump($node);
}