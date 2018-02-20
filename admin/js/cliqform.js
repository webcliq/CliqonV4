/** Cliqon Form Functions - cliqform() 
 *
 *************************************************************************************/

    var Cliqform = (function($) {
        
        // initialise
        // var shared values
        var clqfCfg = {
            useCaching: true,
            langcd: "en",
            formopts: [],
            form: {}
        };

        /**
        * Setup a form
        **/
        function qForm(opts) {

            clqfCfg.formopts = opts;

            // Fieldset
            var fields = [];
            if (typeof(opts.fieldset) != 'object') {
                fields = qFields(opts.formfields);
            } else {
               $.each(opts.fieldset, function(f, fset) {
                    fields = qFieldset(f, fset);
               })
            };

            // Form Preparation
            clqfCfg.form = $("#"+opts.formid);
            $(clqfCfg.form).empty().clqform({
                action: function() {
                    if (typeof(opts.formaction) != 'string') {
                        return "#";
                    } else {
                       return opts.formaction;
                    };
                },
                method: function() {
                    if (typeof(opts.formmethod) != 'string') {
                        return "POST";
                    } else {
                       return opts.formmethod;
                    };
                },
                class: function() {
                    if (typeof(opts.formclass) != 'string') {
                        return "form-horizontal";
                    } else {
                       return opts.formclass;
                    };
                }, html: [{
                    'type':'container', 
                    'class':'container', 
                    'html': fields                  
                }]
            }); 

            // Form JS includes here

            // Form validate
            $.validate({modules : 'html5'});

            // is unique, next reference and next id
            qInputHelpers();

            // autocomplete
            qPopSelect();

            $.hook('formbutton').on('click', function(e) {
                var action = $(this).data('action');
                switch (action) {

                    case "previewbutton": qPreview(opts.formid); break;
                    case "resetbutton": $('#'+opts.formid).clearform(); break;
                    case "submitbutton": qSubmit(opts.formid); break;

                    default: Cliqu.nMsg({buttons: false, type: 'information', text: action}); break;
                }
            });

            // Easy Editor
            var options = {
                buttons: ['bold', 'italic', 'link', 'image', 'alignleft', 'aligncenter', 'alignright', 'list', 'x', 'source'],
                buttonsHtml: {
                    'bold': '<i class="fa fa-bold"></i>',
                    'italic': '<i class="fa fa-italic"></i>',
                    'link': '<i class="fa fa-link"></i>',
                    'insert-image': '<i class="fa fa-picture-o"></i>',
                    'align-left': '<i class="fa fa-align-left"></i>',
                    'align-center': '<i class="fa fa-align-center"></i>',
                    'align-right': '<i class="fa fa-align-right"></i>',
                    
                    'list': '<i class="fa fa-list"></i>',
                    'remove-formatting': '<i class="fa fa-ban"></i>',
                    'source': '<i class="fa fa-cog"></i>'
                }, css: ({
                    minHeight: '100px',
                    maxHeight: '200px'
                }),
            };

            $('.rte').easyEditor(options);

            // JSON Editor


            // Idiom Text fields
        }

        /**
        * Runs through each fieldset (if exists)
        **/
        function qFieldset(f, fieldset) {

                var html = '';
                html += '<fieldset class="'+fieldset.fieldsetCss+'">';
                html += '<legend class="'+fieldset.legendCss+'">'+fieldset.fieldsetLabel+'</legend>';
                html += qFields(fieldset.fields);
                html += '</fieldset>';
                return html;
        }

        /**
        * Runs through each field in the definition or the fields per fieldset
        **/
        function qFields(fields) {
            
            var flds = [];
            flds = flds.concat(qHtml('div', '<p id="forminstructions" class="ml-10">text</p>', {}));
            $.each(fields, function(idx, fld) {

                // Only necessary to define specials
                switch(fld.type) {

                    case "hidden": flds = flds.concat(qHidden(fld)); break;
                    case "select": flds = flds.concat(qSelect(fld)); break;
                    case "autocomplete": flds = flds.concat(qAutoComplete(fld)); break;

                    case "idmtext": flds = flds.concat(qIdiomText(fld)); break;
                    case "json": flds = flds.concat(qJSONEditor(fld)); break;
                    // Is ordinary field but type gets changed to "textarea" on display and get content
                    case "rte": flds = flds.concat(qRichTextEditor(fld)); break;

                    case "radio": flds = flds.concat(qRadio(fld)); break;
                    case "checkbox": flds = flds.concat(qCheckbox(fld)); break;

                    case "buttons": flds = flds.concat(qButtons(fld)); break;
                    case "freetext": flds = flds.concat(qHTML(fld)); break;
                    // Specials

                    // Text, Number, URL, Email - generally a standard input type
                    default: flds = flds.concat(qText(fld)); break;
                }
            });
            return flds;     
        }

        /**
        * Hidden field
        **/
        function qHidden(fld) {

            // Standard options
            var defld = {name : fld.name, id : fld.id, type : fld.type};
            return qOptions(fld, defld);
        }

        /**
        * Text fields, also input types such as number, url, email
        **/
        function qText(fld) {

            // Standard options
            var defld = {name : fld.name, id : fld.id, type : fld.type};
            var frmgrp = qFdiv(fld, qOptions(fld, defld));
            return frmgrp;
        }

        /**
        * Text fields, also input types such as number, url, email
        **/
        function qAutoComplete(fld) {

            // Standard options
            var defld = {name : fld.name, id : fld.id, type : 'text', 'data-options' : fld.options};
            var frmgrp = qFdiv(fld, qOptions(fld, defld));
            return frmgrp;
        }        

        /**
        * Select with options
        **/
        function qSelect(fld) {

            // Standard options
            var defld = {name : fld.name, id : fld.id, type : fld.type};
            var frmgrp = qFdiv(fld, qOptions(fld, defld));
            return frmgrp;
        }

        /**
        * Radio Group with Radio buttons
        **/
        function qRadio(fld) {

        }        

        /**
        * Checkbox group with checkboxes
        **/
        function qCheckbox(fld) {

        }

        /**
        * Textareas in a tabbed div where tabs are languages of site
        **/
        function qIdiomText(fld) {

            var qi = explode('|', fld.idms);
            var idms = {};
            $.each(qi, function(q, idm) {
                var qq = explode(':', idm);
                idms[qq[0]] = qq[1];
            });

            var tbs = [];
            $.each(idms, function(lcdcode, lcdname) {
                
                if(lcdcode == jlcd) {
                    var active = 'active';
                } else {
                    var active = '';
                }

                var item = {
                    type: 'li', class: 'nav-item', html: {
                        type: 'a', class: 'nav-link '+active,
                       'data-toggle': 'tab', href: '#'+lcdcode, role: 'tab', html: lcdname
                    } 
                }; 
                tbs.push(item);
            });

            // Idiom Nav Tabs
            var tablist = {
                type: 'ul',
                class: 'nav nav-tabs smaller', role: 'tablist',
                html: tbs
            }

            var panes = [];
            $.each(idms, function(lcdcode, lcdname) {
                
                var item = {
                    type: 'div', class: 'tab-pane', id: lcdcode, role: 'tabpanel', html: {
                        type: 'textarea', class: 'form-control e100 '+fld.class, name: fld.name+'['+lcdcode+']'
                    } 
                }; panes.push(item);
            });

            // Idiom Nav Panes
            var tabcontent = {
                type: 'container',
                class: 'tab-content', html: panes
            }; 

            var help = {
                type: 'div',
                class: 'clear text-muted col-xs-9 hlf-pad',
                html: fld.help                
            }; 

            var frmgrp = {
                type: 'container',
                class: 'form-group',
                html: [
                    qLabel(fld), 
                    {
                        type: 'div', id: fld.id,
                        css: {height: fld.height},
                        class: 'col-xs-9 hlf-pad',
                        html: [tablist, tabcontent, help]
                    }
                ]              
            };
            return frmgrp;
        }

        /**
        * Displays a JSON EDitor in line
        **/
        function qJSONEditor(fld) {

            var frmgrp = {
                type: 'container',
                class: 'form-group',
                html: [
                    qLabel(fld), 
                    {
                        type: 'div', id: fld.id,
                        css: {height: fld.height},
                        class: fld.class
                    }
                ]              
            };
            return frmgrp;
        }

        /**
        * Displays an in-line Rich Text editor with a few controls
        **/
        function qRichTextEditor(fld) {

            // Standard options
            var defld = {name : fld.name, id : fld.id, type : 'textarea'};
            var frmgrp = qFdiv(fld, qOptions(fld, defld));
            return frmgrp;
        }

        /**
        * Displays a set of buttons
        **/
        function qButtons(fld) {

            btns = new Array();

            $.each(fld.buttons, function(idx, btn) {
                btns.push({
                    type: 'button',
                    class: 'mr5 btn btn-sm right '+btn.class,
                    html: btn.title,
                    'data-hook': 'formbutton',
                    'data-action': btn.action
                });
            });
            var frmgrp = {
                type: 'container',
                class: 'formgroup-group mt10 row', // space top and bottom
                id: 'buttongrp',
                html: [{
                    type: 'div',
                    class: 'col-xs-12 right',
                    html: btns
                }]              
            };
            return frmgrp;
        }

        /**
        * Displays free text but within a form row
        **/
        function qHTML(fld) {
            var frmgrp = {
                type: 'container',
                html: fld.html          
            };
            return frmgrp;
        }

        /**
        * Adds all appropriate options to any fields
        **/
        function qOptions(fld, defld) {

            $.each(fld, function(key, attr) {

                switch(key) {

                    case "class":
                        defld['class'] = "form-control " + attr;
                    break;

                    case "name":
                        defld['name'] = attr;
                        defld['v-model'] = attr;
                    break;

                    

                    default:
                        defld[key] = attr;
                    break;
                }

            });

            return defld;
        } 

        /**
        * The complete form Row or element
        **/
        function qFdiv(fld, defld) {
            return {
                type: 'div',
                class: 'form-group mb5 row',
                html: [qLabel(fld), qIdiv(defld, fld)]              
            }
        }        

        /**
        * The inner form element after the label
        **/
        function qIdiv(defld, fld) {
           
            if(array_key_exists('help', fld)) {
                return {
                    type: 'div',
                    class: 'col-xs-9',
                    html: [
                        defld,
                        {
                            type: 'span',
                            class: 'clear text-muted',
                            html: fld.help
                        }
                    ]
                } 
            } else if(array_key_exists('icon', fld)) {
                return {
                    type: 'div',
                    class: 'col-xs-9',
                    html: [
                        {
                            type: 'i',
                            class: 'right tp5 larger fa fa-'+fld.icon
                        }, defld
                    ]
                } 
            } else {
                return {
                    type: 'div',
                    class: 'col-xs-9',
                    html: defld
                }            
            }
        }

        /**
        * The Label
        **/
        function qLabel(fld) {
            
            if(array_key_exists('required', fld)) {
                var req = '<span class="bold red larger lp5">*</span>';
            } else {
                var req = '';
            };

            var labeltxt = {
                type: 'span',
                class: 'blue',
                html: fld.label+req 
            };

            var lbl = {
                type: 'label', for: fld.id,
                class: 'col-xs-3 form-control-label left', // 
                html: labeltxt         
            };

            return lbl;
        }

        /**
        * Produces an HTML string
        **/
        function qHtml(tag, html, attrs) {
            

            // you can skip html param
            if (typeof(html) != 'string') {
                attrs = html; html = null;
            }
            var h = '<' + tag;
            for (attr in attrs) {
                if(attrs[attr] === false) continue;
                h += ' ' + attr + '="' + attrs[attr] + '"';
            }
            h += html ? ">" + html + "</" + tag + ">" : "/>";

            return {
                'type': 'container',
                'html': h
            }
        }   

        /**
        * Creates a Preview of the form content in a table in a Noty
        **/
        function qPreview(formid) {
            
            var tbody = '';         
            $.each(clqfCfg.formopts.formfields, function(idx, fld) {
                if(fld.type != 'buttons') {
                    var label = '';
                    array_key_exists('label', fld) ? label = fld.label : label = fld.name ;
                    tbody += '<tr class="">';
                    tbody += '<td class="txtright blue e30 text-muted ">'+label+'</td>';
                    tbody += '<td class="txtleft e70">';

                    switch(fld.type) {

                        case "rte": tbody += $('#'+fld.id).html(); break;
                        case "json": tbody += jEditor[fld.id].getText(); break;
                        default: tbody += $('#'+fld.id).val(); break;
                    };

                    tbody += '</td>';
                    tbody += '</tr>';
                }
            });

            var tbl = '<div class="container"><h3 id="tablecaption">'+clqfCfg.formopts.title+'</h3><table class="table table-bordered table-sm table-striped smaller">';
            tbl += '<tbody>'+tbody+'</tbody></table></div>';

            Cliqu.nMsg({timeout: false, text: tbl});
        }

        /**
        * Submits the form
        **/
        function qSubmit(formid) {
            
            // Introduce validation            
            // if(clqfCfg.form.valid() == true) {
                // Get data for form, which will create PostData
                var postdata = "?";
                $.each(clqfCfg.formopts.formfields, function(idx, fld) {
                    

                    switch(fld.type) {

                        case "buttons": null; break;

                        case "rte": postdata += fld.name + "=" + encodeURIComponent($('#'+fld.id).html()) + "&"; break;
                        case "json": postdata += fld.name + "=" + encodeURIComponent(jEditor[fld.id].getText()) + "&"; break;
                        default: postdata += fld.name + "=" + encodeURIComponent($('#'+fld.id).val()) + "&"; break;
                    }   
                });

                // Anything else to add to Postdata ??
                postdata = trim(postdata, "&");
                console.log(postdata);

                aja()
                .method('POST')
                .url(clqfCfg.formopts.urlstr+postdata)
                // .timeout(2500)
                .cache(false)
                .on('200', function(data){
                    // DataVue.collections.$set(data);
                    reLoad();
                })
                .on('40x', function(response){
                    Cliqu.nMsg({buttons: false, type: 'error', text: response}); //something is definitely wrong
                })
                .on('500', function(response){
                    Cliqu.nMsg({buttons: false, type: 'error', text: response}); //oh crap
                })
                .go();
                // Ends

            // };
            return false;
        }

        /**
         * Gets back the Data from the form as an Array
         * adds a RowID
         * converts it to a format usable for the Bootstrap Table
         **/
        function qData(formid) {
            var data = $('#'+formid+' form').serializeArray();
            var result = {
                id: count(vuedata) // Not plus 1 as ID starts from Zero when used by Lstr
            };
            $.each(data, function(key, val) {
                result[val.name] = val.value;
            });
            return result;
        }

        /**
         * Popup in which to show clickable selections
         * @return generates a popup attached to the input field
         **/
        function qPopSelect() {

            // Produces a usable JSON object
            var popts = {
                selector: '.autocomplete',
                html: true,
                content: function() {
                    var list = $(this).data('options');
                    var id = $(this).attr('id');
                    var buffer = '<ul class="list">';
                    $.each(list, function(idx, lbl) { 
                        buffer += '<li rel="'+idx+'" class="list-item" data-id="'+id+'">'+lbl+'</li>'; 
                    });
                    buffer += '</ul>'; 
                    return buffer;                   
                },
                placement: 'top',
                trigger: 'click'
            };
            $('form').popover(popts);

            $('form').on('shown.bs.popover', function () {
                return $('.list-item').on('click', function(e) {
                    var rel = $(this).attr('rel');
                    var id = $(this).data('id');
                    $('#'+id).val(rel);
                })
            })
        }

        // 
        /**
         * is unique, next reference and next id
         * @return generates a form activity
         **/
        function qInputHelpers() {

            $('.isunique').blur(function(e) {
                if($('input[name="'+name+'"]').val() != "") {
                    var table = $('input[name="table"]').val();
                    var tabletype = $('input[name="type"]').val();                 
                    var fldname = $(this).attr('name');
                    aja().method('GET').url('/request/'+jlcd+'/isunique/'+table+'/'+tabletype+'/')
                    .data({fld: fldname, tocheck: $('input[name="'+fldname+'"]').val()}).cache(false)
                    .on('200', function(data){
                        // Test NotOK - value already exists
                        var match = /NotOk/.test(data);
                        if(match == true) {
                            Cliqu.nMsg({type: 'error', text: lstr[24]});
                            $('input[name="'+name+'"]').empty().focus();
                        };         
                    }).on('40x', function(response){
                        Cliqu.nMsg({buttons: false, type: 'error', text: response}); //something is definitely wrong
                    }).on('500', function(response){
                        Cliqu.nMsg({buttons: false, type: 'error', text: response}); //oh crap
                    }).go();  // Ends                    
                }
            });

            $('.nextref').focus(function(e) {
                

                var fldname = $('input[name="'+name+'"]').val()
                var table = $('input[name="table"]').val();
                var tabletype = $('input[name="type"]').val();
                aja().method('GET').url('/request/'+jlcd+'/getnextref/'+table+'/'+tabletype+'/')
                .body({fld: fldname, defval: $('input[name="'+fldname+'"]').attr('placeholder')}).cache(false)
                .on('200', function(data){
                    // Test NotOK - next reference or equivalent
                    var match = /NotOk/.test(data);
                    if(match != true) {
                        $('input[name="'+name+'"]').val();
                    } else {
                        Cliqu.nMsg({type: 'error', text: lstr[22]});
                    };         
                }).on('40x', function(response){
                    Cliqu.nMsg({buttons: false, type: 'error', text: response}); //something is definitely wrong
                }).on('500', function(response){
                    Cliqu.nMsg({buttons: false, type: 'error', text: response}); //oh crap
                }).go();  // Ends
            });

            $('.nextid').focus(function(e) {
                var fldname = $('input[name="'+name+'"]').val()
                var table = $('input[name="table"]').val();
                var tabletype = $('input[name="type"]').val();
                aja().method('GET').url('/request/'+jlcd+'/getnextid/'+table+'/'+tabletype+'/')
                .body({fld: fldname, defval: $('input[name="'+fldname+'"]').attr('placeholder')}).cache(false)
                .on('200', function(data){
                    // Test NotOK - next reference or equivalent
                    var match = /NotOk/.test(data);
                    if(match != true) {
                        $('input[name="'+name+'"]').val();
                    } else {
                        Cliqu.nMsg({type: 'error', text: lstr[22]});
                    };         
                }).on('40x', function(response){
                    Cliqu.nMsg({buttons: false, type: 'error', text: response}); //something is definitely wrong
                }).on('500', function(response){
                    Cliqu.nMsg({buttons: false, type: 'error', text: response}); //oh crap
                }).go();  // Ends
            });
        };

        // explicitly return public methods when this object is instantiated
        return {
            // outSide: inSide,
            form: qForm,
            formPreview: qPreview,
            getData: qData

        };
    })(jQuery); 