/**
 * Cliq.Js
 *
 */

 /** Cliqon Functions - cliq() 
  * cliq.x() - app and utility functions, including:
  *
  *  notyMsg = msg(usroptions)
  *  jsPanelWin = win(usroptions)
  *
  ******************************************************************************************************************/

    var Cliq = (function($) {

        // initialise
        // var shared values
        var cfg = {
            useCaching: true,
            langcd: jlcd,
            sitepath: "http://"+document.location.hostname+"/",
            spinner: new Spinner(),
            subdir: 'tmp/', uploadurl: '/api/'+jlcd+'/fileupload/dbuser/', filescollection: 'file',
            opts: {}, data: {}, app: {}
        };

        var _set = function(key, value)
        {
            cfg[key] = value;
            return cfg[key];
        };

        var _get = function(key)
        {
            return cfg[key];
        };

        var _config = function()
        {
            return cfg;
        };

            /** Generic Help Button
             *
             **/
            var helpButton = function()
            {
                var urlstr = "/api/"+jlcd+"/gethelp/"+cfg.table+"/"+cfg.tabletype+"/";
                aja().method('GET').url(urlstr).cache(false).timeout(2500).type('json')
                .data({type:'help', table: 'dbitem'}) 
                .on('40x', function(response) { error('Page not Found - '+urlstr+':'+response); })
                .on('500', function(response) { error('Server Error - '+urlstr+':'+response); })
                .on('timeout', function(response){ error('Timeout - '+urlstr+':'+response); })
                .on('200', function(response) {
                    if(typeof response == 'object')
                    {
                        // Test NotOK - value already exists
                        var match = /NotOk/.test(response.flag);
                        if(match !== true) {
                            var opts = {
                                content: response.html,
                                contentSize: {
                                    width: 400,
                                    height: 400
                                },
                                headerTitle: '<span class="">'+lstr[4]+'</span>'
                            };
                            var helpPopup = win(opts);  
                        } else { error( 'Ajax function returned error NotOk - '+urlstr+':'+JSON.stringify(response)); }; 
                    } else { error('Response was not JSON object - '+urlstr+':'+response.msg); };
                }).go();   
            }

        /** General Display Utilities
         * 
         * clqAjax()
         * msg()
         * - success()
         * - error()
         * win()
         * popup()
         *******************************************************************************************************/ 

            /** Try and produce a generic Cliq AJA Ajax function
             *
             * @param - string - URL
             * @param - object - any data
             * @return - Response as JSON 
             **/
            var clqAjax = function(urlstr, dta)
            {
                aja().method('GET').url(urlstr).cache(false).timeout(2500).type('json').data(dta)
                .on('40x', function(response) { error('Page not Found - '+urlstr+':'+response)})
                .on('500', function(response) { error('Server Error - '+urlstr+':'+response)})
                .on('timeout', function(response){ error('Timeout - '+urlstr+':'+response)})
                .on('200', function(response) {

                    if(typeof response == 'object')
                    {
                        // Test NotOK - value already exists
                        var match = /NotOk/.test(response.flag);
                        if(!match == true) {
                            cfg.result = response.data;
                        } else { // Error
                            error('Ajax function returned error NotOk - '+urlstr+':'+JSON.stringify(response))
                        }; 

                    } else { error('Response was not JSON object - '+urlstr+':'+response.msg); }
                }).go();               
            }  

            var success = function(text) { return msg({type: 'success', buttons: false, text: text});  }  

            var error = function(text) { return msg({type: 'warning', buttons: false, text: text}); }  
            
            /**
             * Noty Alert box
             * @param - object - Usroptions that overwrite defaults
             **/
            var msg = function(usroptions) 
            {
                // usroptions = array
                var tpl = `
                    <div class="noty_message minh3">
                        <div class="pad center">
                           <h3 class="noty_text bluec"></h3> 
                        </div>
                        <div class="noty_close"></div>
                    </div>
                `;
                var options = {
                    'text': '',
                    'layout':'topCenter',
                    'timeout': 5000,
                    // success (light green), error (pink), warning (orangey cream), information (lilac), notification (lilac)
                    'type':'success', 
                    'buttons':  [
                        {addClass: 'm10 mt0 btn btn-success btn-sm', text: 'Close', onClick: function($noty) { $noty.close(); }}
                    ],
                    'template': tpl,
                     callback: {
                        onShow: function() {},
                        afterShow: function() {},
                        onClose: function() {},
                        afterClose: function() {},
                        onCloseClick: function() {}
                    }
                };
                options = array_replace(options, usroptions);
                var $noty = noty(options);
                return $noty;
            }

        // explicitly return public methods when this object is instantiated
        return {
            // outside: inside
            set: _set,
            get: _get,
            config: _config,
            clqAjax: clqAjax,
            error:error,
            success: success,
            msg: msg
        };    
    })(jQuery); 


function razr(code, data) {

    //escape "@@" into one "@"
    code = code.split("@@").join("\1");
    var parts = code.split("@");
    var buff = parts.map(function (a, b) {
        if (!b) {
            return JSON.stringify(a);
        } /* end if */

        var l = a.split(/([<\n"])/),
            code = l[0];
        return code + "+\n" + JSON.stringify(l.slice(1).join(""));
    }).join("+");

    buff = buff.replace(/(for\(\s*)(\w+)(\s+in)(\s+)(\w+)(\s*\))(\+\r?\n)([^\n]+)\+\n/gm,
        "(function lamb(){var b=[]; $1$2$3$4 $5$6 { if( $5.hasOwnProperty($2)){ $2=$5[$2]; b.push($8);}}; return b.join(''); }())+ ");
    with(data) {
        return eval(buff).split("\1").join("@");
    }

}; /* end razr() */