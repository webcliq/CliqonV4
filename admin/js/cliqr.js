/* CliqR.Js */

/** Cliqon Report Related Functions - cliqr() 
 * Cliqr.x() - app and utility functions, including:
 *
 * reportDesigner() to be done
 * printRecords()
 * displayImages()
 * siteMap()
 *
 ******************************************************************************************************************/

    var Cliqr = (function($) {

        // initialise
        // var shared values
        var rcfg = {
            df: new Object,
        	dr: new Object,
            dc: new Object,
            tables: '', tabletype: '', fields: '',
            where: '', sql: '', filterby: [],
            ceditor: {}, jeditor: {}, teditor: {},
        }, cfg = {};

        var _set = function(key,value)
        {
            rcfg[key] = value;
            return rcfg[key];
        }

        var _get = function(key)
        {
            return rcfg[key];
        }

        /** Report Generator
         * 
         * reportDesigner()
         * - generateSql()
         * - 
         * 
         *************************************************************************************/  

            /** Report Generator  
             *
             * @param - Object with options
             * @return - 
             **/
             var reportDesigner = function(opts)
             {               
                cfg = Cliq.config();
                console.log('Reportdesigner JS loaded');
                
                rcfg.df = new Vue({
                    el: '#reportdesigner',
                    data: opts.defaultdata, // New report or data from saved record
                    methods: {

                        // Buttons at bottom of form, apply to form as a whole
                        clickbutton: function(evt) {
                            var action = evt.target.id;
                            switch(action) {
                               
                                // Preview data using prettyPrint
                                case "viewbutton":
                                    var opts = {
                                        contentSize: {
                                            width: 480,
                                            height: 600
                                        },
                                        content: prettyPrint(this.formdef),
                                        headerTitle: lstr[7]
                                    };
                                    Cliq.win(opts);
                                break;
                                
                                // Resets the data to default settings
                                case "resetbutton":
                                    this.$data = opts.defaultdata;
                                break;
                                
                                // Generates a preview of the report
                                case "generatebutton":
                                    previewReport(this.formdef);
                                break;
                                
                                // Save form definition to database
                                case "savebutton":
                                    updateReport(this.formdef);
                                break;

                            }
                        },

                        // Selects table types appropriate to table selected
                        modelChange: function(e) {
                            var table = e.target.value;
                            var tabletypes = [];
                            $("select[data-name='tabletype'] option").each(function() {
                                if(stristr($(this).data('table'), table) === false) {
                                    $(this).remove();
                                };
                            });
                        },  

                        // Set column in array and then clear for new record OK
                        // Works and works again
                        clickupdate: function(evt, xid) {

                            // Updates the main array from the form
                            this.formdef.d_columns[this.coldef.xid] = {
                                'd_colid': this.coldef.colid, 'd_colname': this.coldef.colname, 'd_colstart': this.coldef.colstart, 'd_colend': this.coldef.colend, 'd_coltype': this.coldef.coltype, 'd_colattrs': this.coldef.colattrs
                            };  
                            var str = makeCol({
                                'xid': this.coldef.xid,
                                'd_colid': this.coldef.colid,
                                'd_colstart': this.coldef.colstart,
                                'd_colend': this.coldef.colend,
                                'd_coltype': this.coldef.coltype,
                                'd_colname': this.coldef.colname
                            });
                            $('#gridwrapper').append(str);

                            // Creates a new coldef array
                            // How many columns in the d_columns array?
                            var numcols = count(this.formdef.d_columns);
                            xid = numcols+1;
                            // Cliq.success(xid);
                            this.coldef =  {'xid': xid, 'colid': '', 'colname': '', 'colstart': '1', 'colend': '1', 'coltype': 'text', 'colattrs': ''};
                        },   

                        // Delete column from form, data and grid
                        clickdelete: function(evt, xid) {
                            // Identify column from d_colid

                            Vue.delete(this.formdef.d_columns, xid);

                            // How many columns now in the d_columns array?
                            var numcols = count(this.formdef.d_columns);

                            // Clear the form
                            this.coldef = {'xid': numcols+1, 'colid': '', 'colname': '', 'colstart': '1', 'colend': '1', 'coltype': 'text', 'colattrs': ''};

                            // Clear the column from Gridwrapper
                            $('div[data-id="'+xid+'"]').remove();
                        }
                    },
                    mounted: function() {
                        console.log('Vue loaded');
                        var that = this;

                        // Write a title and instructions to the grid on start
                            // <div class="gridi pointer" style="grid-column: 1/2; grid-row: 4/5;" data-id="id" v-on:click="clickrow(arraykey)">Id</div>
                            var tirow = `
                                <div class="gridi" style="grid-column: 1/12; grid-row: 1/2;" id="grid_c_common">title</div>
                                <div class="gridi" style="grid-column: 1/24; grid-row: 2/3;" id="grid_c_options">instructions</div>
                                <div class="gridi" style="grid-column: 12/18; grid-row: 1/2;" id="grid_c_parent">table</div>
                                <div class="gridi" style="grid-column: 18/24; grid-row: 1/2;" id="grid_c_order">type</div>
                            `;
                            $('#gridwrapper').html(tirow);    

                            // This puts an initial column on tyhe Grid with an ID of 'id' and XID = 1
                            $.each(this.formdef.d_columns, function(xid, col) {
                                var str = makeCol({
                                    'xid': xid,
                                    'd_colid': col.d_colid,
                                    'd_colstart': col.d_colstart,
                                    'd_colend': col.d_colend,
                                    'd_colname': col.d_colname,
                                    'd_coltype': col.d_coltype
                                });
                                $('#gridwrapper').append(str); 
                            });  

                        // Field support function

                            // Refers to report reference
                            $('.isunique').on('blur', function() {
                                var fldid = $(this).attr('id');
                                var thisfld = $('#'+fldid);  
                                var toslug = $(thisfld).val();
                                $(thisfld).val( $.slugify(toslug) );                                                             
                                modInput(fldid);    
                            });

                            // TOML editor for column attributes
                            $('.toml').each(function() {
                                var fldid = $(this).attr('id'); // d_fldattrs
                                var thisfld = $('#'+fldid);                              
                                var tomledid = document.getElementById(fldid);
                                rcfg.ceditor = CodeMirror.fromTextArea(tomledid,{
                                    lineNumbers: true,
                                    mode: "toml"
                                });
                                var tomlcontent = thisfld.val();
                                rcfg.ceditor.getDoc().setValue(tomlcontent);                                
                            })

                        // Delegation ......
                            $('.gridwrapper').on('click', 'div.pointer', function(e) {
                                var xid = $(this).data('id');
                                // Cliq.success(xid);
                                // Then
                                var cl = that.formdef.d_columns[xid];
                                that.coldef = {
                                    'colid': cl.d_colid, 'colname': cl.d_colname, 'colstart': cl.d_colstart, 'colend': cl.d_colend, 'colattrs': cl.d_colattrs, 'xid': xid, 'coltype': cl.d_coltype
                                };
                                $('#d_groupby option:gt(0)').remove();
                                $('#d_sortby option:gt(0)').remove();
                                $.each(that.formdef.d_columns, function(i, val) {
                                    $("#d_groupby").append($("<option></option>").attr("value", val.d_colid).text(val.d_colname));
                                    $("#d_sortby").append($("<option></option>").attr("value", val.d_colid).text(val.d_colname));
                                }); 
                            });
                        
                    },
                    watch: {
                        // Title
                        'formdef.c_common': function(val) { $('#grid_c_common').text(val); },
                        // Instructions or description
                        'formdef.c_options': function(val) { $('#grid_c_options').text(val); },
                        // Table
                        'formdef.c_parent': function(val) { 
                            $('#grid_c_parent').text(val); 
                            rcfg.df.$data.formdef.c_reference = val+'_'+rcfg.df.$data.formdef.c_order;
                        },
                        // Tabletype
                        'formdef.c_order': function(val) { 
                            $('#grid_c_order').text(val); 
                            rcfg.df.$data.formdef.c_reference = rcfg.df.$data.formdef.c_parent+'_'+val;
                        },
                        'formdef.d_columns': function(valarray) {
                            $('#d_groupby option:gt(0)').remove();
                            $('#d_sortby option:gt(0)').remove();
                            $.each(valarray, function(i, val) {
                                $("#d_groupby").append($("<option></option>").attr("value", val.d_colid).text(val.d_colname));
                                $("#d_sortby").append($("<option></option>").attr("value", val.d_colid).text(val.d_colname));
                            });
                        }
                    }
                });

                /*
                // Grid Wrapper
                $('.gridi').each(function(t) {
                    var cl = $(this).attr('rel');
                    console.log(cl);
                    var grdi = explode('-', cl);
                    $(this).css({'grid-column': grdi[0]+'/'+grdi[1], 'grid-row': grdi[2]+'/'+grdi[3]});
                });
                */
             } 

            /* Normalise a routine 
             * de-replicate the routine to make a column
             **/
             var makeCol = function(col)
             {
                return '<div class="gridi pointer" data-id="'+col.xid+'" style="grid-column: '+col.d_colstart+'/'+col.d_colend+'; grid-row: 4/5;">'+col.d_colname+'</div>';
             }

            /** Display menu of reports  
             *
             * @return - 
             **/
             var displayReports = function()
             {
                cfg = Cliq.config();
                // console.log(fldid, action, table, tabletype);
                var urlstr = '/ajax/'+jlcd+'/listreports/dbcollection/report/';
                aja().method('GET').url(urlstr).cache(false)
                .on('40x', function(response) {Cliq.error('Page not Found - '+urlstr+':'+response);})
                .on('500', function(response) {Cliq.error('Server Error - '+urlstr+':'+response);})
                .on('timeout', function(response) {Cliq.error('Timeout - '+urlstr+':'+response);})
                .on('success', function(response) {
                    if(typeof response == 'object') {
                        // Test NotOK - value already exists
                        var match = /NotOk/.test(response.flag);
                        if(!match == true) { 

                            var opts = {
                                contentSize: {
                                    width: 640,
                                    height: 400
                                },
                                content: response.data,
                                headerTitle: response.title
                            };
                            Cliq.win(opts);

                            // List reports icons
                            $('.reporticon').on('click', function(e) {
                                var dta = $(this).data();
                                switch(dta.action) {

                                    case "editicon":
                                        var urlstr = '/admindesktop/en/reportdesigner/dbitem/report/?recid='+dta.recid;
                                        uLoad(urlstr);
                                    break;

                                    case "viewicon":
                                        runReport(dta.reference);
                                    break;

                                    case "deleteicon":
                                        cfg = Cliq.config();
                                        var params = {
                                            table: 'dbcollection',
                                            tabletype: 'report',
                                            type: 'delete', // deletebefore
                                            recid: dta.recid,
                                            action: 'deleterecord',
                                            before: '',
                                            displaytype: 'reportgenerator',
                                            msg: lstr[27]+': '+dta.recid   
                                        };
                                        return Cliqf.deleteRecords(params);
                                    break;

                                }
                            });

                        } else { Cliq.error('Ajax function returned error NotOk - '+response.msg); }; 
                    } else { Cliq.error('Response was not JSON object - '+urlstr+':'+response); }
                }).go(); 
             }

            /** All the form and other buttons  
             *
             * @return - activities and functions 
             **/
             var actionButton = function(action)
             {
                switch(action) {

                    default: Cliq.success(action); break;
                }
             }

            /** Is unique 
             * Next Reference, Next ID, Is Unique
             * @param - 
             * @param -
             * @return - 
             **/
             var modInput = function(fldid) // eg 'reference'
             {               
                cfg = Cliq.config();
                // console.log(fldid, action, table, tabletype);
                var urlstr = '/ajax/'+cfg.langcd+'/'+action+'/'+cfg.table+'/'+cfg.tabletype+'/';
                aja().method('GET').url(urlstr)
                .data({fld: fldid, prefix:'', currval: $('#'+fldid).val() }).cache(false)
                .on('40x', function(response) {Cliq.error('Page not Found - '+urlstr+':'+response);})
                .on('500', function(response) {Cliq.error('Server Error - '+urlstr+':'+response);})
                .on('timeout', function(response) {Cliq.error('Timeout - '+urlstr+':'+response);})
                .on('success', function(response) {
                    
                    if(typeof response == 'object')
                    {
                        // Test NotOK - value already exists
                        var match = /NotOk/.test(response.flag);
                        if(!match == true) { 

                            if(response.data) {
                                $('#'+fldid).val('');
                                $('#'+fldid).focus();
                                Cliq.msg({type: 'warning', buttons: false, text: 'Value already exists'});
                            };

                        } else { Cliq.error('Ajax function returned error NotOk - '+urlstr+':'+JSON.stringify(response)); }; 
                    } else { Cliq.error('Response was not JSON object - '+urlstr+':'+response.msg); }
                }).go();    
             }

            /** Function to generate a dummy SQL for display purposes 
             * 
             **/
             var generateSql = function()
             {
                // Start
                rcfg.sql = 'SELECT ';

                // Fields
                if(rcfg.fields != '') {
                    rcfg.sql += rcfg.fields;
                    rcfg.sql = trim(rcfg.sql, ',');
                } else {
                    rcfg.sql += '*';
                };

                // Table
                rcfg.sql += ' FROM ';
                if(rcfg.tables != '') {
                    rcfg.tables = trim(rcfg.tables, ',');
                    var tt = explode(',', rcfg.tables);
                    rcfg.sql += tt[0];
                };

                // Table type or first where
                if(rcfg.tabletype != '') {
                    if(strstr(rcfg.where, 'c_type') === false) {
                        rcfg.where += ' c_type = ?';
                        rcfg.filterby[0] = rcfg.tabletype;                        
                    }
                };

                // Add other Filter by to where
                if(rcfg.where != '') {
                   rcfg.sql += ' WHERE '+rcfg.where; 
                };

                cfg.df.$data.formdef.filterby = rcfg.filterby;
                cfg.df.$data.formdef.tabletype = rcfg.tabletype;
                cfg.df.$data.formdef.sql = rcfg.sql;
             }

            /** Update a Report in the Database  
             *
             * @param - object array
             * @internal - sends report definition to server and displays success message or otherwise
             **/
             var updateReport = function(def)
             {
                cfg = Cliq.config();
                var formdata = JSON.stringify(def);
                var urlstr = '/ajax/'+jlcd+'/updatereport/dbcollection/report/';
                aja().method('POST').url(urlstr)
                .data({'formdef':rawurlencode(formdata)}).cache(false)
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
                        } else { Cliq.error('Ajax function returned error NotOk - '+response.msg); }; 
                    } else { Cliq.error('Response was not JSON object - '+urlstr+':'+response.msg); }
                }).go();
             }

        /** General report routines
         *
         * previewReport()
         * runReport()
         * printReport()
         * displayImages()
         * 
         *************************************************************************************/   

            /** Preview a report from Report Designer 
             *
             * @param - array object - unsaved definition
             * @internal - generates a popup window and sends the definition to the PHP in a semi sql state, 
             * so that the PHP returns a table of data or JSON string 
             **/
             var previewReport = function(def)
             {
                cfg = Cliq.config();
                var formdata = JSON.stringify(def);
                var urlstr = '/ajax/'+jlcd+'/previewreport/dbcollection/report/';
                aja().method('POST').url(urlstr)
                .data({'formdef':rawurlencode(formdata)}).cache(false)
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
                                contentSize: {
                                    width: 800,
                                    height: 600
                                },
                                content: response.data,
                                headerTitle: lstr[7]+': '+def.c_common
                            };
                            Cliq.win(opts);

                        } else { Cliq.error('Ajax function returned error NotOk - '+response.msg); }; 
                    } else { Cliq.error('Response was not JSON object - '+urlstr+':'+response); }
                }).go();  
             }

            /** Finds out if the Report has to have runtime variables updated 
             * displays popup to ask questions then calls new window with report
             * @param - string reference of a report, as Recid or c_reference
             * @internal - displays Report in new Window for printing
             **/
             var runReport = function(ref) 
             {
                cfg = Cliq.config();
                var urlstr = '/ajax/'+jlcd+'/getreport/'+cfg.table+'/'+cfg.tabletype+'/';
                aja().method('GET').url(urlstr).cache(false).timeout(2500).type('json')
                .data({reportref: ref, reporttype: 'popupreport'})
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
                                contentSize: {width: 600, height: 470},
                                content: response.html,
                                headerTitle: response.title
                            };
                            Cliq.win(opts);

                        } else { Cliq.error('Ajax function returned error NotOk - '+urlstr+':'+JSON.stringify(response)); }; 
                    } else { Cliq.error('Response was not JSON object - '+urlstr+':'+response.msg); }
                }).go(); 
             }

            /** Print Report in new Window 
             *
             * @param - 
             * @internal
             **/
             var printReport = function() 
             {
                cfg = Cliq.config();
                           
                var urlstr = '/ajax/'+jlcd+'/printreport/'+cfg.table+'/'+cfg.tabletype+'/';
                aja().method('GET').url(urlstr).cache(false).timeout(2500).type('json')
                .data({reporttype: cfg.reporttype})
                .on('40x', function(response) {Cliq.error('Page not Found - '+urlstr+':'+response);})
                .on('500', function(response) {Cliq.error('Server Error - '+urlstr+':'+response);})
                .on('timeout', function(response) {Cliq.error('Timeout - '+urlstr+':'+response);})
                .on('success', function(response) {
                    if(typeof response == 'object')
                    {
                        // Test NotOK - value already exists
                        var match = /NotOk/.test(response.flag);
                        if(!match == true) {
                                                       
                            if(cfg.reporttype == "columnreport") {
                                $('#columnform').empty().html(response.html);
                            } else { // Popupreport
                                var ropts = response.options.reportheader;
                                console.log(ropts);
                                // Create better formatted title
                                var opts = {
                                    content: '<div class="col mr10 pad">'+response.html+'</div>',
                                    contentSize: {
                                        width: ropts.width,
                                        height: ropts.width
                                    },
                                    paneltype: 'modal',
                                    headerTitle: '<span class="">'+ropts.title+'</span>'
                                };
                                var formPopup = Cliq.win(opts);
                            }                             

                        } else { Cliq.error('Ajax function returned error NotOk - '+urlstr+':'+JSON.stringify(response)); }; 
                    } else { Cliq.error('Response was not JSON object - '+urlstr+':'+response.msg); }
                }).go();    
             }

            /** Display a Gallery of images 
             *
             * @param - 
             * @internal
             **/
             var displayImages = function()
             {
                cfg = Cliq.config();
                           
                var urlstr = '/ajax/'+jlcd+'/displayimages/'+cfg.table+'/'+cfg.tabletype+'/';
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
                                                       
                            var ropts = response.options.reportheader;
                            console.log(ropts);
                            // Create better formatted title
                            var opts = {
                                content: '<div class="col mr10 pad">'+response.html+'</div>',
                                contentSize: {
                                    width: ropts.width,
                                    height: ropts.width
                                },
                                paneltype: 'modal',
                                headerTitle: '<span class="">'+ropts.title+'</span>',
                                callback: [
                                    function() {
                                        Galleria.loadTheme(jspath+'galleria.classic.min.js');
                                        Galleria.run('.galleria', {
                                            theme: 'classic'
                                        });
                                    }
                                ]
                            };
                            var formPopup = Cliq.win(opts);
                              
                        } else { Cliq.error('Ajax function returned error NotOk - '+urlstr+':'+JSON.stringify(response)); }; 
                    } else { Cliq.error('Response was not JSON object - '+urlstr+':'+response.msg); }
                }).go();            
             }

        // explicitly return public methods when this object is instantiated
        return {
            // outside: inside
            reportDesigner: reportDesigner,
            displayReports: displayReports,
            displayImages: displayImages,
            runReport: runReport,
            set: _set,
            get: _get
        };    
    })(jQuery); 

/*
    var tw = {
    callback: function (value) { 
        console.log('TypeWatch callback: (' + (this.type || this.nodeName) + ') ' + value); 
    }, wait: 750, highlight: true, allowSubmit: false, captureLength: 3};
    $(this.$el).typeWatch(tw);
*/

    (function($, window) {
      $.fn.replaceOptions = function(options) {
        var self, $option;

        this.empty();
        self = this;

        $.each(options, function(index, option) {
          $option = $("<option></option>")
            .attr("value", option.value)
            .text(option.text);
          self.append($option);
        });
      };
    })(jQuery, window);


/*
                        // Selecting a table from select:id="tables" gets options for select:id = "tabletypes"
                            $('select[name="c_parent"]').on('change', function(e) {
                                var selected = $(this).val();
                                $.each(selected, function(k,s) {
                                    var pts = explode(':', s);
                                    rcfg.tables += pts[0]+',';
                                    if(count(pts) == 2) {
                                        if(strstr(rcfg.tabletype, pts[1]) === false) {rcfg.tabletype += pts[1];};
                                    };                                
                                });
                                rcfg.df.$data.formdef.tables = rcfg.tables;
                                generateSql();
                                // We have a table, such as dbcollection > go and get tabletypes
                                var urlstr = '/ajax/'+jlcd+'/getfields/'+rcfg.table+'/'+rcfg.tabletype+'/';
                                aja().method('GET').url(urlstr).cache(false).timeout(2500).type('json')
                                .on('40x', function(response) {Cliq.error('Page not Found - '+urlstr+':'+response);})
                                .on('500', function(response) {Cliq.error('Server Error - '+urlstr+':'+response);})
                                .on('timeout', function(response) {Cliq.error('Timeout - '+urlstr+':'+response);})
                                .on('success', function(response) {
                                    if(typeof response == 'object') {
                                        // Test NotOK - value already exists
                                        var match = /NotOk/.test(response.flag);
                                        if(!match == true) { 
                                            var $el = $("#fieldselect");
                                            $.each(response.fldoptions, function(n, obj) {
                                                $el.append($("<option></option>").attr("value", obj.value).text(obj.label));
                                            });
                                            rcfg.df.$data.fldoptions = response.fldoptions;   
                                        } else { Cliq.error('Ajax function returned error NotOk - '+urlstr+':'+JSON.stringify(response)); }; 
                                    } else { Cliq.error('Response was not JSON object - '+urlstr+':'+response.msg); }
                                }).go();  
                            }); // End tableselect

                        // Now get the fields to put into the Select statement and populate the columns
                            $('#fieldselect').on('change', function(e) {
                                var selected = $(this).val();
                                $.each(selected, function(k,s) {
                                    if(strstr(rcfg.fields, s) === false) { rcfg.fields += s+','; };                         
                                });
                                rcfg.df.$data.formdef.fields = rcfg.fields;
                                generateSql();

                                // Now we can generate the parts of the form that are dependent on Tabls and Fields, such as Columns, Group, Filter By and Sort Order
                            });
*/