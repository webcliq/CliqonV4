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
            tables: '',
            tabletype: '',
            fields: '',
            where: '',
            sql: '',
            filterby: []
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
                cfg = Clq.config();

                cfg.df = new Vue({
                    el:'#reportdesigner',
                    data: {
                        toptabs: opts.tabs,
                        sections: opts.sections,
                        buttons: opts.buttons,
                        fldoptions: {},
                        formdef: {
                            tables: '',
                            tabletype: '',
                            fields: '',
                            sql: '',
                            filterby: []
                        }
                    },
                    methods: {
                        clickbutton: function(evt) {
                            var action = evt.target.id;
                            switch(action) {
                               
                                //  
                                case "viewbutton":
                                    rcfg.dr.open();
                                break;
                                
                                //
                                case "resetbutton":
                                    Cliq.success('Reset');
                                break;
                                
                                //
                                case "generatebutton":
                                    Cliq.success('Generate - make a report in the popup from the parameters');
                                break;
                                
                                //
                                case "savedreportsbutton":
                                    Cliq.success('List saved reports in popup');
                                break;
                                
                                //
                                case "newreportbutton":
                                    Cliq.success('Create new report - same as reset');
                                break;
                                
                                //
                                case "savebutton":
                                    Cliq.success('Save form to new report - popup');
                                break;
                            }
                        }
                    },
                    mounted: function() {
                        $('#tab_collections').addClass('active');
                        $('#collections').addClass('active');
                        $('#collections').tab('show');

                        rcfg.dr = $("#previewdialog").dialog({
                            autoOpen: false,
                            uiLibrary: 'bootstrap4',
                            resizable: true,
                            minWidth: 200,
                            maxWidth: 600,
                            minHeight: 200,
                            maxHeight: 450,
                            height: 350,
                            modal: false
                        });
                
                        $('#repdestabs a').click(function(e) {
                            e.preventDefault();
                            var id = $(this).data('tabid');
                            console.log(id);
                            $('.tab-pane').removeClass('active');
                            $('.nav-link').removeClass('active');
                            $('#'+id).addClass('active');
                            $(this).addClass('active');
                            $(this).tab('show');
                        });

                        // Selecting a table from select:id="tables" gets options for select:id = "tabletypes"
                        $('#collectionselect').on('change', function(e) {
                            var selected = $(this).val();
                            $.each(selected, function(k,s) {
                                var pts = explode(':', s);
                                rcfg.tables += pts[0]+',';
                                if(count(pts) == 2) {
                                    if(strstr(rcfg.tabletype, pts[1]) === false) {rcfg.tabletype += pts[1];};
                                };                                
                            });
                            cfg.df.$data.formdef.tables = rcfg.tables;
                            generateSql();
                            // We have a table, such as dbcollection > go and get tabletypes
                            var urlstr = '/ajax/'+jlcd+'/getfields/'+rcfg.tables+'/'+rcfg.tabletype+'/';
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
                                        cfg.df.$data.fldoptions = response.fldoptions;   
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
                            cfg.df.$data.formdef.fields = rcfg.fields;
                            generateSql();

                            // Now we can generate the parts of the form that are dependent on Tabls and Fields, such as Columns, Group, Filter By and Sort Order
                        });
                    }
                });
            } 

            /**
             * Function to generate a dummy SQL for display purposes
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

        /** General report routines
         * 
         * printRecords()
         * displayImages()
         * runReport()
         * 
         *************************************************************************************/   

            var printRecords = function() {
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
                                headerTitle: response.title,
                                content: response.html
                            };
                            Cliq.win(opts);

                        } else { Cliq.error('Ajax function returned error NotOk - '+urlstr+':'+JSON.stringify(response)); }; 
                    } else { Cliq.error('Response was not JSON object - '+urlstr+':'+response.msg); }
                }).go(); 
            }

        // explicitly return public methods when this object is instantiated
        return {
            // outside: inside
            reportDesigner: reportDesigner,
            printRecords: printRecords,
            displayImages: displayImages,
            runReport: runReport,
            set: _set,
            get: _get
        };    
    })(jQuery); 