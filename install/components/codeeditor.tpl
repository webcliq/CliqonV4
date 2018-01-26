<!DOCTYPE html>
<html lang="en">
<head>
<title>Edit Config File</title>
<script type="text/javascript" src="@($rootpath)install/js/cliqstartup.js"></script>
<script type="text/javascript" src="@($rootpath)includes/js/phpjs.js"></script>
<script type="text/javascript" src="@($rootpath)includes/js/ace/ace.js"></script>
<link href="@($rootpath)install/css/style.css" rel="stylesheet" type="text/css">
<style type="text/css" media="screen">
    #editor { 
        position: absolute;
        top: 54px;
        right: 0;
        bottom: 0;
        left: 0;
        padding: 5px;
        border-top: 1px solid #ccc;
    }
</style>
</head>
<body style="background-color: lightblue;">
<div class="pad"><button type="button" onClick="saveContents()" style="float: right;">@($save)</button></div>
<br />
<div id="editor">@raw($contents)</div>
<script>
var sitepath = "http://"+document.location.hostname+"/"; 
var jlcd = '@raw($lcd)', url = '/install/@raw($lcd)/';
    
var editor = ace.edit("editor");
editor.setTheme("ace/theme/dawn");
editor.session.setMode("ace/mode/ini");
	
function saveContents() {
    var filecontents = editor.getValue();
    $.ajax({
        url: url+'saveconfigfile/',
        data: {'filecontents': rawurlencode(filecontents)}, 
        type: 'POST',
        success: function(data) {
            return success(data[0]);
        },
        failure: function(data) {
            return error(data[0]);
        }
    });

};
</script>
</body>
</html>