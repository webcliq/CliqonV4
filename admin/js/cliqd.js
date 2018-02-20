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
		};

		/**
		 * Handles all matters related to the display of an Administrative Dashboard
		 * @param - array - a generic array object containing all the settings and data passed from the Server
		 * @return - object - a functional object
		 **/
		var dbdisplay = function(data) {

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
					iconAction(event, icn, crd, crdid) {
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
								var opts = {
									contentAjax: {
										url: crd.formurl+'?ref='+crdid,
										autoload: true, method: 'GET',	dataType: 'html'
									},
									contentSize: {
										width: crd.formwidth,
										height: crd.formheight
									},
									headerTitle: crd.title
								};
								var formPopup = Cliq.win(opts);
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
