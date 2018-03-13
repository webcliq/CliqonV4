<?php
/**
 * (C) 2015 Chris Sprucefield
 * Licensed under Createive Commons - "CC BY-SA 4.0"
 * License deed: http://creativecommons.org/licenses/by-sa/4.0/
 * Legal code: http://creativecommons.org/licenses/by-sa/4.0/legalcode
 *
 * Advanced PHP .INI file reader.
 *
 * Sections such as [ abc:def ] will be put into $var['abc']['def'] = values
 * Names such as a.b.c and a:b:c will be expanded into sublevels.
 * A defined variable, can be referenced in dot notation, and used for substitution, such as:
 * root = "/mnt/some/path"
 * logs.access = "{root}/access_log"
 * Following this, logs.access will be "/mnt/some/path/access_log",
 * and ca be further referred to as {logs.access} for substitution.
 * Multiple elements can be referred, such as: myvar = "The root is {root}, and full path is {logs.access}"
 * Please note - If you do recursive variable substitutions, you are on your own buddy! =)
 * A part of a name can be literal, (uninterpreted) by surrounding it in '', such as
 * a.b.c.'abc.def' = "value" --> ['a']['b']['c']['abc.def'] = "value".
 *
 * Acceptable comment characters are: ;, 
 * Extended with:
 * #include <filename> Includes the file <filename>
 * #define <name> <value> Defines the constant <name> to be <value>, where value is the rest of the line.
 * #iniset <name> <value> Sets the ini parameter <name> to be <value>, where value is the rest of the line.
 *
 * */

use Stringy\Stringy as X;

class Config
{
    /** @var $conf mixed The global cofig, in case you want to keep it as a class call. */
    public static $conf = [];
	
    /** @var $ini array The ini array. */
    private static $ini = "" ;
    /** @var $arr array working variable */
    private static $arr = array() ;

    /** Intent: Return a leaf node value from a N-level config array by dot notation.
     * @method string nodeReplace(string $node, mixed[] $real)
     * @param string $node The leaf node string to be checked for var substitution.
     * @param mixed &$real The array pointer to the full array for reference.
     * @return mixed The replaced value.
     * */
    public static function cfgVal($node, &$real = false)
    {
        $real = $real ? $real : self::$conf ;
        if (strpos($node, ".") > 0)
        {
            $nodes = explode(".", $node);
            $cnode = $real;
            for ($i = 0 ; $i < count($nodes) ; $i++) $cnode = $cnode[$nodes[$i]];
        } else
        {
            $cnode = $real[$node];
        }

        return ($cnode);
    }

    /** Intent: The main .ini file parser.
     * @method mixed[] parseFile(string $file, array $xconf, int $nosub = 0)
     * @param string $file The filename to load.
     * @param array $xconf The array to add.
     * @param int $nosub set to 1 if you want to merge the existing to current config array.
     * @return mixed The cofiguration array.
     * */
    public static function cfgMergeFile($file, $xconf = [], $nosub = 0)
    {
        $conf = [] ;
        // Go fetch the file and parse it.
        if (file_exists($file)) $conf = self::cfgMergeString(self::preProcess($file), $xconf, $nosub);

        if ($xconf === []) self::$conf = $conf = array_merge($xconf, $conf) ;

        return($conf) ;
    }

    public static function cfgMergeString($string, $xconf = [], $nosub = 0)
    {
        $conf = !empty($xconf) ? $conf = array_merge($xconf, self::parse($string)) : $conf = array_merge(self::$conf,self::parse($string)) ;

        // Do the variable substitution.
        if($nosub == 0) {self::subst($conf, $conf);};

        return($conf) ;
    }

    /** Intent: Single ini file reader
     * @method mixed[] parseFile(string $file)
     * @param string $file The filename to load.
     * @return mixed The cofiguration array.
     * */
    public static function cfgReadFile($file)
    {
        $conf = [];
        // Go fetch the file and parse it.
        if(file_exists($file)) {
            $conf = self::parse(self::preProcess($file));
            self::subst($conf, $conf);
        } else {
            $conf['error'] = "No file or no entries found";
        };
        return($conf);
    }

    /** Intent: Parse a .ini file formatted string.
     * @method mixed[] cfgReadString(string $string, array $xconf, int $nosub = 0)
     * @param string $string The ini file formatted string
     * @param array $xconf The array to merge
     * @param int $nosub set to 1 if you want to merge the existing to current config array.
     * @return mixed The cofiguration array.
     * */
    public static function cfgReadString($str) 
    {
        try {
            $conf = self::parse($str);
            self::subst($conf, $conf);
            return($conf);
        } catch (Exception $e) {
            echo $e->getMessage();
        }    
    }

    // Write an array to an ini file.
    public static function cfgWriteString($arr)
    {
        self::array_to_ini($arr) ;
        return self::$ini;
    }

    // Write an array to an ini file.
    public static function cfgWrite($filename, $arr)
    {
        self::array_to_ini($arr) ;
        file_put_contents($filename,self::$ini) ;
    } 

    // Convert an array to an advanced ini file.
    private static function array_to_ini($a,$level = 0)
    {
        if ($level == 0) self::$ini = "" ;
        self::$arr = array_slice(self::$arr, 0, $level) ;

        foreach($a as $key => $val) {
            self::$arr[$level] = $key ;

            if (is_array($val)) {
                self::array_to_ini($val, $level+1) ;
            } else {
                if(is_numeric($val) or $key == 'c_options')  {$str = "{$val}";} else {$str = "'{$val}'";}; 
                if($key == 'c_options') {
                    self::$ini .= $level == 0 ? "{$key} = ".'"'.$str.'"'." \n" : join(".", self::$arr)." = ".'"'.$str.'"'." \n";
                } else if (is_numeric($key)) {
                    self::$ini .= $level == 0 ? "{$key}[] = ".$str." \n" : join(".",array_slice(self::$arr,0,-1))."[] = ".$str." \n";
                } else {
                    self::$ini .= $level == 0 ? "{$key} = ".$str." \n" : join(".", self::$arr)." = ".$str." \n";
                }
            }
        }
        return(self::$ini);
    }

    /** Preprocess the file, looking for # directives.
     * @method string preProcess(string $file)
     * @param string $file The filename to process.
     * @return string The resulting file, less # directives.
     */
     private static function preProcess($file)
     {
        try {
            
            $out = "" ;

            // Break file contents into an array of lines
            $lines = explode("\n", file_get_contents($file)) ;

            // Perform any includes.
            // $lines = self::processIncludes($lines) ;

            foreach($lines as $l => $line) {
                
                $r = explode(" ", preg_replace('/\s+/', ' ', $line), 3);
                if(count($r) == 3) {
                    list($def, $arg1, $arg2) = $r;
                    switch($def) {
                        // Do commands, but exclude from the resulting ini file.
                        case "#define": 
                            define($arg1, $arg2) ; 
                        break ;
                        case "#iniset": 
                            ini_set($arg1 ,$arg2) ; 
                        break ;
                        // Add all remaining to the resulting ini file
                        default: 
                            $out .= "{$line}\n" ; 
                        break ;
                    }                    
                } else {
                    $out .= "{$line}\n" ;
                }    

            }

            return($out) ;

        } catch (Exception $e) {
            echo $e->getMessage();
        }
     }

    /** Process the #include directive.
     * @method array preProcess(array $lines)
     * @param array $lines The lines in the file.
     * @return array The resulting file as an array, less #include directives.
     */
     private static function processIncludes($lines)
     {
        
        try {

            // $rpath = $_SERVER['DOCUMENT_ROOT']."/"; $file = $rpath.$file;
            
            do {
                
                $found = 0 ;

                for ($i = 0 ; $i < count($lines) ; $i++) {

                    $r = list($inc, $file) = explode(" ", preg_replace('/\s+/', ' ', trim($lines[$i]))) ;
                    
                    if(count($r) == 2) {
                        
                        if ($inc == "#include") {
                            $found = 1 ;
                            if (!file_exists($file)) die("Error: File {$file} does not exist in include statement.\n") ;

                            $ins = explode("\n", file_get_contents($file));
                            if ($i == 0)
                            {
                                $pre = [] ;
                                $pos = array_slice($lines, 1) ;
                            } else
                            {
                                $pre = array_slice($lines, 0,$i) ;
                                $pos = array_slice($lines, $i+1) ;
                            }

                            $lines = array_merge($pre, $ins, $pos) ;
                        }                       
                    }
                }

            } while($found == 1) ;

            return($lines) ;
        } catch (Exception $e) {
            echo $e->getMessage();
        }
     }

    /** Intent: The actual parsing of the ini string.
     * @method mixed parse(string $string, array $conf = [])
     * @param string $string The ini file formatted string
     * @param array $conf Existing conf to merge
     * @return mixed The cofiguration array.
     **/
    private static function parse($string, $conf = [])
    {
        $xline = []; global $clq;

        // Cliqon string replace
        $qrepl = ['{common.lcd}', '{lcd}'];  
        $lcd = $clq->get('lcd');     
        $qwith = [$lcd];        
        $string = str_replace($qrepl, $qwith, $string);

        if ($conf === array() && $string != "") {
            $lines = explode("\n", $string) ;
            foreach($lines as $line) {
                $xline[] = trim($line," \t\n\r\0\x0B");
            };
            $mode = defined(INI_SCANNER_TYPED) ? INI_SCANNER_TYPED : INI_SCANNER_NORMAL ;
            $conf = @parse_ini_string( join("\n", $xline), true, $mode);
        }


        // Walk the list of first-level vars.
        if(count($conf) > 1) {
            foreach($conf as $key => $val) {
                // Match : and . as level separators.
                if (preg_match('/[:.]+/',$key)) {
                    // Allow for quoted strings in the dotcolon notation.
                    preg_match_all("/\((?:[^()]|(?R))+\)|'[^']*'|[^().:]+/",$key,$out) ;
                    $list = $out[0] ;

                    $ptr = &$conf ;
                    foreach($list as $name) {
                        $name = trim($name) ;
                        // Fix any quotation marks on the name.
                        if ($name[0] == '"' || $name[0] == "'") $name = substr($name,1,strlen($name)-2) ;

                        // Attempting to put in in same var as two data types, is not gonna happen.
                        // Cause the already set string to become a recursion, but don't break execution.
                        // such as a.b = 1
                        // a.b[] = ... will point to itself.
                        if (isset($ptr[$name]) && !is_array($ptr[$name])) { $ptr[$name] = array(&$ptr[$name]) ; }
                        // If not set, create it...
                        if (!isset($ptr[$name])) { $ptr[$name] = array() ; }

                        $ptr = &$ptr[$name] ;
                    }
                    // Leaf node
                    // If value is an array, insert as a repeated array (a.b[])
                    if (isset($ptr) && !is_array($ptr)){
                        $ptr = array($ptr) ;
                        $ptr[] = $val ;
                    } else {
                        // If the value is an array, check up the names on that one for having dots.
                        $ptr = (is_array($val)) ? array_merge($ptr,(array) self::parse("", $val)) : $val ;
                    }

                    unset($conf[$key]) ;
                } else {
                    // Otherwise, test the subarray for the same things but for second-level.
                    if (is_array($conf[$key])) $conf[$key] = (array) self::parse("", $conf[$key]) ;
                }
            }
        }
    
        return($conf) ;
    }

    /** Intent: Recursively walk all the nodes in the array, and do variable substitution on the leaf nodes.
     * @method void subst(mixed[] &$a, mixed[] &$real)
     * @param mixed[] &$a The array pointer to the live array being referenced.
     * @param mixed[] &$real The array pointer to the full array for reference.
     * return void
     * */
    private static function subst(&$a, &$real)
    {
        if(count($a) > 1) {
            foreach ($a as $key => $val) {
                if (is_array($a[$key])) {
                    self::subst($a[$key], $real);
                } else {
                    // Need to do this twice in case we do a node into node assignment.
                    $a[$key] = self::nodeReplace($a[$key],$real) ;
                    $a[$key] = self::nodeReplace($a[$key],$real) ;
                }
            }
        }
    }

    /** Intent: replace all the variable references in the string, and return the subsituted element.
     * @method string nodeReplace(string $node, mixed[] $real)
     * @param string $node The leaf node string to be checked for var substitution.
     * @param mixed[] &$real The array pointer to the full array for reference.
     * @return string The replaced value.
     * */
    private static function nodeReplace($node, $real)
    {
        // Find all vars and substitute in the node.
        preg_match_all("/{([^}]*)}/", $node, $out);

        foreach ($out[0] as $idx => $var) {

            if (strpos($out[1][$idx], ".") > 0) {
                $nodes = explode(".", $out[1][$idx]);
                $cnode = $real;
                for ($i = 0 ; $i < count($nodes) ; $i++) {
                    if($nodes[$i]) {
                        // Not happy that I have had to introduce a Kludge here. This needs proper debugging
                       $cnode = @$cnode[$nodes[$i]]; 
                    }
                };
            } else {
                if($idx != "lcd" || $idx != "url" || $idx != "") {
                    $cnode = &$real[$out[1][$idx]];
                }
            };
            if(isset($cnode)) {
                $node = str_replace($var, $cnode, $node);
            }
        }

        return ($node);
    }

    /**
     * Attempt to parse new type of config file
     * file contains lines:
     * ; Comment
     * one.two.three = 'value'
     *
     * @param - string - filename and path from base directory
     * @return configuration array
     **/
    static function cfgParseFile($file) 
    {
        $conf = [];
        // Go fetch the file and parse it.
        $file = F::get('basedir').$file;
        if(!file_exists($file)) {
            $conf['error'] = "No file or no entries found";
        } else {

            $lines = explode("\n", file_get_contents($file)) ;
            foreach($lines as $l => $line) {
                
                // Remove empty space at start of line
                
                $line = ltrim($line);
                // If line then starts with a semi-colon, it is a comment
                if(!X($line)->startsWith(';')) {

                    // split line into two parts by = sign
                    $parts = X($line)->split('=');
                    $parts[0] = explode(".", trim($parts[0]));
                    $parts[1] = rtrim(ltrim(trim($parts[1]), "'"),"'");
                    $conf[] = [$parts[0] => $parts[1]];
                }
            }
        };
        return($conf) ;
    }

    /** Get array from file
     * @param - array - variables
     * @return - array - Contents of request in array format
     **/
    static function cfgRead($vars)
    {
        $array = self::getFromDb($vars);
        if(!count($array) < 1) {
            $result = $array;
        } else {
            $result = self::getFromFile($vars);
        }; 
        return $result;
    }

    /** Get array from database
     * @param - array - variables
     * @return - array - Contents of request in array format
     **/
    static function getFromDb($vars)
    {   
        
        global $clq;   
        $sql = "SELECT c_options FROM dbcollection WHERE c_type = ? AND c_reference = ?";
        $value = R::getCell($sql, [$vars['type'], $vars['reference']]);   
        $farray = self::cfgReadString($value);
        if(count($farray) > 0) {
            if($vars['key'] != '') {
                return $farray[$vars['key']];
            } else {
               return $farray; 
            }
        } else {
            return [];
        }
    }

    /** Get array from a TOML file
     * @param - array - variables, consisting of subdirectory and filename
     * @return - array - Contents of request in array format
     **/
    static function getFromFile($vars)
    {
        $farray = self::cfgReadFile($vars['subdir'].$vars['filename'].'.cfg');
        if(count($farray) > 1) {
            if($vars['key'] != '') {
                return $farray[$vars['key']];
            } else {
               return $farray; 
            }
        } else {
            return [];
        }
    }

} 
# alias +h+ class
if(!class_exists("C")){ class_alias('Config', 'C'); };
