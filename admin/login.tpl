@include('partials/admheader.tpl')
</head>
<body class="app flex-row align-items-center puzzle-background ">

  <div class="container">
    <div class="row justify-content-center">
      <div class="col-md-6">
        <div class="card-group mb-0">
          <div class="card p-6 shadow">
            <h2 class="card-header text-white bg-dark">@($cfg['site']['name']) @(Q::cStr('0:Login'))</h2>
              
            <div class="card-body">
                <p class="text-muted">@(Q::cStr('528:Sign In to your account'))</p>
                <form action="#" name="loginform" id="loginform" method="POST" class="form-horizontal">

                    <div class="form-group row">
                        <label class="col-md-3 form-control-label text-right" for="username">@(Q::cStr('1:Username'))</label>
                        <div class="col-md-9">
                            <input id="username" name="username" class="form-control" placeholder="@(Q::cStr('1:Username')) .." type="text" required autofocus>
                            <span class="help-block">@(Q::cStr('529:Please enter your user name'))</span>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-md-3 form-control-label text-right" for="password">@(Q::cStr('2:Password'))</label>
                        <div class="col-md-9">
                            <input id="password" name="password" class="form-control" placeholder="************" type="password" required>
                            <span class="help-block">@(Q::cStr('530:Please enter your password'))</span>
                        </div>
                    </div>


                    <div class="form-group row">
                        <label class="col-md-3 form-control-label text-right" for="idiom">@(Q::cStr('187:Language'))</label>
                        <div class="col-md-9">
                            <select id="langcd" name="langcd" class="form-control custom-select">
                                @foreach($idioms as $lcdcode => $lcdname)
                                <option value="@($lcdcode)" @if($idiom == $lcdcode) selected @endif >@($lcdname)</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-md-3 form-control-label" for="loginbutton"></label>
                        <div class="col-md-9">
                            <button type="button" id="loginbutton" class="btn btn-danger btn-round btn-block">@(Q::cStr('0:Login'))</button>
                        </div>
                    </div>

                </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

<!-- End.Tpl -->
<script>
var jlcd = '@($idiom)', lstr = [], str = [];
var sitepath = "@raw($protocol)"+document.location.hostname+"/";
var jspath = sitepath+"includes/js/";
var viewpath = sitepath+"admin/"; 
var ctrlDown = false, ctrlKey = 17, cmdKey = 91, vKey = 86, cKey = 67;
// basket.clear(true);
basket
.require(  
    {url: jspath+"library.js"},
    {url: viewpath+"js/adminlibrary.js"},
    {url: jspath+"phpjs.js"},
    {url: jspath+"i18n/cliqon."+jlcd+".js"},  
    {url: viewpath+"js/admin.js"}, 
    {url: viewpath+"js/cliq.js"}
).then(function(msg) {

    // Javascript language file load
    lstr = str[jlcd];
    
    var form = $("#loginform");
    $.validate({
        modules : 'html5',
        errorMessagePosition : 'top' // Instead of 'inline' which is default
    });

    $("#loginbutton").on('click', function(e) { 
        e.preventDefault();   
        loginUser(e);         
    });

    form.on('keyup', function(e){
        var keycode = (e.keyCode ? e.keyCode : e.which);
        if(keycode == '13'){
            e.preventDefault();
            loginUser(e);
        }
    });     
    
}, function (error) {
    // There was an error fetching the script
    console.log(error);
}); 

/** Login User  
 * @@param - object - click event
 * @@return - redirect to admin page or display error message
 **/  
 function loginUser(e) 
 {
    // Login used the default language for the browser but is changed by the user at this point
    var urlstr = "/ajax/"+jlcd+"/login/dbuser/";
    return aja().method('POST').url(urlstr).cache(false).timeout(2500).type('json')
    .data({
        username: $('input[name="username"]').fieldValue(),
        password: $('input[name="password"]').fieldValue(),
        langcd: $('select[name="langcd"]').fieldValue()
    })
    .on('40x', function(response) {Cliq.error('Page not Found - '+urlstr+':'+response)})
    .on('500', function(response) {Cliq.error('Server Error - '+urlstr+':'+response)})
    .on('timeout', function(response) {Cliq.error('Timeout - '+urlstr+':'+response)})
    .on('success', function(response) {
        if(typeof response == 'object') {
            
            // Test NotOK - value already exists
            var match = /NotOk/.test(response.flag);
            if(!match == true) {
                uLoad("/admindesktop/"+jlcd+"/dashboard/");
            } else {
                Cliq.error("@(Q::cStr('531:Your username and password were not accepted, Please try again'))");
            }; 

        } else { Cliq.error('Response was not JSON object - '+urlstr+':'+response.msg); }
    }).go();      
 };  

</script>
</body>
</html>
