/** Cliq.Js 
 * Ctrl K4 to fold
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
            idioms: {},
            langcd: jlcd,
            search: '', table: '', tabletype: '', displaytype: '', action: '',
            spinner: new Spinner(),
            formid: 'columnform',
            df: new Object, // Form
            dc: new Object, // Card
            dt: new Object, // Tree or Table etc
            dl: new Object, // List
            da: new Object, // Calendar
            dg: new Object, // Gallery or Grid
            dp: 1, // For Panels and Windows
            dz: {}, opts: {}, data: {}, app: {}, result: {}, row: {}, evt: {},
            subdir: 'tmp/', uploadurl: '/ajax/'+jlcd+'/fileupload/dbuser/', filescollection: 'file',
            // Datatable pagination and operational defaults
            orderby: 'c_reference|asc', 
            search: '',
            select: new Array,            
            records: {
                offset: 0,     
                limit: 15,                              
                start: 1,
                end: 15,
                total: 0,
                page: 1,
                totpages: 1
            },
            pager: {
                size: 3,
                recs: 15
            },
            bingkey: '', gmapsapi: ''
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

        /** Page, Table and Tree Routines
         * 
         * dataGrid()
         * dataTree()
         * dataTable()
         * dataCard()
         * dataList()
         * 
         * calendar()
         * gallery()
         * 
         *************************************************************************************/        

            /** DataGrid
             *
             * @param - 
             * @return - 
             **/
             var dataGrid = function(gridOptions)
             {
                cfg.opts = gridOptions;
                cfg.dg = $('#datagrid').grid(cfg.opts);   

                $('.gridbutton').on('click', function(e) {
                    e.preventDefault(); e.stopImmediatePropagation();
                    Cliq.gridButton(this);
                });  
             };

            /** DataTree
             *
             * @param - 
             * @return - 
             **/
             var dataTree = function(treeOptions)
             {
                console.log('Tree loaded '+cfg.treetype);
                cfg.opts = treeOptions;
                cfg.dt = $('#datatree');
                if(cfg.treetype == 'gjtree') {
                    cfg.dt.gjtree(cfg.opts);
                } else { // jqtree
                    cfg.dt.tree({
                        dataUrl: {
                            url: cfg.opts.dataurl,
                            data: {
                                'treetype': cfg.treetype,
                                'table': cfg.table,
                                'tabletype': cfg.tabletype,
                            }
                        },
                        closedIcon: $('<i class="fa fa-arrow-circle-right"></i>'),
                        openedIcon: $('<i class="fa fa-arrow-circle-down"></i>'),
                        dragAndDrop: true,
                        autOpen: 0,
                        onCreateLi: function(node, $li, is_selected) {
                            var tpl = '';
                            var cells = explode('|', node.id);
                            $.each(cells, function(key, itm) {
                                tpl += `<span class="ml5 table-cell" style="vertical-align:bottom;">`+itm+`</span>`;
                            });
                            $li.find('.jqtree-element').append(tpl+'<span style="vertical-align:bottom;" class="mb0" data-id="'+node.name+'">'+cfg.opts.icons+'</span>');
                        }
                    });

                    // Tree is Mounted, respond to events
                    cfg.dt.on('click', '.treeicon', function(evt) {
                        evt.stopImmediatePropagation(); evt.preventDefault();
                        var recid = $(this).closest('span').data('id');
                        var action = $(this).data('action');
                        rowicon(evt, recid, action);
                    });            

                    // This binds the move to 
                    cfg.dt.bind(
                        'tree.move',
                        function(event) {
                            event.preventDefault();
                            // do the move first, and _then_ POST back.
                            event.move_info.do_move();
                            cfg.data = $(this).tree('toJson');
                            cfg.evt = event;
                            saveTree();
                        }
                    );                    
                }
             };           

            /** DataTable 
             *
             * @param - 
             * @return - 
             **/
             var dataTable = function(tableOptions)
             {             
                cfg.opts = tableOptions;
                cfg.records = cfg.opts.records;
                // var pageselect = explode(',', cfg.opts.pager);

                cfg.dt = new Vue({
                    el: '#'+cfg.opts.tableId,
                    data: {
                        cols: cfg.opts.columns,
                        rows: {},
                        rowicons: cfg.opts.rowicons,
                        records: {
                            recordstxt: lstr[143],
                            fromtxt: lstr[140],
                            totxt: lstr[142],
                            start: cfg.records.start,
                            end: cfg.records.end,
                            total: cfg.records.total
                        },
                        selected: cfg.records.limit,
                        pagerselect: cfg.opts.pagerselect                  
                    },
                    methods: {

                        // Search, sort and filter buttons
                        searchbutton: function(event) {
                            cfg.records.offset = 0;
                            var colname = $(event.target).data('id');
                            cfg.search = colname+'|'+$('input[data-name="'+colname+'"]').val();
                            loadTableData();
                        },

                        // Clear the search field
                        clearbutton: function(event) {
                            var colname = $(event.target).data('id');
                            $('input[data-name="'+colname+'"]').val('');
                            if(cfg.search != '') {
                                cfg.search = '';
                                loadTableData();
                            }
                        },

                        // Row Buttons
                        rowbutton: function(event, row) {
                            var dta = $(event.target).data();
                            return rowicon(event, dta.recid, dta.action, dta);
                        },

                        // Sort or Order buttons
                        sortbutton: function(event) {
                            var colname = $(event.target).data('id');
                            cfg.opts.orderby = colname+'|asc';
                            loadTableData();                       
                        }

                    },
                    mounted: function() {
                        loadTableData();

                        // softblue

                        $('#pageselect').on('change', function(evt) {
                            cfg.records.limit = $(this).val();
                            loadTableData();
                        })
                    }
                });
             };

            /** DataList
             *
             * @param - 
             * @return - 
             **/
             var dataList = function(listOptions)
             {
                
                cfg.opts = listOptions;
                cfg.records = cfg.opts.records;

                cfg.dt = new Vue({
                    el: '#'+cfg.opts.tableId,
                    data: {
                        addbutton: lstr[16],
                        rows: {},
                        listicons: cfg.opts.listicons,
                        records: {
                            recordstxt: lstr[143],
                            fromtxt: lstr[140],
                            totxt: lstr[142],
                            start: cfg.records.start,
                            end: cfg.records.end,
                            total: cfg.records.total
                        },
                        selected: cfg.records.limit,
                        pagerselect: cfg.opts.pagerselect                                            
                    },
                    methods: {
                        // Search, sort and filter buttons
                        searchbutton: function(event) {
                            cfg.records.offset = 0;
                            cfg.search = 'c_reference|'+$('#searchfield').val();
                            loadTableData();
                        },

                        // Clear the search field
                        clearbutton: function(event) {
                            $('#searchfield').val('');
                            if(cfg.search != '') {
                                cfg.search = '';
                                loadTableData();
                            }
                        },
                        listButton: function(evt, collection, key, action) {
                            var dta = $(evt.target).data();
                            return rowicon(evt, collection.id, action, dta);
                        },
                        // All top buttons by action
                        topButton: function(e, action) {
                            switch(action) {
                                case "addrecord":
                                    Cliqf.crudButton(0, 'insert');
                                break;

                                case "helpbutton":
                                    helpButton();
                                break;

                                case "resetbutton":
                                    reLoad();
                                break;

                                case "printbutton":
                                    Cliqr.printRecords();
                                break;

                                default: msg({type: 'information', buttons: false, text: action}); break;
                            };
                            return true;
                        } 
                    },
                    mounted: function() {
                        loadTableData();

                        $('#pageselect').on('change', function(evt) {
                            cfg.records.limit = $(this).val();
                            loadTableData();
                        })
                    }
                }); 
             };            

            /** DataCard  
             * Publishes a series of Panels in a grid
             * @param - object - options passed to the function from the calling PHP
             * @return - HTML to populate the id: admindesktop
             **/
             var dataCard = function(cardOptions)
             {
                cfg.opts = cardOptions;
                // console.log(cfg.opts.data);
                var dta = cfg.opts;

                cfg.dc = new Vue({
                    el: '#datacard',
                    data: {
                        admdatacards: cfg.opts.data,
                        admfooter: lstr[16]
                    },
                    methods: {
                        // Displays popup window with contents of c_document, usually JSON, in a formatted and easily readable style
                        viewButton: function(evt, collection, key) {
                            Cliqv.viewButton(collection.id);   
                        },
                        editButton: function(evt, collection, key) {
                            Cliqf.crudButton(collection.id, 'update');
                        },
                        deleteButton: function(evt, collection, key) {
                            Cliqf.deleteButton(collection.id);
                        }, 
                        topButton: function(e, action) {
                            switch(action) {
                                case "addrecord":
                                    Cliqf.crudButton(0, 'insert');
                                break;

                                case "helpbutton":
                                    helpButton();
                                break;

                                case "reportbutton":
                                    Cliqr.printRecords();
                                break;

                                case "resetbutton":
                                    reLoad();
                                break;

                                default: msg({type: 'information', buttons: false, text: action}); break;
                            }
                        } 
                    },
                    mounted: function() {
                        $('.fit').each(function(e) {
                            var id = $(this).attr('id');
                            $('#'+id).flowtype({minimum: 200, maximum: 580, minFont: 14, maxFont: 40});
                        });      
                    }
                }); // End Vue routine
             };

            /** Calendar
             * now using DHTMLX Scheduler instead of FullCalendar - better presentation and handling of data
             * @param - 
             * @return - 
             **/
             var calendar = function(calOptions)
             {
                cfg.opts = calOptions;
                console.log(cfg.opts);
                cfg.app = cfg.opts.xtra;
                // cfg.opts = $(cfg.opts).not(cfg.opts.xtra).get();

                $.each(cfg.opts.config, function(key, val) {
                    scheduler.config[key] = val;
                });

                $.each(cfg.opts.locale.labels, function(key, val) {
                    scheduler.locale.labels[key] = val;
                }); 

                // default values for filters
                var filters = {};
                $.each(cfg.app.filters, function(key, val) {
                    filters[key] = val;
                }); 

                /*
                Change in Admin.Php
                var filter_inputs = document.getElementById("filters_wrapper").getElementsByTagName("input");
                for (var i=0; i<filter_inputs.length; i++) {
                    var filter_input = filter_inputs[i];

                    // set initial input value based on filters settings
                    filter_input.checked = filters[filter_input.name];

                    // attach event handler to update filters object and refresh view (so filters will be applied)
                    filter_input.onchange = function() {
                        filters[this.name] = !!this.checked;
                        scheduler.updateView();
                    }
                };

                // here we are using single function for all filters but we can have different logic for each view
                scheduler.filter_month = scheduler.filter_day = scheduler.filter_week = function(id, event) {
                    // display event only if its type is set to true in filters obj
                    // or it was not defined yet - for newly created event
                    if (filters[event.type] || event.type == scheduler.undefined) {
                        return true;
                    }

                    // default, do not display event
                    return false;
                };


                // Template needs developing
                scheduler.config.lightbox.sections = [    
                    {name:"text", height:30, map_to:"text", type:"textarea", focus:true},
                    {name:"url", height:30, map_to:"url", type:"textarea"},
                    {name:"description", height:130, map_to:"details", type:"textarea"},
                    {name:"type", height:23, type:"select", options: cfg.app.categories, default_value: "task", map_to:"type" },
                    {name:"time", height:72, type:"time", map_to:"auto"}
                ];
                */
                scheduler.templates.tooltip_date_format = scheduler.date.date_to_str("%Y-%m-%d %H:%i:%s");
                $("#admincalendar").dhx_scheduler({
                    xml_date:"%Y-%m-%d %H:%i",
                    date:new Date(),
                    mode:"month"
                });
                scheduler.load('/ajax/'+jlcd+'/getcalendardata/'+cfg.table+'/'+cfg.tabletype+'/', 'json');    

                $('#export_pdf').on('click', function() {
                    scheduler.toPDF("http://dhtmlxscheduler.appspot.com/export/pdf", "color");
                });

                // fires when the user adds a new event to the scheduler
                scheduler.attachEvent("onEventAdded", function(id, ev){
                    return manageEvent(ev, id, 'insert');
                });

                // occurs after the user has edited an event and saved the changes 
                // after clicking on the edit and save buttons in the event's bar or in the details window
                scheduler.attachEvent("onEventChanged", function(id, ev){
                    return manageEvent(ev, id, 'update');
                });

                scheduler.attachEvent("onEventDeleted", function(id){
                    return manageEvent({}, id, 'delete');
                });

                /*
                scheduler.attachEvent("onClick", function (id, e) {
                    manageEvent(e, id, 'view');
                    return true;
                });
                */
             };

            /** Gallery
             * our own Gallery with Vue, not Galeria. Still best to use this for front end
             * @param - 
             * @return - 
             **/
             var gallery = function(galOptions)
             {
               
                cfg.opts = galOptions;
                console.log(cfg.opts.data);                 
                cfg.dg = new Vue({
                    el: '#gallery',
                    data: {
                        admimages: cfg.opts.data
                    },
                    methods: {
                        editRecord: function(evt, collection) {
                            Cliqf.crudButton(collection.id, 'update'); 
                        },
                        deleteRecord: function(evt, collection) {
                            Cliqf.deleteButton(collection.id);
                        }
                    },
                    mounted: function() {

                        $('.topbutton').on('click', function(e) {
                            var action = $(this).attr('id');
                            switch(action) {
                                // Displays popup window with contents of c_document, usually JSON, in a formatted and easily readable style
                                case "resetbutton": reLoad(); break;                                    
                                case "addrecord":  Cliqf.crudButton(0, 'insert'); break;
                                case "helpbutton": helpButton(); break;
                                case "reportbutton": Cliqr.displayImages(); break;
                            }                    
                        })
                    }
                }); // End Vue routine
             };    

        /** Events
         * 
         * topbtn()
         * rowbtn()
         * rowicon()
         * helpButton()
         * utilButton()      
         *
         **********************************************************************************************************/ 

            /** Top of Page Buttons
             * Reacts to top button a table or tree
             * @param - object - the button
             * @return - the activity
             **/
             var topbtn = function(btn)
             {
                var dta = $(btn).data();
                switch(dta.action) {
                    case "addrecord":
                    case "addbutton":
                        cfg.formid = dta.formid;
                        cfg.formtype = dta.formtype;
                        return Cliqf.crudButton(0, 'insert');                    
                    break;
                    case "editrecord":
                    case "editbutton":
                        cfg.formid = dta.formid;
                        cfg.formtype = dta.formtype;
                        return Cliqf.crudButton(dta.recid, 'update');                    
                    break;
                    case "helpbutton": helpButton(); break;
                    case "resetbutton": reLoad(); break;
                    case "savetree":
                        if(cfg.treetype == 'jqtree') {
                            cfg.data = cfg.dt.tree('toJson');
                            saveTree();
                        } else { // gjtree

                        }                        
                    break;
                    case "reportbutton":
                        if( $(btn).data('type') ) {
                            var type = $(btn).data('type');
                            Cliqr.runReport(type);
                        } else {
                            Cliqr.printRecords();
                        } 
                    break;
                    case "utilbutton": utilButton(btn); break;

                    // More top buttons here
                    case "addbuttonce":
                        cfg.formid = dta.formid;
                        cfg.formtype = dta.formtype;
                        return Cliqf.creatorButton(0, 'insert');                    
                    break;
                    case "userprofile":
                        cfg.formid = '#dataform';
                        cfg.formtype = 'popupform';
                        cfg.table = 'dbuser';
                        cfg.tabletype = ''; 
                        console.log(dta.uid);        
                        return Cliqf.crudButton(dta.uid, 'update');  
                    break;

                    // Group of Admin jStrings buttons
                    case "admjs_addbutton":
                        editTable(btn, 'add');                    
                    break;
                    case "admjs_helpbutton": 
                        cfg.table = 'admjstrings';
                        cfg.tabletype = '';
                        helpButton();
                    break;
                    case "admjs_resetbutton":
                        $('th input').val('');
                        cfg.dg.reload();
                    break;
                    case "admjs_reportbutton":
                        cfg.table = 'admjstrings';
                        cfg.tabletype = '';
                        success('AdminjStrings Report to do');
                    break;
     
                    // Group of Site Update buttons
                    case "siteupd_helpbutton": 
                        cfg.table = 'admsiteupdate';
                        cfg.tabletype = '';
                        helpButton();
                    break;
                    case "siteupd_resetbutton": 
                        success('Reset Site Update display');
                    break;
                    case "siteupd_reportbutton":
                        cfg.table = 'admsiteupdate';
                        cfg.tabletype = '';
                        success('Site Update report');
                    break;

                    case "repdes_helpbutton": 
                        cfg.table = 'reportdesigner';
                        cfg.tabletype = '';
                        helpButton();
                    break;

                    case "newreportbutton":
                        var urlstr = '/admindesktop/'+jlcd+'/reportdesigner/dbitem/report';
                        uLoad(urlstr);
                    break;

                    case "savedreportsbutton":
                        Cliqr.displayReports();
                    break;     

                    case "cancel":
                    case "addarticle":
                    case "editarticle":
                    case "viewarticle":
                        return Cliqb.actionButton(dta);
                    break;               

                    // Ends
                    default: success(dta.action); break;
                }
             };

            /** Top of Grid buttons  
             * Reacts to top button a table or tree
             * @param - object - the button
             * @return - the activity
             **/
             var gridbtn = function(btn)
             {
                var dta = $(btn).data();
                switch(dta.action) {
                    case "changetable":
                        cfg.table = dta.table;
                        $('#gridtitle').empty().html(cfg.table);
                        return cfg.dg.reload({table:cfg.table});                    
                    break;
                    // Ends
                    default: success(action); break;
                }
             };

            /** Generic Row Button for Grids etc. with extra params, pointer to rowicon
             * @param - object - Event
             * @internal - pointer for rowicon as grids have to interrogated for recid and action
             **/
             var rowbtn = function(e)
             {
                var recid = $(e).closest('tr').find('td:first').text();
                var dta = $(e).data();
                rowicon(e, recid, dta.action, dta);
             };

            /** Generic Row Icon for Tables etc.
             * @param - object - Event
             * @param - array - Row data
             * @return the the action
             **/
             var rowicon = function(event, recid, action, dta)
             {
                cfg.recid = recid;
                switch(action) {

                    // Edit Button
                    case "editrecord": return Cliqf.crudButton(recid, 'update'); break;
                    
                    // Main Content Editor
                    case "editcontent": return Cliqf.contentButton(recid); break;

                    // Main Content Editor
                    case "editcode": return Cliqf.codeButton(recid); break;

                    // Run Report
                    case "runreport": return Cliqr.runReport(recid); break;

                    // Delete
                    case "deleterecord": return Cliqf.deleteButton(recid); break;

                    // View
                    case "viewrecord": return Cliqv.viewButton(recid); break;

                    // Restore
                    case "restorerecord": return Cliqf.restoreButton(recid); break;

                    // Add Child
                    case "addchild": Cliqf.crudButton(recid, 'addchild'); break;

                    // Lot more cases here

                    // Edit Button
                    case "editrecordce": return Cliqf.creatorButton(recid, 'update'); break;

                    // Displays popup window with contents of c_document, usually JSON, in a formatted and easily readable style
                    case "viewcontent": return Cliqv.viewButton(recid); break;

                    // Blog - uses pageform
                    case "editarticle": case "viewarticle": Cliqb.actionButton(dta); break;

                    // Responds to clicks to change c_status for a recordset row, 
                    default: return actionButton(dta); break;
                } // End Switch
             }

            /** Generic Help Button
             *
             **/
             var helpButton = function()
             {
                var urlstr = "/ajax/"+jlcd+"/gethelp/"+cfg.table+"/"+cfg.tabletype+"/";
                aja().method('GET').url(urlstr).cache(false).timeout(2500).type('json')
                .data({type:'admhelp', table: 'dbcollection'})
                // .data({type:'help', table: 'dbitem'}) // Frontend when copied
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

            /** Utilities Button
             * @param - object - Dropdown link item
             * @return - mixed - HTML and or Javascript
             **/
             var utilButton = function(evt) 
             {

                var type = $(evt).data('type');
                switch(type) {

                    case "deleteevent": 
                    case "printcalendar":
                        store.session.set('utilitiesaction', action);
                        var opts = {
                            contentAjax: {
                                url: '/ajax/'+jlcd+'/get'+action+'/'+cfg.table+'/'+cfg.tabletype+'/',
                                autoload: true, method: 'GET',  dataType: 'html'
                            }, contentSize: {
                                width: 600, height: 500
                            }, headerTitle: action
                        };
                        var formPopup = win(opts);
                    break;

                    case "deletebefore":
                        var params = {
                            table: 'dbarchive',
                            tabletype: '',
                            type: 'deletebefore', // deletebefore
                            recid: 0,
                            action: 'deletebefore',
                            before: '-60',
                            displaytype: 'datatable',
                            msg: lstr[107]+' -60D'
                        };
                        Cliqf.deleteRecords(params);
                    break;

                    case "changepassword":
                        var dta = $("#cliqontable tbody tr td").find('input:checkbox:checked').data(); 
                        Cliqf.changePasswordButton(dta.uname, dta.email, dta.id);
                    break;

                    case "changestatus":
                        var dta = $("#cliqontable tbody tr td").find('input:checkbox:checked').data(); 
                        Cliqf.changeStatusButton(dta.id);
                    break;

                    default: success(type); break;
                }
             }

            /** Conversion Button
             *
             **/
             var convertButton = function(recid)
             {
                
                var urlstr = '/ajax/'+jlcd+'/tomlconverter/';
                aja().method('GET').url(urlstr).cache(false).timeout(10000).type('json')
                .on('40x', function(response) { error(lstr[1450]+' - '+urlstr+':'+response); })
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
                                    width: 600,
                                    height: 600
                                },
                                headerTitle: '<span class="">Converter</span>',
                                callback: function() {
                                    
                                    $('#filetree').tree({
                                        data: response.data,
                                        closedIcon: $('<i class="fa fa-arrow-circle-right"></i>'),
                                        openedIcon: $('<i class="fa fa-arrow-circle-down"></i>'),
                                        dragAndDrop: false,
                                        selectable: true,
                                        saveState: true,
                                        autOpen: 0
                                    });

                                    $('#filetree').bind('tree.select', function(evt) {
                                        if (evt.node) {
                                            // node was selected
                                            var node = evt.node;
                                            alert(node.name);

                                        } else {}                 
                                    })

                                    $('#filetojson').on('click', function(e) {

                                    });

                                    $('#filetotoml').on('click', function(e) {

                                    });

                                    $('#copyfilecontent').on('click', function(e) {

                                    });
                                    return true;
                                }
                            };
                            var helpPopup = win(opts);  

                        } else { error( 'Ajax function returned error NotOk - '+urlstr+':'+JSON.stringify(response)); }; 

                    } else { error('Response was not JSON object - '+urlstr+':'+response.msg); };
                }).go();  
             }

            /** Generic Grid, Table, List etc,. action button
             * probably use this for internal App purposes
             * how to role this out to others ???
             * 
             **/
             var actionButton = function(dta)
             {
                
                switch(dta.action) {

                    case "changedisplay":
                    case "changeoptions":
                    case "changecategory":
                    case "changelevel":
                    case "changegroup":
                    case "changestatus": 
                        Cliqf.selectButton(cfg, dta);
                    break;

                    default:
                        var myfn = 'Cliqp.'+dta.action+'(cfg, dta)';
                        jQuery.globalEval('('+myfn+')');
                    break;
                }
             }

        /** Subroutines
         *
         * onSuccessFn()
         * onErrorFn()
         * manageCollection()
         * manageEvent()
         * collectionAction() - to be reviewed
         * saveTree()
         * loadTableData()
         * - tablePagination()
         * - pagerText()
         *
         **********************************************************************************************************/

            var onSuccessFn = function(response) { success(response); };
            var onErrorFn = function(response) { error(response); };

            /**
             * Manage Collection
             * create, edit, view (and print), delete
             **/
            var manageCollection = function(action, id) 
            {
                var urlstr = '/ajax/'+jlcd+'/collectionform/dbcollection/collection/?action='+action+'&recid='+id;
                return aja().method('GET').url(urlstr).cache(false).timeout(2500)
                .on('40x', function(response) { error('Page not Found - '+urlstr+':'+response)})
                .on('500', function(response) { error('Server Error - '+urlstr+':'+response)})
                .on('timeout', function(response){ error('Timeout - '+urlstr+':'+response)})
                .on('200', function(response) {
                
                    // first argument to the success callback is the json data object returned by the server
                    if(typeof response == 'object') {                    
                        // Test NotOK - value already exists
                        var match = /NotOk/.test(response.flag);
                        if(match !== true) {

                            // Create a working ID, delete any existing record with that ID, 
                            // Create and save a working Collection record for persistence
                            cfg.data.row = {
                                formdata: response.data.fdata.formfield,
                                formbuttons: response.data.fdata.formbuttons,
                                fielddata: response.data.fdata.fields,
                                formkeys: array_keys(response.data.fdata.form),
                            };
                            store.set(sessid+'_currentformdata', cfg.data.row.formdata);
                            store.set(sessid+'_currentielddata', cfg.data.row.fielddata);

                            // Displaying the raw HTML             
                            $('#admindesktop').empty().html(response.html);

                            // Populate form fields
                            cfg.app.collectionform = new Vue({
                                el: '#form_collection',
                                data: cfg.data.row.formdata
                            });

                            // Populate form buttons
                            cfg.app.collectionbuttons = new Vue({
                                el: '#form_buttons',
                                data: {formbuttons: cfg.data.row.formbuttons},
                                methods: {
                                    formButton: function(evt, action) {
                                        collectionButton(evt, action); 
                                    }
                                }
                            });

                            // Populate column fields
                            cfg.app.collectionfields = new Vue({
                                el: '#field_collection',
                                data: {collectionfields: cfg.data.row.fielddata},
                                methods: {
                                    fieldButton: function(evt, action) {
                                        collectionButton(evt, action); 
                                    }
                                }
                            });

                        } else { error('Ajax function returned error NotOk - '+urlstr+':'+JSON.stringify(response)); };

                    } else { error('Response was not JSON object - '+urlstr+':'+response.msg); };
                }).go();                                        
            };

            var manageEvent = function(ev, recid, action) {

                switch(action) {
                    
                    case "insert":
                    case "update":

                        var urlstr = '/ajax/'+cfg.langcd+'/postform/'+cfg.table+'/'+cfg.tabletype+'/';
                        var frmData = new FormData();

                        // All the necessary fields
                        if(action == 'insert') {
                            frmData.set('c_type', cfg.tabletype);
                            frmData.set('c_level', '50:50:50');
                            frmData.set('id', 0);
                            frmData.set('c_status', 'active');
                            frmData.set('c_order', 'zz');
                            frmData.set('c_parent', '0');
                            frmData.set('c_options', '');
                            frmData.set('c_version', '0');
                            frmData.set('c_lastmodified', '');
                            frmData.set('c_whomodified', '');
                            frmData.set('c_notes', 'Created by Calendar');
                            frmData.set('c_reference', recid);
                        } else {
                            frmData.set('id', recid);
                        }
                        frmData.set('action', action);
                        frmData.set('c_category', ev.type);
                        frmData.set('d_datefrom', moment(ev.start_date).format('YYYY-MM-DD HH:mm:ss')); 
                        frmData.set('d_dateto', moment(ev.end_date).format('YYYY-MM-DD HH:mm:ss')); 
                        frmData.set('d_title', ev.text);
                        frmData.set('d_url', ev.url);
                        frmData.set('c_common', ev.text);
                        frmData.set('d_description', ev.details);

                        $.ajax({
                            url: urlstr, data: frmData,
                            cache: false, contentType: false, processData: false,
                            type: 'POST', async: false, timeout: 25000,
                            success: function(response, statusText, xhr) {
                                
                                // first argument to the success callback is the json data object returned by the server
                                if(typeof response == 'object') {
                                    var match = /NotOk/.test(response.flag);
                                    if(!match == true) {
                                        success('Database updated successfully: ' + JSON.stringify(response.data)); 
                                    } else {
                                        error('Ajax function returned error NotOk - '+JSON.stringify(response.msg))
                                    };  
                                } else {
                                    error('Response was not JSON object - '+JSON.stringify(response))
                                };  

                            }, 
                            error: function(xhr, status, text) {
                                var response = $.parseJSON(xhr.responseText);
                                error(JSON.stringify(response.msg));
                                return false;           
                            }
                        });        
                    break;

                    case "delete":
                        var urlstr = '/ajax/'+cfg.langcd+'/deleterecord/'+cfg.table+'/'+cfg.tabletype+'/';
                        aja().method('POST').url(urlstr).cache(false).timeout(2500).type('json')
                        .data({
                            displaytype: cfg.displaytype,
                            action: 'delete',
                            recid: recid
                        })
                        .on('40x', function(response) { error('Page not Found - '+urlstr+':'+response)})
                        .on('500', function(response) { error('Server Error - '+urlstr+':'+response)})
                        .on('timeout', function(response){ error('Timeout - '+urlstr+':'+response)})
                        .on('200', function(response) {

                            if(typeof response == 'object') {
                                // Test NotOK - value already exists
                                var match = /NotOk/.test(response.flag);
                                if(!match == true) { 
                                    success('Event deleted successfully: ' + JSON.stringify(response.data)); 
                                } else { // Error
                                    error('Ajax function returned error NotOk - '+urlstr+':'+JSON.stringify(response))
                                }; 

                            } else {error('Response was not JSON object - '+urlstr+':'+response.msg)};

                        }).go();                            
                    break;

                    case "view":
                        Cliqf.viewButton(recid);
                    break;

                    case "copy":
                        var urlstr = '/ajax/'+cfg.langcd+'/copyevent/'+cfg.table+'/'+cfg.tabletype+'/';
                        var frmData = new FormData(); 
                        frmData.set('recid', recid);
                        frmData.set('d_datefrom', ev.start_date);
                        frmData.set('d_dateto', ev.end_date);
                        $.ajax({
                            url: urlstr, data: frmData,
                            cache: false, contentType: false, processData: false,
                            type: 'POST', async: false, timeout: 25000,
                            success: function(response, statusText, xhr) {
                                
                                // first argument to the success callback is the json data object returned by the server
                                if(typeof response == 'object') {
                                    var match = /NotOk/.test(response.flag);
                                    if(!match == true) {
                                        success('Database updated successfully: ' + JSON.stringify(response.data)); 
                                    } else {
                                        error('Ajax function returned error NotOk - '+JSON.stringify(response.msg));
                                    };  
                                } else {
                                    error('Response was not JSON object - '+JSON.stringify(response));
                                };  

                            }, 
                            error: function(xhr, status, text) {
                                var response = $.parseJSON(xhr.responseText);
                                error(JSON.stringify(response.msg));
                                return false;           
                            }
                        }); 
                    break;
                }
            }

            /**
             * Used on the Collection Datacard page to directly invoke a listing page from the Collection cards
             * @param - string - table
             * @param - string - tabletype
             * @param - string - page type such as datatable or gallery etc.
             * @return - Javascript and HTML, with help of Template
             **/    
            var collectionAction = function(type, table, tabletype) 
            {
                var urlstr = "/admindesktop/"+jlcd+"/"+type+"/"+table+"/"+tabletype+"/";
                uLoad(urlstr);
            } 

            /** Save a Tree after drag and drop
             *
             *
             **/
             var saveTree = function()
             {
                var move = cfg.evt.move_info;
                var moved_node = move.moved_node.name+'|'+move.moved_node.id;
                var target_node = move.target_node.name+'|'+move.target_node.id;
                var position = move.position;
                var previous_parent = move.previous_parent.name+'|'+move.previous_parent.id;

                // console.log('moved_node', moved_node);
                // console.log('target_node', target_node);
                // console.log('position', position);
                // console.log('previous_parent', previous_parent);

                var urlstr = '/ajax/'+jlcd+'/treenodedrop/'+cfg.table+'/'+cfg.tabletype+'/';
                return aja().method('GET').url(urlstr).cache(false).timeout(2500)
                .data({
                    moved_node: moved_node,
                    target_node: target_node,
                    position: position,
                    previous_parent: previous_parent           
                    // data: cfg.data
                })
                .on('40x', function(response) { error('Page not Found - '+urlstr+':'+response)})
                .on('500', function(response) { error('Server Error - '+urlstr+':'+response)})
                .on('timeout', function(response){ error('Timeout - '+urlstr+':'+response)})
                .on('200', function(response) {
                
                    // first argument to the success callback is the json data object returned by the server
                    if(typeof response == 'object') {                    
                        // Test NotOK - value already exists
                        var match = /NotOk/.test(response.flag);
                        if(match !== true) {
                            $('#columnform').empty().html('f');
                            success(response.msg);
                            cfg.dt.tree('loadDataFromUrl', cfg.opts.dataurl); 
                        } else { error('Ajax function returned error NotOk - '+urlstr+':'+JSON.stringify(response)); };
                    } else { error('Response was not JSON object - '+urlstr+':'+response.msg); };
                }).go();        
             }

            /** Load data for a datalist or datatable
             *
             * @return - Sets the Data for Vue and starts process of populating pager
             **/
             var loadTableData = function()
             {

                var orderby; if(cfg.opts.orderby != '') {orderby = cfg.opts.orderby;} else {orderby = cfg.orderby;};
                var urlstr = cfg.opts.url;
                aja().method('GET').url(urlstr).cache(false).timeout(10000).type('json')
                .data({
                    limit: cfg.records.limit,
                    offset: cfg.records.offset,
                    search: cfg.search,
                    orderby: orderby,
                    field_identifier: 'field'
                })
                // .jsonPaddingName('clientRequest')
                // .jsonPadding('cliqon') // ajajsonp_omni
                .on('40x', function(response) { error('Page not Found - '+urlstr+':'+response)})
                .on('500', function(response) { error('Server Error - '+urlstr+':'+response)})
                .on('timeout', function(response){ error('Timeout - '+urlstr+':'+response)})
                .on('200', function(response) {
                    if(typeof response == 'object')
                    {
                        // Test NotOK - value already exists
                        var match = /NotOk/.test(response.flag);
                        if(!match == true) {

                            cfg.records.offset = response.offset;
                            cfg.records.limit = response.limit;
                            cfg.records.total = response.total;
                            cfg.dt.$data.rows = response.rows;
                            pagerText();

                        } else { error('Ajax function returned error NotOk - '+urlstr+':'+JSON.stringify(response)); };
                    } else { error('Response was not JSON object - '+urlstr+':'+response.msg); };
                }).go();    
             }

            /** Deals with the TWBS Pagination
             *
             * populates a given Div with a Bootstrap pager component
             **/
             var tablePagination = function()
             {

                var cl = 'page-item bold larger';
                cfg.records.totpages = Math.ceil(cfg.records.total / cfg.records.limit);
                // console.log(cfg.records, cfg.pager);
                return $('#tablepagination').twbsPagination({
                    totalPages: cfg.records.totpages,
                    visiblePages: cfg.pager.size,
                    startPage: 1,
                    initiateStartPageClick: false,
                    onPageClick: function (event, thispage) {
                        cfg.records.page = thispage;
                        if(cfg.records.page == 1) {
                           cfg.records.offset = 0;
                        };
                        // This handles the click on the cfg.records.page page number
                        if(cfg.records.page > 1 || cfg.records.page < cfg.records.totpages) {
                            cfg.records.offset = ((cfg.records.page - 1) * cfg.records.limit);
                        };
                        loadTableData();
                    },
                    first: '<<', prev: '<', next: '>', last: '>>',
                    nextClass: cl, prevClass: cl, lastClass: cl,
                    firstClass: cl, pageClass: 'page-item', activeClass: 'active',
                    disabledClass: 'disabled', anchorClass: 'page-link'
                });
             }

            /** Pager Text
             * page: 1, pageLength: 15, visiblePages: 7, totrecs: 1, offset: 0, limit: 0 
             * cfg.offset = Is the exact number of records into the recordset and is generated from response.offset;
             * cfg.page = Representation of a valid and current page number - say 1 to 7 - it is calculated from Offset using Limit
             * cfg.limit = response.limit - the number of records to display
             * cfg.totrecs = response.totrecs - the total number of records
             **/
             var pagerText = function() 
             {               
                cfg.pager.recs = cfg.records.limit;
                // Value for start, end and page must always be numeric
                cfg.records.start = parseInt(cfg.records.start);
                cfg.records.end = parseInt(cfg.records.end);
                cfg.records.page = parseInt(cfg.records.page);

                if(cfg.records.offset > cfg.records.start) {
                    cfg.records.start = cfg.records.offset + 1;
                    cfg.records.end = cfg.records.offset + cfg.records.limit;
                };

                // End number can never be more than total 
                if(cfg.records.end > cfg.records.total || (cfg.records.limit + cfg.records.offset) > cfg.records.total) {
                    cfg.records.end = cfg.records.total;
                };

                if(cfg.records.offset == 0) {
                    cfg.records.start = 1;
                    cfg.records.end = cfg.records.limit;
                }

                /*
                // From the response we need to work out the pager text values
                if(cfg.records.offset > 1 || cfg.records.offset < cfg.records.total) {
                    cfg.records.start = cfg.records.offset;
                } else if((cfg.records.offset + cfg.records.limit) > cfg.records.total) {
                    cfg.records.start = ((cfg.records.total - cfg.records.offset) + 1);
                } else if((cfg.records.offset + cfg.records.limit) < cfg.records.total) {
                    cfg.records.start = ((cfg.records.total - cfg.records.offset) + 1);
                }     
                */

                // console.log(cfg.records, cfg.pager);                   

                cfg.dt.$data.records.start = cfg.records.start;
                cfg.dt.$data.records.end = cfg.records.end;
                cfg.dt.$data.records.total = cfg.records.total;    

                if(cfg.records.total > cfg.records.limit) {
                    tablePagination();
                }            
             }

        /** TOML / JSON convert
         *
         * convertButton()
         * getArrayFile()
         * fileToJson()
         * fileToToml()
         *
         ************************************************************************************************************/
            
            var convertButton = function(recid)
            {
                var urlstr = '/ajax/'+jlcd+'/tomlconverter/';
                aja().method('GET').url(urlstr).cache(false).timeout(10000).type('json')
                .on('40x', function(response) { error(lstr[1450]+' - '+urlstr+':'+response); })
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
                                    width: 700,
                                    height: 600
                                },
                                headerTitle: '<span class="">Converter</span>',
                                callback: function() {
                                    
                                    cfg.dt = $('#filetree').tree({
                                        data: response.data,
                                        closedIcon: $('<i class="fa fa-arrow-circle-right"></i>'),
                                        openedIcon: $('<i class="fa fa-arrow-circle-down"></i>'),
                                        dragAndDrop: false,
                                        selectable: true,
                                        autoOpen: false
                                    });

                                    $('#filetree').bind('tree.dblclick', function(evt) {
                                        if(evt.node) {
                                            getArrayFile(evt.node);
                                        } else {}                 
                                    })

                                    $('#filetojson').on('click', function(e) {
                                        return fileToJson(e);
                                    });

                                    $('#filetotoml').on('click', function(e) {
                                        return fileToToml(e);
                                    });

                                    var clipboard = new Clipboard('#copyfilecontent');

                                    return true;
                                }
                            };
                            var helpPopup = win(opts);  

                        } else { error( 'Ajax function returned error NotOk - '+urlstr+':'+JSON.stringify(response)); }; 
                    } else { error('Response was not JSON object - '+urlstr+':'+response.msg); };
                }).go();  
            }
		
            var getArrayFile = function(node)
            {
                if(stristr(node.name, 'cfg')) {

                    var urlstr = '/ajax/'+jlcd+'/dotomlconvert/';
                    aja().method('GET').url(urlstr).cache(false).timeout(10000).type('json')
                    .data({filepath: '/'+node.id+'/'+node.name})
                    .on('40x', function(response) { error(lstr[1450]+' - '+urlstr+':'+response); })
                    .on('500', function(response) { error('Server Error - '+urlstr+':'+response); })
                    .on('timeout', function(response){ error('Timeout - '+urlstr+':'+response); })
                    .on('200', function(response) {
                        if(typeof response == 'object')
                        {
                            // Test NotOK - value already exists
                            var match = /NotOk/.test(response.flag);
                            if(match !== true) {
                                cfg.data = response.data;
                                $('#filecontent').val(cfg.data.json);
                            } else { error( 'Ajax function returned error NotOk - '+urlstr+':'+JSON.stringify(response)); }; 
                        } else { error('Response was not JSON object - '+urlstr+':'+response.msg); };
                    }).go();  
                } else {
                    cfg.dt.tree('selectNode');
                    return false;
                }                  
            }

            var fileToJson = function(e) 
            {
                $('#filecontent').val(cfg.data.json);
            }

            var fileToToml = function(e)
            {
                $('#filecontent').val(cfg.data.toml);
            }

        /** Administrative methods and activities
         *
         * convertArray()
         * importdata()
         * exportdata()
         * dbschema()
         * - dictionaryedit()
         * - dictionarycopy()
         * jsStrings()
         * - editTable()
         * - saveTable()
         * maintainIdiom()
         * - addNewIdiom()
         * - deleteIdiom()
         *
         ********************************************************************************************************/

            /** Javascript routines to convert an import file in Configuration array format 
             * 
             * @param - array - options
             * @return - test or written content
             **/    
             var convertarray = function(opts)
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
                            inputfile: null,
                            dbwrite: ''
                        },
                        testform: {
                            testfile: null
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
                                        if(match !== true) {

                                            var content = prettyPrint(response.result, {
                                                expanded: true, 
                                                maxDepth: 5
                                            });
                                            $('#convertresults').empty().html(content);
                                            
                                        } else {
                                            error('Ajax function returned error NotOk - '+urlstr+':'+JSON.stringify(response.msg));
                                        };                          
                                    } else {
                                        error('Response was not JSON object - '+urlstr+':'+JSON.stringify(response));
                                    };
                                    
                                },
                                error: function(xhr, status, text) {
                                    spinner.stop();
                                    var response = $.parseJSON(xhr.responseText);
                                    error(JSON.stringify(response.msg));
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
                                            error('Ajax function returned error NotOk - '+urlstr+':'+JSON.stringify(response.msg))
                                        };                          
                                    } else {
                                        error('Response was not JSON object - '+urlstr+':'+JSON.stringify(response))
                                    };
                                    
                                },
                                error: function(xhr, status, text) {
                                    spinner.stop();
                                    var response = $.parseJSON(xhr.responseText);
                                    msg({buttons: false, type: 'error', text: JSON.stringify(response.msg)});
                                }
                            });         
    
                            return false;
                        });     
                    }
                }); 
             }

            /** Javascript routines to import a CSV formatted data file into the database 
             * 
             * @param - array - options
             * @return - test or written content
             **/                
             var importdata = function(opts) 
             {

                var idms = new Vue({
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
                                            error('Ajax function returned error NotOk - '+urlstr+':'+JSON.stringify(response.msg));
                                        };                          
                                    } else {
                                        error('Response was not JSON object - '+urlstr+':'+JSON.stringify(response));
                                    };
                                    
                                },
                                error: function(xhr, status, text) {
                                    spinner.stop();
                                    var response = $.parseJSON(xhr.responseText);
                                    error(JSON.stringify(response.msg));
                                }
                            });         
    
                            return false;
                        });     
                    }
                });                                 
             }

            /** Javascript routines to export data to a CSV or Array config file  
             * 
             * @param - array - options
             * @return - test or written content
             **/                
             var exportdata = function(opts)
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
                                            error('Ajax function returned error NotOk - '+urlstr+':'+JSON.stringify(response.msg));
                                        };  

                                    } else {
                                        error('Response was not JSON object - '+urlstr+':'+JSON.stringify(response));
                                    };
                                    
                                },
                                error: function(xhr, status, text) {
                                    spinner.stop();
                                    var response = $.parseJSON(xhr.responseText);
                                    error(JSON.stringify(response.msg));
                                }
                            });         
    
                            return false;
                        }); 
                    }
                });                                 
             }

            /** Data Dictionary and supporting functions 
             * Javascript routines to support the editing and update of Tabletype definitions when stored in a database
             * @param - array - options
             * @return - test or written content
             **/ 
             var dbschema = function(e)
             {
                $.hook('dbschemabutton').on('click', function(e) {                  
                    var dta = $(this).data();
                    switch(dta.action) {
                        case "dictionaryedit": dictionaryEdit(dta); break;
                        case "dictionarycopy": dictionaryCopy(dta); break;
                    } // End switch
                });
             }

                /** dbSchema sub function - dictionaryEdit()
                 * 
                 * @param(data from button)
                 * @return - object - functions executed
                 **/
                 var dictionaryEdit = function(dta)
                 {
                    var opts = {
                        contentSize: {width: 600, height: 470},
                        headerTitle: 'Edit Dictionary',
                        contentAjax: {
                            url: '/ajax/'+jlcd+'/dictionaryedit/'+dta.table+'/'+dta.type,
                            then: [
                                function (response, textStatus, jqXHR, panel) {
                                    if(typeof response == 'object') {
                                        // Test NotOK - value already exists
                                        var match = /NotOk/.test(response.flag);
                                        if(!match == true) {                                    

                                            this.content.append(response.data);

                                            $('#submitform').on('click', function(e) {
                                                
                                                e.preventDefault();
                                                var fieldsused = $('#fieldsused').getValue();
                                                var fieldsconfig = $('#fieldsconfig').getValue();
                                                var urlstr = '/ajax/'+jlcd+'/dictionarywrite/'+dta.table+'/'+dta.type;
                                                return aja()
                                                .method('post').url(urlstr).cache(false).timeout(2500)
                                                .data({'fieldsused': fieldsused, 'fieldsconfig': fieldsconfig})
                                                .on('40x', function(response) { error('Page not Found - '+urlstr+':'+response)})
                                                .on('500', function(response) { error('Server Error - '+urlstr+':'+response)})
                                                .on('timeout', function(response){ error('Timeout - '+urlstr+':'+response)})
                                                .on('200', function(response) {
                                                    if(typeof response == 'object') {
                                                        var match = /NotOk/.test(response.flag);
                                                        if(!match == true) {
                                                            success(lstr[125]);                     
                                                        } else {
                                                            error('Ajax function returned error NotOk - '+urlstr+':'+JSON.stringify(response))
                                                        }
                                                    } else {
                                                        error('Response was not JSON object')
                                                    }
                                                }).go() 
                                            });   

                                            $('#resetform').on('click', function(e) {
                                                $('#dataform').clearForm();
                                            });

                                        } else {error('Ajax function returned error NotOk - '+response.msg)}; 
                                    } else {error( 'Response was not JSON object - '+urlstr+':'+JSON.stringify(response) )}  
                                },
                                function (jqXHR, textStatus, errorThrown, panel) {this.content.append(jqXHR.responseText).css('padding', '20px');}
                            ] // End then
                        }
                    };
                    win(opts);
                 }

                /** dbSchema sub function - dictionaryCopy()
                 * 
                 * @param(data from button)
                 * @return - object - functions executed
                 **/
                 var dictionaryCopy = function(dta)
                 {
                    var urlstr = '/ajax/'+jlcd+'/dictionarycopy/'+dta.table+'/'+dta.type;
                    return aja()
                    .method('get').url(urlstr).cache(false).timeout(2500)
                    .on('40x', function(response) { error('Page not Found - '+urlstr+':'+response)})
                    .on('500', function(response) { error('Server Error - '+urlstr+':'+response)})
                    .on('timeout', function(response){ error('Timeout - '+urlstr+':'+response)})
                    .on('200', function(response) {
                        if(typeof response == 'object') {
                            var match = /NotOk/.test(response.flag);
                            if(!match == true) {

                                var text = str_replace('<br />', '\r\n', response.data);
                                msg({
                                    buttons:  [
                                        {addClass: 'm10 mt10 btn btn-success btn-sm', text: lstr[30], onClick: function($noty) {    
                                            $noty.close(); 
                                        }},
                                        {addClass: 'm10 mt10 btn btn-danger btn-sm', text: lstr[60], onClick: function($noty) { 
                                            copyTextToClipboard(text);
                                            success(lstr[152]);
                                            $noty.close();
                                        }}
                                    ],
                                    timeout: false,
                                    type: 'info',
                                    text: '<div class="pad"><pre style="overflow:hidden;">'+text+'</pre></div>'  
                                }); 
                                                    
                            } else {
                                error('Ajax function returned error NotOk - '+urlstr+':'+JSON.stringify(response))
                            }
                        } else {
                            error('Response was not JSON object')
                        }
                    }).go()
                 }

            /** Javascript strings 
             * Javascript routines to support the editing and update of the strings used to make the JS functions multi-lingual
             * @param - array - options
             * @return - test or written content
             **/ 
             var jsStrings = function(gridOptions, idioms)
             {
                cfg.opts = gridOptions;
                cfg.idioms = idioms;
                cfg.displaytype = 'admjstrings';

                cfg.dg = $('#datagrid').grid(cfg.opts); 
                cfg.data = cfg.dg.getAll(true);               

                $('.editRecord').on('click', function(e) { 
                    e.stopImmediatePropagation(); 
                    editTable(e, 'edit'); 
                });
             }

                /**
                 **/
                var editTable = function(evt, action)
                {
                
                    var form = '<div class="m0"><p class="m0 mb10">'+lstr[15]+'</p>';
                    if(action == 'add') {
                        $.each(cfg.idioms, function(lcdcode, lcdname) {
                            form += '<textarea class="form-control h50" name="text['+lcdcode+']" placeholder="'+lcdname+'" ></textarea><br />';
                        });
                    } else {;
                        cfg.row.id = $(evt.target).closest('tr').data('position');
                        cfg.row = cfg.dg.get(cfg.row.id);
                        $.each(cfg.idioms, function(lcdcode, lcdname) {
                            form += '<textarea class="form-control h50" name="text['+lcdcode+']">'+cfg.row[lcdcode]+'</textarea><br />';
                        });
                    };  
                    form += '</div>';

                    var opts = {
                        type: 'info',
                        text: form,
                        closeWith: ['button'],
                        buttons: [
                            {addClass: 'm10 mt0 btn btn-danger btn-sm mt10', text: lstr[8], onClick: function($noty) { 
                                $noty.close(); 
                                saveTable(action);
                            }},
                            {addClass: 'm10 mt0 btn btn-primary btn-sm mt10', text: lstr[30], onClick: function($noty) { 
                                $noty.close(); 
                            }}                              
                        ],
                        timeout: false,
                    };
                    return msg(opts);
                }

                /**
                 * Sends existing and new data to server. Postdata and newdata
                 * Newdata row will overwrite postdata row by id and new stream written to disk
                 * Success and reload
                 **/
                var saveTable = function(action) 
                {

                    var newtext = {};
                    $.each(cfg.idioms, function(lcdcode, lcdname) {
                      newtext[lcdcode] = $('textarea[name="text['+lcdcode+']"]').getValue();
                    });
                    
                    if(action == 'edit') {
                        newtext.id = cfg.row.id;
                    } else {
                        newtext.id = count(cfg.data);
                    };  

                   // console.log('NewData: '+JSON.stringify(newtext));            

                    var urlstr = '/ajax/'+jlcd+'/postadminjstrings/';
                    return aja().method('post').url(urlstr).cache(false).timeout(2500).type('json')
                    .data({ postdata: JSON.stringify(cfg.data), newdata: JSON.stringify(newtext) })
                    .on('40x', function(response) { error('Page not Found - '+urlstr+':'+response)})
                    .on('500', function(response) { error('Server Error - '+urlstr+':'+response)})
                    .on('timeout', function(response){ error('Timeout - '+urlstr+':'+response)})
                    .on('200', function(response) {
                    
                        if(typeof response == 'object')
                        {
                            var match = /NotOk/.test(response.flag);
                            if(!match == true) {
                                success(response.msg);
                                cfg.dg.reload();

                            } else { // Error
                                error('Ajax function returned error NotOk - '+urlstr+':'+JSON.stringify(response));
                            }; 

                        } else { error('Response was not JSON object - '+urlstr+':'+response.msg); }
                    }).go();                  
                }

            /** Routines to maintain languages 
             *
             * @param - array array of languages
             **/
             var maintainIdiom = function(idioms)
             {
               cfg.df = new Vue({
                    el: '#admmaintainidiom',
                    data: {
                        idioms: idioms,
                        inputform: {
                            lcdcode: '',
                            lcdname: '',
                            inputfile: '',
                            dbwrite: '',
                            cfgwrite: ''
                        },
                        newidiomcode: '',
                        newidiomname: ''                       
                    },
                    methods: {
                        deleteIdiom: function(evt) {
                            var lcdcode = $(evt.target).data('lcdcode');
                            var lcdname = $(evt.target).data('lcdname');
                            deleteIdiom(lcdcode, lcdname);
                        },
                        addIdiom: function() {
                            $('#addidiomform').removeClass('hide');
                        },
                        downloadTemplate: function() {  
                            var urlstr = '/ajax/'+jlcd+'/doidiomtemplatedownload/dbcollection/string/';
                            aja().method('POST').url(urlstr)
                            .on('40x', function(response) { error('Page not Found - '+urlstr+':'+response)})
                            .on('500', function(response) { error('Server Error - '+urlstr+':'+response)})
                            .on('timeout', function(response){ error('Timeout - '+urlstr+':'+response)})
                            .on('200', function(response) {
                                
                                if(typeof response == 'object') {
                                    var match = /NotOk/.test(response.flag);
                                    if(!match == true) {
                                        download(response.content, response.filename, 'text/plain');
                                    } else { // Error
                                        error('Ajax function returned error NotOk - '+urlstr+':'+JSON.stringify(response));
                                    }; 

                                } else { error('Response was not JSON object - '+urlstr+':'+response.msg); }
                            }).go();                                
                        }
                    },
                    mounted: function() {
         
                        $('#inputform').submit(function(evt) {
                           
                            evt.preventDefault();
                            var target = document.getElementById('addidiomform');
                            var opts = {};
                            var spinner = new Spinner(opts).spin(target);
                            $('#idiomresults').empty();
                            var urlstr = '/ajax/'+jlcd+'/dolcdimport/dbcollection/string/';
                            var form = $('#inputform');
                            
                            // Now get Data from the Vue Instance
                            var postData = cfg.df.$data.inputform;    
                            var frmData = new FormData();
                            $.each(postData, function(fld, val) {
                                frmData.set(fld, val);
                            })
                
                            // AJAX Form upload for a single file
                            var file = $('input[type=file]', form)[0].files[0];     
                            frmData.append('filename', file.name);                                          
                            frmData.append(file.name, file, file.filename); 

                            $.ajax({
                                url: urlstr, data: frmData, cache: false, contentType: false,
                                processData: false, type: 'POST', async: false, timeout: 25000,
                                success: function(response) {
                                    
                                    if(typeof response == 'object') {
                                        var match = /NotOk/.test(response.flag);
                                        if(!match == true) {
                                            $('#idiomresults').empty();
                                            spinner.stop();
                                            success(lstr[33]);
                                            $.jsontotable(response.result, { id: '#idiomresults', header: false, className: 'table table-condensed table-sm' });
                                        } else {
                                            error('Ajax function returned error NotOk - '+urlstr+':'+JSON.stringify(response.msg))
                                        };                          
                                    } else {
                                        error('Response was not JSON object - '+urlstr+':'+JSON.stringify(response))
                                    };
                                    
                                },
                                error: function(xhr, status, text) {
                                    var response = $.parseJSON(xhr.responseText);
                                    error(JSON.stringify(response.msg));
                                }
                            });         
    
                            return false;
                        }); 

                        $('#newidiombutton').on('click', function(e) {
                            return addNewIdiom();
                        })
                    }
                })
             }

            /** Add a new language 
             *
             **/
             var addNewIdiom = function()
             {
                Cliq.msg({
                    buttons:  [
                        {addClass: 'm10 mt10 btn btn-primary btn-sm', text: lstr[8], onClick: function($noty) { 
                            
                            var target = document.getElementById('idiomresults');
                            var opts = {};
                            var spinner = new Spinner(opts).spin(target);

                            var urlstr = '/ajax/'+jlcd+'/addnewidiom/';
                            var frmdata = new FormData();
                            frmdata.append('lcdname', cfg.df.$data.newidiomname); 
                            frmdata.append('lcdcode', cfg.df.$data.newidiomcode);                   
                            $.ajax({
                                url: urlstr, data: frmdata,
                                cache: false, contentType: false, processData: false,
                                type: 'POST', async: false, timeout: 25000,
                                success: function(response) {

                                    if(typeof response == 'object') {
                                        var match = /NotOk/.test(response.flag);
                                        if(!match == true) {
                                            spinner.stop();
                                            var content = prettyPrint(response.data, {
                                                expanded: true, 
                                                maxDepth: 5
                                            });
                                            $('#idiomresults').empty().html(content);
                                            $noty.close();
                                        } else {
                                            error('Ajax function returned error NotOk - '+urlstr+':'+JSON.stringify(response.msg));
                                        };                          
                                    } else {
                                        error('Response was not JSON object - '+urlstr+':'+JSON.stringify(response));
                                    };
                                },
                                error: function(xhr, status, text) {
                                    var response = $.parseJSON(xhr.responseText);
                                    error(JSON.stringify(response.msg));                           
                                }
                            });                            
                        }},
                        {addClass: 'm10 mt10 btn btn-default btn-sm', text: lstr[30], onClick: function($noty) {    
                            $noty.close(); 
                        }}                                                                          
                    ],
                    timeout: false,
                    closeWith: ['button'],
                    type: 'info',
                    text: '<p>'+lstr[156]+'</p><p>'+lstr[157]+'</p><p>'+lstr[158]+':</p><p>'+cfg.df.$data.newidiomname+': '+cfg.df.$data.newidiomcode+'</p>'   
                });
             }

            /** Delete existing language 
             *
             * @param - string - language code
             * @param - string - language name. Used for confirmation purposes only
             **/
             var deleteIdiom = function(lcdcode, lcdname)
             {
                Cliq.msg({
                    buttons:  [
                        {addClass: 'm10 mt10 btn btn-primary btn-sm', text: lstr[8], onClick: function($noty) { 
                            var urlstr = '/ajax/'+jlcd+'/deleteidiom/';
                            aja().method('POST').url(urlstr).cache(false).timeout(2500).type('json')
                            .data({'lcdcode': lcdcode})
                            .on('40x', function(response) {Cliq.error('Page not Found - '+urlstr+':'+response);})
                            .on('500', function(response) {Cliq.error('Server Error - '+urlstr+':'+response);})
                            .on('timeout', function(response) {Cliq.error('Timeout - '+urlstr+':'+response);})
                            .on('success', function(response) {
                                if(typeof response == 'object') {
                                    // Test NotOK - value already exists
                                    var match = /NotOk/.test(response.flag);
                                    if(!match == true) {  
                                        uLoad();                    
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
                    type: 'error',
                    text: '<p>'+lstr[156]+'</p><p>'+lstr[157]+'</p><p>'+lstr[159]+':</p><p>'+cfg.df.$data.newidiomname+': '+cfg.df.$data.newidiomcode+'</p>'
                });
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
            
            /** Noty Alert box
             * 
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

            /** jsPanel  
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
                                if(array_key_exists('content', usroptions)) {
                                    var rpt = '<h3 class="pad">'+usroptions.headerTitle+'</h3><div>'+usroptions.content+'</div>';
                                    return $.print(rpt);
                                } else {
                                    return event.data.content.print();
                                }
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

            calendar: calendar,
            gallery: gallery,
            datatable: dataTable,
            loadTableData: loadTableData,
            datagrid: dataGrid,
            datatree: dataTree,
            datacard: dataCard,
            datalist: dataList,
            topButton: topbtn,
            gridButton: gridbtn,
            rowButton: rowbtn,
            helpButton: helpButton,
            convertButton: convertButton,
            convertArray: convertarray,
            importData: importdata,
            exportData: exportdata,
            dbSchema: dbschema,
            jsStrings: jsStrings,
            maintainIdiom: maintainIdiom,
            popup: popup,
            clqAjax: clqAjax,
            win: win,
            error:error,
            success: success,
            msg: msg
        }; 

    })(jQuery); 
	
    $.noty.themes.bootstrapTheme = {
        name: 'bootstrapTheme',
        modal: {
            css: {
                position: 'fixed',
                width: '100%',
                height: '100%',
                backgroundColor: '#000',
                zIndex: 10000,
                opacity: 0.6,
                display: 'none',
                left: 0,
                top: 0
            }
        },
        style: function() {

            var containerSelector = this.options.layout.container.selector;
            $(containerSelector).addClass('list-group');

            this.$bar.addClass( "list-group-item" ).css('padding', '0px');

            switch (this.options.type) {
                case 'alert': case 'notification':
                    this.$bar.addClass( "list-group-item-info" );
                    break;
                case 'warning':
                    this.$bar.addClass( "list-group-item-warning" );
                    break;
                case 'error':
                    this.$bar.addClass( "list-group-item-danger" );
                    break;
                case 'information':
                    this.$bar.addClass("list-group-item-default");
                    break;
                case 'success':
                    this.$bar.addClass( "list-group-item-success" );
                    break;
            }

            this.$message.css({
                fontSize: '13px',
                lineHeight: '16px',
                textAlign: 'left',
                padding: '0px 10px 0px 10px',
                width: 'auto',
                position: 'relative'
            });
        },
        callback: {
            onShow: function() {  },
            onClose: function() {  }
        }
    };

	