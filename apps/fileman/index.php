<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<title>elFinder 2.1.x source version with PHP connector</title>
		<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=2" />
		<script type="text/javascript" src="js/basket.js"></script>
		<link rel="stylesheet" type="text/css" href="css/jquery-ui.min.css" />
		<link rel="stylesheet" type="text/css" href="css/elfinder.full.css">
		<link rel="stylesheet" type="text/css" href="css/codemirror.css">
		<link rel="stylesheet" type="text/css" href="css/material.css">
	</head>
	<body style="margin: 0; padding: 0;">

		<!-- Element where elFinder will be created (REQUIRED) -->
		<div id="elfinder" style=""></div>
		<script type="text/javascript" charset="utf-8">
			var sitepath = "http://"+document.location.hostname+"/"; 
			var jspath = sitepath+"apps/fileman/js/";
			basket
			.require(
				{url: jspath+"jquery.js"},
			    {url: jspath+"jquery-ui.min.js"},
			    {url: jspath+"elfinder.min.js"},
			    {url: jspath+"codemirror.js"},
			    {url: jspath+"xml.js"},
			    {url: jspath+"css.js"},
			    {url: jspath+"javascript.js"},
			    {url: jspath+"htmlmixed.js"},
			    {url: jspath+"php.js"},
			    {url: jspath+"yaml.js"},
			    {url: jspath+"vue.js"},
			    {url: jspath+"fullscreen.js"}
			).then(function(msg) {	

		        var elf = $('#elfinder').elfinder({
		            lang: 'en',             // es
		            height: 598,
		            url : 'php/connector.minimal.php',  // connector URL (REQUIRED)
					uiOptions : {
					    // toolbar configuration
					    toolbar : [
					        ['back', 'forward'],
					        // ['reload'],
					        // ['home', 'up'],
					        ['mkdir', 'mkfile', 'upload'],
					        ['open', 'download', 'getfile'],
					        ['info'],
					        // ['quicklook'],
					        ['copy', 'cut', 'paste'],
					        
					        ['duplicate', 'rename', 'edit', 'rm'],  // , 'resize'
					        ['extract', 'archive'],
					        ['view'],['help'], 
					        ['search']
					        
					        
					    ],

					    // directories tree options
					    tree : {
					        // expand current root on init
					        openRootOnLoad : true,
					        // auto load current dir parents
					        syncTree : true
					    },

					    // navbar options
					    navbar : {
					        minWidth : 100,
					        maxWidth : 300
					    },

					    // current working directory options
					    cwd : {
					        // display parent directory in listing as ".."
					        oldSchool : false
					    }
					},
					resizable: false,
					dialogWidth: 500,
					commandsOptions: {
						edit: {
							mimes: [],
							editors: [{
								mimes: ['text/plain', 'text/html', 'text/javascript'],
								load: function(textarea) {
									var mimeType = this.file.mime;
									return CodeMirror.fromTextArea(textarea, {
										mode: mimeType,
										lineNumbers: true,
										width: 500,
										indentUnit: 2,
										theme: 'material',
										extraKeys: {
											"F11": function(cm) {
												cm.setOption("fullScreen", !cm.getOption("fullScreen"));
											},
											"Esc": function(cm) {
												if (cm.getOption("fullScreen")) cm.setOption("fullScreen", false);
											}
										}
									});
								},
								save: function(textarea, editor) {
									$(textarea).val(editor.getValue());
								}
							}]
						}
					}
		        }).elfinder('instance');   

			}, function (error) {
			    // There was an error fetching the script
			    console.log(error);
			}); 
		</script>	
	</body>
</html>
