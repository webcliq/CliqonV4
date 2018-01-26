<?php
/**
 * Files Class
 *
 * handles all functions and activities related reading and writing files
 *
 * @category   Web application framework
 * @package    Cliqon
 * @author     Original Author <conkascom@gmail.com>
 * @copyright  2016 Conkas cb
 * @license    http://www.php.net/license/3_01.txt  PHP License 3.01
 * @version    Release: 4.1.0
 * @link       http://cliqon.com
 */
class Files
{
	 public $thisclass = "Files";
	 public $root;

	 public function __construct() 
	 {
      global $cfg;
      global $clq;
	 }

	 public static function setRoot()
	 {
		  return $_SERVER['DOCUMENT_ROOT']."/";
	 }

    /** File system functions
     *
     * copyFile()
     * openFile()
     * createFile()
     * readFile()
     * existsFile()
     * writeFile()
     * appendFile()
     * closeFile()
     * deleteFile()
     * deleteDirectory()
     * renameFile()
     * listFiles()
     *
     *************************************************************************************************************/

        /** Get file size 
         * 
         * @param - object - file pointer
         * @param - boolean - is it readable
         * @param - numeric - number of decimals or precision
         * @return - numeric - size of file
         * @todo - 
         **/
         function fileSize($fp, $readable = false, $decimals = 2)
         {
            $sp = self::setRoot();
            $mf = $sp.$fp;
            $size = filesize($mf);
          
            if($readable == true) {
              $sz = 'BKMGTP';
              $factor = floor((strlen($size) - 1) / 3);
              $size = sprintf("%.{$decimals}f", $size / pow(1024, $factor)) . @$sz[$factor];
            }

          return $size;
         }
 
        /** Copy file 
         * 
         * @param - string - URL of file
         * @param - string - file name
         * @return - boolean - true / false
         * @todo - 
         **/       
         public static function copyFile($url, $filename)
         {
          $file = fopen($url,"rb");
          if(!$file) {
            return false;
          } else {
            $fc=fopen($filename,"wb");
            while(!feof($file)) {
              $line=fread($file,1028);
              fwrite($fc,$line);
            }
            fclose($fc);
            return true;
          }
         }

        /** Open file 
         * 
         * @param - object - file pointer 
         * @param - string - with attribute or read or write
         * @return - object - handle
         * @todo - 
         **/
         public static function openFile($fp, $op = "r") 
         {
            try {
                $sp = self::setRoot();
                $mf = $sp.$fp;
                $handle = fopen($mf, $op); //implicitly creates file
                return $handle;
            } catch(Exception $e) {
                echo "Open File : ".$e->getMessage();
            }
         }

        /** Create a file
         * 
         * @param - object - file pointer 
         * @return - object - handle
         * @todo - 
         **/
         public static function createFile($fp) 
         {
            try {
                $handle = self::openFile($fp, 'w');
                return $handle;
            } catch(Exception $e) {
                echo "Create File : ".$e->getMessage();
            }
         }

        /** Read file
         * 
         * @param - object - file pointer 
         * @return - string - content of file
         * @todo - 
         **/
         public static function readFile($fp) 
         {
            try {
                $sp = self::setRoot();
                $mf = $sp.$fp;
                $handle = self::openFile($fp, 'r');
                $data = fread($handle, filesize($mf));
                fclose($handle);
                return $data;
            } catch(Exception $e) {
                echo "Read File : ".$e->getMessage();
            }
         }

        /** Does file exist ??  
         * 
         * @param - object - file pointer 
         * @return - boolean - true / false or error message
         * @todo - 
         **/
         public static function existsFile($fp)
         {
            try {
                $sp = self::setRoot();
                $mf = $sp.$fp;
                if(is_readable($mf) === true ) {
                    return true;
                } else {
                    return false;
                }
            } catch(Exception $e) {
                echo "Read File : ".$e->getMessage();
            }    
         }

        /** Write data to file - replace existing 
         * 
         * @param - object - file pointer 
         * @param - string - data to be written
         * @return - string - Flag or Error
         * @todo - 
         **/
         public static function writeFile($fp, $data) 
         {
            try {
                $handle = self::openFile($fp, 'w');
                fwrite($handle, $data);
                self::closeFile($handle);
                $newdata = self::readFile($fp);
                if($newdata == $data){
                    return "OK";
                } else {
                    return "NotOk";
                };
            } catch(Exception $e) {
                echo "Write File : ".$e->getMessage();
            }
         }

        /** Write to a file - appean to end of existing
         * 
         * @param - object - file pointer 
         * @param - string - data to be written
         * @return - object - handle
         * @todo - 
         **/
         public static function appendFile($fp, $data) 
         {
          try {
            $sp = self::setRoot();
            $mf = $sp.$fp;
            $olddata = self::readFile($fp);
            $newdata = $olddata.$data;
            return self::writeFile($fp, $newdata);
          } catch(Exception $e) {
              echo "Append to File : ".$e->getMessage();
          }
         }

        /** Close file
         * 
         * @param - object - handle
         * @return - boolean - true / false
         * @todo - 
         **/
         public static function closeFile($handle) 
         {
            return fclose($handle);
         }

        /** Delete file
         * 
         * @param - object - file pointer 
         * @return - string - Flag or Error
         * @todo - 
         **/
         public static function deleteFile($fp) 
         {
            try {
                $sp = self::setRoot();
                $mf = $sp.$fp;
                unlink($mf);
                if(!file_exists($mf)){
                    return "OK";
                } else {
                    return "NotOk";
                };
            } catch(Exception $e) {
                echo "Delete File : ".$e->getMessage();
            }
         }

        /** Rename existing file
         * 
         * @param - string - old name
         * @param - string - new name
         * @return - string - Ok or NotOk flage or error message
         * @todo - 
         **/
         public static function renameFile($fp1, $fp2) 
         {
            try {
                $sp = self::setRoot();
                $of = $sp.$fp1;
                $nf = $sp.$fp2;    
                if(!rename($of, $nf)) {
                    return "NotOk";
                } else {
                    return "Ok";
                };   
            } catch(Exception $e) {
                echo "Delete File : ".$e->getMessage();
            }
         }

        /** Move file from directory to another
         *
         * @param - string - filename
         * @param - string - current directory
         * @param - string - new directory
         * @return - boolean true or error
         **/
         public static function moveFile($fn, $currdir, $newdir)
         {
            try {
                $sp = self::setRoot();
                $of = str_replace('//', '/', $sp.$currdir.'/'.$fn);
                $nf = str_replace('//', '/', $sp.$newdir.'/'.$fn);
                if(!rename($of, $nf)) {
                    return "NotOk";
                } else {
                    return "Ok";
                };   
            } catch(Exception $e) {
                echo "Delete File : ".$e->getMessage();
            }    
         }

        /** List files in a directory  
         * 
         * @param - string - filepath and name of directory
         * @param - string - restrict to this extension, defaults to all
         * @return - array of files that match params
         **/
         public static function listFiles($fp, $ext = "*")
         {
            try {
                global $clq;
                $files = [] ;
                $files[0] = Q::cStr('375:Please select file');
                $search = $clq->get('basedir').$fp.$ext;
                foreach(glob($search) as $f => $file) {
                $file = str_replace($clq->get('basedir').$fp, "", $file);
                $files[] = $file;
            };
            if(count($files > 1)) {
                unset($files[0]);
            };
            return $files;
            } catch (Exception $e) {
                return [$e->getMessage()];
            }
         }

        /** Create a directory
         * 
         * @param - string - new directory name
         * @return - string - Ok or NotOk flage or error message
         * @todo - 
         **/
         public static function makeDir($dir)
         {
            try {
                // Make sure the receiving directory exists
                $pp = pathinfo($mf);
                if(!mkdir($pp['dirname'], 0777, true)) {
                    throw new Exception("Receiving subdirectory:".$sp.$dir." does not exist and could not be created");
                };
                return ['flag' => 'NotOk', 'html' => ''];
            } catch(Exception $e) {
                return ['flag' => 'NotOk', 'html' => $e->getMessage()];
            }
         }

        /** Delete a directory and contents
         * 
         * @param - string - new directory name
         * @return - string - Ok or NotOk flage or error message
         * @todo - 
         **/
         public static function deleteDirectory($d) 
         { 
            $sp = self::setRoot();
            $dir = $sp.$d;
            if (!file_exists($dir)) { return true; }
            if (!is_dir($dir) || is_link($dir)) {
                return unlink($dir);
            }
            foreach (scandir($dir) as $item) { 
                if ($item == '.' || $item == '..') { continue; }
                if (!deleteDirectory($dir . "/" . $item, false)) { 
                    chmod($dir . "/" . $item, 0777); 
                    if (!deleteDirectory($dir . "/" . $item, false)) return false; 
                }; 
            } 
            return rmdir($dir); 
         }

        /** Display existing files in a tree 
         * 
         * @param - array - arguments
         * @return - string - Ok or NotOk flage or error message
         * @todo - 
         **/
         function displayFiles($vars)
         {
            try {

                $html = '<style>'.self::readFile('admin/css/listfiles.css').'</style>';
                $html .= H::div(['class' => 'container'],
                    H::div(['class' => 'row mt10'],
                        H::div(['class' => 'col-5', 'id' => 'filetree']),
                        H::div(['class' => 'col-7', 'id' => 'fileeditor'],
                             H::div(['class' => 'mb5', 'id' => 'toolbar'],
                                H::button(['class' => 'btn btn-sm btn-primary mr5', 'type' => 'button', 'id' => 'filetojson'], Q::cStr('9999:JSON')),
                                H::button(['class' => 'btn btn-sm btn-primary mr5', 'type' => 'button', 'id' => 'filetotoml'], Q::cStr('9999:TOML')),
                                H::button(['class' => 'btn btn-sm btn-danger', 'data-clipboard-action' => 'copy', 'data-clipboard-target' => '#filecontent', 'type' => 'button', 'id' => 'copyfilecontent'], Q::cStr('376:Copy'))
                            ),
                            H::div(['class' => '', 'id' => 'content'],
                                H::textarea(['class' => 'form-control h540', 'id' => 'filecontent'], $_SERVER['DOCUMENT_ROOT'].'/')
                            )   
                        )
                    )
                );

                /* to use:
                pattern = glob pattern to match
                flags = glob flags
                path = path to search
                depth = how deep to travel, -1 for unlimited, 0 for only current directory
                */
                $tree = self::folderTree('/', '*.cfg');
                
                return ['flag' => 'Ok', 'html' => $html, 'data' => $tree];
            } catch(Exception $e) {
                return ['flag' => 'NotOk', 'html' => $e->getMessage()];
            }      
         }

        /** Folder tree helper function
         * 
         * @param - string - path
         * @param - string - pattern
         * @return - string - Ok or NotOk flage or error message
         * @todo - 
         **/
         protected static function folderTree($path = '', $pattern = '*') {
            
            $tree = [];
         
            $files = scandir($_SERVER['DOCUMENT_ROOT'].$path);
            $difflist = "cache,tmp,archive,js,includes,img,partials,css,public,_errorpages,apps,assets,docs,framework,install,log,controllers";
            $diff = explode(',', $difflist);

            foreach($files as $f => $file) {
                if (!in_array($file, array(".",".."))) {
                    if (is_dir($file)) {
                        if(!in_array($file, $diff)) {
                            $tree[] = ['name' => $file, 'children' => self::folderTree($path.DIRECTORY_SEPARATOR.$file)];
                        } 
                    } else {
                        if(stristr($file, 'cfg')) {
                            $path = str_replace('//','/',$path);
                            $tree[] = ['id' => $path, 'name' => $file];
                        }
                    } 
                }
            };
            return $tree;
         }       

        /** Reads a TOML formatted file and returns it as both JSON and TOML
         * 
         * @param - array - args containing file name plus path
         * @return - string - Ok or NotOk flag plus data or error message
         * @todo - 
         **/
         function convertFile($vars)
         {
            try {

                $fp = self::exists($vars['rq']['filepath']);

                $array = C::cfgReadFile($_SERVER['DOCUMENT_ROOT'].$fp);
                $json = json_encode($array);
                
                $val = self::readFile($fp);
                // $val = preg_replace("/\t/", " ", $val); // tabs with spaces
                // $val = preg_replace("/\s+/", " ", $val); // Multiple spaces with single space
                $toml = preg_replace("/\r\n/", "\n", $val); // Carriage return and newline
                $data = [
                    'json' => $json,
                    'toml' => $toml
                ];
                return ['flag' => 'Ok', 'data' => $data];
            } catch(Exception $e) {
                return ['flag' => 'NotOk', 'html' => $e->getMessage()];
            } 
         }

    /** File Editor functions
     *
     * displayFileEditor()
     *
     ******************************************************************************************************************/

        /** Display the file editor
         * for a popup window
         * @param - array - arguments 
         * @return - array - containing a Ok or NotOk flag and HTML or error message
         * @todo - 
         **/
         function displayFileEditor($vars)
         {

            try {

                global $clq;
                $table = $vars['table'];
                $tabletype = $vars['tabletype'];
                $rq = $vars['rq'];
                $ref = $rq['ref'];
                $idiom = $clq->get('idiom'); 
                
                // Do we read in a file
                if($ref != "") {
                    $ro = ['readonly' => 'true'];
                    $fp = '/models/'.$table.'.'.$ref.'.cfg';
                    $val = self::readFile($fp);
                    // $val = preg_replace("/\t/", " ", $val); // tabs with spaces
                    // $val = preg_replace("/\s+/", " ", $val); // Multiple spaces with single space
                    $val = preg_replace("/\r\n/", "\n", $val); // Carriage return and newline (not respected by CodeEditor display) with just Newline
                } else {
                    $ro = [];
                    $val = "";
                    $fp = '/models/'.$table.'.{collection}.cfg';
                }             

                $frm = H::div(['class' => 'col mr10 pad'],
                    H::form(['class' => '', 'action' => '/api/'.$idiom.'//', 'method' => 'POST', 'name' => 'popupform', 'id' => 'popupform'],
   
                        H::div(['class' => 'form-group'],
                            H::label(['class' => '', 'for' => 'filename'], Q::cStr('186:File name')),
                            H::input(['class' => 'form-control', 'id' => 'filename', 'name' => 'filename', 'required' => 'required', 'value' => $fp, 'style' => 'width:80%;', $ro]),
                            H::button(['type' => 'submit','class' => 'btn btn-sm btn-primary', 'style' => 'width:16%; float:right; margin-top: -34px;'], Q::cStr('105:Submit'))
                        ),                          

                        H::style('.CodeMirror{border: 1px solid #ccc; height: 540px;}'),

                        H::div(['class' => 'form-group'],
                            H::label(['class' => '', 'for' => 'filecontent'], Q::cStr('7:File content')),
                            H::textarea(['class' => 'form-control h500', 'id' => 'filecontent', 'name' => 'filecontent'], $val)
                        )                        
                    )
                );
                return [
                    'flag' => "Ok",
                    'html' => $frm
                ];

            } catch (Exception $e) {
                return [
                    'flag' => "NotOk",
                    'html' => $e->getMessage()
                ];
            }               
         }


} // Class ends

# alias +Z = Files+ class
if(!class_exists("Y")){ class_alias('Files', 'Y'); };

