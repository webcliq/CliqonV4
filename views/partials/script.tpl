<script>
var sitepath = "@raw($protocol)"+document.location.hostname+"/"; 
var jspath = sitepath+"includes/js/";
var viewpath = sitepath+"views/"; 
var jlcd = '@($idiom)', lstr = [], str = [];

// basket.clear(true);
basket
.require(  
    {url: jspath+"library.js"},
    {url: jspath+"jinplace.js"},
    {url: viewpath+"js/library.js", key: "init"},
    {url: jspath+"phpjs.js"}, 
    {url: jspath+"i18n/cliqon."+jlcd+".js"},   
    {url: viewpath+"js/cliq.js"}
).then(function(msg) {

    // Javascript language file load
    lstr = str[jlcd];
    // Dropzone.autoDiscover = false;
    var sessid = Cookies.get('PHPSESSID');

    $('.contenteditable').jinplace({submitFunction: function(opts, value) {return postContent(opts, value)}});    

    // Quark
    // for(t=document.querySelectorAll`*`,i=t.length;i--;)for(s=t[i].classList,c=s.length;c--;)z=s[c].split`-`,u=z[1],t[i].style[z[0]]=~~u?u+'px':u;

}, function (error) {
    // There was an error fetching the script
    console.log(error);
}); 

var postContent = function(dta, value) {

    aja().method('POST').url(dta.url).cache(false).timeout(2500).type('json').data({
        'value': value,
        'reference': dta.object
    })
    .on('40x', function(response) {Cliq.error('Page not Found - '+urlstr+':'+response);})
    .on('500', function(response) {Cliq.error('Server Error - '+urlstr+':'+response);})
    .on('timeout', function(response) {Cliq.error('Timeout - '+urlstr+':'+response);})
    .on('success', function(response) {
        if(typeof response == 'object') {
            // Test NotOK - value already exists
            var match = /NotOk/.test(response.flag);
            if(!match == true) {  
                Cliq.success(response.msg); 
                $('#id_'+dta.object).html(response.data);
            } else { Cliq.error('Ajax function returned error NotOk - '+JSON.stringify(response)); };
        } else { Cliq.error('Response was not JSON object - '+urlstr+':'+response.msg); };
    }).go(); 

    return value;
}

</script>