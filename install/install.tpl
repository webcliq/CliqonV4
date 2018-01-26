@include('partials/head.tpl')

</head>
<body>
<div id="container" class="container fw" style="margin-top:20px; position:relative;">
<div id="loading" style="" class="loadinghide" ></div>
<table align="center" border="0" cellpadding="0" cellspacing="0">
<tr><td> 
<!-- Smart Wizard -->
      <h2>@raw($istr[0])</h2> 
        <div id="wizard" class="swMain">
            <ul>
              <li><a href="#step-1">
                <label class="stepNumber">1</label>
                <span class="stepDesc">
                   @raw($istr[1])<br />
                   <small>@raw($istr[2])</small>
                </span>
              </a></li>
                <li><a href="#step-2">
                <label class="stepNumber">2</label>
                <span class="stepDesc">
                   @raw($istr[3])<br />
                   <small>@raw($istr[4])</small>
                </span>
            </a></li>
                <li><a href="#step-3">
                <label class="stepNumber">3</label>
                <span class="stepDesc">
                   @raw($istr[5])<br />
                   <small>@raw($istr[6])</small>
                </span>                   
             </a></li>
                <li><a href="#step-4">
                <label class="stepNumber">4</label>
                <span class="stepDesc">
                   @raw($istr[7])<br />
                   <small>@raw($istr[8])</small>
                </span>                   
            </a></li>
            </ul>

            <div id="step-1">   
            <h2 class="StepTitle">@raw($istr[42])</h2>
            
            <div class="pad">
            <ul type="disk">
              <li>@raw($istr[11])</li>
              <li>@raw($istr[12])</li>
              <li>@raw($istr[13])</li>
              <li>@raw($istr[14])</li>
            </ul>

            <p class=""><strong>@raw($istr[11])</strong></p>
            <p>@raw($istr[51])</p>


            <p class=""><strong>@raw($istr[15])</strong></p>
            <p>@raw($istr[52])</p>

            <p><strong>@raw($istr[13])</strong></p>
            <p>@raw($istr[53])</p>

            <p><strong>@raw($istr[14])</strong></p>
            <p>@raw($istr[54])</p> 
            <p>@raw($istr[55])</p>

            <div style="width:120px;">
              <button type="button" id="directorybutton">@raw($istr[16])</button>
            </div>
            <div style="float:left; margin-left:140px; margin-top:-35px; width: 400px;" class="clqtable" id="directories">
            </div>
          </div>
        </div>


        <div id="step-2">
            <h2 class="StepTitle">@raw($istr[18])</h2>  
            
            <div class="pad">
            <ul type="disk">
              <li>@raw($istr[19])</li>
            </ul>

            <p class=""><strong>@raw($istr[10])</strong></p>
            <p>@raw($istr[56])</p>
            <p>@raw($istr[57])</p>
            <p>@raw($istr[58])</p>
            <p>@raw($istr[59])</p>

            <!-- Form -->
            <form name="configform" id="configform" method="POST" action="#" novalidate>
          
              <!-- 1st Column -->
              <div class="form" style="width:200px; float:left;"   /> 
              
                  <p class="inline-field">
                      <label class="label">@raw($istr[21]):</label>
                      <select class="field size4" style="padding: 8px 3px;" name="type" id="type" autofocus="true" tabindex=1 >
                        <option value="mysql" selected="selected">MySQL</option>
                        <option value="sqlite">SQLite</option>
                        <option value="pgsql">Postgres</option>
                        <option value="pgsql">Firebird</option>
                      </select>
                  </p>           
                  <p class="inline-field">
                      <label class="label">@raw($istr[22]):</label>
                      <input type="text" class="field size4 placeholder" name="dbname" id="dbname" placeholder="cliqon" required="required" tabindex=3 />
                  </p>

                  <p class="inline-field">
                      <label class="label">@raw($istr[76]):</label>
                      <input type="text" class="field size4" name="rootuser" id="rootuser" placeholder="root" tabindex=5 />
                  </p>

                  <p class="inline-field">
                      <label class="label">@raw($istr[23]):</label>
                      <input type="text" class="field size4" name="username" id="username" placeholder="user" required="required"  tabindex=7 />
                  </p>

                  <hr />

                  <p class="inline-field">
                      <label class="label">@raw($istr[69]):</label>
                      <input type="text" class="field size4" name="name" id="name" placeholder="Cliqon" required="required" tabindex=9 />
                  </p>
                  <p class="inline-field">
                      <label class="label">@raw($istr[70]):</label>
                      <input type="text" class="field size4" name="description" id="description" placeholder="Web application framework"  tabindex=11 />
                  </p>   
                  <p class="inline-field">
                      <label class="label">@raw($istr[46]):</label>
                      <input type="text" class="field size4" name="siteurl" id="siteurl" placeholder="@raw($istr[48])" required="required"  tabindex=13 />
                  </p>  

              </div>
              
              <!-- 2nd Column -->
              <div class="form" style="width:500px; float:left;">

                  <p class="inline-field">
                      <label class="label">@raw($istr[26]):</label>
                      <input type="text" class="field size4" name="server" id="server" value="localhost" required="required"  tabindex=2 />
                  </p>
                  <p class="inline-field">
                      <label class="label">@raw($istr[27]):</label>
                      <input type="text" class="field size4" name="portno" id="portno" value="3306" required="required"  tabindex=4 />
                  </p>
                  <p class="inline-field">
                      <label class="label">@raw($istr[77]):</label>
                      <input type="text" class="field size4" name="rootpassword" id="rootpassword" placeholder="root password" tabindex=6 />
                  </p>
                  <p class="inline-field">
                      <label class="label">@raw($istr[24]):</label>
                      <input type="text" class="field size4" name="password" id="password" placeholder="password"  tabindex=8 />
                  </p>

                  <hr />

                  <p class="inline-field">
                      <label class="label">@raw($istr[29]):</label>
                      <input type="text" class="field" name="idiomarray" id="idiomarray" style="width:500px;" value="en|English,es|Español,de|Deutsch"  tabindex=10 />
                  </p>
                  <p class="inline-field">
                      <label class="label">@raw($istr[30]):</label>
                      <input type="text" class="field" name="idiomflags" id="idiomflags" style="width:500px;" value="en|en.gif,es|es.gif,de|de.gif"  tabindex=12 />
                  </p>
                  <p class="inline-field">
                      <label class="label">@raw($istr[47]):</label>
                      <input type="email" class="field size4" name="adminemail" id="adminemail" placeholder="@raw($istr[49])" tabindex=14 />
                  </p> 
         
              </div>

              <!-- Form Buttons -->
              <div style="clear:both;">
                <button type="button" id="previewconfigform" >@raw($istr[9])</button>
                <button type="button" id="submitconfigform" >@raw($istr[10])</button>
                <button type="button" id="editconfigform" >@raw($istr[50])</button>
              </div>
              <!-- End Form Buttons -->
            
            </form> <!-- End Form --> 

          </div>       
        </div>  

        <div id="step-3">
          <h2 class="StepTitle">@raw($istr[5])</h2> 
          <div class="pad">
            <ul type="disk">
              <li>@raw($istr[66])</li>
              <li>@raw($istr[31])</li>
              <li>@raw($istr[32])</li>
            </ul>

            <p class=""><strong>@raw($istr[66])</strong></p>
            <p>@raw($istr[67])</p>

            <div style="width:140px;"><button id="databasecreatebutton">@raw($istr[68])</button></div>
            <div style="float:left; margin-left:140px; margin-top:-30px;" id="dbresult"></div><br />

            <p class=""><strong>@raw($istr[31])</strong></p>
            <p>@raw($istr[60])</p>
            <p>@raw($istr[61])</p>

            <div style="width:120px;"><button id="tablecreatebutton">@raw($istr[33])</button></div>
            <div style="float:left; margin-left:140px; margin-top:-30px;" id="tableresult"></div><br />

            <p style=""><strong>@raw($istr[71])</strong></p>

            <form name="adminuserform" id="adminuserform">
              <p class="inline-field">
                <label class="label">@raw($istr[25]):</label><br />
                <input type="text" class="field size3" name="adminuser" id="adminuser" placeholder="admin" />
              </p>

              <p class="inline-field">  
                <label class="label">@raw($istr[28]):</label><br />
                <input type="text" class="field size3" name="adminpassword" id="adminpassword" placeholder="********" />
              </p>
            </form>

            <div style="width:120px;">
              <button type="button" id="dataimportbutton">@raw($istr[62])</button>
            </div>
            <div style="float:left; margin-left:140px; margin-top:-30px;" id="dataimport" ></div>

          </div>                                      
        </div>

        <div id="step-4">
          <h2 class="StepTitle">@raw($istr[35])</h2>    
          <div class="pad">
            <ul type="disk">
              <li>@raw($istr[36])</li>
              <li>@raw($istr[37])</li>
              <li>@raw($istr[38])</li>
            </ul>

            <p class=""><strong>@raw($istr[39])</strong></p>
            <p>@raw($istr[63])</p>

            <p><strong>@raw($istr[40])</strong></p>
            <p>@raw($istr[64])</p>

            <p><strong>@raw($istr[41])</strong></p>
            <p>@raw($istr[65])</p> 
          </div>                            
        </div>
        </div>
<!-- End SmartWizard Content -->        
        
</td></tr>
</table>
</div>  
<script>
var sitepath = "http://"+document.location.hostname+"/"; 
var jspath = sitepath+"install/js/"; 
var includepath = sitepath+"includes/js/"; 
var jlcd = '@raw($lcd)', str = [], url = '/install/@raw($lcd)/';
var lstr = {
  0: '@raw($istr[43])'
};
// basket.clear(true);
basket
.remove('cliq')
.require(
  {url: includepath+"phpjs.js"},
  {url: jspath+"cliqstartup.js", key:"cliq", skipCache: true},   
  {url: jspath+"smartwizard.js"}

).then(function(msg) {

  $(this).ajaxStart(function() {
    $('#loading').removeClass('loadinghide');
    $('#loading').show();
  }).ajaxStop(function() {
    $('#loading').fadeOut(500);
  });   

  // Smart Wizard   
  $('#wizard').smartWizard({
    labelNext:'@raw($istr[43])',
    labelPrevious:'@raw($istr[44])',
    labelFinish:'@raw($istr[45])',
  });

  $('#directorybutton').on('click', function(e) {
      e.preventDefault;
      e.stopImmediatePropagation;
      $('#directories').load(url + 'directories');
  });

  $('#previewconfigform').on('click', function(e) {
      
      e.preventDefault;
      e.stopImmediatePropagation;

      var postdata = $('#configform').serialize();
      postdata = str_replace('&', '<br />', postdata);
      postdata = str_replace('%5B%5D','', postdata);
      postdata = str_replace('%2C',',', postdata);
      postdata = str_replace('%7C','|', postdata);
      postdata = str_replace('%C3%B1','n', postdata);
                      
      notyMsg({text:postdata});  
      return false;  
  });

  $('#submitconfigform').on('click', function(e) {

      e.preventDefault; e.stopImmediatePropagation;

      var postdata = $('#configform').serialize();
      // notyMsg({text: 'url+'?'+postdata});

      $.ajax({
          url: url+'createconfigfile/',
          data: postdata, type: 'POST',
          success: function(data) {
              return success(data[0]);
          },
          failure: function(data) {
              return error(data[0]);
          }
      });  

      return false;
  });

  $('#editconfigform').on('click', function(e) {
      TINY.box.show({iframe: url+'editconfigfile/?subaction=read', top: 40, boxid:'frameless', width: 640, height:740, fixed:false, opacity:20});
  });

  $('.close').on('click', function() {
      var id = $(this).attr('rel');
      $('#' + id).hide();
  });

  $('#databasecreatebutton').on('click', function() {
      $('#dbresult').html('<img src="'+sitepath+'install/img/loader.gif" style="" />');
      $('#dbresult').load(url + 'createdatabase/');
  });
  
  $('#tablecreatebutton').on('click', function() {
      $('#tableresult').html('<img src="'+sitepath+'install/img/loader.gif" style="" />');
      $('#tableresult').load(url + 'createtables/');
  });    
  
  $('#dataimportbutton').on('click', function() {
      $('#dataimport').html('<img src="'+sitepath+'install/img/ loader.gif" style="" />');
      $('#dataimport').load(url + 'createbasedata/?adminuser='+$('input[name="adminuser"]').val()+'&adminpassword='+$('input[name="adminpassword"]').val());
  });

  $('.buttonFinish').on('click', function(e) {
      $.ajax({
          url: url+'deleteinstaller/', type: 'POST',
          success: function(data) {
              uLoad('http://' + document.location.hostname + '/admindesktop/'+jlcd+'/dashboard/');
          },
          failure: function(data) {
              return error(data[0]);
          }
      }); 
  });


  
}, function (error) {
    // There was an error fetching the script
    console.log(error);
});
</script>
</body>
</html>

