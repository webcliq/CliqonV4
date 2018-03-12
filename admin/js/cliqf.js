/* CliqF.Js */

/** Cliqon Form Related Functions - cliqf() 
 * cliqf.x() - app and utility functions, including:
 ******************************************************************************************************************/

    var Cliqf = (function($) {

        // initialise
        // var shared values
        var fcfg = {
        	dz: new Object,
            resetForm: {},
            cancelPageForm: {},
            formset: {},
            panels: {}, opts: {},
            jeditid: '',
            ceditor: {}, jeditor: {}, teditor: {},
            popup_window: ''
        }, cfg = {};

        var _set = function(key,value)
        {
            fcfg[key] = value;
            return fcfg[key];
        }

        var _get = function(key)
        {
            return fcfg[key];
        }

        /** Generic CRUD Form Routines and Subroutines
         * crudButton()
         * vueForm()
         * - formMounted()
         * - frmBtn()
         * 
         * 
         *************************************************************************************/	

            /** Create or Edit a Record
             * 
             * @param - number - 0 or valid record number
             * @param - string - insert or update
             * @return - HTML display a form
             **/
	         var crudButton = function(recid, action) 
	         {           
	            
	            cfg = Cliq.config();
	            cfg.recid = recid;
	            cfg.action = action;

                // Formid acts as HTML ID and switcher
                // Maybe we could switch this on the fly according to width of screen etc.
                // Think about User Agent Here

                if(cfg.formtype == "pageform") {
                	var urlstr = '/admindesktop/'+jlcd+'/page/'+cfg.table+'/'+cfg.tabletype+'/?action=pageform&recid='+recid;
                	uload(urlstr);
                } else {
            		var urlstr = '/ajax/'+jlcd+'/getform/'+cfg.table+'/'+cfg.tabletype+'/';
		            aja().method('GET').url(urlstr).cache(false).timeout(2500).type('json')
		            .data({
		                displaytype: cfg.displaytype,
		                formtype: cfg.formtype,
		                action: action,
		                recid: recid
		            })
		            .on('40x', function(response) {Cliq.error('Page not Found - '+urlstr+':'+response);})
		            .on('500', function(response) {Cliq.error('Server Error - '+urlstr+':'+response);})
		            .on('timeout', function(response) {Cliq.error('Timeout - '+urlstr+':'+response);})
		            .on('success', function(response) {

		                if(typeof response == 'object')
		                {
		                    // Test NotOK - value already exists
		                    var match = /NotOk/.test(response.flag);
		                    if(!match == true) {
		                    	
		                    	// Post process returned data
			                    cfg.data = JSON.parse(response.script);	
			                    cfg.action = response.action; // c or u	                    	
		                    	if(cfg.formtype == "columnform") {
									$('#columnform').empty().html(response.html);
		                    	} else { // Popupform
				            		
		                    		// Create better formatted title
		                    		var title ="";
		                    		recid == 0 ? title = lstr[16] : title = lstr[20]+' - '+recid ;
				            		var opts = {
					                    content: '<div class="col mr10 pad">'+response.html+'</div>',
					                    contentSize: {
					                        width: response.model.width,
					                        height: response.model.height
					                    },
					                    paneltype: 'modal',
					                    headerTitle: '<span class="">'+title+': '+cfg.tabletype+'</span>'
					                };
					                var formPopup = Cliq.win(opts);
		                    	}
								vueForm();								

		                    } else { Cliq.error('Ajax function returned error NotOk - '+urlstr+':'+JSON.stringify(response)); }; 
		                } else { Cliq.error('Response was not JSON object - '+urlstr+':'+response.msg); }
		            }).go(); 
                } // If Formid != Pageform ends
	         }

            /** vueForm
             * Attaches Vue to the Form
             * @param - 
             * @internal - sets cfg.df
             **/
	         var vueForm = function() {       		 		
        		cfg.df = new Vue({
                    el:cfg.data.el,
                    data:cfg.data.data,
                    methods: {
                        // Used with Cliqon 3 field dropdown dat3
                        plunk: function(id) { 
                            var d = $('#day_'+id).val();
                            var m = $('#month_'+id).val();
                            var y = $('#year_'+id).val();
                            Vue.set(cfg.df, 'd_'+id, d+'-'+m+'-'+y);        
                        },

                        // Other button functions

                        // All forms
                        previewbutton: function(evt) {
                        	frmBtn(evt, 'previewbutton');
                        },

                        // All forms
                        resetbutton: function(evt) {
                        	frmBtn(evt, 'resetbutton');
                        },

                        // Appears on pageform only
                        cancelbutton: function(evt) {
                        	frmBtn(evt, 'cancelbutton');
                        },

                        // Submit Button is not a submit
                        submitbutton: function(evt) {
                        	frmBtn(evt, 'submitbutton');
                        },

                        // Click Icon Handlers
                        clickicon: function(event) {
                        	var action = $(event.target).data('action');
                        	frmBtn(event.target, action);
                        },

                        // Image handlers
	                    onFileChange(e) {
	                    	e.stopImmediatePropagation();
	                    	var fldid = $(e.target).data('fldid');
	                        var files = e.target.files || e.dataTransfer.files;
	                        if(!files.length) {
	                           return; 
	                        };
	                        this.createImage(files[0], fldid);
	                    },
	                    createImage(file, fldid) {
	                        var d_image = new Image();
	                        var reader = new FileReader();
	                        var vm = this;

	                        reader.onload = (e) => {
	                            vm.d_image = e.target.result;
	                        };
	                        reader.readAsDataURL(file);
	                    },
	                    removeImage: function (e) {        
	                        e.stopImmediatePropagation();
	                        this.d_image = '';
	                    },

                        // More here

                        modelChange: function(e) {
                        	var table = e.target.value;
                        	var tabletypes = [];
                        	$("select[data-name='tabletype'] option").each(function() {
    							if(stristr($(this).data('table'), table) === false) {
    								$(this).remove();
    							};
							});
                        }

                    },
                    mounted: function() {
                        formMounted(cfg);
                    }
                });
	         }

            /** formMounted()
             * Instantiates a variety of functions and responses to events that are associated with a Form
             * that has been published as a Vue template
             * @param - 
             * @return - 
             **/
			 var formMounted = function(cliqcfg)
			 {
				cfg = cliqcfg;
				var id = 'dataform'; 

				// Execute any code that had to be sent with the page
				$.globalEval(cfg.data.mounted);

				// HTML5 Text Types
					$('input[type="text"], input[type="email"], input[type="url"]').each(function() {
		           		
		           		var fldid = $(this).attr('id');
		           		var thisfld = $('#'+fldid);

						if( thisfld.hasClass('nextref') ) {		
							modInput(fldid, 'getnextref','');
						};
						
						if( thisfld.hasClass('nextid') ) {
							modInput(fldid, 'getnextid', '');
						};
						
						if( thisfld.hasClass('nextentry') ) {
							var prefix = thisfld.data('prefix');
							modInput(fldid, 'getnextentry', prefix);
						};
						
						$('.isunique').on('blur', function() {
							modInput(fldid, 'isunique', '');	
						});

                        $('.slugified').on('blur', function() {
                            var toslug = $(thisfld).val();
                            $(thisfld).val( $.slugify(toslug) );
                            modInput(fldid, 'isunique', '');    
                        });

						if( thisfld.hasClass('autocomplete') ) {
							var urlstr = $(thisfld).data('url');
							$(thisfld).bootcomplete({
        						url: urlstr,
        						method: 'GET',
        						minLength: 3
    						});
						};

					});		

				// For Textareas
					$('textarea.json, textarea.toml').each(function() {

		           		var fldid = $(this).attr('id');
		           		var thisfld = $('#'+fldid);

						if( thisfld.hasClass('json') ) {	
							var jsonte = document.getElementById(fldid);
							fcfg.jeditor = CodeMirror.fromTextArea(jsonte, {
								lineNumbers: true,
								mode: "javascript"							
							});
							var jsoncontent = thisfld.val();
							fcfg.jeditor.getDoc().setValue(jsoncontent);
						};

						if( thisfld.hasClass('toml') ) {	
							var tomledid = document.getElementById(fldid);
							fcfg.ceditor = CodeMirror.fromTextArea(tomledid,{
								lineNumbers: true,
								mode: "toml"
							});
							var tomlcontent = thisfld.val();
							fcfg.ceditor.getDoc().setValue(tomlcontent);
						};
					});

				// File and Image

					$('div.dropzone').each(function() {
						
		           		var fldid = $(this).attr('id');
		           		var thisfld = $('#'+fldid);

						fcfg.dz = thisfld.dropzone({
							url: cfg.uploadurl,					
							init: function() {
								cfg.subdir = $(this).data('subdir');
								cfg.uploadurl = $(this).data('uploadurl');
								cfg.filescollection = $(this).data('filescollection');
							},
							autoProcessQueue: true,
							headers: {
								subdir: cfg.subdir
							},
                            createImageThumbnails: true,
                            resizeHeight: 120,
							paramName: cfg.filescollection,
							maxFilesize: 1, maxFiles: 1,
							success: function(data) {
								if(data.status == 'success') {
									Vue.set(cfg.df, fldid, data.name);
								} else {
									msg({buttons:false, type:'danger', text:data.warnings.toString()});
								}
								$(fcfg.dz).find('.dz-progress').addClass('hide');
							}
						});				
					});

				// Tab Handling

				// Level Handling

					$('.accesslevel').each(function(e) {
		           		
		           		var fldid = $(this).attr('id');

		           		// Get
		           		var xval = $("#"+fldid+"_val").val();
						var defval = explode(':', xval);

						$("#"+fldid+"_r").val(defval[0]);
						$("#"+fldid+"_w").val(defval[1]);
						$("#"+fldid+"_d").val(defval[2]);
						
						// Set
						$(".spinboxes").on("change", function() {
							var curval = $("#"+fldid+"_r").val() + ":" + $("#"+fldid+"_w").val() + ":" + $("#"+fldid+"_d").val();
							$("#"+fldid+"_val").val(curval);
							Vue.set(cfg.df, fldid, curval);
						}); 
					});

				// Tags 
					$('.tagit').tagit({
						availableTags: false,
						singleField: true,
						tagLimit: 10						
					});	

				// Repeater
		        	$('#dataform').repeater({
			            // start with an empty list of repeaters. Set your first (and only) "data-repeater-item" with style="display:none;" and pass the following configuration flag
			            initEmpty: false,
			            // "show" is called just after an item is added.  The item is hidden at this point.  If a show callback is not given the item will have $(this).show() called on it.
			            show: function () {
			                $(this).slideDown();
			            },
			            // "hide" is called when a user clicks on a data-repeater-delete element.  The item is still visible.  "hide" is passed a function as its first argument which will properly remove the item. "hide" allows for a confirmation step, to send a delete request to the server, etc.  If a hide callback is not given the item will be deleted.
			            hide: function (deleteElement) {
			                if(confirm('Are you sure you want to delete this element?')) {
			                    $(this).slideUp(deleteElement);
			                }
			            },
			            // You can use this if you need to manually re-index the list for example if you are using a drag and drop library to reorder list items.
			            ready: function (setIndexes) {
			                return false;
			            },
			            // Removes the delete button from the first list item, defaults to false.
			            isFirstItemUndeletable: true
			        })

				// JSONeditors if exist 

					$('#dataform div[data-type=jsoneditor]').each(function(e) {
						fcfg.jeditid = $(this).attr('id');
						var options = {
								search: false,
								mode: "code",
							    modes: ["code", "form", "tree"]
						};
						if(cfg.action == 'u') { // Update
							var jeditordata = cfg.data.data[fcfg.jeditid];
						} else { // Create
							var jsondata = $('input[name="'+fcfg.jeditid+'"]').val();
							var jeditordata = JSON.parse(jsondata);
						}
						fcfg.jeditor = createJSONEditor('#'+fcfg.jeditid, options);
						fcfg.jeditor.set(jeditordata);		
					});	

				// Date handling including Datepicker
					
					$('select.dateselect').on('change select mouseover', function(e) {
						var id = explode('_', $(this).attr('id'))[1];
						var d = $('#day_'+id).val();
						var m = $('#month_'+id).val();
						var y = $('#year_'+id).val();
						Vue.set(cfg.df, 'd_'+id, d+'-'+m+'-'+y);
					});
					
					$('.datepicker').each(function(e) {
						var fldid = $(this).attr('id');
						var dp = $(this).datepicker({
							format: 'dd-mm-yyyy',
							uiLibrary: 'bootstrap4',
         					iconsLibrary: 'fontawesome',
         					locale: jlcd+'-'+jlcd		
						});
					})
	
				// Autocomplete - In PHP

			    // Password
					$.hook('confirmpassword').on('blur', function(e) {
						var id = $(this).attr('id');
						var p1 = $('#'+id).val();
						var p2 = $('#'+id+'_confirm').val();
						if(p1 != p2) {
							$('#'+id).empty().focus();
							Cliq.msg({buttons:false, type:'error', text:lstr[137]});
						}
					})

				// Slider
					$('.slider').slider({
						tooltip: 'show',
						ticks: [0,1,2,3,4,5],
						ticks_tooltip: true
					});
					$('.slider').on('slide', function(evt) {
						Vue.set(cfg.df, evt.target.id, evt.value);
					})

				// Reduced width Trumbo editor
					$.trumbowyg.svgPath = sitepath+'admin/css/icons.svg';
					$('#'+id+' textarea.texteditor').each(function() {
						$(this).trumbowyg({
						    btns: [
						        ['formatting'],
						        ['foreColor', 'backColor'],
						        ['strong', 'em', 'horizontalRule'],
						        ['link'],
						        ['insertImage'],
						        ['justifyLeft', 'justifyCenter', 'justifyRight'],
						        ['unorderedList', 'orderedList'],
						        ['viewHTML','fullscreen']
						    ],
						    autogrow: true,
						    autogrowOnEnter: true
						});	
		           	});

                // TinyMCE Editor

                    var tinypath = jspath+'tinymce';
                    tinymce.baseURL = tinypath;
                    fcfg.teditor = $('.tiny').tinymce({
                        document_base_url: tinypath,
                        script_url: tinypath,
                        height: 435,
                        theme: 'modern',
                        skin: 'cliqon',
                        plugins: [
                            'advlist code codemirror anchor autosave charmap colorpicker contextmenu hr image imagetools insertdatetime lists link nonbreaking paste print preview searchreplace table template textcolor textpattern visualblocks visualchars wordcount fullscreen'
                        ],
                        toolbar1: 'translate | undo redo | insert | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image | forecolor backcolor | print preview | code fullscreen',
                        image_advtab: true,
                        codemirror: {
                            indentOnInit: true, // Whether or not to indent code on init.
                            fullscreen: true,   // Default setting is false
                            path: jspath+'codemirror', // Path to CodeMirror distribution
                            config: {           // CodeMirror config object
                               mode: 'application/x-httpd-php',
                               lineNumbers: false
                            },
                            width: 800,         // Default value is 800
                            height: 600,        // Default value is 550
                            saveCursorPosition: true,    // Insert caret marker
                            jsFiles: [          // Additional JS files to load
                               'mode/clike/clike.js',
                               'mode/php/php.js'
                            ]
                        },
                        setup: function(editor) {
                            // Translate - to be done
                            editor.addButton('translate', {
                                icon: 'moon',
                                // image: '',
                                tooltip: 'Translate Record',
                                onclick: function () {
                                    var fldid = $(this).attr('id'); // $did = v-model
                                    var textfrom; 
                                    $.each(cfg.idioms, function(lcdcode, lcdname) {
                                        if(lcdcode != jlcd) {
                                            textfrom = cfg.df.$data[fldid+'_'+jlcd]; 
                                            $.ajax({
                                                url: "https://api.microsofttranslator.com/V2/Ajax.svc/Translate",
                                                dataType: "jsonp", jsonp: "oncomplete", crossDomain: true,
                                                data: {appId: cfg.bingkey, from: jlcd, to: lcdcode, contentType: "text/plain", text: textfrom},
                                                success: function(data, status){
                                                    // console.log(data);
                                                    cfg.df.$data[fldid+'_'+lcdcode] = data;
                                                }
                                            });
                                        }
                                    });
                                }
                            })                                              
                        } // End Editor Setup
                    }); 

				// Checkbox handling
					$('.checkbox0').each(function() {
						var fid = $(this).attr('name');
						var vals = explode(',', $('input[id="'+fid+'"]').getValue());			
						$('input[name="'+fid+'"]').fieldArray(vals);


					});

					$('.cliqcheckbox').on('change', function(e) {
						var fid = $(this).attr('name');
						var newvals = $('input[name="'+fid+'"]').getValue();
						Vue.set(cfg.df, fid, newvals);
					});
		
				// Click Icon Handlers
					$('.translatebutton').on('click', function(e) {
						var fldid = $(this).attr('id'); // $did = v-model
						var textfrom; 
						$.each(cfg.idioms, function(lcdcode, lcdname) {
							if(lcdcode != jlcd) {
								textfrom = cfg.df.$data[fldid+'_'+jlcd];
								$.ajax({
									url: "https://api.microsofttranslator.com/V2/Ajax.svc/Translate",
									dataType: "jsonp", jsonp: "oncomplete",	crossDomain: true,
									data: {appId: cfg.bingkey, from: jlcd, to: lcdcode, contentType: "text/plain", text: textfrom},
									success: function(data, status) {
										cfg.df.$data[fldid+'_'+lcdcode] = data;
									},
									error: function(xhr, status, text) {
										var response = $.parseJSON(xhr.responseText);
										Cliq.error(JSON.stringify(response));
									}
								});
							}
						})
					})

				// Miscellaneous
			    	// $('.currency').maskMoney();		

                    $('.form-inline').each(function(e) {
                        var fldid = $(this).attr('id');
                        var thisfld = $('#'+fldid);

                        if( $(thisfld).hasClass('watch') ) {
                            switch(fldid) {
                                case "model":
                                    $('select[data-id="c_parent"], select[data-id="c_category"]').on('change', function() {
                                        var c_category = $('select[data-id="c_category"]').getValue();
                                        var c_parent = $('select[data-id="c_parent"]').getValue();
                                        $('select[data-id="c_reference"]').setValue(c_parent+'_'+c_category);
                                        return cfg.df.$data.c_reference = c_parent+'_'+c_category;
                                    });
                                break;

                                // Other cases of watch here if required
                            } // End switch
                        }; // End 'watch'

                        // Other cases here if required
                    });		
			 }	

            /** formButtons
             * Handles all Form Buttons - submit, reset, cancel, preview etc.
             * @param - object - the Event object
             * @param - string - button action
             * @return - action
             **/
	         var frmBtn = function(evt, action)
	         {	            
	            
	            switch(action) {
	                case "submitbutton": 
                        // Make sure button is type = button, not type = submit !!
						evt.preventDefault();
						var urlstr = $(cfg.data.el).attr('action');						
						var frmData = getFormData(false);
						$.ajax({
							url: urlstr, data: frmData,
							cache: false, contentType: false, processData: false,
							type: 'POST', async: false, timeout: 25000,
							success: handleResponse, error: handleError
						});           
	                break;

	                case "previewbutton":
						var frmData = getFormData(false);
                        cfg.spinner.stop();
						var tbl = `<div class="container maxh30 scrollable">
                            <table class=\"table table-sm table-bordered table-striped\">
                        `;
						// Display the key/value pairs
						for(var pair of frmData.entries()) {
                            
                            switch(pair[0]) {

                            	case "d_image":
		                            tbl += `<tr style=\"font-weight:normal; font-size: 12px;\">
		                                <td class=\"text-right orangec e30\">`+pair[0]+`</td>
		                                <td class=\"text-left bluec e70\"><img src=\"`+rawurldecode(pair[1])+`\" class=\"h120\" /></td>
		                            </tr>`;
                            	break;

                            	// Exclude fields
                            	case "token": case "c_level":
                            		$tbl += "";
                            	break;

                            	default:
		                            tbl += `<tr style=\"font-weight:normal; font-size: 12px;\">
		                                <td class=\"text-right orangec e30\">`+pair[0]+`</td>
		                                <td class=\"text-left bluec e70\"><pre>`+rawurldecode(pair[1])+`</pre></td>
		                            </tr>`;
                            	break;
                            }

						}
						tbl += `</table></div>`;

                        Cliq.win({
                            content: tbl,
                            headerTitle: lstr[7]                            
                        });                   
	                break;

	                case "resetbutton":
						if(cfg.formid == 'pageform') {
							var q = cfg.resetPageForm;
							pageForm(q.table, q.tabletype, q.action, q.recid);
						} else { // columnform
							$('#dataform').clearform();
						}               
	                break;

	                case "cancelbutton":
	                	collectionAction(store.session('contenttype'), table, tabletype);
	                break;

	                // Good test
	                case "lookupcompany": 
	                	var options = {
	                		// url: '/ajax/'+jlcd+'/getcompanies/dbdirectory/',
	                		headerTitle: 'Lookup Company',
	                		target: evt,
	                		data: {},
	                		parent: '#columnform'
	                	}
	                	Clq.popup(options);
	                break;

	                // Directory
	                case "geolocate": Cliq.success('Geo Locate'); break;
	                case "getcoords": Cliq.success('Get Coordinates'); break;
	                case "gotowebsite": Cliq.success('Go to Website'); break;
	                case "makeemail": Cliq.success('Send an email to this Company'); break;
	                case "getlink": 
	                	
	                	var urlstr = 'http://google.com/';
	                	fcfg.popup_window = $.popupWindow(urlstr, {
							height:500,width:800,toolbar:false,scrollbars:false,status:false,resizable:true,
							left:50,top:80,center:false,createNew:true,name:'weblink',location:false,menubar:false
	                	});

	                break;
	                
	                // More here

	                // Transfer a value from a Select to an associated Input field
	                case "transferval":
	                	var fldid = $(evt).data('id');
	                	var newval = $('select[data-id="'+fldid+'"]').getValue();
	                	Vue.set(cfg.df, fldid, newval);
	                break;

	                case "maintainval": return addOption(evt); break;

	                default: Cliq.success(action); break;

	            }
	         }			        
			
	        /** Add a new value to an Options or Categories list
			 *
			 *
			 **/
			 var addOption = function(evt)
			 {
	            var fldid = $(evt).data('id');
	            var listname = $(evt).data('listname');
	            cfg = Cliq.config();
	            var tpl = `
				<form name="subpopupform" id="subpopupform" action="#" method="POST" class="pad">
					<h5>`+lstr[15]+`</h5>
					<input type="hidden" name="fldid" value="`+fldid+`" />
					<input type="hidden" name="listname" value="`+listname+`" />
					<div class="form-group row">
						<label for="x_value" class="col-sm-3 col-form-label text-right">`+lstr[100]+`</label>
						<div class="col-sm-9">
						<input type="text" class="form-control " name="x_value" id="x_value" placeholder="value">
						</div>
					</div>`;

				  $.each(cfg.idioms, function(lcdcode, lcdname){
				  	tpl += `
					<div class="form-group row">
			    		<label for="x_label_`+lcdcode+`" class="col-sm-3 col-form-label text-right">`+lcdname+`</label>
			    		<div class="col-sm-9">
			    		<input type="text" class="form-control" id="x_label_`+lcdcode+`" name="x_label_`+lcdcode+`" placeholder="">
			    		</div>
			  		</div>
				  	`;
				  });

				tpl += `</form>`;
	            return Cliq.msg({
	                buttons:  [
	                    {addClass: 'm10 mt10 btn btn-danger btn-sm', text: lstr[30], onClick: function($noty) {    
	                        $noty.close(); 
	                    }},
	                    {addClass: 'm10 mt10 btn btn-primary btn-sm', text: lstr[16], onClick: function($noty) { 
	                        
	                        var frmData = $('#subpopupform').formHash();    
	                        var urlstr = '/ajax/'+jlcd+'/addnewoption/dbcollection/list/';
	                        aja().method('POST').url(urlstr).cache(false).timeout(2500).type('json').data(frmData)
				            .on('40x', function(response) {Cliq.error('Page not Found - '+urlstr+':'+response);})
				            .on('500', function(response) {Cliq.error('Server Error - '+urlstr+':'+response);})
				            .on('timeout', function(response) {Cliq.error('Timeout - '+urlstr+':'+response);})
				            .on('success', function(response) {

	                            if(typeof response == 'object') {
	                                // Test NotOK - value already exists
	                                var match = /NotOk/.test(response.flag);
	                                if(!match == true) {  
	                                	Vue.set(cfg.df, fldid, response.newval); 
	                                	$noty.close();                            
			                        } else { Cliq.error('Ajax function returned error NotOk - '+urlstr+':'+JSON.stringify(response)); };
			                    } else { Cliq.error('Response was not JSON object - '+urlstr+':'+response.msg); };
			                }).go();                             
	                    }}                                              
	                ],
	                timeout: false,
	                type: 'success',
	                closeWith: ['button'],
	                text: '',
	                template: tpl     
	            }); 		 	
			 }

            /** Delete button - invoked from Grid or List row
             * 
             * @param - string - record ID to be deleted
             * @return - JSON with response
             **/
	         var deleteButton = function(recid) 
	         {
	        	cfg = Cliq.config();
	        	var params = {
	        		table: cfg.table,
	        		tabletype: cfg.tabletype,
	            	type: 'delete', // deletebefore
	                recid: recid,
	                action: 'deleterecord',
	                before: '',
	                displaytype: cfg.displaytype,
	                msg: lstr[27]+': '+recid   
	            };
	            return deleteRecords(params);
	         }

            /** Delete records - invoked from Grid or List row
             * 
             * support different types of delete, apart from by Id
             * @param - array - Parameters: see deleteButton for template
             * @return - JSON with response
             **/
	         var deleteRecords = function(params) 
	         {
	            cfg = Cliq.config();
	            return Cliq.msg({
	                buttons:  [
	                    {addClass: 'm10 mt10 btn btn-success btn-sm', text: lstr[30], onClick: function($noty) {    
	                        $noty.close(); 
	                    }},
	                    {addClass: 'm10 mt10 btn btn-danger btn-sm', text: lstr[114], onClick: function($noty) { 
	                        
	                        var urlstr = '/ajax/'+jlcd+'/'+params.action+'/'+params.table+'/'+params.tabletype+'/';
	                        aja().method('POST').url(urlstr).cache(false).timeout(2500).type('json')
	                        .data({
	                            displaytype: params.displaytype,
	                            action: params.type,
	                            recid: params.recid,
	                            before: params.before
	                        })
				            .on('40x', function(response) {Cliq.error('Page not Found - '+urlstr+':'+response);})
				            .on('500', function(response) {Cliq.error('Server Error - '+urlstr+':'+response);})
				            .on('timeout', function(response) {Cliq.error('Timeout - '+urlstr+':'+response);})
				            .on('success', function(response) {

	                            if(typeof response == 'object') {
	                                // Test NotOK - value already exists
	                                var match = /NotOk/.test(response.flag);
	                                if(!match == true) {  
	                                    
	                                    var originalnoty = $noty;
	                                    Cliq.success(lstr[146]+': ' + JSON.stringify(response.msg));
	                                    originalnoty.close();

	                                    switch(params.displaytype) {
	                                    	
	                                    	case "datagrid": cfg.dg.reload(); break;
	                                    	case "datatable": Cliq.loadTableData(); break;
	                                    	default: reLoad(); break;
	                                    };	                                    
	                                                        
			                        } else { Cliq.error('Ajax function returned error NotOk - '+urlstr+':'+JSON.stringify(response)); };
			                    } else { Cliq.error('Response was not JSON object - '+urlstr+':'+response.msg); };
			                }).go();                             
	                    }}                                              
	                ],
	                timeout: false,
	                closeWith: ['button'],
	                type: 'warning',
	                text: params.msg      
	            }); 
	         }
	        
	    /** Specialised Form routines and Buttons
	     *
	     * restoreButton(recid)
	     * creatorButton(recid, action)
	     * contentButton()
	     * codeButton()
	     * - makePanel()
	     * - addPanel()
	     * selectButton()
	     * yesnoButton()
	     * checkboxButton()
	     *
	     ************************************************************************************************/

	     	/** Reverts a dbitem record from dbarchive
	     	 *
	     	 * @param - string - 
	     	 * @return
	     	 **/
             var restoreButton = function(recid)
             {
	            cfg = Cliq.config();
	            return Cliq.msg({
	                buttons:  [
	                    {addClass: 'm10 mt10 btn btn-success btn-sm', text: lstr[30], onClick: function($noty) {    
	                        $noty.close(); 
	                    }},
	                    {addClass: 'm10 mt10 btn btn-danger btn-sm', text: lstr[148], onClick: function($noty) { 
	                        
	                        var urlstr = '/ajax/'+cfg.langcd+'/restorerecord/'+cfg.table+'/'+cfg.tabletype+'/';
	                        aja().method('POST').url(urlstr).cache(false).timeout(2500).type('json')
	                        .data({
	                            displaytype: cfg.displaytype,
	                            action: 'restore',
	                            recid: recid
	                        })
				            .on('40x', function(response) {Cliq.error('Page not Found - '+urlstr+':'+response);})
				            .on('500', function(response) {Cliq.error('Server Error - '+urlstr+':'+response);})
				            .on('timeout', function(response) {Cliq.error('Timeout - '+urlstr+':'+response);})
				            .on('success', function(response) {

	                            if(typeof response == 'object') {
	                                // Test NotOK - value already exists
	                                var match = /NotOk/.test(response.flag);
	                                if(!match == true) {  
	                                    
	                                    var originalnoty = $noty;
	                                    Cliq.success(lstr[147]+': ' + JSON.stringify(response.msg));
	                                    originalnoty.close();
										Cliq.loadTableData();                                     
	                                                        
			                        } else { Cliq.error('Ajax function returned error NotOk - '+urlstr+':'+JSON.stringify(response)); };
			                    } else { Cliq.error('Response was not JSON object - '+urlstr+':'+response.msg); };
			                }).go();                             
	                    }}                                              
	                ],
	                timeout: false,
	                type: 'info',
	                text: lstr[148]+': '+recid       
	            });
             }

            /** Create or Edit a Record using Record Creator
             * 
             * @param - number - 0 or valid record number
             * @param - string - insert or update
             * @return - HTML display a form
             **/
	         var creatorButton = function(recid, action) 
	         {            
	            cfg = Cliq.config(); cfg.recid = recid; cfg.action = action;
        		var urlstr = '/ajax/'+jlcd+'/getcreatorform/'+cfg.table+'/';
	            aja().method('GET').url(urlstr).cache(false).timeout(2500).type('json')
	            .data({action: action, recid: recid, table: cfg.table})
	            .on('40x', function(response) {Cliq.error('Page not Found - '+urlstr+':'+response);})
	            .on('500', function(response) {Cliq.error('Server Error - '+urlstr+':'+response);})
	            .on('timeout', function(response) {Cliq.error('Timeout - '+urlstr+':'+response);})
	            .on('success', function(response) {
	                if(typeof response == 'object')
	                {
	                    // Test NotOK - value already exists
	                    var match = /NotOk/.test(response.flag);
	                    if(!match == true) {
	                    	
	                    	// Post process returned data
		                    cfg.opts = JSON.parse(response.opts);	
		                    cfg.action = response.action; // c or u	                    	
							$('#columnform').empty().html(response.html);	
			        		cfg.df = new Vue({
			                    el:cfg.opts.el,
			                    data:cfg.opts.data,
			                    methods: {	
			                        submitbutton: function(evt) {
				                        // Make sure button is type = button, not type = submit !!
										evt.preventDefault();
				                        var urlstr = '/ajax/'+cfg.langcd+'/postcreatorform/'+cfg.table+'/';
										var frmData = getFormData(false);
										$.ajax({
											url: urlstr, data: frmData,
											cache: false, contentType: false, processData: false,
											type: 'POST', async: false, timeout: 25000,
											success: handleResponse, error: handleError
										});       	
			                        }
			                    },
			                    mounted: function() {
			                    	var thisfld = $('#text');
									var tomledid = document.getElementById('text');
									fcfg.ceditor = CodeMirror.fromTextArea(tomledid,{
										lineNumbers: true,
										mode: "toml"
									});
									fcfg.ceditor.setSize({width: '100%', height: '600px'});
									var tomlcontent = thisfld.val();
									fcfg.ceditor.getDoc().setValue(tomlcontent);
			                    }
			                });													
	                    } else { Cliq.error('Ajax function returned error NotOk - '+urlstr+':'+JSON.stringify(response)); }; 
	                } else { Cliq.error('Response was not JSON object - '+urlstr+':'+response.msg); }
	            }).go(); 
	         }

            /** Content button, invoked from Grid or List row
             * 
             * This function provides popup windows with TinyMCE as an editor
             * @param - integer - Record ID
             * @param - string - default multi-lingual text field
             * @return - 
             **/     
	         var contentButton = function(recid) 
	         {
	            
	            cfg = Cliq.config();

                var urlstr = "/ajax/"+jlcd+"/editcontent/"+cfg.table+"/"+cfg.tabletype+"/";
                aja().method('GET').url(urlstr).cache(false).timeout(2500).type('json')
                .data({
	                displaytype: cfg.displaytype, // datalist
	                viewtype: 'popupview',
	                recid: recid,
	                fldname: 'd_text'
	            })
	            .on('40x', function(response) {Cliq.error('Page not Found - '+urlstr+':'+response);})
	            .on('500', function(response) {Cliq.error('Server Error - '+urlstr+':'+response);})
	            .on('timeout', function(response) {Cliq.error('Timeout - '+urlstr+':'+response);})
	            .on('success', function(response) {

                    if(typeof response == 'object')
                    {
                        // Test NotOK - value already exists
                        var match = /NotOk/.test(response.flag);
                        if(!match == true) {
                            var data = response.data;
                            var opts = {
                                content: response.html,
                                contentSize: {
                                    width: 860,
                                    height: 642
                                },
                                headerTitle: '<span class="">'+lstr[31]+'</span>',
                                callback: function() {

                                    sitepath = "http://"+document.location.hostname+"/";

					        		// Manage Tabs
				        			$('#contenttabs a:first').tab('show');
									$('#contenttabs a').click(function (e) {
									   e.preventDefault()
									   $(this).tab('show')
									});

									$('textarea.rte').on('focusin', function(e) {
										if ($(e.target).closest(".mce-window").length) {
											e.stopImmediatePropagation();
										}
									}); 

                                    var tinypath = jspath+'tinymce';
									tinymce.baseURL = tinypath;
									$('.rte').tinymce({
										document_base_url: tinypath,
										script_url: tinypath,
										theme: 'modern',
										skin: 'cliqon',
                                        content_css: sitepath+'views/css/cliqon_theme.css',  // 
                                        content_style: 'html {padding: 10px 20px; min-height: 400px;}',
										plugins: [
											'advlist code codemirror anchor autosave charmap colorpicker contextmenu hr image imagetools insertdatetime lists link nonbreaking paste print preview searchreplace table template textcolor textpattern visualblocks visualchars'
										], // wordcount
										toolbar1: 'savebutton translate | undo redo | insert | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image | forecolor backcolor | print preview | code',
										// templates: '/dir/templates.php' - URL return JSON output
										image_advtab: true,
										external_filemanager_path: jspath + 'tinymce/plugins/filemanager/',
										filemanager_title:'Filemanager',
										external_plugins: { 'filemanager' : jspath + 'tinymce/plugins/filemanager/plugin.min.js'},
										codemirror: {
										    indentOnInit: true, // Whether or not to indent code on init.
										    fullscreen: true,   // Default setting is false
										    path: jspath+'codemirror', // Path to CodeMirror distribution
										    config: {           // CodeMirror config object
										       mode: 'application/x-httpd-php',
										       lineNumbers: false
										    },
										    width: 800,         // Default value is 800
										    height: 600,        // Default value is 550
										    saveCursorPosition: true,    // Insert caret marker
										    jsFiles: [          // Additional JS files to load
										       'mode/clike/clike.js',
										       'mode/php/php.js'
										    ]
										},
										setup: function(editor) {
											// Save to Database
											editor.addButton('savebutton', {
											  	icon: 'save',
                                                classes: 'bypassChanges',
											  	tooltip: 'Save Record',
											  	onclick: function (e) {													
                                                    e.stopImmediatePropagation();

                                                    // Get any tinymce Editors if exist and update the Vue instance with Tiny editor content
                                                    
                                                    var tinyeditors = tinymce.EditorManager.editors;
                                                    var frmData = new FormData();
                                                    frmData.set('recid', recid);
                                                    frmData.set('fldname', 'd_text');
                                                    $.each(tinyeditors, function(i, teditor) {
                                                        var fld = trim(teditor.id, '_te'); // works fine
                                                        var val = tinymce.get(teditor.id).getContent();
                                                        frmData.set(fld, rawurlencode(val));
                                                    }); 

                                                    var urlstr = '/ajax/'+cfg.langcd+'/savecontent/'+cfg.table+'/'+cfg.tabletype+'/';                                                       
                                                    $.ajax({
                                                        url: urlstr, data: frmData,
                                                        cache: false, contentType: false, processData: false,
                                                        type: 'POST', async: false, timeout: 25000,
                                                        success: function(response, statusText, xhr) {
                                                            if(typeof response == 'object') {
                                                                // Test NotOK - value already exists
                                                                var match = /NotOk/.test(response.flag);
                                                                if(!match == true) {
                                                                    // Stops erroneous leave page error
                                                                    $('#dataform').submit(function(e) {return false;});
                                                                    for (var i = tinymce.editors.length - 1 ; i > -1 ; i--) {
                                                                        var ed_id = tinymce.editors[i].id;
                                                                        tinyMCE.execCommand("mceRemoveEditor", true, ed_id);
                                                                    }; 
                                                                    Cliq.success(lstr[10]);
                                                                    window.jsPanel.closeChildpanels("body"); 
                                                                    return;
                                                                } else { // Error
                                                                    Cliq.error('Ajax function returned error NotOk - '+response.msg);
                                                                }; 

                                                            } else {
                                                                Cliq.error('Response was not JSON object - '+JSON.stringify(response));
                                                            }
                                                        }, 
                                                        error: function(xhr, status, text) {
                                                            Cliq.error('Error saving Text to Database - '+urlstr+':'+text);
                                                        }
                                                    }); 	
													return true;					     	
											  	}
											}),
											// Translate
                                            editor.addButton('translate', {
                                                icon: 'moon',
                                                // image: '',
                                                tooltip: 'Translate Record',
                                                onclick: function (e) {
                                                    var tinyeditors = tinymce.EditorManager.editors;
                                                    var vals = [];
                                                    $.each(tinyeditors, function(i, teditor) {
                                                        var fld = trim(teditor.id, '_te'); // works fine
                                                        var val = tinymce.get(teditor.id).getContent();
                                                        vals[fld] = val;      
                                                    });
                                                    var textfrom = vals[data.fldname+'_'+jlcd];
                                                    
                                                    $.each(data.idioms, function(lcdcode, lcdname) {
                                                        if(lcdcode != jlcd) {
                                                            
                                                            $.ajax({
                                                                url: "https://api.microsofttranslator.com/V2/Ajax.svc/Translate",
                                                                dataType: "jsonp", jsonp: "oncomplete", crossDomain: true,
                                                                data: {appId: data.bingkey, from: jlcd, to: lcdcode, contentType: "text/plain", text: textfrom},
                                                                success: function(ttxt, status){
                                                                    $.each(tinyeditors, function(i, teditor) {
                                                                        var fld = trim(teditor.id, '_te'); // works fine
                                                                        if(fld == data.fldname+'_'+lcdcode) {
                                                                            tinymce.get(teditor.id).setContent(ttxt);
                                                                        }
                                                                    });                                          
                                                                }
                                                            }); 
                                                        }
                                                    }); // End for each languuage plus text
                                                }
                                            })											
										} // End Editor Setup
									});		
                                }
                            };
                            var contentEditor = Cliq.win(opts);  
	                    } else { Cliq.error('Ajax function returned error NotOk - '+urlstr+':'+JSON.stringify(response)); }; 
	                } else { Cliq.error('Response was not JSON object - '+urlstr+':'+response.msg); }
	            }).go();   
	         }

            /** Code button, invoked from Grid or List row
             * 
             * This function provides popup windows with Codemirror as an editor
             * @param - integer - Record ID
             * @return - 
             **/     
	         var codeButton = function(recid)
	         {
	            cfg = Cliq.config();
	            cfg.fldname = 'd_text';

                var urlstr = "/ajax/"+jlcd+"/editcode/"+cfg.table+"/"+cfg.tabletype+"/";
                aja().method('GET').url(urlstr).cache(false).timeout(2500).type('json')
                .data({
	                displaytype: cfg.displaytype, // datalist
	                viewtype: 'popupview',
	                recid: recid,
	                fldname: cfg.fldname
	            })
	            .on('40x', function(response) {Cliq.error('Page not Found - '+urlstr+':'+response);})
	            .on('500', function(response) {Cliq.error('Server Error - '+urlstr+':'+response);})
	            .on('timeout', function(response) {Cliq.error('Timeout - '+urlstr+':'+response);})
	            .on('success', function(response) {

                    if(typeof response == 'object')
                    {
                        // Test NotOK - value already exists
                        var match = /NotOk/.test(response.flag);
                        if(!match == true) {
                            var opts = {
                                content: response.html,
                                contentSize: {
                                    width: 860,
                                    height: 642
                                },
                                headerTitle: '<span class="">'+lstr[105]+'</span>',
                                callback: function() {
							
									var jsonte = document.getElementById(cfg.fldname);
									fcfg.ceditor = CodeMirror.fromTextArea(jsonte, {
										lineNumbers: true,
										theme: 'cobalt',
										mode: "toml"						
									});
									addPanel("top", recid);
									var jsoncontent = $('#'+cfg.fldname).val();
									fcfg.ceditor.getDoc().setValue(jsoncontent);		

                                }
                            };
                            var contentEditor = Cliq.win(opts);  
	                    } else { Cliq.error('Ajax function returned error NotOk - '+urlstr+':'+JSON.stringify(response)); }; 
	                } else { Cliq.error('Response was not JSON object - '+urlstr+':'+response.msg); }
	            }).go();   
	         }

		     // Code Mirror Panel support routines
				var makePanel = function(where, recid) {
					
					var node = document.createElement("div"); 
					var widget, save;

					node.id = "panel-menu";
					node.className = "h30 lightgray panel" + where;
						
						// Menu
						save = node.appendChild(document.createElement("i"));
						save.setAttribute("title", lstr[138]);
						save.setAttribute("class", "fa fa-fw fa-save fa-2x tp3 ml30");

						CodeMirror.on(save, "click", function() {
							var frmData = new FormData();
							frmData.set('recid', recid);
							frmData.set('fldname', cfg.fldname);
							frmData.set(cfg.fldname, fcfg.ceditor.getDoc().getValue());
							var urlstr = '/ajax/'+cfg.langcd+'/savecode/'+cfg.table+'/'+cfg.tabletype+'/';
							$.ajax({
								url: urlstr, data: frmData,
								cache: false, contentType: false, processData: false,
								type: 'POST', async: false, timeout: 25000,
								success: function(response, statusText, xhr) {
							        if(typeof response == 'object') {
				                        // Test NotOK - value already exists
				                        var match = /NotOk/.test(response.flag);
				                        if(!match == true) {
				                        	Cliq.msg({type: 'success', buttons: false, text: response.msg})
				                        } else { // Error
											Cliq.msg({type: 'warning', buttons: false, text: 'Ajax function returned error NotOk - '+response.msg})
				                        }; 

				                    } else {
				                    	Cliq.msg({type: 'warning', buttons: false, text: 'Response was not JSON object - '+JSON.stringify(response)})
				                    }
								}, 
								error: function(xhr, status, text) {
									Cliq.msg({type: 'warning', buttons: false, text: 'Error saving Text to Database - '+urlstr+':'+text})
								}
							});			
							return true;
						});
						
						// label = node.appendChild(document.createElement("span"));
  						// label.textContent = "Menu";

					return node;
				}
				var addPanel = function(where, recid) {
	  				var node = makePanel(where, recid);
	  				fcfg.panels[node.id] = fcfg.ceditor.addPanel(node, {position: where, stable: true});
				}

            /** Select Button
             * displays a generic list in a Noty and operator selects new value from select list
             * record is update and row / display updated
             *
             * @param - Cfg Config
             * @param - Data from the button or icon
             * @return - displays a Noty and activates its processes
             **/ 
             var selectButton = function(cfg, dta)
             {
	        	var usrparams = {
	            	'type': 'select',
	                'postdata': {'formlettype': 'select'}
	            };
	            return notyFormPopup(cfg, dta, usrparams);
             }

            /** Yes No Button
             * displays a Radio group  in a Noty with various label/value pairs. 
             * Operator selects new values, record is updated and row / display updated
             * 
             * @param - Cfg Config
             * @param - Data from the button or icon
             * @return - displays a Noty and activates its processes
             **/ 
             var yesnoButton = function(cfg, dta)
             {
	        	var params = {
	            	'type': 'radiogroup',
	                'postdata': {'formlettype': 'radiogroup'}
	            };
	            return notyFormPopup(cfg, dta, usrparams);        	
             }

            /** Checkbox Group Button
             * displays a Check box group in a Noty with various label/value pairs. 
             * Operator selects new values, record is updated and row / display updated
             * 
             * @param - Cfg Config
             * @param - Data from the button or icon
             * @return - displays a Noty and activates its processes
             **/ 
             var checkboxButton = function(cfg, dta)
             {
	        	var params = {
	            	'type': 'checkboxgroup',
	                'postdata': {'formlettype': 'checkboxgroup'}
	            };
	            return notyFormPopup(cfg, dta, usrparams);      	
             }

            /** Generic Noty Popup try await .........
             * 
             * @param - Cfg Config
             * @param - Data from the button or icon
             * @return - displays a Noty and activates its processes
             **/            
             var notyFormPopup = function(cfg, dta, usrparams)
             {

	        	var commonparams = {
	        		'table': cfg.table,
	        		'tabletype': cfg.tabletype,
	            	'type': '',
	            	'fldname': dta.id,
	                'displaytype': cfg.displaytype, // datatable
	                'postdata': {
	                	'displaytype': cfg.displaytype,
	                	'action': dta.action, // example - changestatus
	                	'ajabuster': 'anything',
	                	'formlettype': '',
	                	'fldname': dta.id,
	                	'params': dta.params,
	                	'recid': cfg.recid
	                }
	            };
	            var params = array_replace_recursive(commonparams, usrparams);

				var tpl = `<form id="notyform" class="form"></form>`;
             	var urlstr = '/ajax/'+jlcd+'/getformletdata/'+params.table+'/'+params.tabletype+'/';
             	// Get the values for the template - 
                aja().method('GET').url(urlstr).cache(false).timeout(2500).type('json').data(params.postdata)
                .on('40x', function(response) { error('Page not Found - '+urlstr+':'+response)})
                .on('500', function(response) { error('Server Error - '+urlstr+':'+response)})
                .on('timeout', function(response){ error('Timeout - '+urlstr+':'+response)})
                .on('200', function(response) {

                    if(typeof response == 'object')
                    {
                        // Test NotOK - value already exists
                        var match = /NotOk/.test(response.flag);
                        if(!match == true) {
                            
							// Display the Noty
				            Cliq.msg({
				                buttons:  [
				                    {addClass: 'm10 mt10 btn btn-primary btn-sm', text: lstr[8], onClick: function($noty) { 
				                        
				                        // Update the value in the database

				                        var urlstr = '/ajax/'+jlcd+'/postvalue/'+params.table+'/'+params.tabletype+'/';
				                        aja().method('POST').url(urlstr).cache(false).timeout(2500).type('json')
				                        .data({
				                            'recid': params.postdata.recid,
				                            'fldname': dta.id,
				                            'newvalue': $('#'+dta.id).getValue()
				                        })
							            .on('40x', function(response) {Cliq.error('Page not Found - '+urlstr+':'+response);})
							            .on('500', function(response) {Cliq.error('Server Error - '+urlstr+':'+response);})
							            .on('timeout', function(response) {Cliq.error('Timeout - '+urlstr+':'+response);})
							            .on('success', function(response) {

				                            if(typeof response == 'object') {
				                                // Test NotOK - value already exists
				                                var match = /NotOk/.test(response.flag);
				                                if(!match == true) {  
				                                    
				                                    var originalnoty = $noty;
				                                    Cliq.success(lstr[146]+': ' + JSON.stringify(response.msg));
				                                    originalnoty.close();

				                                    switch(params.displaytype) {
				                                    	
				                                    	case "datagrid": cfg.dg.reload(); break;
				                                    	case "datatable": Cliq.loadTableData(); break;
				                                    	default: reLoad(); break;
				                                    };	                                    
				                                                        
						                        } else { Cliq.error('Ajax function returned error NotOk - '+urlstr+':'+JSON.stringify(response)); };
						                    } else { Cliq.error('Response was not JSON object - '+urlstr+':'+response.msg); };
						                }).go();                             
				                    }},
				                    {addClass: 'm10 mt10 btn btn-default btn-sm', text: lstr[30], onClick: function($noty) {    
				                        $noty.close(); 
				                    }}			                                                                
				                ],
				                timeout: false,
			                	closeWith: ['button'],
				                type: 'info',
				                text: tpl      
				            });    

                            $("#notyform").clqform({"action" : "#", "method" : "POST", "html" : response.data}); 				                                          
                        
                        } else { // Error
                            error('Ajax function returned error NotOk - '+urlstr+':'+JSON.stringify(response))
                        }; 

                    } else { error('Response was not JSON object - '+urlstr+':'+response.msg); }
                }).go(); 
             }

	    /** Form and Button stuff related to Users
	     *
	     * changePasswordButton(uname, email, recid)
	     * resetPassword(msg)
	     * passwordStrength(pwd)
	     * changeUserStatusButton(recid)
	     * changeStatus(msg) - not sure this is used, consider deprecated
	     *
	     ************************************************************************************************/

            var changePasswordButton = function(uname, email, recid)
            {
                cfg = Cliq.config();
                cfg.action = 'lostpassword'; cfg.displaytype = 'lostpassword';
                var urlstr = '/ajax/'+jlcd+'/'+cfg.action+'/'+cfg.table+'/'+cfg.tabletype+'/';
                var frmData = new FormData();
                frmData.append('id', recid);
                $.ajax({
                    url: urlstr, data: frmData,
                    cache: false, contentType: false, processData: false,
                    type: 'POST', async: false, timeout: 25000,
                    success: handleResponse, error: handleError
                });
            }

            /**
             * Display a formlet to permit the reset of a password
             * @param - array JSON - usroptions to configure a Noty
             * @return - string HTML - creates a Noty popup with content
             **/
            var resetPassword = function(msg) {
                var options = $.parseJSON(msg);
                var $noty = Cliq.msg(options); // returns $noty

                $('.closenoty').on('click', function(e) {
                    $noty.close();
                });

                $('.submitnoty').on('click', function(e) {
                    e.preventDefault();
                    var newp = $('input[name="c_password"]').val();
                    var confp = $('input[name="c_password_confirm"]').val();
                    
                    // Check if Passwords are equal
                    if(newp === confp) {
                        // Check if password is strong enough
                        newp = passwordStrength(newp);
                        if(newp != "") {
                            var uid = $('input[name="id"]').val();
                            cfg.action = "resetpassword";
                            var frmData = new FormData();
                            frmData.append('c_password', newp);
                            frmData.append('id', uid);
                            frmData.append('token', jwt);
                            var urlstr = '/api/'+jlcd+'/'+cfg.action+'/'+cfg.table+'/'+cfg.tabletype+'/';
                            $.ajax({
                                url: urlstr, data: frmData,
                                cache: false, contentType: false, processData: false,
                                type: 'POST', async: false, timeout: 25000,
                                success: handleResponse, error: handleError, complete: function() {
                                    $noty.close();
                                }
                            });  
                        } else {
                            Cliq.error(lstr[149]);
                        };
                    } else {
                        Cliq.error(lstr[137]);
                    }
                });         
            }

            var passwordStrength = function(pwd) 
            {
                var strongRegex = new RegExp("^(?=.{8,})(?=.*[A-Z])(?=.*[a-z])(?=.*[0-9])(?=.*\\W).*$", "g");
                var mediumRegex = new RegExp("^(?=.{7,})(((?=.*[A-Z])(?=.*[a-z]))|((?=.*[A-Z])(?=.*[0-9]))|((?=.*[a-z])(?=.*[0-9]))).*$", "g");
                var enoughRegex = new RegExp("(?=.{6,}).*", "g");

                // Change if necessary
                if(strongRegex.test(pwd)) {
                    return pwd;
                } else {
                    return '';
                }
            }

            var changeUserStatusButton = function(recid)
            {
                cfg = Cliq.config();
                cfg.action = 'changeuserstatus'; cfg.displaytype = 'changestatus';
                var urlstr = '/ajax/'+jlcd+'/'+cfg.action+'/'+cfg.table+'/'+cfg.tabletype+'/';
                var frmData = new FormData();
                frmData.append('id', recid);
                $.ajax({
                    url: urlstr, data: frmData,
                    cache: false, contentType: false, processData: false,
                    type: 'POST', async: false, timeout: 25000,
                    success: handleResponse, error: handleError
                });
            }

            var changeStatus = function(msg)
            {
                var options = $.parseJSON(msg);
                var $noty = Cliq.msg(options); // returns $noty

                $('.closenoty').on('click', function(e) {
                    $noty.close();
                });

                $('.submitnoty').on('click', function(e) {
                    e.preventDefault();
                    var sts = $('input[name="c_status"]').val();
                    var uid = $('input[name="id"]').val();
                    cfg.action = "dochangeuserstatus";
                    var frmData = new FormData();
                    frmData.append('c_status', sts);
                    frmData.append('id', uid);
                    var urlstr = '/api/'+jlcd+'/'+cfg.action+'/'+cfg.table+'/'+cfg.tabletype+'/';
                    $.ajax({
                        url: urlstr, data: frmData,
                        cache: false, contentType: false, processData: false,
                        type: 'POST', async: false, timeout: 25000,
                        success: handleResponse, error: handleError, complete: function() {
                            $noty.close();
                        }
                    });  
                }); 
            }

        /** Form Support Functions 
         * modInput()
         * getFormData()
         * handleResponse()
         * handleError()
         *
         *************************************************************************************/	

         	/**
         	 * Next Reference, Next ID, Is Unique
         	 * @param - 
         	 * @param -
         	 * @return - 
         	 **/
			var modInput = function(fldid, action, prefix) // eg 'reference'
			{               
                cfg = Cliq.config();
               	// console.log(fldid, action, table, tabletype);
                var urlstr = '/ajax/'+cfg.langcd+'/'+action+'/'+cfg.table+'/'+cfg.tabletype+'/';
                aja().method('GET').url(urlstr)
                .data({fld: fldid, prefix:prefix, currval: $('#'+fldid).val() }).cache(false)
	            .on('40x', function(response) {Cliq.error('Page not Found - '+urlstr+':'+response);})
	            .on('500', function(response) {Cliq.error('Server Error - '+urlstr+':'+response);})
	            .on('timeout', function(response) {Cliq.error('Timeout - '+urlstr+':'+response);})
	            .on('success', function(response) {
                    
					if(typeof response == 'object')
					{
						// Test NotOK - value already exists
						var match = /NotOk/.test(response.flag);
						if(!match == true) {
							
							switch(action) {
								case "getnextref":
								case "getnextentry":
								case "getnextid":
									Vue.set(cfg.df, fldid, response.data);
									$('#'+fldid).val(response.data);
								break;
								
								case "isunique":
									if(response.data) {
										$('#'+fldid).val('');
										$('#'+fldid).focus();
										Cliq.msg({type: 'warning', buttons: false, text: 'Value already exists'});
									};
								break;
							};		
							
	                    } else { Cliq.error('Ajax function returned error NotOk - '+urlstr+':'+JSON.stringify(response)); }; 
	                } else { Cliq.error('Response was not JSON object - '+urlstr+':'+response.msg); }
	            }).go(); 	
			}

         	/**
         	 * A function to collect any and all data, including files from a form
         	 * @param - boolean - add filename
         	 * @param - string - 
         	 * @return - object - the FormData
         	 **/
         	var getFormData = function(addfilename) {

         		// Test config files
         		// console.log(cfg, fcfg);
         		// cfg contains:  displaytype, table and tabletype, langcd  		
         		
         		// Define the Form ID
         			var id = 'dataform'; var thisform = $('#'+id);

         		// Start a Spinner
         			var target = document.getElementById(cfg.formid); cfg.spinner.spin(target);	

				// Get any tinymce Editors if exist and update the Vue instance with Tiny editor content
					var tinyeditors = tinymce.EditorManager.editors;
					if(count(tinyeditors) > 0) {
						$.each(tinyeditors, function(i, teditor) {
							var name = trim(teditor.id, '_te'); // works fine
							var val = tinymce.get(teditor.id).getContent();
							// rawurlencode() ??
							Vue.set(cfg.df, teditor.id, val);
						});	
					};

				// If Trumbowyg editor is being used
					$('#'+id+' textarea.texteditor').each(function() {
		           		var fldid = $(this).attr('id');
		           		// var te = $('.trumbowyg-editor').trumbowyg('html');
		           		var te = $(this).trumbowyg('html');
		           		// rawurlencode() ??
		           		Vue.set(cfg.df, fldid, te);	
		           	});	

				// If Tagit
					$('.tagit').each(function() {
						var fldid = $(this).attr('id');
						var tags = $("#"+fldid).tagit("assignedTags");
						Vue.set(cfg.df, fldid, tags);
					});
	           	
				// JSONeditors if exist - only one JSON Editor if exist and update the Vue instance with Jsoneditor content
					$('#'+id+' div[data-type=jsoneditor]').each(function() {
						fcfg.jeditor = findJSONEditor('#'+fcfg.jeditid);
						var jeditdata = fcfg.jeditor.get();
						jeditdata = JSON.stringify(jeditdata);
						// rawurlencode() ??
						Vue.set(cfg.df, fcfg.jeditid, jeditdata);
					});	

                // If Codeeditor is being used
	                $('.toml').each(function() {
	                    var fldid = $(this).attr('id');
	                    var tomlcontent = fcfg.ceditor.getValue();                   
	                    Vue.set(cfg.df, fldid, rawurlencode(tomlcontent));  
	                });  

	            // If currency and maskMoney is being used
	            $('.currency').each(function() {
	            	var fldid = $(this).attr('id');
	            	var cur = $(this).getValue();
	            	Vue.set(cfg.df, fldid, cur);
	            })             
	           	
         		// validation here	if required

				// Now get Data from the Vue Instance
					var postData = cfg.df.$data;	
				
				// Test Form Content
					// console.log(postData);	           				

				// Now convert Postdata to FormData
					var frmData = new FormData();
					$.each(postData, function(fld, val) {
						frmData.set(fld, val);
					});

					frmData.append('token', jwt);
				
				// Add any AJAX Form upload for a single file
					if( $('#'+id+' :input').hasClass('form-control-file') ) {
						var file = $('input[type=file]', thisform)[0].files[0];		
						if(addfilename) {
							frmData.append(addfilename, file.name);	
						} else {
							frmData.append('filename', file.name);	
						}											
						frmData.append(file.name, file, file.filename);	
					}

				// New image handling will get image contents anyway
	
				return frmData;		
         	}

         	/**
         	 * After form submits
         	 **/
         	var handleResponse = function(response, statusText, xhr)
         	{
				// Stop and close the Spinner
				cfg.spinner.stop();

				var table = cfg.table;
				var tabletype = cfg.tabletype;

				// first argument to the success callback is the json data object returned by the server
				if(typeof response == 'object') {
					var match = /NotOk/.test(response.flag);
					if(!match == true) {

						// If popup form, close the window
						if(cfg.formtype == 'popupform') {
							jsPanel.closeChildpanels('body');
						} else if(cfg.formtype == 'columnform') {
							$('#columnform').empty().html(response.data);
						} else if(cfg.formtype == 'pageform') {
							// Introduce history
							exit;
						};

						var txt; cfg.action == 'insert' ? txt = lstr[9] : txt = lstr[10];
						switch(cfg.displaytype) {

							case "recordcreator": 
							case "datagrid":
								var tbl = prettyPrint(response.data.row);
								$('#columnform').empty().html(tbl);
								Cliq.success(txt);
								cfg.dg.reload();
							break;

                            case "datacard":
                                var urlstr = '/ajax/'+jlcd+'/getcarddata/'+cfg.table+'/'+cfg.tabletype+'/';
                                aja().method('GET').url(urlstr).cache(false).timeout(2500).type('json')
                                .on('40x', function(response) { Cliq.error('Page not Found - '+urlstr+':'+response)})
                                .on('500', function(response) { Cliq.error('Server Error - '+urlstr+':'+response)})
                                .on('timeout', function(response){ Cliq.error('Timeout - '+urlstr+':'+response)})
                                .on('200', function(response) {
                                    if(typeof response == 'object') {
                                        // Test NotOK - value already exists
                                        var match = /NotOk/.test(response.flag);
                                        if(!match == true) {
                                        	Cliq.success(txt);
                                            cfg.dc.$data.admdatacards = response.data;
                                        } else { // Error
                                            Cliq.error('Ajax function returned error NotOk - '+urlstr+':'+JSON.stringify(response))
                                        }; 
                                    } else { Cliq.error('Response was not JSON object - '+urlstr+':'+response.msg); }
                                }).go(); 
                            break;

                            case "datalist": 
                            	Cliq.success(txt);
                                Cliq.loadTableData(); 
                            break;

                            case "datatable":
                            	// Cliq.loadTableData();
                            	// Cliq.success(txt);
								var urlstr = "/admindesktop/"+jlcd+"/"+cfg.displaytype+"/"+table+"/"+tabletype+"/";
                				uLoad(urlstr);
                            break;

                            case "lostpassword": rrd(response.msg); break;
                            case "changestatus": changeStatus(response.msg); break;

                            case "datatablesnet":
                            	Cliq.success(response.data.msg);
								// var urlstr = "/plugin/"+jlcd+"/"+cfg.displaytype+"/"+table+"/"+tabletype+"/";
                				// uLoad(urlstr); 
                				cfg.dt = $('#datatable').DataTable();                         	
                            	cfg.dt.ajax.reload();
                            break;

							default:
								var urlstr = "/admindesktop/"+jlcd+"/"+cfg.displaytype+"/"+table+"/"+tabletype+"/";
                				// uLoad(urlstr);
							break;
						}

						
					} else {
						Cliq.error('Ajax function returned error NotOk - '+JSON.stringify(response.msg))
					}; 							
				} else {
					Cliq.error('Response was not JSON object - '+JSON.stringify(response))
				};
         	}

         	/**
         	 * Handles any 500 Errors from the AJAX routine
         	 */
         	var handleError = function(xhr, status, text) 
         	{
				cfg.spinner.stop();
				var response = $.parseJSON(xhr.responseText);
				Cliq.error(JSON.stringify(response.msg));
				return false;
         	}

        /** Import - Export Routines
         *
         * convertArray()
         * importData()
         * exportData()
         * siteUpdate()
         * - doSiteUpdate()
         * siteMap()
         * - setSiteMap()
         *
         *************************************************************************************/	

			/**
			 * Javascript routines to convert an import file in Configuration array format
			 * @param - array - options
			 * @return - test or written content
			 **/				
			var convertArray = function(opts) 
			{		
				var idms = new Vue({
					el: '#admconvertarray',
					data: {
						formdata: {
							idioms: opts.idioms, 
							tables: opts.tables,
							tabletypes: opts.tabletypes
						},
						inputform: {
							idiom: 'en',
							table: 'dbitem',
							tabletype: 'text',
							inputfile: '',
							dbwrite: ''
						},
						testform: {
							testfile: ''
						}
					},
					mounted: function() {

						$('#testform').submit(function(evt) {
							evt.preventDefault();
							var target = document.getElementById('testform');
							var opts = {};
							var spinner = new Spinner(opts).spin(target);
							$('#convertresults').empty();
		
							var form = $('#testform');
							var frmdata = new FormData();
															
							$.each($('input[type=file]',form)[0].files, function (i, file) {
								frmdata.append(file.filename, file);
								frmdata.append(file.name, file);
							});
							
							var urlstr = '/ajax/'+jlcd+'/dotestarray/';
							
							$.ajax({
								url: urlstr, data: frmdata,
								cache: false, contentType: false, processData: false,
								type: 'POST', async: false, timeout: 5000,
								success: function(response) {
									spinner.stop();
									if(typeof response == 'object') {
										var match = /NotOk/.test(response.flag);
										if(!match == true) {

											var content = prettyPrint(response.result, {
												expanded: true, 
												maxDepth: 5
											});
											$('#convertresults').empty().html(content);
											
										} else {
											Cliq.error('Ajax function returned error NotOk - '+urlstr+':'+JSON.stringify(response.msg));
										}; 	

									} else {
										Cliq.error('Response was not JSON object - '+urlstr+':'+JSON.stringify(response))
									};
									
								},
								error: function(xhr, status, text) {
									spinner.stop();
									var response = $.parseJSON(xhr.responseText);
									Cliq.error(JSON.stringify(response.msg));
								}
							});			
	
							return false;
						});									
				
						$('#inputform').submit(function(evt) {
							evt.preventDefault();
							var target = document.getElementById('inputform');
							var opts = {};
							var spinner = new Spinner(opts).spin(target);
							$('#convertresults').empty();
		
							var form = $('#inputform');
							var frmdata = new FormData();
							
							$.each($(':input', form ), function(i, fld){
								frmdata.append( $(fld).data('name'), $(fld).getValue() );
							});
							
							$.each($('input[type=file]',form)[0].files, function (i, file) {
								frmdata.append(file.filename, file);
								frmdata.append(file.name, file);
							});
							
							var data = $.parseJSON( $('#formresult').html() );	
							var urlstr = '/ajax/'+jlcd+'/doconvertarray/'+data.table+'/'+data.tabletype+'/';
							
							$.ajax({
								url: urlstr, data: frmdata,
								cache: false, contentType: false, processData: false,
								type: 'POST', async: false, timeout: 25000,
								success: function(response) {
									spinner.stop();
									if(typeof response == 'object') {
										var match = /NotOk/.test(response.flag);
										if(!match == true) {
											
											var content = prettyPrint(response.result, {
												expanded: true, 
												maxDepth: 5
											});
											$('#convertresults').empty().html(content);
											
										} else {
											Cliq.msg({type: 'warning', buttons: false, text: 'Ajax function returned error NotOk - '+urlstr+':'+JSON.stringify(response.msg)})
										}; 							
									} else {
										Cliq.msg({type: 'warning', buttons: false, text: 'Response was not JSON object - '+urlstr+':'+JSON.stringify(response)})
									};
									
								},
								error: function(xhr, status, text) {
									spinner.stop();
									var response = $.parseJSON(xhr.responseText);
									Cliq.msg({buttons: false, type: 'error', text: JSON.stringify(response.msg)});
								}
							});			
	
							return false;
						});		
					}
				});									
			}

			/**
			 * Javascript routines to import a CSV formatted data file into the database
			 * @param - array - options
			 * @return - test or written content
			 **/				
			var importData = function(opts) 
			{
				cfg = Cliq.config;
				cfg.df = new Vue({
					el: '#admimportdata',
					data: {
						formdata: {
							idioms: opts.idioms, 
							tables: opts.tables,
							tabletypes: opts.tabletypes
						},
						inputform: {
							idiom: 'en',
							table: 'dbitem',
							tabletype: 'text',
							inputfile: '',
							dbwrite: '',
							header: '',
							delimiter: ',', 
							encloser: '"',
							escape: '\\',
							longestline: 0
						}
					},
					mounted: function() {
						
						$('#inputform').submit(function(evt) {
							evt.preventDefault();
							var target = document.getElementById('inputform');
							var opts = {};
							var spinner = new Spinner(opts).spin(target);
							$('#convertresults').empty();
		
							var form = $('#inputform');
							var frmdata = new FormData();
							
							$.each($(':input', form ), function(i, fld){
								frmdata.append( $(fld).data('name'), $(fld).getValue() );
							});
							
							$.each($('input[type=file]',form)[0].files, function (i, file) {
								frmdata.append(file.filename, file);
								frmdata.append(file.name, file);
							});
							
							var data = $.parseJSON( $('#formresult').html() );	
							var urlstr = '/ajax/'+jlcd+'/doimportdata/'+data.table+'/'+data.tabletype+'/';
							
							$.ajax({
								url: urlstr, data: frmdata,
								cache: false, contentType: false, processData: false,
								type: 'POST', async: false, timeout: 25000,
								success: function(response) {
									spinner.stop();
									if(typeof response == 'object') {
										var match = /NotOk/.test(response.flag);
										if(!match == true) {
											
											var content = prettyPrint(response.result, {
												expanded: true, 
												maxDepth: 5
											});
											$('#convertresults').empty().html(content);
											
										} else {
											Cliq.msg({type: 'warning', buttons: false, text: 'Ajax function returned error NotOk - '+urlstr+':'+JSON.stringify(response.msg)})
										}; 							
									} else {
										Cliq.msg({type: 'warning', buttons: false, text: 'Response was not JSON object - '+urlstr+':'+JSON.stringify(response)})
									};
									
								},
								error: function(xhr, status, text) {
									spinner.stop();
									var response = $.parseJSON(xhr.responseText);
									Cliq.msg({buttons: false, type: 'error', text: JSON.stringify(response.msg)});
								}
							});			
	
							return false;
						});		
					}
				});									
			}

			/**
			 * Javascript routines to export data to a CSV or Array config file
			 * @param - array - options
			 * @return - test or written content
			 **/				
			var exportData = function(opts)
			{
				var idms = new Vue({
					el: '#admexportdata',
					data: {
						formdata: {
							idioms: opts.idioms, 
							tables: opts.tables,
							tabletypes: opts.tabletypes
						},
						exportform: {
							idiom: 'en',
							table: 'dbitem',
							tabletype: 'text',
							csvorarray: '',
							doexport: ''
						}
					},
					mounted: function() {
						
						$('#exportform').submit(function(evt) {
							evt.preventDefault();
							var target = document.getElementById('exportform');
							var opts = {};
							var spinner = new Spinner(opts).spin(target);
							$('#convertresults').empty();
		
							var form = $('#exportform');
							var frmdata = new FormData();
							
							$.each($(':input', form ), function(i, fld){
								frmdata.append( $(fld).data('name'), $(fld).getValue() );
							});
													
							var data = $.parseJSON( $('#formresult').html() );	
							var urlstr = '/ajax/'+jlcd+'/doexportdata/'+data.table+'/'+data.tabletype+'/';
							
							$.ajax({
								url: urlstr, data: frmdata,
								cache: false, contentType: false, processData: false,
								type: 'POST', async: false, timeout: 25000,
								success: function(response) {
									spinner.stop();
									if(typeof response == 'object') {
										var match = /NotOk/.test(response.flag);
										if(!match == true) {
											
											var content = prettyPrint(response.result, {
												expanded: true, 
												maxDepth: 5
											});
											$('#convertresults').empty().html(content);
											
										} else {
											Cliq.msg({type: 'warning', buttons: false, text: 'Ajax function returned error NotOk - '+urlstr+':'+JSON.stringify(response.msg)})
										}; 							
									} else {
										Cliq.msg({type: 'warning', buttons: false, text: 'Response was not JSON object - '+urlstr+':'+JSON.stringify(response)})
									};
									
								},
								error: function(xhr, status, text) {
									spinner.stop();
									var response = $.parseJSON(xhr.responseText);
									Cliq.msg({buttons: false, type: 'error', text: JSON.stringify(response.msg)});
								}
							});			
	
							return false;
						});	
					}
				});									
			}

			/**
			 * Javascript routines to manage the site update functions
			 * @param - array - options
			 * @return - test or written content
			 **/				
			var siteUpdate = function(opts)
			{
				
		    	$('.topbutton').on('click', function(e) {
		    		e.preventDefault(); e.stopImmediatePropagation();
		    		Cliq.topButton(this);
		    	});	

				var tree = $('#tree').gjtree({
					iconsLibrary: 'fontawesome',
					primaryKey: 'id',
					width: 460,
                    uiLibrary: 'bootstrap4',
                    dataSource: opts.data,
                    selectionType: 'multiple'
                    // imageUrlField: 'flagUrl'
                });

				$('#btnSave').on('click', function () {
					var selections = tree.getSelections();
					doSiteUpdate(selections, 'dofilesdownload');
				});

				$('#btnCopy').on('click', function () {
					var selections = tree.getSelections();
					doSiteUpdate(selections, 'dofilescopy');
				});


				var readTokenFromResponse = function(response, attr) {
					return $(response).find('tr th:contains(' + attr + ')').parent().find('td').text()
				};

				var tpl = `
					<div class="list-group-item list-group-item-action flex-column align-items-start">
						<div class="d-flex w-100 justify-content-between">
							<h6 class="mb-1 redc bold">{title}</h6>
							<small class="text-muted">{date}</small>
						</div>
						<p class="mb-1">{shortBodyPlain}</p>
						<a href="{url}" target="_blank"><small class="text-muted bluec"><i class="fa fa-external-link-square fa-fw"></i>{url}</small></a>
					</div>
				`;

				jQuery(function($) {
					$("#rssfeed").rss(opts.rssfeedaddress, {
						limit: 10,
						// dateFormat: 'dddd MMM Do',
						layoutTemplate: '<div class="list-group">{entries}</div>',
						entryTemplate: tpl,
					})
				})
			}

			// Private function
			var doSiteUpdate = function(selections, action)
			{

				cfg = Cliq.config();  
	            var urlstr = '/ajax/'+jlcd+'/'+action+'/';
	            aja().method('GET').url(urlstr).cache(false).timeout(2500).type('json')
	            .data({selectedfiles: selections})
	            .on('40x', function(response) {Cliq.error('Page not Found - '+urlstr+':'+response);})
	            .on('500', function(response) {Cliq.error('Server Error - '+urlstr+':'+response);})
	            .on('timeout', function(response) {Cliq.error('Timeout - '+urlstr+':'+response);})
	            .on('success', function(response) {

	                if(typeof response == 'object')
	                {
	                    // Test NotOK - value already exists
	                    var match = /NotOk/.test(response.flag);
	                    if(!match == true) {
	                        Cliq.success(response.msg);                       
	                    } else { Cliq.error('Ajax function returned error NotOk - '+urlstr+':'+JSON.stringify(response)); }; 
	                } else { Cliq.error('Response was not JSON object - '+urlstr+':'+response.msg); }
	            }).go();    
			}

	        var siteMap = function(opts)
	        {
	            console.log('Sitemap Loaded');
	            cfg = Cliq.config();

	            // Initial 
	            setSiteMap(opts);

		    	$('.topbutton').on('click', function(e) {
		    		e.preventDefault(); e.stopImmediatePropagation();
		    		Cliq.topButton(this);
		    	});	

	            var fldid = opts.fieldid;
	            var thisfld = $('#'+fldid);	   

				$('#resetbutton').on('click', function(evt) {
					$('#'+opts.formid).clearform();
				});

				$('#generatebutton').on('click', function(evt) {
					evt.preventDefault();
					var urlstr = '/ajax/'+cfg.langcd+'/postsitemap/'+cfg.table+'/'+cfg.tabletype+'/';

					var frmData = new FormData;
					frmData.set(fldid, fcfg.ceditor.getDoc().getValue());

					$.ajax({
						url: urlstr, data: frmData,
						cache: false, contentType: false, processData: false,
						type: 'POST', async: false, timeout: 25000,
						success: function(response) {
			                
			                if(typeof response == 'object')
			                {
			                    // Test NotOK - value already exists
			                    var match = /NotOk/.test(response.flag);
			                    if(!match == true) {
			                        Cliq.success(response.msg);  
			                        setSiteMap(opts);                     
			                    } else { // Error
			                        Cliq.error('Ajax function returned error NotOk - '+response.msg)
			                    }; 

			                } else {
			                    Cliq.error( 'Response was not JSON object - '+urlstr+':'+JSON.stringify(response) )
			                }
							
						}, 
						error: function(xhr, status, text) {
							Cliq.error(text);
						}
					});			
				});
	        }		    

	        // Private function
			var setSiteMap = function(opts)
			{
	            var urlstr = '/ajax/'+cfg.langcd+'/getsitemap/'+cfg.table+'/'+cfg.tabletype+'/';
                aja().method('GET').url(urlstr).cache(false).timeout(2500).type('json')
	            .on('40x', function(response) {Cliq.error('Page not Found - '+urlstr+':'+response);})
	            .on('500', function(response) {Cliq.error('Server Error - '+urlstr+':'+response);})
	            .on('timeout', function(response) {Cliq.error('Timeout - '+urlstr+':'+response);})
	            .on('success', function(response) {

                    if(typeof response == 'object')
                    {
                        // Test NotOK - value already exists
                        var match = /NotOk/.test(response.flag);
                        if(!match == true) {

                        	$('textarea[id="d_text"]').setValue(response.data);
 							$('#sitemap').empty().html(response.html);

				            var fldid = opts.fieldid;
				            var thisfld = $('#'+fldid);	         
							var tomledid = document.getElementById(fldid);
							var editor = CodeMirror.fromTextArea(tomledid,{
								// lineNumbers: true,
								autofocus: true,
								// theme: 'cobalt',
								mode: "toml"
							});
							var tomlcontent = thisfld.getValue();
							fcfg.ceditor.getDoc().setValue(tomlcontent);

                        } else { // Error
                            Cliq.error('Ajax function returned error NotOk - '+urlstr+':'+JSON.stringify(response))
                        }; 

                    } else {
                        Cliq.error('Response was not JSON object - '+urlstr+':'+response.msg)
                    }
    
                }).go();				
			}

		/** File System and Model Routines
		 *
		 * fileAdd()
		 * fileEdit(reference)
		 * fileDelete(reference)
         *
		 *******************************************************************************************************************/

		 	var fileAdd = function()
		 	{
		 		return fileEdit('');
		 	}

		 	var fileEdit = function(ref)
		 	{
		 		cfg = Cliq.config();
				var urlstr = "/ajax/"+jlcd+"/fileeditor/"+cfg.table+"/"+cfg.tabletype+"/";
	            aja().method('GET').url(urlstr).cache(false).timeout(2500).type('json')
	            .data({
	            	ref: ref
	            })
	            .on('40x', function(response) {Cliq.error('Page not Found - '+urlstr+':'+response);})
	            .on('500', function(response) {Cliq.error('Server Error - '+urlstr+':'+response);})
	            .on('timeout', function(response) {Cliq.error('Timeout - '+urlstr+':'+response);})
	            .on('success', function(response) {

	                if(typeof response == 'object')
	                {
	                    // Test NotOK - value already exists
	                    var match = /NotOk/.test(response.flag);
	                    if(!match == true) {
    						var opts = {
			                    content: response.html,
			                    contentSize: {
			                        width: 580,
			                        height: 680
			                    },
			                    paneltype: 'modal',
			                    headerTitle: '<span class="">'+lstr[20]+'</span>'
			                };
				            var filePopup = Cliq.win(opts);    

		                    var thisfld = $('#filecontent');
							var tomledid = document.getElementById('filecontent');
							var editor = CodeMirror.fromTextArea(tomledid,{
								lineNumbers: true,
								mode: "toml"
							});
							var tomlcontent = thisfld.val();
							fcfg.ceditor.getDoc().setValue(tomlcontent); 

							$('#popupform').submit(function(evt) {
								evt.preventDefault();
								var urlstr = '/ajax/'+cfg.langcd+'/writefile/'+cfg.table+'/'+cfg.tabletype+'/';
								var frmData = new FormData();
									frmData.set('filepath', $('input[name="filename"]').val());
									frmData.set('content', editor.getDoc().getValue());
								$.ajax({
									url: urlstr, data: frmData,
									cache: false, contentType: false, processData: false,
									type: 'POST', async: false, timeout: 25000,
									success: function(response, statusText, xhr) {
										
										// first argument to the success callback is the json data object returned by the server
										if(typeof response == 'object') {
											var match = /NotOk/.test(response.flag);
											if(!match == true) {
												jsPanel.closeChildpanels('body');
												reload();
											} else {
												Cliq.error('Ajax function returned error NotOk - '+JSON.stringify(response.msg));
											}; 	
										} else {
											Cliq.error('Response was not JSON object - '+JSON.stringify(response));
										};	

									}, 
									error: function(xhr, status, text) {
										var response = $.parseJSON(xhr.responseText);
										Cliq.error(JSON.stringify(response.msg));
										return false;			
									}
								});									
							})   
 			                      
	                    } else { Cliq.error('Ajax function returned error NotOk - '+urlstr+':'+JSON.stringify(response)); }; 
	                } else { Cliq.error('Response was not JSON object - '+urlstr+':'+response.msg); }
	            }).go(); 
		 	}

		 	var fileDelete = function(ref)
		 	{
	            cfg = Cliq.config();
	            return Cliq.msg({
	                buttons:  [
	                    {addClass: 'm10 mt10 btn btn-success btn-sm', text: 'Close', onClick: function($noty) {    
	                        $noty.close(); 
	                    }},
	                    {addClass: 'm10 mt10 btn btn-danger btn-sm', text: 'Delete', onClick: function($noty) { 
	                        
	                        var urlstr = '/ajax/'+cfg.langcd+'/deletefile/'+cfg.table+'/'+cfg.tabletype+'/';
	                        aja().method('POST').url(urlstr).cache(false).timeout(2500).type('json')
	                        .data({
	                        	filepath: '/models/'+cfg.table+'.'+ref+'.cfg'
	                        })
				            .on('40x', function(response) {Cliq.error('Page not Found - '+urlstr+':'+response);})
				            .on('500', function(response) {Cliq.error('Server Error - '+urlstr+':'+response);})
				            .on('timeout', function(response) {Cliq.error('Timeout - '+urlstr+':'+response);})
				            .on('success', function(response) {

	                            if(typeof response == 'object') {
	                                // Test NotOK - value already exists
	                                var match = /NotOk/.test(response.flag);
	                                if(!match == true) {  
	                                    Cliq.success('File successfully deleted: ' + JSON.stringify(response.data));
	                                    reLoad();
	                                } else { // Error
	                                    Cliq.error('Ajax var returned error NotOk - '+urlstr+':'+JSON.stringify(response))
	                                }; 
	                            } else {
	                                Cliq.error('Response was not JSON object - '+urlstr+':'+data)
	                            }
	                            
	                        }).go();                                
	                    }}                                              
	                ],
	                timeout: false,
	                type: 'warning',
	                text: 'Delete: '+ref       
	            }); 
		 	}

        // explicitly return public methods when this object is instantiated
        return {
            // outside: inside
            formMounted: formMounted,
            crudButton: crudButton,
            creatorButton: creatorButton,
            contentButton: contentButton,
            codeButton: codeButton,
            deleteButton: deleteButton,
            deleteRecords: deleteRecords,
            restoreButton: restoreButton,
            changePasswordButton: changePasswordButton,
            changeStatusButton: changeUserStatusButton,
            siteUpdate: siteUpdate,
            siteMap: siteMap,
            fileAdd: fileAdd,
            fileEdit: fileEdit,
            fileDelete: fileDelete,
	     	selectButton: selectButton,
	     	yesnoButton: yesnoButton,
	     	checkboxButton: checkboxButton,

            set: _set,
            get: _get
        }; 

    })(jQuery); 
