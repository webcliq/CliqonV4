<?php
/**
 * Menu Generation class - extends HTML
 * Fold = Ctrl K3
 * @category   Web application framework
 * @package    Cliq
 * @author     Original Author <support@webcliq.com>
 * @copyright  2017 Webcliq
 * @license    http://www.php.net/license/3_01.txt  PHP License 3.01
 * @version    Release: 4.2.0
 * @link       http://cliqon.com
 */
class Menu extends HTML
{

	const THISCLASS = "Menu extends HTML";
	const CLIQDOC = "c_document";
	public static $menuhtml = "";
	public static $menuscript = "";
	public static $menudata = [];
	public static $idioms;
	public $config;
	private static $table;
	private static $type;


	public function __construct() 
	{
		global $clq;
		global $cfg;
		self::$idioms = $cfg['site']['idioms'];
		$this->config = $clq->resolve('Config');
	}

    /** Dynamic Menus
     * 
     * pubMenu() - main menu publisher
     *
     * 
     * 
     ********************************************************************************************************/

    	/**
    	 * Publish a Dynamic Menu
	     * generates a dynamic menu for use by different framwework types
	     * default is Bootstrap 4 but others can and will be supported
	     *
	     * @param - array - args
	     * @return - string - HTML for a menu
	     **/
	    static function pubMenu(array $vars)
	    {
			$method = self::THISCLASS.'->'."pubMenu()";
			try {
				
				global $clq;
				if(!is_array($vars)) {
					throw new Exception("No arguments");
				};

				// Sets type of menu - security is assumed for all menus
				// current options are: bootstrap4, to be implemented are purecss
				array_key_exists('type', $vars) ? $type = $vars['type']: $type = "bootstrap4";
				// current options are: admin, website, mobile
				array_key_exists('view', $vars) ? $view = $vars['view']: $view = "admin";
				// current options are: navbrand, topleftmenu, toprightmenu, leftsidemenu, rightsidemenu, footer, footermenu, megamenu
				array_key_exists('subtype', $vars) ? $subtype = $vars['subtype']: $subtype = "topleftmenu";

				if($view == 'admin') {
					self::$table = "dbcollection";
					$adm = "adm";
				} else {
					self::$table = "dbitem";
					$adm = "";
				};
				self::$type = $adm.$subtype;

				switch($type) {

					case "purecss":
						$m = 'purecss_'.$subtype;
						$menu = self::$m($vars);
					break;

					case "bootstrap3":
						$m = 'bootstrap3_'.$subtype;
						$menu = self::$m($vars);
					break;

					case "bootstrap4":
					default:
						$m = 'bootstrap4_'.$subtype;
						$menu = self::$m($vars);
					break;
				}

				// Complete
				return $menu;              

			} catch (Exception $e) {
				return $method.', e: '.$e->getMessage().', v: '.print_r($vars);
			}
	    }	

    /** Bootstrap 4
     * 
     * _navbrand() - 
     * _topleftmenu() -
     * _toprightmenu() -
     * _leftsidemenu() -
     * _rightsidemenu() -
     * _footer() -
     * _footermenu() -
     * _megamenu() - 
     *
     * _singleitemmenu() -
     * _adminusermenu() -
     * _menuitem() -
     * 
     ********************************************************************************************************/

	    /** Bootstrap 4 Nav brand OK
	     * 
	     * @param - array - variables
	     * @return - string HTML snippet
	     **/
	    protected static function bootstrap4_navbrand(array $vars)
	    {
	    	$html = H::button(['class' =>'navbar-toggler mobile-sidebar-toggler d-lg-none', 'type' => 'button'], '&#9776;');
	    	$html .= H::a(['class' =>'navbar-brand', 'href' => '/']);
	    	return $html;
	    }

	    /** Bootstrap 4 top left menu in a navbar OK
	     * 
	     * @param - array - variables
	     * @return - string HTML snippet
	     **/
	    protected static function bootstrap4_topleftmenu(array $vars)
	    {	
	    	
	    	$html = H::ul(['class' =>'nav navbar-nav d-md-down-none'],
	    		H::li(['class' =>'nav-item'],
	    			H::a(['class' =>'nav-link navbar-toggler sidebar-toggler', 'href' => '#'], "&#9776;")
	    		),
	    		self::bootstrap4_singleitemmenu($vars)
	    	);
	    	return $html;
	    }

	    /** Bootstrap 4 top right menu in a nav bar OK
	     *
	     * @param - array - variables
	     * @return - string HTML snippet
	     **/
	    protected static function bootstrap4_toprightmenu(array $vars)
	    {
	    	if($vars['view'] == "admin") {
	    		$m = 'bootstrap4_adminusermenu';
	    	} else {   		
	    		$m = 'bootstrap4_singleitemmenu';
	    	};
	    	$mnu = self::$m($vars);

	    	$html = H::ul(['class' =>'nav navbar-nav ml-auto mr20'], $mnu);
	    	return $html;    	
	    }

	    /** Bootstrap 4 administrative user menu OK
	     *
	     * @param - array - variables
	     * @return - string HTML snippet
	     **/
	    protected static function bootstrap4_adminusermenu(array $vars)
	    {

			global $clq;
			$ses = $clq->resolve('Session');
			$mnu = "";
			$mnu .= H::li(['class' => 'nav-item dropdown'],
				H::a(['class' => 'nav-link dropdown-toggle nav-link', 'data-toggle' => 'dropdown', 'href' => '#', 'role' => 'button', 'aria-haspopup' => 'true', 'aria-expanded' => 'false'],
					H::img(['class' => 'img-avatar', 'src' => '/public/images/'.Q::makeAvatar($_SESSION['UserName'], 50), 'title' => $_SESSION['UserName'], 'class' => '', 'alt' => $_SESSION['UserName']])
				),
				H::div(['class' => 'dropdown-menu dropdown-menu-right'],
					H::div(['class' => 'dropdown-header text-center bold'], Q::cStr('391:Account')),
					H::a(['class' => 'dropdown-item', 'href' => '/apps/webmail/index.php?', 'target' => '_blank'], H::i(['class' => 'fa fa-envelope-o']).Q::cStr('392:Messages')),
					H::a(['class' => 'dropdown-item', 'href' => '/apps/notes/index.php?#/folder', 'target' => '_blank'], H::i(['class' => 'fa fa-tasks']).Q::cStr('326:Tasks')),
					H::div(['class' => 'dropdown-header text-center'], Q::cStr('393:Profile')),
					H::ul(['class' => 'list-group'],
						H::li(['class' => 'list-group-item'], Q::cStr('94:Name').' : '.A::getUserName($_SESSION['UserName'], 2)),
						H::li(['class' => 'list-group-item'], Q::cStr('96:Group').' : '.Q::fList($_SESSION['UserGroup'], 'usergroups')),
						H::li(['class' => 'list-group-item list-group-item-success bold'], Q::cStr('99:Level').' : '.$_SESSION['UserLevel']),
						H::li(['class' => 'list-group-item'], Q::cStr('87:Email').' : '.$_SESSION['UserEmail'])
					),
					H::div(['class' => 'dropdown-header text-center bold'], Q::cStr('88:Settings')),
					H::a(['class' => 'dropdown-item topbutton', 'data-uid' => $_SESSION['UserID'], 'data-action' => 'userprofile', 'href' => '#'], H::i(['class' => 'fa fa-wrench']).Q::cStr('88:Settings')),
					H::a(['class' => 'dropdown-item', 'href' => '/ajax/en/logout/'], H::i(['class' => 'fa fa-lock']).Q::cStr('92:Logout'))
				)
			);

			// Add Toggler			
			$mnu .= H::li(['class' => 'nav-item d-md-down-none'],
				H::a(['class' =>'nav-link navbar-toggler aside-menu-toggler', 'href' => '#'], "&#9776;")
			);
			return $mnu;
	    }

	    /** Bootstrap 4 right side menu OK
	     *
	     * @param - array - variables
	     * @return - string HTML snippet
	     **/
	    protected static function bootstrap4_rightsidemenu(array $vars)
	    {
	    	$html = "";

	    	// Tabs
	    	$tabs = '
		        <li class="nav-item">
		          <a class="nav-link active" data-toggle="tab" href="#timeline" role="tab"><i class="icon-list"></i></a>
		        </li>
		        <li class="nav-item">
		          <a class="nav-link" data-toggle="tab" href="#messages" role="tab"><i class="icon-speech"></i></a>
		        </li>
	    	';
	    	$html .= H::ul(['class' => 'nav nav-tabs', 'role' => 'tablist'], $tabs);

	    	// Tab panes or content
	    	$content = '
				<div class="tab-pane" id="timeline" role="tabpanel">
					Timeline
				</div>
				<div class="tab-pane" id="messages" role="tabpanel">
					Messages
				</div>
	    	';
	    	$html .= H::div(['class' => 'tab-content'], $content);

	    	return $html;
	    }

	    /** Bootstrap 4 footer OK
	     *
	     * @param - array - variables
	     * @return - string HTML snippet
	     **/
	    protected static function bootstrap4_footer(array $vars)
	    {
	    	global $clq; $lcd = $clq->get('lcd');
            $footerarray = self::menuRead($vars);
			$fhrefs = "";
			foreach($footerarray as $key => $entry) {
				$entry['d_css'] = 'plain';
				$fhrefs .= '&nbsp;|&nbsp;'.H::a(self::entryDetail($entry, 'footerlink'), $entry['d_title'][$lcd]);
			};
			$vars2 = array(
                'filename' => 'admin',         // If file, name of file without extension (.cfg)
                'subdir' => 'admin/config/',   // If file, name of subdirectory
                'type' => 'service',           // If database, value of c_type
                'reference' => 'admin',        // If database, value of c_reference
                'key' => ''
            );
            $admcfg = C::cfgRead($vars2);	
			$footer = H::span([], Q::cCfg('site.copyrightmessage').$fhrefs);
			return $footer;
	    }	 

	    /** Bootstrap 4 singleitemmenu OK
	     *
	     * @param - array - variables
	     * @return - string HTML snippet
	     **/
	    protected static function bootstrap4_singleitemmenu(array $vars)
	    {
            global $clq; $lcd = $clq->get('lcd');
            $tlmenuarray = self::menuRead($vars);
			// L::cLog($tlmenuarray);						
	        
	        $mnu = "";
			foreach($tlmenuarray as $id => $entry) {
				$mnu .= H::li(['class' => 'nav-item px-3 hint--bottom-left hint--info', 'aria-label' => $entry['d_description'][$lcd]],
					H::a(self::entryDetail($entry),
						H::i(['class' => 'mr5 fa fa-fw fa-'.self::trap($entry, 'd_icon')]), 
						$entry['d_title'][$lcd]
					)
				);
			};
	        return $mnu;
	    }	       

	    /** Bootstrap 4 left side menu OK
	     *
	     * @param - array - variables
	     * @return - string HTML snippet
	     **/
	    protected static function bootstrap4_leftsidemenu(array $vars)
	    {
            global $clq; $lcd = $clq->get('lcd');
            $slmenuarray = []; 
            $slmenuarray = self::menuRead($vars);
			// L::cLog($slmenuarray);	
			$mnu = "";
			foreach($slmenuarray as $key => $entry) {
				
				if(self::testForSubmenu($entry['c_reference']) > 0) {
					
					$subitms = "";
            		$submenuarray = []; 
            		$submenuarray = self::menuRead($vars, $entry['c_reference']);				
					foreach($submenuarray as $skey => $sentry) {			
						$subitms .= self::bootstrap4_menuitem($sentry);						
					};	
					
					$mnu .= H::li(['class' => 'nav-item nav-dropdown '.self::trap($entry, 'd_css')],
						H::a(['class' => 'nav-link nav-dropdown-toggle', 'href' => '#'],
							H::i(['class' => 'fa fa-fw fa-'.self::trap($entry, 'd_icon'), 'style' => 'color: #fff;']),
							$entry['d_title'][$lcd]),
						H::ul(['class' => 'nav-dropdown-items greybg', 'style' => 'overflow:hidden'], $subitms)
					);
					
				} else {
					$mnu .= self::bootstrap4_menuitem($entry);								
				}
			};

			$nav = H::nav(['class' => 'sidebar-nav'],
				H::ul(['class' => 'nav'], $mnu)
			);
			return $nav;
	    }

	    /** Bootstrap 4 menuitem OK
	     *
	     * @param - array - variables
	     * @return - string HTML snippet
	     **/
	    protected static function bootstrap4_menuitem(array $entry)
	    {
			global $clq; $lcd = $clq->get('lcd');
			array_key_exists('d_description', $entry) ? $x = $entry['d_description'][$lcd] : $x = '';
			array_key_exists('d_title', $entry) ? $l = $entry['d_title'][$lcd] : $l = '';

			$mnu = H::li(['class' => 'hint--right hint--medium nav-item', 'style' => 'width: 100%', 'aria-label' => $x],
				H::a(self::entryDetail($entry), H::i(['class' => 'fa fa-fw fa-'.self::trap($entry, 'd_icon'), 'style' => 'color: #fff;']), $l)
			);
			return $mnu;
	    }	    

	    /** Bootstrap 4 footer menu 
	     * to be completed, needed for website only
	     * @param - array - variables
	     * @return - string HTML snippet
	     **/
	    protected static function bootstrap4_footermenu(array $vars)
	    {


	    }

    /** Bootstrap 3
     * 
     * 
     ********************************************************************************************************/

    /** Pure Css
     * 
     * 
     ********************************************************************************************************/

	/** Plain Lists
	 * 
	 * publishList()
	 * - arrayToList()
	 * - displayElementH()
	 * - arrayToJson()
	 * - displayElementJ()
	 *
	 ********************************************************************************************************/

   		/**
         * Generates an Plain multidimensional Unordered list
         * @author = Webcliq May 2015
         * @var = an input array plus a flag indicating whether out put is in HTML or JSON
         * @return = Plain HTML unordered list or JSON
         * @todo
         **/
        function publishList($array, $def = null, $json = false) {

            if($json == true) {
                return self::arrayToJson($array, $def);
            } else {
                return self::arrayToList($array, $def);
            }
        }

        // HTML
        private function arrayToList($array, $def) {
            
            $list = "";
            foreach($array as $id => $row) {
                if(is_array($row['submenu'])) {
                    $list .= self::displayElementH($id, $row);
                    $list .= self::arrayToList($row['submenu'], $def);
                    $list .= '</li>';
                } else {
                    $list .= self::displayElementH($id, $row);
                    $list .= '</li>';
                }
            }
            $mnu = H::ul(['class' => 'sitemapnav col4', 'style' => 'list-style-type: none;'], $list);;
            return $mnu;
        }

        private function displayElementH($id, $entry) {
 
			global $clq;
			$entry['id'] = $id;
			array_key_exists('title', $entry) ? $entry['title'] = Q::cStr($entry['title']) : null ;
			array_key_exists('label', $entry) ? $l = Q::cStr($entry['label']) : $l = '';
			array_key_exists('icon', $entry) ? $i = H::i(['class' => 'fa fa-fw fa-'.$entry['icon']]) : $l = '';

			if(!array_key_exists('url', $entry)) {			
				$href = "";
				array_key_exists('page', $entry) ? $href .= "/".$entry['page']."/".$clq->get('idiom')."/" : null ;
				array_key_exists('type', $entry) ? $href .= $entry['type']."/" : null ;
				array_key_exists('table', $entry) ? $href .= $entry['table']."/" : null ;
				array_key_exists('tabletype', $entry) ? $href .= $entry['tabletype']."/" : null ;
				$entry['href'] = $href;
			}

			$mnu = H::li(['class' => self::trap($entry, 'css')],
				H::a(self::entryDetail($entry), $i, $l)
			);
			return $mnu;
        }

        // JSON
        private function arrayToJson($array, $def) {
            
            $json = '{';
            foreach($array as $id => $row) {
                if(is_array($row['submenu'])) {
                    $json .= self::displayElementJ($row);
                    $json .= self::arrayToJson($row['submenu'], $def);
                    $json .= '],';
                } else {
                    $json .= self::displayElementJ($row);
                    $json .= '],';
                }
            }
            $json = trim($json, ',');
            $json .= '}';
            return $json;
        }

        /**
        * Generates a line of JSON but may do this differently by creating an array line and then JSON encode it
        **/
        private function displayElementJ($row) {
          
          	global $clq;
            // Text
            $text = '"text":"'.Q::cStr($row['text']).'",'; $json = "";

            // Optional Title
            if(array_key_exists('title', $row)) {$title = '"title":"'.Q::cStr($row['title']).'",';} else {$title = "";};

            // Class
            if(array_key_exists('class', $row)) {$class = '"class":"'.$row['class'].'",';} else {$class = "";};

            // Data-Action
            if(array_key_exists('action', $row)) {$action = '"data-action":"'.$row['action'].'",';} else {$action = "";};

            // Rel
            if(array_key_exists('rel', $row)) {$rel = '"rel":"'.$row['rel'].'",';} else {$rel = "";};

            // Data-Level
            if(array_key_exists('attr', $row)) {$attr = '"data-level":"'.$row['attr'].'",';} else {$attr = "";};

            // Data-Params - Used by Footer and Tiny Box for Dialogue sizing (implode a comma separated string)
            if(array_key_exists('params', $row)) {$params = '"data-params":"'.$row['params'].'",';} else {$params = "";};

            // Icon
            if(array_key_exists('icon', $row)) {$icon = '"icon":"'.$row['icon'].'",';} else {$icon = "";};

            // Url
            if(array_key_exists('url', $row)) {$url = '"url":"'.$row['url'].'",';} else {$url = "";};

            // VAL = Array Line Number
            // PID = Parent ID

            $json .= '{';
            $json .= $class.$action.$attr.$params.$icon.$url;
            $json = trim($json, ',');
            $json .= '},';
            return $json;
        }

	/** Common Utilities
	 * 
	 * menuRead() - read a menu config from database or file
	 * - getFromDb() - from menuRead()
	 * - getFromFile - from menuRead()
	 * entryDetail()
	 * trap()
	 *
	 ********************************************************************************************************/

		/** Menu Read
		 *
		 * @param - array - $vars coming forward
		 * @return - array - an appropriate menu array for the caller
		 *
		 **/
		protected static function menuRead($vars, $parent = '0')
		{
			if($vars['view'] == 'admin') {
				$adm = "adm"; $subdir = "admin/data/"; $table = "dbcollection";
			} else {
				$adm = ""; $subdir = "data/"; $table = "dbitem";
			};
			$args = array(
                'filename' => $table.'.'.$adm.$vars['subtype'], 
                'subdir' => $subdir,
                'table' => $table,       
                'tabletype' => $adm.$vars['subtype'],   
                'key' => ''
            );

			$menuarray = self::getFromDb($args);       
	        if(count($menuarray) < 1) {
	            $menuarray = self::getFromFile($args);
	        }; 

	        // Filter and Sort here
	        $filteredmenu = array_filter($menuarray, function($value) use ($parent) {
	        	$lev = explode(':', $value['c_level']);
	        	if(
					$value['c_parent'] == $parent and
					A::uLevel() >= $lev[0] and
					$value['c_status'] == "active"
	        	) {
	        		return true;
	        	} else {
	        		return false;
	        	}
	        });

			$menu = Q::array_orderby($filteredmenu, 'c_order', SORT_ASC);

	        return $menu;
		}

		protected static function getFromDb($args)
		{
        	$method = self::THISCLASS.'->'.__FUNCTION__."()";
        	try {

        		global $clq; $db = $clq->resolve('Db');
        		
        		// Gets all the menu data from the database for this menutype
	            $sql = "SELECT * FROM ".$args['table']." WHERE c_type = ?";
	            $rawrs = R::getAll($sql, [$args['tabletype']]); 
	            $rs = D::extractAndMergeRecordset($rawrs);
				return $rs;

	        } catch(Exception $e) {
				$err = [
					'method' => $method,
					'errmsg' => $e->getMessage(),
					'vars' => $args,
					'query' => $sql,	
					'dbrecordset' => $rawrs,
					'recordset' => $rs
				];
				L::cLog($err);
				return false;
	        } 
		}	
	
		protected static function getFromFile($args)
		{
			$menuarray = C::getFromFile($args);
			return $menuarray;
		}	

		/** Menu Entry Detail
		 * 
		 * introduce security
		 * @return - array - for inclusion in HTML
		 **/
		protected static function entryDetail($entry, $hook = 'menulink')
		{	
			$itm = []; global $clq; $lcd = $clq->get('lcd');

            /*
            	id = 201
				c_reference = 'ASM1001'
				c_type = 'admleftsidemenu'
				c_category = 'home' ; field name
				c_level = '60:70:90'
				c_order = 'aa'
				c_parent = '0'
				c_options = ''
				c_notes = 'Created from configuration file'
				c_lastmodified = ''
				c_whomodified = ''
				c_revision = '0'
				c_status = 'active'
				c_common = 'Dashboard' 
				d_title.en = 'Dashboard' ; label
				d_title.es = 'Tablero' ; title
				d_description.en = 'Principal page of Administration System'
				d_description.es = 'Página principal del sistema administrativo'
				d_page = 'desktop'
				d_type = 'dashboard'
				d_table = 'dbcollection'
				d_tabletype = ''
				d_text = ''
				d_icon = 'dashboard'
				d_css = ''
				d_submenu = 'false'


			*/
			if(array_key_exists('c_options', $entry) and $entry['c_options'] != "") {
				$row = explode(',', $entry['c_options']);
				foreach($row as $i => $pr) {
					$p = explode('|', $pr);
					$itm[$p[0]] = $p[1];
				}
			} else {
				$itm['class'] = self::trap($entry, 'd_css', 'nav-link');
				if(array_key_exists('d_url', $entry)) {
					$itm['href'] = $entry['d_page'];
					$itm['target'] = '_blank';
				} else {$itm['href'] = '#'; $itm['data-hook'] = $hook;};

				array_key_exists('d_page', $entry) ? $itm['data-page'] = $entry['d_page'] : $itm['data-page'] = 'nopage';
				array_key_exists('d_type', $entry) ? $itm['data-type'] = $entry['d_type'] : null ;
				array_key_exists('d_action', $entry) ? $itm['data-action'] = $entry['d_action'] : null ;
				array_key_exists('d_table', $entry) ? $itm['data-table'] = $entry['d_table'] : null ;
				array_key_exists('d_tabletype', $entry) ? $itm['data-tabletype'] = $entry['d_tabletype'] : null ;
				array_key_exists('d_params', $entry) ? $itm['data-params'] = $entry['d_params'] : null ;
			}
			return $itm;
		}

		/** Simple facility to stop errors on empty entries
		 * 
		 * @param - array - property array
		 * @param - string - key
		 * @return - valid string
		 **/
		protected static function trap($entry, $key, $def = '')
		{
			if(array_key_exists($key, $entry)) {
				if($entry[$key] != '') {
					$x = $entry[$key];
				} else {
					$x = $def;
				}
			} else {
				$x = $def;
			};
			return $x;
		}

		protected static function testForSubmenu($ref)
		{
			return R::count(self::$table, 'c_type = ? AND c_parent = ?', [self::$type, $ref]);
		}

}
# alias +e+ class
if(!class_exists("M")){ class_alias('Menu', 'M'); };