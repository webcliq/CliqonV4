/* CliqV.Js */

/** Cliqon View Functions - cliqv() 
 *
 *************************************************************************************/

    var Cliqv = (function($) {
    
        // initialise
        // var shared values
        var vcfg = {
            useCaching: true,
            langcd: "en",
            viewopts: [],
            dv: new Object,
            view: {}
        }, cfg = {};

            var _set = function(key,value)
            {
                vcfg[key] = value;
                return vcfg[key];
            }

            var _get = function(key)
            {
                return vcfg[key];
            }

            /** 
             * View button, invoked from Grid or List row
             * @param - Record Id
             * @return - New display activity
             **/     
            var viewButton = function(recid) 
            {
                
                cfg = Cliq.config(); var action = '', viewtype = '', result = ''; 
                switch(cfg.displaytype) {

                    // Can display Record in Right Column
                    case "datagrid":
                    case "datatree":
                        action = 'viewrecord';
                        viewtype = 'columnview';
                        result = 'column';
                    break;

                    // Display in popup window
                    case "datatable":
                    case "datacard":
                    case "calendar":
                    case "gallery":
                        action = 'viewrecord';
                        viewtype = 'popupview';
                        result = 'popup';
                    break;    

                    // Display content in popup window                              
                    case "datalist":
                        action = 'viewcontent';
                        viewtype = 'popupview';
                        result = 'popup';
                    break;
                }; 

                // The Ajax and display routine
                var urlstr = '/ajax/'+cfg.langcd+'/'+action+'/'+cfg.table+'/'+cfg.tabletype+'/';
                aja().method('GET').url(urlstr).cache(false).timeout(2500).type('json')
                .data({
                    displaytype: cfg.displaytype,
                    viewtype: viewtype,
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
                            
                            switch(result) {
                                case "column":
                                    $('#columnform').empty().html(response.html);
                                break;

                                case "popup":
                                    var opts = {
                                        // Probably change to contentAjax
                                        content: response.html,
                                        headerTitle: lstr[62],
                                        theme: 'bootstrap-warning',
                                        contentSize:     {
                                            width: 700,
                                            height: 610
                                        }
                                    };
                                    vcfg.dv = Cliq.win(opts);
                                break;

                                case "page":

                                break;
                            };
                            
                        } else { Cliq.error('Ajax function returned error NotOk - '+urlstr+':'+JSON.stringify(response)); }; 
                    } else { Cliq.error('Response was not JSON object - '+urlstr+':'+response.msg); }
                }).go(); 
            }

            /**
            * Setup a view
            **/
            function qView(data, viewtitle) {

                var tbody = qFormatTablebody(qViewFields(data));
                var content = qFormatView(tbody);
                var options = {
                    content: content,
                    title: viewtitle
                };                    
                Cliq.win(options);
            }

            /**
            * Runs through each field in the definition or the fields per fieldset
            **/
            function qViewFields(fields) {
                
                var flds = [];
                $.each(fields, function(idx, fld) {

                    // Only necessary to define specials
                    switch(fld.vtype) {

                        // case "buttons": flds = flds.concat(qButtons(fld)); break;
                        case "numeric":
                            flds[idx] = {
                                vclass: 'txtright',
                                vlabel: fld.label,
                                vdata: fld.data
                            }; 
                        break;

                        // Text, URL, Email - generally a standard input type
                        case "string":
                        default: 
                            flds[idx] = {
                                vclass: 'txtleft',
                                vlabel: fld.label,
                                vdata: fld.data
                            }; 
                        break;
                    }
                });

                return flds;     
            }

            function qFormatView(tbody) {
                var tbl = '<table class="table table-striped" id="printthis">';
                tbl += '<tbody id="popubtablebody">'+tbody+'</tbody></table>';
                return tbl;
            }

            function qFormatTablebody(flds) {
                var tbody = ''; 
                Object.keys(flds).forEach(function(fld) {
                    var row = '';
                    row += '<tr class="">';
                    row += '<td class="txtright blue e30 text-muted ">'+flds[fld]['vlabel']+'</td>';
                    row += '<td class="e70 '+flds[fld]['vclass']+'">'+flds[fld]['vdata']+'</td>';
                    row += '</tr>';
                    tbody += row;
                });
                return tbody;         
            }

        // explicitly return public methods when this object is instantiated
        return {
            // outSide: inSide,
            view: qView,
            viewButton: viewButton
        };

    })(jQuery);        