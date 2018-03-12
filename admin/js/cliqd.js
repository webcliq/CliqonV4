/* CliqD.Js */

/** Cliqon Dashboard Functions - cliqd() **/
		
	var Cliqd = (function($) {

		// initialise
		// var shared values
		var dcfg = {
			useCaching: true,
			langcd: jlcd,
			data: {}, app: {},
			spinner: new Spinner(),
			formset: {},
			subdir: 'tmp/',
			uploadurl: '/ajax/'+jlcd+'/fileupload/',
			table: 'dbcollection',
			tabletype: 'admdashboard',
			idioms: {}, panels: {}, icons: {},
			dz: {}
		}, cfg = {};

		/**
		 * Handles all matters related to the display of an Administrative Dashboard
		 * @param - array - a generic array object containing all the settings and data passed from the Server
		 * @return - object - a functional object
		 **/
		var dbdisplay = function(data) {

			// getdashboard, dodashboard
			cfg = Cliq.config();
			console.log('Dashboard JS Loaded');

			// Intercooler.debug();
			dcfg.panels = data.panels;

			// Global Variable = Dz
			dcfg.dz = new Vue({
				el: '#clqpanel',
				data: {
					admdbicons: data.icons,
					admdbpanels: data.panels
				},
				methods: {
					iconAction(event, icn, crd, recid) {
						console.log(icn.action);
						var that = event.target;					
						switch(icn.action) {

							case "resize":
								if( $(that).hasClass('fa-compress') ){
									$(that).removeClass('fa-compress').addClass('fa-expand');
								} else {
									$(that).removeClass('fa-expand').addClass('fa-compress');
								};
								$("#"+crdid+"_content").slideToggle();
							break;

							case "minimize":
								if( $(that).hasClass('fa-minus-square-o') ){
									$(that).removeClass('fa-minus-square-o').addClass('fa-plus-square-o');
									$("#"+crdid+"_content").addClass('hidden');
								} else {
									$(that).removeClass('fa-plus-square-o').addClass('fa-minus-square-o');
									$("#"+crdid+"_content").removeClass('hidden');
								};	
								$("#"+crdid).toggleClass("min"); 
							break;

							case "settings":
								Cliqf.crudButton(recid, 'update');
							break;

							case "dummy":
							default:
								return false;
							break;
						}
						// return false;
					}
				},
				// define class=treeli-placeholder
				mounted: function() {
					
					$("#clqpanel").sortable({
						items: "> div.card",
						placeholder: "softyellow",
						forcePlaceholderSize: true,
						// forceHelperSize: true,
						handle: '.fa-arrows',
						cursor: "grabbing",
						cursorAt: {right:50},					
						opacity: 0.8,
						containment: "parent",					
						activate: function(event, ui) {
							$(ui.item).addClass('lightgray');
						},
						update: function(event, ui) {
							$(ui.item).removeClass('lightgray');
						}						
					});

					$('[data-toggle="tooltip"]').tooltip();
					$('[data-toggle="popover"]').popover();

					require(viewpath+'js/coolclock.js');
					
					// Add other functions here
					// Javascript to generate iframe, check user key is valid

					var f = check_valid_oanda_link();
					var iframe_source = document.location.protocol + "//www.oanda.com/embedded/converter/show/b2FuZGFlY2N1c2VyLy9kZWZhdWx0/" + f + "/en/";
					var iframe_style = "width: 200px; height: 250px;";

					var ifrm = document.createElement('iframe');
					ifrm.setAttribute('src', iframe_source);
					ifrm.setAttribute('style', iframe_style);
					ifrm.setAttribute('scrolling', 'no');
					ifrm.setAttribute('width', '200');
					ifrm.setAttribute('height', '350');
					ifrm.setAttribute('frameBorder', '0');

					var cc_link = document.getElementById('oanda_cc_link');
					var ecc_div = document.getElementById('oanda_ecc');

					if (cc_link) {
					    ecc_div.insertBefore(ifrm, ecc_div.firstChild);
					} else {
					    document.getElementById('oanda_ecc').appendChild(ifrm);
					}
				}
			});	
		}

		/**
		 * 
		 * @param - 
		 * @param -
		 * @return - 
		 **/
		var callErrorfunction = function() { 
			var notcompleted = Cliq.msg({buttons:false, type: 'warning', text: 'No matching records'}) 
		}

		var check_valid_oanda_link = function(){
		    var link2 = document.getElementById('oanda_cc_link');
		    if ((typeof link2 === "undefined") || (link2 == null) || (/https?\:\/\/www\.oanda\.com(\/lang\/[A-Za-z]{2})?\/currency\/converter|https?:\/\/fxtrade\.oanda\.com/.exec(link2.href) == null)) {
		        return 1;
		    } else {
		        return 0;
		    }
		}

		// explicitly return public methods when this object is instantiated
		return {
		   // outside: inside
		   dbDisplay: dbdisplay
		};   
	})(jQuery);

	/*
	.droppable({
			greedy: true,
			drop: function(e, ui) {
				e.stopImmediatePropagation();
				// Find this
				var curr = $(this).find('li.li_selected');
				var thisid = $(curr).attr('id');
				// Find previous
				var previd = $(curr).prev().attr('id');
				// Find next
				var nextid = $(curr).next().attr('id');
				Cliqu.msg({type: 'information', timeout: false, buttons: false, text: previd+' < '+thisid+' > '+nextid}); 
				return false;     
			}
	});
	*/

!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src="https://weatherwidget.io/js/widget.min.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","weatherwidget-io-js");