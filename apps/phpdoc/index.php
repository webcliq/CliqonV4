<?php

/**
 * PHPDocFill - Easily Document your PHP Scripts
 * Copyright (C) 2013 Clement Nedelcu
 * Version 1.0 BETA 2
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see http://www.gnu.org/licenses/
 * @package PHPDocFill
 */

//////////////////////////////////////
//          -CONFIGURATION-         //
// Set a password to access the app //
define("APP_USER", "admin");         
// Note: 8 characters minimum
define("APP_PASSWORD", ""); //
// Or change this (not recommended) //
define("REQUIRES_PASSWORD", false);
//////////////////////////////////////



///////////////////////
// CODE BEGINS BELOW //

// Block types
define("BLOCK_PAGE", "PAGE");
define("BLOCK_CODE", "CODE");
define("T_SPECIAL_BRACKET",15090134);

/**
 * Represents one actual document-block comment
 * Static class containing dynamic methods (constructor, outputs...) and static methods (InsertDocBlock, ...)
 * Handles all the docblock related work
 */
class DocBlock {
	var $valid = false;
	var $tags = "";
	var $shortDescription = "";
	var $longDescription = "";
	var $type = 0;
	
	var $comment = null;
	var $associatedCode = null;
	var $associatedHlCode = null;
	var $line = 0;
	var $docline = 0;
	var $indent = "";

	/**
	 * Builds a docblock object from a raw comment
	 * @param string $comment The raw comment read from the source file (optional)
	 * @param string $associatedCode The code line associated to the docblock (optional)
	 * @param string $blockType The type of block being created (optional)
	 */
	function __construct($comment="", $associatedCode="", $blockType="") {
		
		if (!$comment && !$associatedCode && !$blockType) return;
		
		// Figure out indent (for later output)
		if ( preg_match('@^([\t\040]*).*$@', $associatedCode, $matches) ) {
			$this->indent = $matches[1];
		}
		
		// Save tokens
		$this->comment = trim($comment);
		$this->associatedCode = $associatedCode;
		$this->associatedHlCode = @str_ireplace("&lt;?php&nbsp;","",@highlight_string("<?php ".trim($this->associatedCode),true));
		$this->type = $blockType;
		
		// What's the source?
		$source = $comment; // element 1 is the token source
		
		$source = trim($source);
		$source = str_replace("/**","",$source);
		$source = str_replace("*/","",$source);
		$lines = explode("\n", $source);
		$source = array();
		foreach($lines as $line) {
			$line = trim($line);
			$line = str_replace("\r", "", $line);
			// If the line doesn't start by *, we aren't interested
			if (substr($line,0,1) == "*" ) $source[] = trim(substr($line,1));
		}
		if (!count($source)) return false;
		 
		// Short description?
		if (substr($source[0],0,1) != "@") {
			$this->shortDescription = $source[0];
		}
		 
		// Long description?
		for($i=1; $i<count($source); $i++) {
			if (substr($source[$i],0,1) == "@") break;
			$this->longDescription .= $source[$i]."\n";
		}
		$this->longDescription = trim($this->longDescription);
		 
		// Tags?
		$this->tags = array();
		for($i=count($source)-1; $i>=0; $i--) {
			if (trim($source[$i]) == "") continue;
			if (substr($source[$i],0,1) != "@") break;
			$this->tags[] = $source[$i];
		}
		$this->tags = array_reverse($this->tags);
		$this->tags = implode(PHP_EOL,$this->tags);
		 
		// There needs to be at least a short description, or a long description, or a tags section for the block to be valid
		if ($this->shortDescription || $this->longDescription || $this->tags) $this->valid = true;
		
		// Convert to UTF8
		$this->shortDescription = !isUtf8($this->shortDescription) ? utf8_encode($this->shortDescription) : $this->shortDescription;
		$this->longDescription = !isUtf8($this->longDescription) ? utf8_encode($this->longDescription) : $this->longDescription;
		$this->tags = !isUtf8($this->tags) ? utf8_encode($this->tags) : $this->tags;
		
		return $this->valid;
	}

	/**
	 * Provides an output of the dobclock as array of string
	 * @param bool $utf8 if true, result will be sent UTF8-encoded; false will be UTF8-decoded or left as is
	 * @return array An array of strings to be imploded and saved in a file
	 */
	function Output($utf8=false) {
		$out = array();
		$out[] = "/**";
		if ($this->shortDescription)
			$out[] = " * ".str_replace("\r\n"," ",str_replace("\n", " ",$this->shortDescription));
		if ($this->longDescription) {
			$lines = preg_split("/((\r?\n)|(\n?\r))/", $this->longDescription);
			// do we insert a blank line? 
			//if ($this->shortDescription) // there is already a short description, so, yep
			//	$out[] = " *";
			foreach($lines as $line)
				$out[] = " * ".$line;
		}
		
		if ($this->tags) {
			$lines = preg_split("/((\r?\n)|(\n?\r))/", $this->tags);
			foreach($lines as $line)
				$out[] = " * ".$line;
		}
		$out[] = " */";
		
		// do we want UTF8 ?
		$result = array();
		$indent = $this->indent;
		$i = 0;
		foreach($out as $o) {
			if ($i == 1) $indent .= " ";
			$i++;
			if (isUtf8($o) && !$utf8) // string is in UTF8, but we DONT want UTF8
				$result[] = utf8_decode($indent.trim($o));
			elseif (!isUtf8($o) && $utf8) // string is NOT in UTF8, but we WANT UTF8
				$result[] = utf8_encode($indent.trim($o));
			else
				$result[] = $indent.trim($o);
		}
		
		return $result;
	}
	

	/**
	 * Outputs the docblock as a trimmed string comment (for use in a textarea or tooltip)
	 * @return string The docblock as string, including opening and closing tags
	 */
	function OutputComment() {
		$pagecomment = trim($this->shortDescription) ? PrepString($this->shortDescription)."<br />" : "";
		$pagecomment .= trim($this->longDescription) ? PrepString($this->longDescription)."<br />" : "";
		$pagecomment .= trim($this->tags) ? "<i>".PrepString($this->tags)."</i>" : "";
		$pagecomment = $pagecomment ? $pagecomment : "<i>(Undocumented)</i>";
		$pagecomment = "<div class='pagecomment'>$pagecomment</div>";
		return $pagecomment;
	}
	

	/**
	 * Returns the docblock "name"
	 * The docblock name is the name of the object being commented.
	 * For example the name of a function, of a class, of an interface...
	 * It is mostly used for generating the documentation manual.
	 * @return string The name of the object being commented
	 */
	function GetName() {
		if ($this->type == BLOCK_PAGE) return "";
		$source = "<?php ".trim($this->associatedCode);
		$interesting = array( T_FUNCTION, T_STRING, T_VARIABLE, T_CLASS, T_INCLUDE, T_INCLUDE_ONCE, T_REQUIRE, T_REQUIRE_ONCE, T_INTERFACE, T_CONSTANT_ENCAPSED_STRING );
		$alltokens = token_get_all($source);
		$tokens = array();
		foreach($alltokens as $token) {
			if (!in_array($token[0],$interesting)) continue;
			//if ($token[0] == T_STRING && strtolower($token[1]) != "define") continue; // a string, but not "define"... not useful to us			
			$tokens[] = $token;
		}
		
		switch($this->type) {
			case "T_FUNCTION": return $tokens[1][1];
			case "T_CLASS": return $tokens[1][1];
			case "T_INTERFACE": return $tokens[1][1];
			case "T_STRING": return $tokens[1][1];
			case "T_INCLUDE": case "T_INCLUDE_ONCE": case "T_REQUIRE": case "T_REQUIRE_ONCE": return $tokens[1][1];
		}
		return $this->type;
	}
	

	/**
	 * Returns the class of the code element concerned by the docblock
	 * If the docblock is about a class, this function returns "class"
	 * If the docblock is about a function, this function returns "function"
	 * Check the code to see the list of possible values
	 * @return string The type of element associated to the docblock (class, function, ...)
	 */
	function GetClass() {
		switch($this->type) {
			case BLOCK_PAGE: return "page";
			case "T_FUNCTION": return "function";
			case "T_CLASS": return "class";
			case "T_INTERFACE": return "interface";
			case "T_STRING": return "define";
			case "T_INCLUDE": case "T_INCLUDE_ONCE": case "T_REQUIRE": case "T_REQUIRE_ONCE": return "include";
		}
		return "";
	}
		
	/**
	 * Parses code to build the list of commentable elements and their associated docblocks
	 * Using the token_get_all function to generate the list of tokens from the given PHP source code,
	 * this function analyzes your code and attempts to detect all commentable elements as well as their associated docblocks.
	 * @param string $source The source code to analyze
	 * @return array An array of DocBlock instances
	 */
	static function ParseCode($source) {
		global $phpTokens;
		
		$alltokens = token_get_all($source);
		$tokens = array();
		$interesting = array( 
			T_DOC_COMMENT, T_FUNCTION, T_STRING, T_VARIABLE, T_CLASS, T_INCLUDE, T_INCLUDE_ONCE, T_REQUIRE, 
			T_REQUIRE_ONCE, T_CONSTANT_ENCAPSED_STRING, T_DNUMBER,T_LNUMBER, T_SPECIAL_BRACKET, T_INTERFACE);
		$opentagfound = false;
		foreach($alltokens as $token) {
			if ($token[0] == T_OPEN_TAG) $opentagfound = true;
			
			if (!is_array($token) && $token != ")") continue;
			if ($token == ")") $token = array(T_SPECIAL_BRACKET, ")", 0);
			if (!in_array($token[0], $interesting)) continue;
			$token[3] = @token_name($token[0]);
			$tokens[] = $token;
		}
		
		// NO open tag? there is no PHP code at all?
		if (!$opentagfound) return null;
		
		// Find Php Comment blocks
		$blocks = array();
		
		// Now, we have all interesting & consecutive tokens
		$pageDocBlock = null;
		$lines = preg_split("/((\r?\n)|(\n?\r))/", $source);
		
		// Check all tokens
		for($i=0; $i<count($tokens); $i++) {
			$token = @$tokens[$i];
			$nextToken = @$tokens[$i+1];
			$previousToken = @$tokens[$i-1];
			
			
			// Trying to detect if there is a Page Docblock
			// First doc bloc should be a T_DOC_COMMENT. 
			// It should contain @package
			if ($i == 0 && $token[0] == T_DOC_COMMENT) {
				if (stripos($token[1], "@package") !== FALSE) {
					// We found the page docblock!
					$block = new DocBlock($token[1], "(Page DocBlock)", BLOCK_PAGE);
					$block->line = $token[2];
					$block->docline = $token[2];
					$blocks[] = $block;
					$pageDocBlock = $block;
					continue;
				}
			}
			
			// So, what's this comment block for?
			if (!in_array($token[0],array(T_FUNCTION,T_CLASS,T_INTERFACE,T_STRING,T_INCLUDE,T_INCLUDE_ONCE,T_REQUIRE,T_REQUIRE_ONCE))) continue; // nothing of interest (bad syntax?) => continue...
			if ($token[0] == T_STRING && strtolower($token[1]) != "define") continue; // a string, but not "define"... not useful to us
			
			// Filter out elements we don't want to see
			if ($token[0] == T_FUNCTION && !$_POST['Dfunc']) continue;
			elseif ($token[0] == T_CLASS && !$_POST['Dclas']) continue;
			elseif ($token[0] == T_INTERFACE && !$_POST['Dinte']) continue;
			elseif ($token[0] == T_STRING && !$_POST['Dcons']) continue;
			elseif (in_array($token[0], array(T_INCLUDE,T_INCLUDE_ONCE,T_REQUIRE,T_REQUIRE_ONCE)) && !$_POST['Dincl']) continue;
			
			// Filter out anonymous functions (non anonymous functions have their name/T_STRING right after the keyword 'function')
			if ($token[0] == T_FUNCTION && $nextToken[0] != T_STRING) continue;
			
			// Do we have a docblock for this token?
			$relevantDocBlock = null;
			if ($previousToken[0] == T_DOC_COMMENT) {
				// Is it the page token?
				if ($i == 1 && $pageDocBlock) { // yup, it's the page token, no action
					//$relevantDocBlock = null;
				} else {
					// This is a doc block likely meant for the following object
					$relevantDocBlock = $previousToken;
				}
			}
			// Create empty docblock
			if (!$relevantDocBlock) {
				$relevantDocBlock = array(T_DOC_COMMENT,"/**\r\n*\r\n*/",0);
			}
			
			// Find out associated tokens, depending on the current token type
			$associatedCode = $lines[ $token[2]-1 ];
			$type = $token[3];
			
			$block = new DocBlock($relevantDocBlock[1],$associatedCode, $type);
			$block->line = $token[2];
			$block->docline = $relevantDocBlock[2];
			//echo "<pre>"; print_r(htmlentities($lines));die();			
			$blocks[] = $block;
		}
		
		return $blocks;
	}
		
	/**
	 * Calculates the commenting completion index of a file
	 * @param string $file Path of the file to be analyzed
	 * @return string An fully formed HTML span tag containing the properly colored percentage
	 */
	static function AnalyzeFile($file) {
		$completion = -1;
		$source = @file_get_contents($file);
		$complete = 0;
		$total = 0;
		if ($source) {
			$blocks = DocBlock::ParseCode($source);
			if ($blocks !== null) {
				$pageblock = false;
				foreach($blocks as $block) {
					$complete += $block->valid ? 1 : 0;
					if ($block->type == BLOCK_PAGE) $pageblock = true;
					if ($block->type == BLOCK_PAGE && !$_POST['Dpage']) continue;
					$total++;
				}
				if (!$pageblock && $_POST['Dpage']) $total++;
				
				// Statistics
				if ($total > 0) {
					$completion = round( $complete / $total * 100);
				}
			}
		}
		return DocBlock::PrepareBullet($completion,$complete,$total);
	}

	/**
	 * Provides an HTML span tag containing the completion index with the proper color
	 * @param int $completion The completion percentage
	 * @param int $done The elements that are already documented
	 * @param int $total The total amount of commentable elements in the source
	 * @return string Some HTML code to insert before a file name in the menu
	 */
	static function PrepareBullet($completion,$done,$total) {
		$text = "Documentation completion: $done/$total [$completion%]";
		if ($completion == -1) { $color = 'black'; $text = "No PHP code found or nothing to document"; }
		elseif ($completion == 0) { $color = '#CC0000';  }
		elseif ($completion == 100) { $color = 'green'; }
		else $color = 'orange';
		$completion = $completion == -1 ? "-" : $completion."%";
		return "<span title='$text' style='display: inline-block; margin-right: 5px; width: 25px; font-size: 9px; color: $color;'>$completion</span>";
	}
		
	/**
	 * Insert a docblock into a file before the specified code
	 * @param string $file Path of the concerned file 
	 * @param string $doc DocBlock instance to insert in the file
	 * @param string $type Type of docblock to insert
	 * @param string $code The code to verify against the code found at $line
	 * @param string $line Line that should contain the code of the associated docblock
	 */
	function InsertDocBlock($file, $doc, $type, $code="", $line=0) {
		// Read file...
		$source = @file_get_contents($file);
		if (!$source) die("ERR3"); // error 3 => can't read file
		
		// Split the code in an array
		$lines = preg_split("/((\r?\n)|(\n?\r))/", $source);
		
		// Figure out line number
		// Make sure the retained line contains the same code as previously, otherwise we may have a problem
		if ($type != BLOCK_PAGE) {
			$linenumber = intval($line);		
			if (trim($lines[$linenumber-1]) != trim($code) ) die("ERR1");			
			// Figure out indent
			if ( preg_match('@^([\t\040]*).*$@', $lines[$linenumber-1], $matches) ) {
				$doc->indent = $matches[1];
			}
			
		} else {
			// For the page block, we have to find the first open tag and insert after
			$tokens = token_get_all($source);
			$found = false;
			foreach($tokens as $token) {
				if ($token[0] == T_OPEN_TAG) {
					$found = true;
					$linenumber = intval($token[2])+1;
				}
			}
			if (!$found) die("ERR4");
		}
		
		// Which line breaks does the source use?
		$lbtype = "\n";
		if (substr_count($source,"\r\n") > 0) $lbtype = "\r\n";

		// Insert docblock
		array_splice($lines, $linenumber-1, 0, $lbtype.@implode($lbtype,$doc->Output( isUtf8($source) )) );
				
		// final source
		$source = implode($lbtype, $lines);
		//rename($file,$file.".bak");
		file_put_contents($file, $source);
	}

	/**
	 * Update an existing docblock
	 * @param string $file File in which the docblock should be updated
	 * @param DocBlock $doc DocBlock instance that contains the text to insert
	 * @param int $line Code line where the existing docblock should be located
	 */
	static function UpdateDocBlock($file, $doc, $line) {
		
		// Read file...
		$source = @file_get_contents($file);
		if (!$source) die("ERR3"); // error 3 => can't read file
		
		// Now, we process the file, and make sure the original comment is still at the right location, or it's been moved
		$tokens = token_get_all($source);
		$found = false;
		$oldcomment = "";
		foreach($tokens as $token) {
			if ($token[0] == T_DOC_COMMENT && $token[2] == $line) {
				$found = true;
				$oldcomment = $token[1];
			}
		}
		if (!$found) die("ERR4");

		// Set indent
		$code = $_POST['Code'];
		if ( preg_match('@^([\t\040]*).*$@', $code, $matches) ) {
			$doc->indent = $matches[1];
		}
		
		// Which line breaks does the source use?
		$lbtype = "\n";
		if (substr_count($source,"\r\n") > 0) $lbtype = "\r\n";
		$final = implode($lbtype, $doc->Output(isUtf8($source)));
		
		// Simply search & replace old docblock
		$source = str_replace($oldcomment, trim($final), $source);
		//rename($file,$file.".bak");
		file_put_contents($file, $source);
	}

	/**
	 * Scans current folder and subfolders for .php files
	 */
	static function AJAX_Scan() {
		$list = RecursiveScanDir(__DIR__);
		usort($list, "strnatcasecmp");
		
		// No files?
		if (!$list || !count($list)) die("No .php files located in this<br />folder and/or subfolders");
		
		// For each file we'll present something nice
		$out = "";
		foreach($list as $file) {
			
			// If Mode is set to 1, we need to parse the file and check the completion
			$analysis = "";
			if ($_POST['Mode']) {
				$analysis = DocBlock::AnalyzeFile($file);
			}
			
			$out .= "
				$analysis<a href='#' data-filename='".rawurlencode($file)."' class='filelink'>$file</a><br />
			";
		}
		
		// Ouptut
		echo $out;
		
		// Exit "Gracefully"
		exit(0);
	}
	

	/**
	 * Analyzes a given file and produces an HTML table of all commentable elements
	 */
	static function AJAX_Analyze() {
		// Which file?
		$file = $_POST['File'];
		
		// Prevent loading illegal files
		if ( strpos($file,":") !== FALSE || strpos($file,"..") !== FALSE || strtolower(substr(pathinfo($file,PATHINFO_EXTENSION),0,3)) != "php") die("Invalid file");
		
		// Does this file exist?
		if (!file_exists($file)) die("Error: file not found");
		
		// File too big?
		if (filesize($file) > 4*1024*1024) die("Error: this file is over 4 megabytes, parsing would take too long.");
		
		// Read file...
		$source = @file_get_contents($file);
		if (!$source) die("ERR3");
		
		// Display
		$blocks = DocBlock::ParseCode($source);
		if ($blocks === null) {
			echo "This PHP script does not seem to contain any PHP code. It cannot be documented.";
		} else {
		
			//echo "<pre>";
			$out = "
				<table cellspacing=0 cellpadding=0 id='blocklist'>
					<thead>
						<tr>
							<th>&nbsp;</th>
							<th>Done</th>
							<th>Line</th>
							<th>Code</th>
						</tr>
					</thead>
					<tbody>
			";
			$pageblock = false;
			$complete = 0;
			$i = 0;
			$rows = "";
			foreach($blocks as $block) {
				if ($block->type == BLOCK_PAGE) $pageblock = true;
				if ($block->type == BLOCK_PAGE && !$_POST['Dpage']) continue;
				$jsoncomment = str_replace("\n",'',str_replace("\r",'',str_replace('"','&quot;', strip_tags(implode('<br />',$block->Output(true)),"<br>"))));
				$state = $block->valid ? "<span class='valid comment' title='Comment' data-comment=\"$jsoncomment\">Yes</span>" : "-<span class='todo'>No</span>-";
				$jsonobj = json_encode($block);
				$rows .= "
					<tr class='".($i%2==1?"odd":"")."'>
						<td><button data-index='$i' id='action$i' data-type='{$block->type}' data-obj=\"". str_replace('"','&quot;', $jsonobj)."\" class='action'>Edit</button></td>
						<td align=center>$state</td>
						<td align=center><a href='#line".$block->line."' style='text-decoration: none;' title='Jump to code (when &quot;Display Source&quot; option is enabled)'>".$block->line."</a></td>
						<td>".($block->type != BLOCK_PAGE ? $block->associatedHlCode : "(Page DocBlock)")."</td>
					</tr>
				";
				$complete += $block->valid ? 1 : 0;
				$i++;
			}
			
			if (!$pageblock && $_POST['Dpage']) $rows = "<tr class='dbmissing'><td><button data-stuff='' class='action'>Edit</button></td><td align=center colspan=3><span class=todo>Page DocBlock is missing</span></td></tr>".$rows;
			$out .= $rows."</tbody></table>";
			
			// Statistics
			$total = count($blocks);
			if (!$pageblock) $total++;
			$completion = round( $complete / $total * 100);
			$out .= "<br />Current code commenting completion: <b>$completion%</b><br />";
			
			// No rows at all?
			if (!$rows) $out = "No code to document.";
		} 
		
		// Output
		echo $out;
			
		// Do we show the source?
		if ($_POST['ShowSource']) {
			echo "<h2>Source Code</h2>";
			$lines = preg_split("/((\r?\n)|(\n?\r))/", $source);
			echo highlight_php( isUtf8($source) ? implode(PHP_EOL,$lines) : utf8_encode(implode(PHP_EOL,$lines)),true);
		}
		
		die();	
	}

	/**
	 * Save changes, either inserting a new docblock or updating an existing docblock
	 */
	static function AJAX_Save() {
		// Which file?
		$file = $_POST['File'];
		
		// Prevent loading illegal files
		if ( strpos($file,":") !== FALSE || strpos($file,"..") !== FALSE || strtolower(substr(pathinfo($file,PATHINFO_EXTENSION),0,3)) != "php") die("ERR0");
		
		// Does this file exist?
		if (!file_exists($file)) die("ERR2");
		
		// File too big?
		if (filesize($file) > 4*1024*1024) die("ERR0"); 
		
		// Prepare docblock
		$doc = new DocBlock("","","");
		$doc->shortDescription = str_replace("*/", "", trim($_POST['SD']));
		$doc->longDescription = str_replace("*/", "", trim($_POST['LD']));
		$doc->tags = str_replace("*/", "", trim($_POST['TAGS']));

		// What are we doing? UPDATING or INSERTING?
		if ($_POST['MODE'] === "0") { // 0 = Inserting a new doc block
			// Which block are we inserting? is it the page docblock?
			if ($_POST['Line'] === "0") {
				DocBlock::InsertDocBlock( $file, $doc, BLOCK_PAGE );
				die("OK");
			}
			
			// --Not a page docblock--
			DocBlock::InsertDocBlock( $file, $doc, BLOCK_CODE, $_POST['Code'], $_POST['Line'] );
			die("OK");
		}
		elseif ($_POST['MODE'] === "1") { // UPDATING a docblock
			
			// Lets see...
			DocBlock::UpdateDocBlock($file, $doc, $_POST['DocLine']); 
			die("OK");
		}	
	}

	/**
	 * Generates HTML manual as PHPDocFill.html file in the same folder
	 */
	static function AJAX_Manual() {
		
		// OK, what do we do?
		// 1. scan files
		$list = RecursiveScanDir(__DIR__);
		usort($list,"strnatcasecmp");
		
		// 2. for each file, we'll gather the insides
		$allblocks = array();
		$files = array();
		$bid = 0;
		foreach($list as $file) {
			$source = @file_get_contents($file);
			if (!$source) continue;
			$fileblocks = DocBlock::ParseCode($source);
			if ($fileblocks === null) continue;
			foreach($fileblocks as $block) {
				$bid++;
				$block->filename = $file;
				$block->id = $bid;
				$blockkey = $block->GetName()." [{$block->filename}] {$block->line}"; // a unique identifier to sort the classes
				$allblocks[$block->GetClass()][$blockkey] = $block;
				$files[$file][] = $block;
			}
		}
		
		// 3. generate list of files output
		if ($_POST['Dpage']) {
			$fileout = "<div id='files'>";
			foreach($files as $filename=>$blocks) {			
				// Find page docblock
				$pagecomment = "";
				foreach($blocks as $block) 
					if ($block->type == BLOCK_PAGE) 
						$pagecomment = nl2br(str_replace("\n\n","\n",trim($block->shortDescription."\n".$block->longDescription."\n<i>".$block->tags."</i>")));
				$pagecomment = !isUtf8($pagecomment) ? utf8_encode($pagecomment) : $pagecomment;
				$elements = array(); 
				
				// Prepare list of contained elements
				foreach($blocks as $block) {
					$class = $block->GetClass();
					$name = $block->GetName();
					$bid = $block->id;
					$documented = $block->valid ? "" : "Undocumented";
					if ($name)
						$elements[$class][] = "<a href='#$class$bid' title='$documented' class='$documented'><span class='objname'>$name</span></a>";
				}
				
				// Prepare for output
				$elementsout = "";
				foreach($elements as $class=>$blocks) {
					$elementsout .= "<span class='$class bold'>$class</span>".implode(", ", $blocks)."<br />";
				}
				$pagecomment = $pagecomment ? "<div class='pagecomment'>$pagecomment</div>" : "";
				$id = dechex(crc32($filename));
				$fileout .= "
					<div class='file'>
						<a name='file$id'><div class='filename'>$filename</div>
						$pagecomment
						<div class='contains'>$elementsout</div>
					</div>
				";
			}
			$fileout .= "</div>";
			$countfiles = count($files);
			$tabfile = "<li><a href='#files'>Files ($countfiles)</a></li>";
		}
		
		// 4. Generate list of classes
		if ($_POST['Dclas']) {
			$classout = "<div id='classes'>";
			if (!is_array($allblocks['class'])) $allblocks['class'] = array();
			uksort($allblocks['class'], "strnatcasecmp" );
			foreach($allblocks['class'] as $block) {
				// Prepare docs
				$pagecomment = $block->OutputComment();
				
				// Prepare information
				$bid = $block->id;
				$name = $block->GetName();
				$classname = $block->GetClass();
				$fid = dechex(crc32($block->filename));
				$filename = $block->filename;
				$line = $block->line;
				
				// Prepare for output
				$classout .= "
					<div class='file'>
						<a name='$classname$bid'></a><div class='classname'>$name</div>
						From: <a href='#file$fid'>$filename</a> (line $line)<br />
						<pre>$pagecomment</pre>
					</div>
				";			
			}
			$classout .= "</div>";
			$countclasses = count($allblocks['class']);
			$tabclasses = "<li><a href='#classes'>Classes ($countclasses)</a></li>";
		}
		
		// 5. Generate list of functions
		if ($_POST['Dfunc']) {
			$fnout = "<div id='functions'>";
			if (!is_array($allblocks['function'])) $allblocks['function'] = array();
			uksort($allblocks['function'], "strnatcasecmp" );
			foreach($allblocks['function'] as $block) {
				// Prepare docs
				$pagecomment = $block->OutputComment();
				
				// Prepare information
				$bid = $block->id;
				$name = $block->GetName();
				$classname = $block->GetClass();
				$fid = dechex(crc32($block->filename));
				$filename = $block->filename;
				$code = $block->associatedHlCode;
				$line = $block->line;
				
				// Prepare for output
				$fnout .= "
					<div class='file'>
						<a name='$classname$bid'></a><div class='fnname'>$name</div>
						From: <a href='#file$fid'>$filename</a> (line $line)<br />
						Context: $code<br />
						<pre>$pagecomment</pre>
					</div>
				";
			}
			$fnout .= "</div>";
			$countfunctions = count($allblocks['function']);
			$tabfunctions = "<li><a href='#functions'>Functions ($countfunctions)</a></li>";
		}
		
		// 6. Generate list of interfaces
		if ($_POST['Dinte']) {
			$intout = "<div id='interfaces'>";
			if (!is_array($allblocks['interface'])) $allblocks['interface'] = array();
			uksort($allblocks['interface'], "strnatcasecmp" );
			foreach($allblocks['interface'] as $block) {
				// Prepare docs
				$pagecomment = $block->OutputComment();
				
				// Prepare information
				$bid = $block->id;
				$name = $block->GetName();
				$classname = $block->GetClass();
				$fid = dechex(crc32($block->filename));
				$filename = $block->filename;
				$line = $block->line;
				
				// Prepare for output
				$intout .= "
					<div class='file'>
						<a name='$classname$bid'></a><div class='intname'>$name</div>
						From: <a href='#file$fid'>$filename</a> (line $line)<br />
						<pre>$pagecomment</pre>
					</div>
				";
			}
			$intout .= "</div>";
			$countint = count($allblocks['interface']);
			$tabint = "<li><a href='#interfaces'>Interfaces ($countint)</a></li>";
		}
		
		// 7. Generate list of constants
		if ($_POST['Dcons']) {
			$defout = "<div id='define'>";
			if (!is_array($allblocks['define'])) $allblocks['define'] = array();
			uksort($allblocks['define'], "strnatcasecmp" );
			foreach($allblocks['define'] as $block) {
				// Prepare docs
				$pagecomment = $block->OutputComment();
				
				// Prepare information
				$bid = $block->id;
				$name = $block->GetName();
				$classname = $block->GetClass();
				$fid = dechex(crc32($block->filename));
				$filename = $block->filename;
				$line = $block->line;
				$code = $block->associatedHlCode;
				
				// Prepare for output
				$defout .= "
					<div class='file'>
						<a name='$classname$bid'></a><div class='defname'>$name</div>
						From: <a href='#$classname$fid'>$filename</a> (line $line)<br />
						Context: $code<br />
						<pre>$pagecomment</pre>
					</div>
				";
			}
			$defout .= "</div>";
			$countdef = count($allblocks['define']);
			$tabdef = "<li><a href='#define'>Constants ($countdef)</a></li>";
		}
		
		// 8. Generate list of constants
		if ($_POST['Dincl']) {
			$incout = "<div id='include'>";
			if (!is_array($allblocks['include'])) $allblocks['include'] = array();
			uksort($allblocks['include'], "strnatcasecmp" );
			foreach($allblocks['include'] as $block) {
				// Prepare docs
				$pagecomment = $block->OutputComment();
				
				// Prepare information
				$bid = $block->id;
				$name = $block->GetName();
				$classname = $block->GetClass();
				$fid = dechex(crc32($block->filename));
				$filename = $block->filename;
				$line = $block->line;
				$code = $block->associatedHlCode;
				
				// Prepare for output
				$incout .= "
					<div class='file'>
						<a name='$classname$bid'></a><div class='incname'>$name</div>
						From: <a href='#$classname$fid'>$filename</a> (line $line)<br />
						Context: $code<br />
						<pre>$pagecomment</pre>
					</div>
				";
			}
			$incout .= "</div>";
			$countinc = count($allblocks['include']);
			$tabinc = "<li><a href='#include'>Includes ($countinc)</a></li>";
		}
		
		// FINAL STEP: Prepare presentation
		$date = date("F j, Y, H:i");
		$out = ("<html>
	<head>
		<title>Documentation manual generated by PHPDocFill</title>
		<!-- Include jQuery & jQuery UI 鏩 -->
		<meta charset='utf-8' />
		<link rel='stylesheet' href='http://code.jquery.com/ui/1.10.3/themes/smoothness/jquery-ui.css' />
		<link rel='stylesheet' href='http://fonts.googleapis.com/css?family=Oxygen:400,700|PT+Sans+Narrow:700' />
		<script type='text/javascript' src='http://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js'></script>
		<script type='text/javascript' src='http://code.jquery.com/ui/1.10.3/jquery-ui.js'></script>
		<style>
			body{ margin:20px;padding:0;font-size: 12px;font-family: Oxygen, Verdana, Tahoma, Arial;color: black;background: white;}
			.file{ font-family: Oxygen, Verdana; margin: 15px; }
			.filename { font: bold 150% 'PT Sans Narrow', sans-serif; color: #666; letter-spacing: 1.4px; border-bottom: 1px solid #1365ad; }
			.classname { font: bold 150% 'PT Sans Narrow', sans-serif; color: #666; letter-spacing: 1.4px; border-bottom: 1px solid #ad1365; }
			.fnname { font: bold 150% 'PT Sans Narrow', sans-serif; color: #666; letter-spacing: 1.4px; border-bottom: 1px solid #13ad65; }
			.intname { font: bold 150% 'PT Sans Narrow', sans-serif; color: #666; letter-spacing: 1.4px; border-bottom: 1px solid #f4ba3e; }
			.defname { font: bold 150% 'PT Sans Narrow', sans-serif; color: #666; letter-spacing: 1.4px; border-bottom: 1px solid #d375e3; }
			.incname { font: bold 150% 'PT Sans Narrow', sans-serif; color: #666; letter-spacing: 1.4px; border-bottom: 1px solid #7c90aa; }
			.generated { font-size: 10px; margin: 10px; text-align: center; } 
			.bold { font-weight: bold; }
			.class { display: inline-block; width: 65px; color: #1365ad; !important; }
			.function { display: inline-block; width: 65px; color: #1365ad !important; }
			.interface { display: inline-block; width: 65px; color: #1365ad !important; }
			.include { display: inline-block; width: 65px; color: #1365ad !important; }
			.define { display: inline-block; width: 65px; color: #1365ad !important; }
			.objname { font-weight: normal !important; }
			h1 { font: bold 300% 'PT Sans Narrow', sans-serif; color: #666; letter-spacing: 1.4px; border-bottom: solid 2px #1365ad; height: 45px; text-transform: uppercase; margin-top: -10px; margin-bottom: 15px; }
			a { font-family: Oxygen, Verdana; text-decoration: none; }
			pre { margin: 0; }
			.pagecomment { margin: 2px; color:#922e2e; font-family: Courier New; }
			.asterisk { color: red; }
			.Undocumented { font-style: italic; color: #777 !important; }
		</style>
	</head>
	<body>
		<h1>My Project</h1>
		<div id='tabs'>
			<ul>
				$tabfile
				$tabfunctions
				$tabclasses
				$tabint
				$tabdef
				$tabinc
			</ul>
			
			$fileout
			$fnout
			$classout
			$intout
			$defout
			$incout
		</div>
		
		<div class='generated'>
			Generated by <a href='http://cnedelcu.net/phpdocfill/' style='color:black;'>PHPDocFill</a> - $date
		</div>
		
		<script>
		jQuery.fn.extend({
		 scrollToMe: function () {
			if (jQuery(this).length < 1) return;
		   var y = jQuery(this).offset().top;
		   jQuery('html,body').animate({scrollTop: y}, 100);
		}});
		
		var elementToScroll = '';
		function DoScrollTo() {
			$(elementToScroll).scrollToMe();
		}
		
		$(function() {
			$('#tabs').tabs();
			$('a').click(function(e) {
				var link = $(this).attr('href');
				var index = 0;
				if (link.indexOf('#function') > -1) 
					index = $('#functions').index();
				else if (link.indexOf('#class') > -1) 
					index = $('#classes').index();
				else if (link.indexOf('#interface') > -1) 
					index = $('#interfaces').index();
				else if (link.indexOf('#include') > -1) 
					index = $('#include').index();
				else if (link.indexOf('#define') > -1) 
					index = $('#define').index();
				else if (link.indexOf('#file') > -1) {
					// File links: a bit different
					index = $('#files').index();
					$('#tabs').tabs('option', 'active', index-1);
					return true;
				}
				else 
					return true;

				$('#tabs').tabs('option', 'active', index-1);
				elementToScroll = '[name='+link.replace('#','')+']';
				setTimeout('DoScrollTo()',100);
			});
		});
		</script>
	</body>
</html>
		");
		
		// Save file
		if (!file_put_contents("PHPDocFill.html", ($out))) die("The file could not be saved. You might not have the proper writing permissions in the ".getcwd()." folder.");
		
		die("The manual has been generated and saved as PHPDocFill.html.<br /><a href='PHPDocFill.html' target=_blank>Click here to view this file in your browser</a>");
	}
}

/**
 * Returns true if the given $string is UTF8-encoded
 * This uses a function of the mbstring library if available, or a custom coded function
 */
function isUtf8($string) {
	if (function_exists("mb_check_encoding") && is_callable("mb_check_encoding")) {
		return mb_check_encoding($string, 'UTF8');
	}
	return preg_match('%^(?:
		  [\x09\x0A\x0D\x20-\x7E]            # ASCII
		| [\xC2-\xDF][\x80-\xBF]             # non-overlong 2-byte
		|  \xE0[\xA0-\xBF][\x80-\xBF]        # excluding overlongs
		| [\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}  # straight 3-byte
		|  \xED[\x80-\x9F][\x80-\xBF]        # excluding surrogates
		|  \xF0[\x90-\xBF][\x80-\xBF]{2}     # planes 1-3
		| [\xF1-\xF3][\x80-\xBF]{3}          # planes 4-15
		|  \xF4[\x80-\x8F][\x80-\xBF]{2}     # plane 16
	)*$%xs', $string);
}


/**
 * Highlights PHP code, with line numbers and anchors
 */
function highlight_php($string) {
	$Line = explode("\n",$string);
	for($i=1;$i<=count($Line);$i++)
		$line .= "&nbsp;<a name='line$i'>".$i."</a>&nbsp;<br>";

	$Code = highlight_string($string,true);
	$header='
		<table border="0" cellpadding="0" cellspacing="0" width="95%" style="">
			<tr>
				<td width="3%" valign="top" style="background-color: #F2F2F2;"><code>'.$line.'</code></td>
				<td width="97%" valign="top" style="background-color: white;"><div style="white-space: nowrap; overflow: auto;"><code>';

	$footer = $Code.'</div></code></td>
		</tr>
	</table>';
	return $header.$footer;
}

/**
 * Performs a recursive directory scan to find .php files
 */
function RecursiveScanDir($dir, $prefix = '') {
	$dir = rtrim($dir, '\\/');
	$result = array();
	foreach (scandir($dir) as $f) {
		if ($f !== '.' and $f !== '..') {
			if (is_dir("$dir/$f")) {
				$result = array_merge($result, RecursiveScanDir("$dir/$f", "$prefix$f/"));
			} else {
				if ( strtolower(substr(pathinfo($f,PATHINFO_EXTENSION),0,3)) == "php" && pathinfo(__FILE__,PATHINFO_BASENAME ) != pathinfo($f,PATHINFO_BASENAME ) )
					$result[] = $prefix.$f;
			}
		}
	}
	return $result;
}

/**
 * Prepares a string for HTML UTF-8 display
 */
function PrepString($str) {
	return trim(htmlentities($str,ENT_COMPAT,"UTF-8"));
}

// Verify that password has been set
if (strlen(APP_PASSWORD)<8 && REQUIRES_PASSWORD) {
	die("<div style='text-align: center; margin: 100px; font-family: Verdana, Tahoma;'><h1>Password not set</h1><b>Thank you for using PHPDocFill</b><br />Before using PHPDocFill you must set a password (at least 8 characters).<br />To do so, open PHPDocFill.php with a text editor and check the first line.</div>");
}

// Password has been entered by the user, is it right?
if (REQUIRES_PASSWORD && ($_SERVER['PHP_AUTH_USER'] != APP_USER || $_SERVER['PHP_AUTH_PW'] != APP_PASSWORD)) {
    header('WWW-Authenticate: Basic realm="ENTER THE USERNAME AND PASSWORD DEFINED WITHIN THE SCRIPT"');
    header('HTTP/1.0 401 Unauthorized');
    exit;
} 


// Route action
$action = "AJAX_".preg_replace("@[^A-Za-z]*@","",@$_POST['Action']);
if (method_exists('DocBlock',$action))
	DocBlock::$action();


?>
<html>

<head>
	<title>Document your PHP scripts</title>
	
	<!-- Include jQuery & jQuery UI -->
	<link rel="stylesheet" href="http://code.jquery.com/ui/1.10.3/themes/smoothness/jquery-ui.css" />
	<link rel="stylesheet" href="http://fonts.googleapis.com/css?family=Oxygen:400,700|PT+Sans+Narrow:700" />
	<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
	<script type="text/javascript" src="http://code.jquery.com/ui/1.10.3/jquery-ui.js"></script>
	
	
	<style>
		body{
			margin:20px;
			padding:0;
			font-size: 13px;
			font-family: Oxygen, Verdana, Tahoma, Arial;
			color: black;
			background: white;
		}
		
		#menu {
			display: block; 
			width: 175px;
			overflow-x: auto;
			border: 2px solid #DDD;
			padding: 5px;
			white-space: nowrap;
			float: left;
		}
		
		#generator {
			border: 2px solid #DDDDFF;
			margin-left: 200px;
			padding: 10px;
		}
		
		#filelist {
			/* margin-top: 10px; */
		}
		
		a {
			color: black;
		}
		
		.open {
			font-weight: bold;
			color: #1365ad;
		}
		
		#filename {
			margin-top: -5px;
		}
		
		h2 {
			padding: 5px;
			background: #1365ad;
			color: white;
		}
		
		.valid { font-weight: bold; color: green; }
		.todo { font-weight: bold; color: red; }
		.bold { font-weight: bold !important; }
		li { margin-bottom: 15px; }
		#blocklist td {
			font-size: 12px;
			border-right: 1px solid #F2F2F2;
			padding-left: 2px;
			font-family: Oxygen, Verdana;
		}
		#blocklist th {
			background: #EEE;
			border-bottom: 2px solid #888;
			font-size: 13px;
			border-right: 1px solid #CDCDCD;
			padding: 5px;
			font-family: Oxygen, Verdana;
		}
		.odd { background: #F0F0F0; }
		.dbmissing { background: #FFF0F0; }
		
		label, input { display:block; }
		textarea { width: 95%; height: 80px; }
		input.text { margin-bottom:12px; width:95%; padding: .4em; }
		fieldset { padding:0; border:0; margin-top:25px; }
		.ui-dialog .ui-state-error { padding: .3em; }
		.validateTips { border: 1px solid transparent; padding: 0.3em; }
		.inline { display: inline; }
		#menu a { text-decoration: none; }
		
		#dialog-form code {
			display: block;
			width: 580px;
			max-width: 580px;
			white-space: normal;
			word-wrap: break-word;
		}
		
		h1 {
			font: bold 300% 'PT Sans Narrow', sans-serif;
			color: #666;
			letter-spacing: 1.4px;
			border-bottom: solid 1px #000099;
			height: 70px;
			text-transform: uppercase;
			margin-top: -10px;
		}
		
		#subtitle {
			margin-top: -50px;
			margin-bottom: 20px;
			font-size: 11px;
		}
		
		#toolbar {
			padding: 4px;
			display: block;
			margin-bottom: 8px;
			font-size: 12px;
		}
		
		div.ui-tooltip {
			max-width: 800px;
		}
	</style>

</head>

<body>
	<h1 style="cursor: pointer;" onClick="javascript:document.location='<?= $_SERVER['REQUEST_URI'] ?>';">PHP<span style='color:#1365ad;'>Doc</span>Fill: easily document your PHP scripts</h1>
	
	<div id="subtitle">
		Version 1.0 Written by <a href='http://cnedelcu.net/phpdocfill/' target=_blank>Clement Nedelcu</a>. 
		For more information regarding the PHPDoc format, please consult the <a target=_blank href='http://manual.phpdoc.org/HTMLSmartyConverter/HandS/phpDocumentor/tutorial_phpDocumentor.pkg.html'>official manual</a>
	</div>
	
	<div id="toolbar" class="ui-widget-header ui-corner-all">
		<button id="btrefresh">Refresh file list</button>
		<button id="btscan">Scan all files</button>
		<button id="btgen">Generate manual</button>

		<span style="font-weight: normal; margin-left: 10px;">
			<input type="checkbox" onClick="LoadFile();" name="showsource" id="showsource" class="inline" /><label class="inline" for="showsource">Display source</label>
			<span style='color: #888;'>&nbsp;&nbsp;|||&nbsp;&nbsp;</span>  
			Elements to document [<a href='http://manual.phpdoc.org/HTMLSmartyConverter/HandS/phpDocumentor/tutorial_elements.pkg.html' target=_blank>?</a>]:
			<input type="checkbox" onClick="LoadFile(); ScanFiles(currentScanMode);" name="page" id="docpage" class="inline" checked /><label class="inline" for="docpage">Files</label>
			<input type="checkbox" onClick="LoadFile(); ScanFiles(currentScanMode);" name="func" id="docfunc" class="inline" checked /><label class="inline" for="docfunc">Functions</label> 
			<input type="checkbox" onClick="LoadFile(); ScanFiles(currentScanMode);" name="clas" id="docclas" class="inline" checked /><label class="inline" for="docclas">Classes</label> 
			<input type="checkbox" onClick="LoadFile(); ScanFiles(currentScanMode);" name="inte" id="docinte" class="inline" checked /><label class="inline" for="docinte">Interfaces</label> 
			<input type="checkbox" onClick="LoadFile(); ScanFiles(currentScanMode);" name="cons" id="doccons" class="inline" /><label class="inline" for="doccons">Constants</label> 
			<input type="checkbox" onClick="LoadFile(); ScanFiles(currentScanMode);" name="incl" id="docincl" class="inline" /><label class="inline" for="docincl">Includes</label> 
		</span>
	</div>
	
	<div id="menu">
		<div id="filelist">
			
		</div>
	</div>
	
	<div id="generator">
		<h2 id="filename">Welcome to PHPDocFill</h2>
		
		<div id="commentor">
			<div style="text-align: justify;">
				<b>Welcome to PHP<span style='color:#1365ad'>Doc</span>Fill, a simple tool that allows you to easily complete your PHP script documentation in PHPDoc format.</b><br />
				PHPDocFill was written with one simple purpose: filling up holes in your documentation. 
				Have you ever needed to write PHP code so fast that you didn't have time to properly comment it? 
				When you need to catch up, your only option is usually to open up every single PHP file and 
				figure out by yourself what needs to be completed. On top of that, if your IDE doesn't support automatic creation of PHPDoc-like 
				documentation comment blocks, you'll have to manually type down slash, asterisk, asterisk, line break, space, asterisk, and so on.
				PHPDocFill suppresses all these annoying steps and makes documenting your code a whote lot easier!
				<br /><br />
				
				<code><span style="color: #000000">
<span style="color: #FF8000">/**
<br />&nbsp;*&nbsp;A PHPDoc formatted comment is often referred to as "DocBlock".
<br />&nbsp;*&nbsp;It may contain a short description, a long description, and some tags.
<br />&nbsp;*&nbsp;Want to know more about PHPDoc? <a href='http://en.wikipedia.org/wiki/PHPDoc' target=_blank>Read on</a>.
<br />&nbsp;*/</span>
</span>
</code>

				<br /><br />
				
				<b>PHPDocFill also allows you to generate a simple HTML manual.</b><br />
				But bear in mind that when it comes to generating documentation, 
				it will not fully replace powerful tools like <a href='http://phpdoc.org/' target=_blank>PhpDocumentor</a>. 
				The manual that PHPDocFill generates consists of a simple index of the code elements (that you can select at the top of this page) 
				along with their raw descriptions and tags if any. This version of PHPDocFill does not support the parsing 
				and the proper presentation of tags. As stated before, you'll want to use a more advanced tool for better presentation.
				<br /><br />
				
				<b>Important considerations before you begin using PHPDocFill:</b>
				<ul>
					<li>
						Make <b>backups</b> of your code before you start commenting! While PHPDocFill was successfully used in a large variety of situations, 
						the author cannot guarantee that the tool won't break your code or insert comments where you didn't think it would. 
					</li>
										
					<li>
						PHPDocFill is a free open-source tool written by an independent developer, released under the GPL license version 3.
						This program is distributed in the hope that it will be useful,  
						but WITHOUT ANY WARRANTY; without even the implied warranty of
						MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
						If you intend to modify or redistribute it please make sure to consult the <a href='http://www.gnu.org/licenses/gpl.txt' target=_blank>GNU General Public Licence</a>.
					</li>
				</ul>
				
				<br />
				<b>To <span style='color:#1365ad'>get started</span>, select a file from the menu on the left.</b><br /> 
				If you don't see any item in the list, try placing PHPDocFill in a folder full of .php files.
				
				
			</div>
		</div>
	</div>
		
	<div id="dialog-form" title="Document your code">
		<span id="concernedcode"></span>
			<fieldset>
				<label for="shortdesc">Short description:</label>
				<input placeholder="You should enter at least a short description or a long description" type="text" name="shortdesc" id="shortdesc" class="text ui-widget-content ui-corner-all" /><br />
				<label for="longdesc">Long description:</label>
				<textarea style='white-space: nowrap; overflow: auto;' name="longdesc" id="longdesc" class="text ui-widget-content ui-corner-all"></textarea><br /><br />
				<label for="tags">Tags (<a tabindex="-1" href='http://manual.phpdoc.org/HTMLSmartyConverter/HandS/phpDocumentor/tutorial_tags.pkg.html' target=_blank>check tag reference</a>):</label>
				<textarea style='white-space: nowrap; overflow: auto;' name="tags" id="tags" class="text ui-widget-content ui-corner-all"></textarea>
			</fieldset>
	</div>	
	
	<script>
		var currentFile = "";
		var insertAtLine = 0;
		var codeLine = "";
		var saveMode = 0; // 0=new, 1=edit
		var docLine = 0;
		var currentIndex = 0;
		var docblockType = ""; 
		var currentScanMode = 0; // 0=just list files, 1=analyze files too
		var openNext = false;
		
		function SaveDocBlock() {
			var shortdesc = $('#shortdesc').val();
			var longdesc = $('#longdesc').val();
			var tags = $('#tags').val();
			
			// Is it valid?
			/*if (!shortdesc && !longdesc) {
				alert('This docblock is invalid: you must enter at least a short description or a long description.');
				return false;
			}*/
			
			// If this is supposed to be a page docblock, does it contain the @package tag?
			if (docblockType == "PAGE") {
				if (tags.toLowerCase().indexOf("@package") == -1) {
					alert('A page docblock must contain the @package tag, otherwise it is not considered valid.');
					return false;
				}
			}
			
			$('#concernedcode').html("<b><i>PLEASE WAIT WHILE SAVING YOUR CODE...</i></b>");
			$.post(
				"<?= pathinfo(__FILE__,PATHINFO_BASENAME ) ?>",
				{"Action": "Save", "File": decodeURIComponent(currentFile), "Line": insertAtLine, "DocLine": docLine, "Code": codeLine, "SD": shortdesc, "LD": longdesc, "TAGS": tags, "MODE": saveMode },
				function(data) {
					if (data == "OK") {
						LoadFile();
						if (currentScanMode) ScanFiles(currentScanMode);
					} else if (data == "ERR0") {
						alert('ERROR: illegal/invalid file.');
					} else if (data == "ERR1") {
						alert('ERROR: could not save comment. The file may have been modified: PhpDocFill cannot locate the code above which the comment was supposed to be inserted.');
					} else if (data == "ERR2") {
						alert('ERROR: the file could not be located. It may have been deleted?');
					} else if (data == "ERR3") {
						alert('ERROR: could not read/write to file. PhpDocFill may not have the proper permissions to write the file?');
					} else if (data == "ERR4") {
						alert('ERROR: the docblock you were editing was not found. There may be an error with the file syntax or the docblock has been removed.');
					} else {
						alert('ERROR: could not save comment. An unexpected error occurred');
					}
				}
			);
			return true;
		}
		
		function CheckCtrlEnter(e) {
			if (e.ctrlKey && e.keyCode == 13) {
				// Ctrl-Enter pressed
				SaveAndEditNext();
			}
		}
		
		function SaveAndEditNext() {
			openNext = true;
			if (!SaveDocBlock()) {
				return;
			}
			currentIndex++;
			if (!document.getElementById('action'+currentIndex)) {
				$( "#dialog-form" ).dialog( "close" );
				return;
			}
			// Switch to the next one
			//$("#action"+currentIndex).click();
		}	
						
		function LoadFile() {
			var file = currentFile;
			if (!currentFile) return;
			$( "#dialog-form" ).dialog( "close" );
			$("#filename").html( decodeURIComponent(file) );
			$("#commentor").html("Please wait, PhpDocFill is now loading the selected PHP script and analyzing code...");
			$.post(
				"<?= pathinfo(__FILE__,PATHINFO_BASENAME ) ?>",
				{
					"Action": "Analyze", "File": decodeURIComponent(file), 
					"ShowSource": c('showsource'),
					"Dpage": c('docpage'), "Dfunc": c('docfunc'), "Dclas": c('docclas'),  
					"Dinte": c('docinte'), "Dcons": c('doccons'), "Dincl": c('docincl')
				},
				function(data) {
					$("#commentor").html(data);
					$(".action").button().click(function(e) {
						$( "#dialog-form" ).dialog( "open" );
						var obj = $(this).data('obj');
						if (obj == undefined) {
							docblockType = "PAGE";
							$('#concernedcode').html ( "<b>Page DocBlock</b><br />Note: the page docblock will be inserted on the line below the first PHP code opening tag (the first <i>&lt;?php</i> found in the file). A valid page docblock must contain at least @package tag." );
							$('#longdesc').val( "" );
							$('#shortdesc').val( "" );
							$('#tags').val( "@package " );
							$('#shortdesc').focus();
							insertAtLine = 0;
							codeLine = "";
							return;
						}
						docblockType = ($(this).data("type") == "PAGE") ? "PAGE" : "";
						$('#longdesc').val( obj.longDescription );
						$('#shortdesc').val( obj.shortDescription );
						$('#tags').val( obj.tags );
						$('#concernedcode').html( obj.associatedHlCode );
						$('#shortdesc').focus();
						
						// new block or old block?
						saveMode = obj.valid ? 1 : 0;
						
						// On which line is the existing comment? (if any)
						docLine = obj.docline;
						insertAtLine = obj.line;
						codeLine = obj.associatedCode;
						
						// What is the index of the part we are editing?
						currentIndex = parseInt($(this).data('index'));
					});
					
					// Edit next?
					if (openNext) {
						openNext = false;
						$("#action"+currentIndex).click();
					}
					
				}
			);
		}
		
		function ScanFiles(mode) {
			$("#filelist").html("Please wait, PhpDocFill is now scanning the current folder and all its subfolders to find PHP scripts...");
			$.post(
				"<?= pathinfo(__FILE__,PATHINFO_BASENAME ) ?>",
				{"Action": "Scan", "Mode": mode,
					"Dpage": c('docpage'), "Dfunc": c('docfunc'), "Dclas": c('docclas'),  
					"Dinte": c('docinte'), "Dcons": c('doccons'), "Dincl": c('docincl')				
				},
				function(data) {
					$("#filelist").html(data);
				}
			);
		}
		
		function GenerateManual() {
			$('#filename').html("PHPDocFill.html");
			$('#commentor').html("Please wait while generating the manual... <br />This can take a few seconds...");
			$.post(
				"<?= pathinfo(__FILE__,PATHINFO_BASENAME ) ?>",
				{"Action": "Manual",
					"Dpage": c('docpage'), "Dfunc": c('docfunc'), "Dclas": c('docclas'),  
					"Dinte": c('docinte'), "Dcons": c('doccons'), "Dincl": c('docincl')				
				},
				function(data) {
					$('#commentor').html(data);
				}
			);
		}
		
		function c(e) { return document.getElementById(e).checked ? 1 : 0; }
		
		$(function() {
			
			$("#btrefresh").button().click(function(event) {
				currentScanMode = 0;
				ScanFiles(0);
			});
			$("#btscan").button().click(function(event) {
				currentScanMode = 1;
				ScanFiles(1);
			});
			$("#btgen").button().click(function(event) {
				//ScanFiles();
				GenerateManual();
			});
			
			$(document).delegate(".filelink","click",function(event) {
				event.preventDefault();
				$( ".filelink" ).each(function( index ) {
					$(this).removeClass("open");
				});
				$(this).addClass("open");
				currentFile = $(this).data("filename");
				LoadFile();
				return false;
			});			
			
			$( "#dialog-form" ).dialog({
				autoOpen: false,
				height: "auto",
				width: 650,
				position: { my: "center", at: "center", of: $("#filename")	},
				modal: false,
				buttons: {
					"sen": {
						'class': 'bold',
						'text': "Save & edit next",
						click: SaveAndEditNext
					},
					"sec": {
						'text': "Save & close",
						click: function() {
							openNext = false;
							if (!SaveDocBlock()) return;
							$( this ).dialog( "close" );
						}
					},
					"next": {
						'text': "Discard & edit next",
						click: function() {
							// Switch to the next one
							currentIndex++;
							if (!document.getElementById('action'+currentIndex)) {
								alert('You have reached the last item in this file.');
								$( this ).dialog( "close" );
								return;
							}
							$("#action"+currentIndex).click();
						}
					},
					"cancel": {
						'text': 'Cancel',
						click: function() {
							$( this ).dialog( "close" );
						}
					}
				},
				close: function() {
				}
			});
			$('#shortdesc').keydown(CheckCtrlEnter);
			$('#longdesc').keydown(CheckCtrlEnter);
			$('#tags').keydown(CheckCtrlEnter);
			
			ScanFiles(0);
			
			$( document ).tooltip({ show: { effect: "none", delay: 0}, content: function() {
				if ($(this).hasClass('comment')) {
					return $(this).data('comment');
				} else {
					return $(this).attr( "title" );
				}
			} });
		});
		
		
	
	</script>
</body>

</html>