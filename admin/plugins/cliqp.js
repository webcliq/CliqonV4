// Cliqp.Js

/** Cliqon Plugin Functions template - Cliqp.fn() 
 *
 *************************************************************************************/

var Cliqp = (function($) {

    // initialise
    // var shared values
    var pcfg = {
        useCaching: true,
        idioms: {},
        langcd: jlcd,
        sitepath: "http://"+document.location.hostname+"/",
        spinner: new Spinner(),
        dp: new Object
    }, cfg = {};

    var _set = function(key,value)
    {
        pcfg[key] = value;
        return pcfg[key];
    }

    var _get = function(key)
    {
        return pcfg[key];
    }

    var _config = function()
    {
        return pcfg;
    };

    /**
     * Generic testing page for the creation of test pages
     * @param - array - of options
     * @return - Javascript and HTML, with help of Template
     **/        
    var testRig = function(opts) 
    {
        store.session.set('contenttype', 'other');
        var vm = new Vue({
            el: '#admtestrig',
            data: {
                inputform: {
                    idiom: 'en',
                    inputfile: '',
                    anydata:'This data'
                }
            },
            mounted: function() {
                
                $('#inputform').submit(function(evt) {
                    
                    evt.preventDefault();
                    var frmData = getFormData(false, 'inputform');
                    var urlstr = '/ajax/'+jlcd+'/dotestrig/';

                    $.ajax({
                        url: urlstr, data: frmData,
                        cache: false, contentType: false, processData: false,
                        type: 'POST', async: false, timeout: 25000,
                        success: handleResponse, error: handleError
                    });         

                    return false;
                });     
            }
        }); 
    }; 


    // explicitly return public methods when this object is instantiated
    return {
        // outSide: inSide,
        'config': _config,
        'set': _set,
        'get': _get
    };

})(jQuery);    