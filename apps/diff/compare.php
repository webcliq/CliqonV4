<?php
$rootpath = "../../";
$diffpath = $rootpath."apps/diff/";
require_once($rootpath."includes/gateway.php");

if(!isset($_SESSION['CLQ_Username']) && $_REQUEST['action'] != "login") {die('Access denied');};

$pathfile = $_GET['pathfile']; // Path/File from Root or Remote

// Get the equivalent hash file from the repository - in this case our OwnCloud Server
// Write file to disk
$getfile = "http://webcliq:grouse@own.ojonet.net/remote.php/webdav/cliqonlite/".$pathfile;
$curl = new clqcurl();
$hashfile = $curl->get($getfile);
$remfile = $diffpath.'remote_file.txt';
$handle = fopen($remfile, 'w') or die('Cannot open file:  '.$remfile);
fwrite($handle, $hashfile);
fclose($handle);

echo '
<!DOCTYPE html >
	<html>
		<head>
			<meta http-equiv="Content-type" content="text/html; charset=utf-8"/>
			<title>PHP LibDiff Check</title>
			<link rel="stylesheet" href="styles.css" type="text/css" charset="utf-8"/>
		</head>
		<body>
		';

		// Include the diff class
		require_once $diffpath.'/Diff.php';

		// Include two sample files for comparison
		$a = explode("\n", file_get_contents($rootpath.$pathfile));
		$b = explode("\n", file_get_contents($remfile));

		// Options for generating the diff
		$options = array(
			//'ignoreWhitespace' => true,
			//'ignoreCase' => true,
		);

		// Initialize the diff class
		$diff = new Diff($a, $b, $options);


		echo '<h2>Side by Side:  '.$pathfile.'</h2>';
		// Generate a side by side diff
		require_once $diffpath.'/Diff/Renderer/Html/SideBySide.php';
		$renderer = new Diff_Renderer_Html_SideBySide;
		echo $diff->Render($renderer);

		/*
		echo '<h2>Inline Diff</h2>';
		// Generate an inline diff
		require_once $diffpath.'/Diff/Renderer/Html/Inline.php';
		$renderer = new Diff_Renderer_Html_Inline;
		echo $diff->render($renderer);
		*/

		echo '
		</pre>
	</body>
</html>
';

// Ends