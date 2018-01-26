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
                           <h5 class="noty_text bluec"></h5> 
                        </div>
                        <div class="noty_close"></div>
                    </div>
                `;
                var options = {
                    'text': '',
                    'layout':'topCenter',
                    'theme':'bootstrapTheme',
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

            /**
             * JSPanel Window - supports HTML content direct, HTML content from existing Div, Content by Ajax and iFrame
             * @param - object - Usroptions that overwrite defaults
             **/
            var win = function(usroptions)
            {
                cfg.dp++;
                var thisid = 'jsPanel-'+cfg.dp;
        
                // drag - fa-arrows-alt
                var tb = `
                <div class="bluec col">
                    <span class="col-6 txt-right mr-20 right">
                        <i class="fa fa-fw fa-print pointer" data-action="print"></i>
                        <i class="fa fa-fw fa-expand pointer" data-action="maximize"></i>
                        <i class="fa fa-fw fa-compress pointer" data-action="normalize"></i>
                        <i class="fa fa-fw fa-close pointer" data-action="close"></i>
                    </span>
                    <span class="col-6 left ml-20 txt-left bold">`+usroptions.headerTitle+`</span>
                </div>
                `;

                var htb = [{
                    item:     tb,
                    event:    "click",
                    callback: function(event){ 
                        
                        var action = $(event.target).data('action');

                        switch(action) {
                            case "print":
                                return event.data.content.print()
                            break;

                            case "close":
                                return event.data.close();
                            break;

                            case "maximize":
                                return event.data.maximize();
                            break;

                            case "normalize":
                                return event.data.normalize();
                            break;

                            default:
                                return false;
                            break;
                        }
                    }
                }];

                var options = {
                    container:       'body',
                    content:         false,
                    contentAjax:     false,
                    contentIframe:   false,
                    contentOverflow: {"overflow-x": "hidden", "overflow-y": "scroll"},
                    contentSize:     {
                        width: 600,
                        height: 610
                    },
                    custom:          false,
                    dblclicks:       false,
                    footerToolbar:  false,
                    headerControls: {controls: 'none'},
                    headerLogo: '<img src="'+sitepath+'admin/img/logo.png" class="h30 ml10 mt10 mb0" id="panelHdr" />',
                    headerRemove:  false,
                    template:  false,
                    headerTitle: false,
                    headerToolbar: htb,
                    theme: 'bootstrap-default',
                    id: thisid,
                    maximizedMargin: {
                        top:    85,
                        right:  25,
                        bottom: 25,
                        left:   25
                    },
                    minimizeTo:         true,
                    position:           'center', // all other defaults are set in jsPanel.position()
                    dragit: {
                        handles:  "#panelHdr",
                        opacity: 0.8
                    },
                    resizeit: {
                        handles:   'n, e, s, w, ne, se, sw, nw',
                        minWidth:  400,
                        minHeight: 400
                    }
                };
                options = array_replace(options, usroptions);
                $.jsPanel({config: options});  
                $('#'+thisid).css('z-index', 10000+cfg.dp);
            }  

            var popup = function(options) 
            {
                      
                var thisid = uniqid();
                var tb = `
                <div class="e30" id="popup_box">
                    <div class="card shadow round4" style="width: 100%" id="`+thisid+`_modal">
                        <div class="card-header">`+options.headerTitle+`</div>
                        <div class="card-body" id="`+thisid+`_content">`+lstr[144]+` ....</div>
                        <div class="card-footer">
                            <button type="button" id="close_button" class="btn btn-sm btn-primary">`+lstr[30]+`</button>
                            <button type="button" id="cancel_button" class="btn btn-sm">`+lstr[17]+`</button>
                        </div>
                    </div>    
                </div>        
                `;  

                $(options.parent).append(tb);
                    new Tether({
                        element: '#popup_box',
                        target: options.parent,
                        attachment: 'middle center',
                        targetAttachment: 'middle center',
                        targetOffset: '20% 0%',
                        constraints: [
                            {
                              to: 'window',
                              pin: ['top', 'bottom']
                            },
                            {
                              to: 'scrollParent',
                              pin: ['top', 'bottom']
                            }
                          ],
                        targetModifier: 'visible'
                    });            
                    showpopup();

                $('#close_button').on('click', function() {
                    hidepopup();
                });
               
                aja().method('GET').url(options.url).cache(false).timeout(25000).type('json')
                .data(options.data)
                .on('40x', function(response) { Cliq.error('Page not Found - '+options.url+':'+response)})
                .on('500', function(response) { Cliq.error('Server Error - '+options.url+':'+response)})
                .on('timeout', function(response){ Cliq.error('Timeout - '+options.url+':'+response)})
                .on('200', function(response) {

                    if(typeof response == 'object')
                    {
                        // Test NotOK - value already exists
                        var match = /NotOk/.test(response.flag);
                        if(!match == true) {
                            // $('#'+thisid+'_content').html(response.data);
                        } else { // Error
                            Cliq.error('Ajax function returned error NotOk - '+options.url+':'+JSON.stringify(response))
                        }; 

                    } else { Cliq.error('Response was not JSON object - '+options.url+':'+response.msg); }
                }).go();    
            }

            function showpopup()
            {
                $("#popup_box").fadeToggle();
                $("#popup_box").css({"visibility":"visible","display":"block"});
            }

            function hidepopup()
            {
                $("#popup_box").fadeToggle(500);
                $("#popup_box").css({"visibility":"hidden","display":"none"});
            }

	
        // explicitly return public methods when this object is instantiated
        return {
            // outside: inside
            set: _set,
            get: _get,
            config: _config,
            popup: popup,
            clqAjax: clqAjax,
            win: win,
            error:error,
            success: success,
            msg: msg
        };    
    })(jQuery); 


