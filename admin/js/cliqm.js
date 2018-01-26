/* CliqM.Js */

/** Cliqon Menu and Footer Functions - cliqm() 
 * cliqm.x()
 *
 *
 *******************************************************************************************************************/
    var Cliqm = (function($) {

        // initialise
        // var shared values
        var mcfg = {
            useCaching: true,
            langcd: "en"
        };
        
        /**
         * Menu links in fixed top menu of admin page
         * @param - object - the click event
         * @param - object - the DOM element
         * @return - false   
         **/
        function mnuLink(e, btn)
        {
            e.stopPropagation(); e.preventDefault();
            var data = $(btn).data(); 

            // Does it go to a Page, or is it a popup
          	if(data.page == 'nopage') {
				
				switch (data.type) {
					
                    case "url": wLoad(data.href); break;

					case "tasks":
					case "events":
					case "profile":
					case "settings":
					case "email": alert(data.type); break;

					case "logout": exitSystem(data.params); break;
					case "clearlogs": clearLogs(data.params); break;
					case "clearcache": clearCache(data.params); break;

                    case "pagelink": 
                    default:                    
                        pageLink(data.page, data.type, data.params); 
                    break; 
				};

          	} else {
					
                // Standard and repeatable
                var urlstr = '/'+data.page+'/'+jlcd+'/'+data.type;
                data.table != '' ? urlstr += '/'+data.table: null ;
                if(data.tabletype != '' && data.tabletype != undefined) {
                    urlstr += '/'+data.tabletype;
                }
                if(data.params != '' && data.params != undefined) {
                    urlstr += '/'+data.params;
                }
                uLoad(urlstr); 
          	}
        }
        
        /**
         * Footer links in fixed footer of admin page
         * @param - object - the click event
         * @param - object - the DOM element
         * @return - false
         **/
        function ftrLink(e, btn)
        {
            e.stopPropagation(); e.preventDefault();
            var data = $(btn).data(); 
            // Does it go to a Page, or is it a popup
            if(data.page == 'nopage') {

                switch (data.type) {
                    
                    case "url": wLoad(data.href); break;
                    case "tomlconverter": Cliq.convertButton(data.params); break;
                    default: alert(data.type); break;                    
                };

            } else if(data.page == 'app') {

                var wh = explode(',', data.params);
                var urlstr = '/'+data.page+'/'+data.action+'/';
                var opts = {
                    headerTitle: '<span class="caps">'+data.action+'</span>',
                    contentSize: wh[0]+' '+wh[1],
                    contentIframe: {
                        src: urlstr + '?langcd=' + jlcd,
                        name: 'popupframe',
                        style: {'border': 0, 'margin':0, 'padding':0, 'overflow':'hidden'},
                        height: (wh[1] - 5)
                    }
                };
                Cliq.win(opts);             
                return true;  

            } else {

                // Standard and repeatable
                var urlstr = '/'+data.page+'/'+jlcd+'/'+data.action;
                data.table != '' ? urlstr += '/'+data.table: null ;
                data.tabletype != '' ? urlstr += '/'+data.tabletype: null ;
                data.params != '' ? urlstr += '/?params='+data.params : null ;
                uLoad(urlstr); 
            }
        }

		function otherLink(e, btn) 
		{
			Cliq.msg({text: rel, buttons: false});
		}
		
		function pageLink(page, action, params) 
		{
			var urlstr = '/'+page+'/'+jlcd+'/'+action+'/'+params+'/';
			uLoad(urlstr);
		}

		/**
		* Clear all files in /log
		**/
		function clearLogs(params)
		{
			var opts = {
				timeout: false,
				text: lstr[136],
				type: 'info',
				buttons: [
					{addClass: 'm10 mt0 btn btn-danger btn-sm mt10', text: lstr[136], onClick: function($noty) { 
						var urlstr = '/ajax/en/clearlogs/';
						$.get(urlstr).done(function(data) {
    						$noty.close();
  						});
					}},
					{addClass: 'm10 mt0 btn btn-primary btn-sm mt10', text: lstr[30], onClick: function($noty) { $noty.close(); }}
				]
			};
			Cliq.msg(opts);
		}

		/**
		* Clear Front and Admin Cache
		**/
		function clearCache(params)
		{
			var opts = {
				timeout: false,
				text: lstr[57],
				type: 'info',
				buttons: [
					{addClass: 'm10 mt0 btn btn-danger btn-sm mt10', text: lstr[57], onClick: function($noty) { 
						var urlstr = '/ajax/en/clearcache/';
						$.get(urlstr).done(function(data) {
                            basket.clear();                            
    						$noty.close();
  						});
					}},
					{addClass: 'm10 mt0 btn btn-primary btn-sm mt10', text: lstr[30], onClick: function($noty) { $noty.close(); }}
				]
			};
			Cliq.msg(opts);
		}

		function exitSystem(params)
		{
			wLoad('/ajax/'+jlcd+'/logout/');
		}
		
        // explicitly return public methods when this object is instantiated
        return {
            // outside: inside
            footerLink: ftrLink,
            menuLink: mnuLink,
            otherLink: otherLink
        };   

    })(jQuery); 