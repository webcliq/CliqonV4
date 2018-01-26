<script>
var sitepath = "http://"+document.location.hostname+"/"; 
var jspath = sitepath+"includes/js/";
var viewpath = sitepath+"views/"; 
var jlcd = '@($idiom)', lstr = [], str = [];

// basket.clear(true);
basket
.require(  
    {url: jspath+"library.js"},
    {url: viewpath+"js/library.js", key: "init"},
    {url: jspath+"phpjs.js"}, 
    {url: jspath+"i18n/cliqon."+jlcd+".js"},   
    {url: viewpath+"js/cliq.js"}
).then(function(msg) {

    // Javascript language file load
    lstr = str[jlcd];
    // Dropzone.autoDiscover = false;
    var sessid = Cookies.get('PHPSESSID');
    

}, function (error) {
    // There was an error fetching the script
    console.log(error);
}); 

</script>