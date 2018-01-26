/*!
* basket.js
* v0.5.2 - 2015-02-07
* http://addyosmani.github.com/basket.js
* (c) Addy Osmani;  License
* Created by: Addy Osmani, Sindre Sorhus, Andrée Hansson, Mat Scales
* Contributors: Ironsjp, Mathias Bynens, Rick Waldron, Felipe Morais
* Uses rsvp.js, https://github.com/tildeio/rsvp.js
*/
(function( window, document ) {
    'use strict';

    var head = document.head || document.getElementsByTagName('head')[0];
    var storagePrefix = 'basket-';
    var defaultExpiration = 5000;
    var inBasket = [];

    var addLocalStorage = function( key, storeObj ) {
        try {
            localStorage.setItem( storagePrefix + key, JSON.stringify( storeObj ) );
            return true;
        } catch( e ) {
            if ( e.name.toUpperCase().indexOf('QUOTA') >= 0 ) {
                var item;
                var tempScripts = [];

                for ( item in localStorage ) {
                    if ( item.indexOf( storagePrefix ) === 0 ) {
                        tempScripts.push( JSON.parse( localStorage[ item ] ) );
                    }
                }

                if ( tempScripts.length ) {
                    tempScripts.sort(function( a, b ) {
                        return a.stamp - b.stamp;
                    });

                    basket.remove( tempScripts[ 0 ].key );

                    return addLocalStorage( key, storeObj );

                } else {
                    // no files to remove. Larger than available quota
                    return;
                }

            } else {
                // some other error
                return;
            }
        }

    };

    var getUrl = function( url ) {
        var promise = new RSVP.Promise( function( resolve, reject ){

            var xhr = new XMLHttpRequest();
            xhr.open( 'GET', url );

            xhr.onreadystatechange = function() {
                if ( xhr.readyState === 4 ) {
                    if ( ( xhr.status === 200 ) ||
                            ( ( xhr.status === 0 ) && xhr.responseText ) ) {
                        resolve( {
                            content: xhr.responseText,
                            type: xhr.getResponseHeader('content-type')
                        } );
                    } else {
                        reject( new Error( xhr.statusText ) );
                    }
                }
            };

            // By default XHRs never timeout, and even Chrome doesn't implement the
            // spec for xhr.timeout. So we do it ourselves.
            setTimeout( function () {
                if( xhr.readyState < 4 ) {
                    xhr.abort();
                }
            }, basket.timeout );

            xhr.send();
        });

        return promise;
    };

    var saveUrl = function( obj ) {
        return getUrl( obj.url ).then( function( result ) {
            var storeObj = wrapStoreData( obj, result );

            if (!obj.skipCache) {
                addLocalStorage( obj.key , storeObj );
            }

            return storeObj;
        });
    };

    var wrapStoreData = function( obj, data ) {
        var now = +new Date();
        obj.data = data.content;
        obj.originalType = data.type;
        obj.type = obj.type || data.type;
        obj.skipCache = obj.skipCache || false;
        obj.stamp = now;
        obj.expire = now + ( ( obj.expire || defaultExpiration ) * 60 * 60 * 1000 );

        return obj;
    };

    var isCacheValid = function(source, obj) {
        return !source ||
            source.expire - +new Date() < 0  ||
            obj.unique !== source.unique ||
            (basket.isValidItem && !basket.isValidItem(source, obj));
    };

    var handleStackObject = function( obj ) {
        var source, promise, shouldFetch;

        if ( !obj.url ) {
            return;
        }

        obj.key =  ( obj.key || obj.url );
        source = basket.get( obj.key );
                
        obj.execute = obj.execute !== false;

        shouldFetch = isCacheValid(source, obj);

        if( obj.live || shouldFetch ) {
            if ( obj.unique ) {
                // set parameter to prevent browser cache
                obj.url += ( ( obj.url.indexOf('?') > 0 ) ? '&' : '?' ) + 'basket-unique=' + obj.unique;
            }
            promise = saveUrl( obj );

            if( obj.live && !shouldFetch ) {
                promise = promise
                    .then( function( result ) {
                        // If we succeed, just return the value
                        // RSVP doesn't have a .fail convenience method
                        return result;
                    }, function() {
                        return source;
                    });
            }
        } else {
            source.type = obj.type || source.originalType;
            source.execute = obj.execute;
            promise = new RSVP.Promise( function( resolve ){
                resolve( source );
            });
        }

        return promise;
    };

    var injectScript = function( obj ) {
        var script = document.createElement('script');
        script.defer = true;
        // Have to use .text, since we support IE8,
        // which won't allow appending to a script
        script.text = obj.data;
        head.appendChild( script );
    };

    var handlers = {
        'default': injectScript
    };

    var execute = function( obj ) {
        if( obj.type && handlers[ obj.type ] ) {
            return handlers[ obj.type ]( obj );
        }

        return handlers['default']( obj ); // 'default' is a reserved word
    };

    var performActions = function( resources ) {
        return resources.map( function( obj ) {
            if( obj.execute ) {
                execute( obj );
            }

            return obj;
        } );
    };

    var fetch = function() {
        var i, l, promises = [];

        for ( i = 0, l = arguments.length; i < l; i++ ) {
            promises.push( handleStackObject( arguments[ i ] ) );
        }

        return RSVP.all( promises );
    };

    var thenRequire = function() {
        var resources = fetch.apply( null, arguments );
        var promise = this.then( function() {
            return resources;
        }).then( performActions );
        promise.thenRequire = thenRequire;
        return promise;
    };

    window.basket = {
        require: function() {
            for ( var a = 0, l = arguments.length; a < l; a++ ) {
                arguments[a].execute = arguments[a].execute !== false;
                
                if ( arguments[a].once && inBasket.indexOf(arguments[a].url) >= 0 ) {
                    arguments[a].execute = false;
                } else if ( arguments[a].execute !== false && inBasket.indexOf(arguments[a].url) < 0 ) {  
                    inBasket.push(arguments[a].url);
                }
            }
                        
            var promise = fetch.apply( null, arguments ).then( performActions );

            promise.thenRequire = thenRequire;
            return promise;
        },

        remove: function( key ) {
            localStorage.removeItem( storagePrefix + key );
            return this;
        },

        get: function( key ) {
            var item = localStorage.getItem( storagePrefix + key );
            try {
                return JSON.parse( item || 'false' );
            } catch( e ) {
                return false;
            }
        },

        clear: function( expired ) {
            var item, key;
            var now = +new Date();

            for ( item in localStorage ) {
                key = item.split( storagePrefix )[ 1 ];
                if ( key && ( !expired || this.get( key ).expire <= now ) ) {
                    this.remove( key );
                }
            }

            return this;
        },

        isValidItem: null,

        timeout: 5000,

        addHandler: function( types, handler ) {
            if( !Array.isArray( types ) ) {
                types = [ types ];
            }
            types.forEach( function( type ) {
                handlers[ type ] = handler;
            });
        },

        removeHandler: function( types ) {
            basket.addHandler( types, undefined );
        }
    };

    // delete expired keys
    basket.clear( true );

})( this, document );

// create-stylesheet 0.2.1
// Andrew Wakeling <andrew.wakeling@gmail.com>
// create-stylesheet may be freely distributed under the MIT license.
var _stylesheet = {};
/**
 * For awareness of KB262161, if 31 or more total stylesheets exist when invoking appendStyleSheet, insertStyleSheetBefore or replaceStyleSheet, an error will be thrown in ANY browser.
 * If you really want to disable this error (for non-IE), set this flag to true.
 *
 * Note: Once you hit 31 stylesheets in IE8 & IE9, you will be unable to create any new stylesheets successfully (regardless of this setting) and this will ALWAYS cause an error.
 */
_stylesheet.ignoreKB262161 = false;

/**
 * Create an empty stylesheet and insert it into the DOM before the specified node. If no node is specified, then it will be appended at the end of the head.
 *
 * @param node - DOM element
 * @param callback - function(err, style)
 */
function insertEmptyStyleBefore(node, callback) {
    var style = document.createElement('style');
    style.setAttribute('type', 'text/css');
    var head = document.getElementsByTagName('head')[0];
    if (node) {
        head.insertBefore(style, node);
    } else {
        head.appendChild(style);
    }
    if (style.styleSheet && style.styleSheet.disabled) {
        head.removeChild(style);
        return callback('Unable to add any more stylesheets because you have exceeded the maximum allowable stylesheets. See KB262161 for more information.');
    }
    callback(null, style);
}

/**
 * Set the CSS text on the specified style element.
 * @param style
 * @param css
 * @param callback - function(err)
 */
function setStyleCss(style, css, callback) {
    try {
        // Favor cssText over textContent as it appears to be slightly faster for IE browsers.
        if (style.styleSheet) {
            style.styleSheet.cssText = css;
        } else if ('textContent' in style) {
            style.textContent = css;
        } else {
            style.appendChild(document.createTextNode(css));
        }
        return callback(null);
    } catch (e) {
        // Ideally this should never happen but there are still obscure cases with IE where attempting to set cssText can fail.
        callback(e);
    }
}

/**
 * Remove the specified style element from the DOM unless it's not in the DOM already.
 *
 * Note: This isn't doing anything special now, but if any edge-cases arise which need handling (e.g. IE), they can be implemented here.
 * @param node
 */
function removeStyleSheet(node) {
    if (node.tagName === 'STYLE' && node.parentNode) {
        node.parentNode.removeChild(node);
    }
}

/**
 * Create a stylesheet with the specified options.
 * @param options - options object. e.g. {ignoreKB262161: true, replace: null, css: 'body {}' }
 * @param callback - function(err, style)
 *
 * options
 * - css; The css text which will be used to create the new stylesheet.
 * - replace; Specify a style element which will be deleted and the new stylesheet will take its place. This overrides the 'insertBefore' option.
 * - insertBefore; If specified, the new stylesheet will be inserted before this DOM node. If this value is null or undefined, then it will be appended to the head element.
 */
function createStyleSheet(options, callback) {
    if (!_stylesheet.ignoreKB262161 && document.styleSheets.length >= 31) {
        callback('Unable to add any more stylesheets because you have exceeded the maximum allowable stylesheets. See KB262161 for more information.');
    }

    insertEmptyStyleBefore(options.replace ? options.replace.nextSibling : options.insertBefore, function (err, style) {
        if (err) {
            callback(err);
        } else {
            setStyleCss(style, options.css || "", function (err) {
                if (err) {
                    removeStyleSheet(style);
                    callback(err);
                } else {
                    // TODO: If we want to transfer any attributes from an existing style node, this is the time and place to do it.
                    if (options.replace) {
                        removeStyleSheet(options.replace);
                    }
                    callback(null, style);
                }
            });
        }
    });
}

_stylesheet = {
    appendStyleSheet: function (css, callback) {
        createStyleSheet({
            css: css
        }, callback);
    },
    insertStyleSheetBefore: function (node, css, callback) {
        createStyleSheet({
            insertBefore: node,
            css: css
        }, callback);
    },
    replaceStyleSheet: function (node, css, callback) {
        createStyleSheet({
            replace: node,
            css: css
        }, callback);
    },
    removeStyleSheet: removeStyleSheet
};

!function(e){if("object"==typeof exports&&"undefined"!=typeof module)module.exports=e();else if("function"==typeof define&&define.amd)define([],e);else{var n;n="undefined"!=typeof window?window:"undefined"!=typeof global?global:"undefined"!=typeof self?self:this,n.StackTrace=e()}}(function(){var e;return function n(e,r,t){function o(a,s){if(!r[a]){if(!e[a]){var u="function"==typeof require&&require;if(!s&&u)return u(a,!0);if(i)return i(a,!0);var c=new Error("Cannot find module '"+a+"'");throw c.code="MODULE_NOT_FOUND",c}var l=r[a]={exports:{}};e[a][0].call(l.exports,function(n){var r=e[a][1][n];return o(r?r:n)},l,l.exports,n,e,r,t)}return r[a].exports}for(var i="function"==typeof require&&require,a=0;a<t.length;a++)o(t[a]);return o}({1:[function(n,r,t){!function(o,i){"use strict";"function"==typeof e&&e.amd?e("error-stack-parser",["stackframe"],i):"object"==typeof t?r.exports=i(n("stackframe")):o.ErrorStackParser=i(o.StackFrame)}(this,function(e){"use strict";var n=/(^|@)\S+\:\d+/,r=/^\s*at .*(\S+\:\d+|\(native\))/m,t=/^(eval@)?(\[native code\])?$/;return{parse:function(e){if("undefined"!=typeof e.stacktrace||"undefined"!=typeof e["opera#sourceloc"])return this.parseOpera(e);if(e.stack&&e.stack.match(r))return this.parseV8OrIE(e);if(e.stack)return this.parseFFOrSafari(e);throw new Error("Cannot parse given Error object")},extractLocation:function(e){if(e.indexOf(":")===-1)return[e];var n=/(.+?)(?:\:(\d+))?(?:\:(\d+))?$/,r=n.exec(e.replace(/[\(\)]/g,""));return[r[1],r[2]||void 0,r[3]||void 0]},parseV8OrIE:function(n){var t=n.stack.split("\n").filter(function(e){return!!e.match(r)},this);return t.map(function(n){n.indexOf("(eval ")>-1&&(n=n.replace(/eval code/g,"eval").replace(/(\(eval at [^\()]*)|(\)\,.*$)/g,""));var r=n.replace(/^\s+/,"").replace(/\(eval code/g,"(").split(/\s+/).slice(1),t=this.extractLocation(r.pop()),o=r.join(" ")||void 0,i=["eval","<anonymous>"].indexOf(t[0])>-1?void 0:t[0];return new e({functionName:o,fileName:i,lineNumber:t[1],columnNumber:t[2],source:n})},this)},parseFFOrSafari:function(n){var r=n.stack.split("\n").filter(function(e){return!e.match(t)},this);return r.map(function(n){if(n.indexOf(" > eval")>-1&&(n=n.replace(/ line (\d+)(?: > eval line \d+)* > eval\:\d+\:\d+/g,":$1")),n.indexOf("@")===-1&&n.indexOf(":")===-1)return new e({functionName:n});var r=n.split("@"),t=this.extractLocation(r.pop()),o=r.join("@")||void 0;return new e({functionName:o,fileName:t[0],lineNumber:t[1],columnNumber:t[2],source:n})},this)},parseOpera:function(e){return!e.stacktrace||e.message.indexOf("\n")>-1&&e.message.split("\n").length>e.stacktrace.split("\n").length?this.parseOpera9(e):e.stack?this.parseOpera11(e):this.parseOpera10(e)},parseOpera9:function(n){for(var r=/Line (\d+).*script (?:in )?(\S+)/i,t=n.message.split("\n"),o=[],i=2,a=t.length;i<a;i+=2){var s=r.exec(t[i]);s&&o.push(new e({fileName:s[2],lineNumber:s[1],source:t[i]}))}return o},parseOpera10:function(n){for(var r=/Line (\d+).*script (?:in )?(\S+)(?:: In function (\S+))?$/i,t=n.stacktrace.split("\n"),o=[],i=0,a=t.length;i<a;i+=2){var s=r.exec(t[i]);s&&o.push(new e({functionName:s[3]||void 0,fileName:s[2],lineNumber:s[1],source:t[i]}))}return o},parseOpera11:function(r){var t=r.stack.split("\n").filter(function(e){return!!e.match(n)&&!e.match(/^Error created at/)},this);return t.map(function(n){var r,t=n.split("@"),o=this.extractLocation(t.pop()),i=t.shift()||"",a=i.replace(/<anonymous function(: (\w+))?>/,"$2").replace(/\([^\)]*\)/g,"")||void 0;i.match(/\(([^\)]*)\)/)&&(r=i.replace(/^[^\(]+\(([^\)]*)\)$/,"$1"));var s=void 0===r||"[arguments not available]"===r?void 0:r.split(",");return new e({functionName:a,args:s,fileName:o[0],lineNumber:o[1],columnNumber:o[2],source:n})},this)}}})},{stackframe:10}],2:[function(e,n,r){function t(){this._array=[],this._set=Object.create(null)}var o=e("./util"),i=Object.prototype.hasOwnProperty;t.fromArray=function(e,n){for(var r=new t,o=0,i=e.length;o<i;o++)r.add(e[o],n);return r},t.prototype.size=function(){return Object.getOwnPropertyNames(this._set).length},t.prototype.add=function(e,n){var r=o.toSetString(e),t=i.call(this._set,r),a=this._array.length;t&&!n||this._array.push(e),t||(this._set[r]=a)},t.prototype.has=function(e){var n=o.toSetString(e);return i.call(this._set,n)},t.prototype.indexOf=function(e){var n=o.toSetString(e);if(i.call(this._set,n))return this._set[n];throw new Error('"'+e+'" is not in the set.')},t.prototype.at=function(e){if(e>=0&&e<this._array.length)return this._array[e];throw new Error("No element indexed by "+e)},t.prototype.toArray=function(){return this._array.slice()},r.ArraySet=t},{"./util":8}],3:[function(e,n,r){function t(e){return e<0?(-e<<1)+1:(e<<1)+0}function o(e){var n=1===(1&e),r=e>>1;return n?-r:r}var i=e("./base64"),a=5,s=1<<a,u=s-1,c=s;r.encode=function(e){var n,r="",o=t(e);do n=o&u,o>>>=a,o>0&&(n|=c),r+=i.encode(n);while(o>0);return r},r.decode=function(e,n,r){var t,s,l=e.length,f=0,p=0;do{if(n>=l)throw new Error("Expected more digits in base 64 VLQ value.");if(s=i.decode(e.charCodeAt(n++)),s===-1)throw new Error("Invalid base64 digit: "+e.charAt(n-1));t=!!(s&c),s&=u,f+=s<<p,p+=a}while(t);r.value=o(f),r.rest=n}},{"./base64":4}],4:[function(e,n,r){var t="ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/".split("");r.encode=function(e){if(0<=e&&e<t.length)return t[e];throw new TypeError("Must be between 0 and 63: "+e)},r.decode=function(e){var n=65,r=90,t=97,o=122,i=48,a=57,s=43,u=47,c=26,l=52;return n<=e&&e<=r?e-n:t<=e&&e<=o?e-t+c:i<=e&&e<=a?e-i+l:e==s?62:e==u?63:-1}},{}],5:[function(e,n,r){function t(e,n,o,i,a,s){var u=Math.floor((n-e)/2)+e,c=a(o,i[u],!0);return 0===c?u:c>0?n-u>1?t(u,n,o,i,a,s):s==r.LEAST_UPPER_BOUND?n<i.length?n:-1:u:u-e>1?t(e,u,o,i,a,s):s==r.LEAST_UPPER_BOUND?u:e<0?-1:e}r.GREATEST_LOWER_BOUND=1,r.LEAST_UPPER_BOUND=2,r.search=function(e,n,o,i){if(0===n.length)return-1;var a=t(-1,n.length,e,n,o,i||r.GREATEST_LOWER_BOUND);if(a<0)return-1;for(;a-1>=0&&0===o(n[a],n[a-1],!0);)--a;return a}},{}],6:[function(e,n,r){function t(e,n,r){var t=e[n];e[n]=e[r],e[r]=t}function o(e,n){return Math.round(e+Math.random()*(n-e))}function i(e,n,r,a){if(r<a){var s=o(r,a),u=r-1;t(e,s,a);for(var c=e[a],l=r;l<a;l++)n(e[l],c)<=0&&(u+=1,t(e,u,l));t(e,u+1,l);var f=u+1;i(e,n,r,f-1),i(e,n,f+1,a)}}r.quickSort=function(e,n){i(e,n,0,e.length-1)}},{}],7:[function(e,n,r){function t(e){var n=e;return"string"==typeof e&&(n=JSON.parse(e.replace(/^\)\]\}'/,""))),null!=n.sections?new a(n):new o(n)}function o(e){var n=e;"string"==typeof e&&(n=JSON.parse(e.replace(/^\)\]\}'/,"")));var r=s.getArg(n,"version"),t=s.getArg(n,"sources"),o=s.getArg(n,"names",[]),i=s.getArg(n,"sourceRoot",null),a=s.getArg(n,"sourcesContent",null),u=s.getArg(n,"mappings"),l=s.getArg(n,"file",null);if(r!=this._version)throw new Error("Unsupported version: "+r);t=t.map(String).map(s.normalize).map(function(e){return i&&s.isAbsolute(i)&&s.isAbsolute(e)?s.relative(i,e):e}),this._names=c.fromArray(o.map(String),!0),this._sources=c.fromArray(t,!0),this.sourceRoot=i,this.sourcesContent=a,this._mappings=u,this.file=l}function i(){this.generatedLine=0,this.generatedColumn=0,this.source=null,this.originalLine=null,this.originalColumn=null,this.name=null}function a(e){var n=e;"string"==typeof e&&(n=JSON.parse(e.replace(/^\)\]\}'/,"")));var r=s.getArg(n,"version"),o=s.getArg(n,"sections");if(r!=this._version)throw new Error("Unsupported version: "+r);this._sources=new c,this._names=new c;var i={line:-1,column:0};this._sections=o.map(function(e){if(e.url)throw new Error("Support for url field in sections not implemented.");var n=s.getArg(e,"offset"),r=s.getArg(n,"line"),o=s.getArg(n,"column");if(r<i.line||r===i.line&&o<i.column)throw new Error("Section offsets must be ordered and non-overlapping.");return i=n,{generatedOffset:{generatedLine:r+1,generatedColumn:o+1},consumer:new t(s.getArg(e,"map"))}})}var s=e("./util"),u=e("./binary-search"),c=e("./array-set").ArraySet,l=e("./base64-vlq"),f=e("./quick-sort").quickSort;t.fromSourceMap=function(e){return o.fromSourceMap(e)},t.prototype._version=3,t.prototype.__generatedMappings=null,Object.defineProperty(t.prototype,"_generatedMappings",{get:function(){return this.__generatedMappings||this._parseMappings(this._mappings,this.sourceRoot),this.__generatedMappings}}),t.prototype.__originalMappings=null,Object.defineProperty(t.prototype,"_originalMappings",{get:function(){return this.__originalMappings||this._parseMappings(this._mappings,this.sourceRoot),this.__originalMappings}}),t.prototype._charIsMappingSeparator=function(e,n){var r=e.charAt(n);return";"===r||","===r},t.prototype._parseMappings=function(e,n){throw new Error("Subclasses must implement _parseMappings")},t.GENERATED_ORDER=1,t.ORIGINAL_ORDER=2,t.GREATEST_LOWER_BOUND=1,t.LEAST_UPPER_BOUND=2,t.prototype.eachMapping=function(e,n,r){var o,i=n||null,a=r||t.GENERATED_ORDER;switch(a){case t.GENERATED_ORDER:o=this._generatedMappings;break;case t.ORIGINAL_ORDER:o=this._originalMappings;break;default:throw new Error("Unknown order of iteration.")}var u=this.sourceRoot;o.map(function(e){var n=null===e.source?null:this._sources.at(e.source);return null!=n&&null!=u&&(n=s.join(u,n)),{source:n,generatedLine:e.generatedLine,generatedColumn:e.generatedColumn,originalLine:e.originalLine,originalColumn:e.originalColumn,name:null===e.name?null:this._names.at(e.name)}},this).forEach(e,i)},t.prototype.allGeneratedPositionsFor=function(e){var n=s.getArg(e,"line"),r={source:s.getArg(e,"source"),originalLine:n,originalColumn:s.getArg(e,"column",0)};if(null!=this.sourceRoot&&(r.source=s.relative(this.sourceRoot,r.source)),!this._sources.has(r.source))return[];r.source=this._sources.indexOf(r.source);var t=[],o=this._findMapping(r,this._originalMappings,"originalLine","originalColumn",s.compareByOriginalPositions,u.LEAST_UPPER_BOUND);if(o>=0){var i=this._originalMappings[o];if(void 0===e.column)for(var a=i.originalLine;i&&i.originalLine===a;)t.push({line:s.getArg(i,"generatedLine",null),column:s.getArg(i,"generatedColumn",null),lastColumn:s.getArg(i,"lastGeneratedColumn",null)}),i=this._originalMappings[++o];else for(var c=i.originalColumn;i&&i.originalLine===n&&i.originalColumn==c;)t.push({line:s.getArg(i,"generatedLine",null),column:s.getArg(i,"generatedColumn",null),lastColumn:s.getArg(i,"lastGeneratedColumn",null)}),i=this._originalMappings[++o]}return t},r.SourceMapConsumer=t,o.prototype=Object.create(t.prototype),o.prototype.consumer=t,o.fromSourceMap=function(e){var n=Object.create(o.prototype),r=n._names=c.fromArray(e._names.toArray(),!0),t=n._sources=c.fromArray(e._sources.toArray(),!0);n.sourceRoot=e._sourceRoot,n.sourcesContent=e._generateSourcesContent(n._sources.toArray(),n.sourceRoot),n.file=e._file;for(var a=e._mappings.toArray().slice(),u=n.__generatedMappings=[],l=n.__originalMappings=[],p=0,g=a.length;p<g;p++){var h=a[p],m=new i;m.generatedLine=h.generatedLine,m.generatedColumn=h.generatedColumn,h.source&&(m.source=t.indexOf(h.source),m.originalLine=h.originalLine,m.originalColumn=h.originalColumn,h.name&&(m.name=r.indexOf(h.name)),l.push(m)),u.push(m)}return f(n.__originalMappings,s.compareByOriginalPositions),n},o.prototype._version=3,Object.defineProperty(o.prototype,"sources",{get:function(){return this._sources.toArray().map(function(e){return null!=this.sourceRoot?s.join(this.sourceRoot,e):e},this)}}),o.prototype._parseMappings=function(e,n){for(var r,t,o,a,u,c=1,p=0,g=0,h=0,m=0,d=0,v=e.length,_=0,y={},w={},b=[],C=[];_<v;)if(";"===e.charAt(_))c++,_++,p=0;else if(","===e.charAt(_))_++;else{for(r=new i,r.generatedLine=c,a=_;a<v&&!this._charIsMappingSeparator(e,a);a++);if(t=e.slice(_,a),o=y[t])_+=t.length;else{for(o=[];_<a;)l.decode(e,_,w),u=w.value,_=w.rest,o.push(u);if(2===o.length)throw new Error("Found a source, but no line and column");if(3===o.length)throw new Error("Found a source and line, but no column");y[t]=o}r.generatedColumn=p+o[0],p=r.generatedColumn,o.length>1&&(r.source=m+o[1],m+=o[1],r.originalLine=g+o[2],g=r.originalLine,r.originalLine+=1,r.originalColumn=h+o[3],h=r.originalColumn,o.length>4&&(r.name=d+o[4],d+=o[4])),C.push(r),"number"==typeof r.originalLine&&b.push(r)}f(C,s.compareByGeneratedPositionsDeflated),this.__generatedMappings=C,f(b,s.compareByOriginalPositions),this.__originalMappings=b},o.prototype._findMapping=function(e,n,r,t,o,i){if(e[r]<=0)throw new TypeError("Line must be greater than or equal to 1, got "+e[r]);if(e[t]<0)throw new TypeError("Column must be greater than or equal to 0, got "+e[t]);return u.search(e,n,o,i)},o.prototype.computeColumnSpans=function(){for(var e=0;e<this._generatedMappings.length;++e){var n=this._generatedMappings[e];if(e+1<this._generatedMappings.length){var r=this._generatedMappings[e+1];if(n.generatedLine===r.generatedLine){n.lastGeneratedColumn=r.generatedColumn-1;continue}}n.lastGeneratedColumn=1/0}},o.prototype.originalPositionFor=function(e){var n={generatedLine:s.getArg(e,"line"),generatedColumn:s.getArg(e,"column")},r=this._findMapping(n,this._generatedMappings,"generatedLine","generatedColumn",s.compareByGeneratedPositionsDeflated,s.getArg(e,"bias",t.GREATEST_LOWER_BOUND));if(r>=0){var o=this._generatedMappings[r];if(o.generatedLine===n.generatedLine){var i=s.getArg(o,"source",null);null!==i&&(i=this._sources.at(i),null!=this.sourceRoot&&(i=s.join(this.sourceRoot,i)));var a=s.getArg(o,"name",null);return null!==a&&(a=this._names.at(a)),{source:i,line:s.getArg(o,"originalLine",null),column:s.getArg(o,"originalColumn",null),name:a}}}return{source:null,line:null,column:null,name:null}},o.prototype.hasContentsOfAllSources=function(){return!!this.sourcesContent&&(this.sourcesContent.length>=this._sources.size()&&!this.sourcesContent.some(function(e){return null==e}))},o.prototype.sourceContentFor=function(e,n){if(!this.sourcesContent)return null;if(null!=this.sourceRoot&&(e=s.relative(this.sourceRoot,e)),this._sources.has(e))return this.sourcesContent[this._sources.indexOf(e)];var r;if(null!=this.sourceRoot&&(r=s.urlParse(this.sourceRoot))){var t=e.replace(/^file:\/\//,"");if("file"==r.scheme&&this._sources.has(t))return this.sourcesContent[this._sources.indexOf(t)];if((!r.path||"/"==r.path)&&this._sources.has("/"+e))return this.sourcesContent[this._sources.indexOf("/"+e)]}if(n)return null;throw new Error('"'+e+'" is not in the SourceMap.')},o.prototype.generatedPositionFor=function(e){var n=s.getArg(e,"source");if(null!=this.sourceRoot&&(n=s.relative(this.sourceRoot,n)),!this._sources.has(n))return{line:null,column:null,lastColumn:null};n=this._sources.indexOf(n);var r={source:n,originalLine:s.getArg(e,"line"),originalColumn:s.getArg(e,"column")},o=this._findMapping(r,this._originalMappings,"originalLine","originalColumn",s.compareByOriginalPositions,s.getArg(e,"bias",t.GREATEST_LOWER_BOUND));if(o>=0){var i=this._originalMappings[o];if(i.source===r.source)return{line:s.getArg(i,"generatedLine",null),column:s.getArg(i,"generatedColumn",null),lastColumn:s.getArg(i,"lastGeneratedColumn",null)}}return{line:null,column:null,lastColumn:null}},r.BasicSourceMapConsumer=o,a.prototype=Object.create(t.prototype),a.prototype.constructor=t,a.prototype._version=3,Object.defineProperty(a.prototype,"sources",{get:function(){for(var e=[],n=0;n<this._sections.length;n++)for(var r=0;r<this._sections[n].consumer.sources.length;r++)e.push(this._sections[n].consumer.sources[r]);return e}}),a.prototype.originalPositionFor=function(e){var n={generatedLine:s.getArg(e,"line"),generatedColumn:s.getArg(e,"column")},r=u.search(n,this._sections,function(e,n){var r=e.generatedLine-n.generatedOffset.generatedLine;return r?r:e.generatedColumn-n.generatedOffset.generatedColumn}),t=this._sections[r];return t?t.consumer.originalPositionFor({line:n.generatedLine-(t.generatedOffset.generatedLine-1),column:n.generatedColumn-(t.generatedOffset.generatedLine===n.generatedLine?t.generatedOffset.generatedColumn-1:0),bias:e.bias}):{source:null,line:null,column:null,name:null}},a.prototype.hasContentsOfAllSources=function(){return this._sections.every(function(e){return e.consumer.hasContentsOfAllSources()})},a.prototype.sourceContentFor=function(e,n){for(var r=0;r<this._sections.length;r++){var t=this._sections[r],o=t.consumer.sourceContentFor(e,!0);if(o)return o}if(n)return null;throw new Error('"'+e+'" is not in the SourceMap.')},a.prototype.generatedPositionFor=function(e){for(var n=0;n<this._sections.length;n++){var r=this._sections[n];if(r.consumer.sources.indexOf(s.getArg(e,"source"))!==-1){var t=r.consumer.generatedPositionFor(e);if(t){var o={line:t.line+(r.generatedOffset.generatedLine-1),column:t.column+(r.generatedOffset.generatedLine===t.line?r.generatedOffset.generatedColumn-1:0)};return o}}}return{line:null,column:null}},a.prototype._parseMappings=function(e,n){this.__generatedMappings=[],this.__originalMappings=[];for(var r=0;r<this._sections.length;r++)for(var t=this._sections[r],o=t.consumer._generatedMappings,i=0;i<o.length;i++){var a=o[i],u=t.consumer._sources.at(a.source);null!==t.consumer.sourceRoot&&(u=s.join(t.consumer.sourceRoot,u)),this._sources.add(u),u=this._sources.indexOf(u);var c=t.consumer._names.at(a.name);this._names.add(c),c=this._names.indexOf(c);var l={source:u,generatedLine:a.generatedLine+(t.generatedOffset.generatedLine-1),generatedColumn:a.generatedColumn+(t.generatedOffset.generatedLine===a.generatedLine?t.generatedOffset.generatedColumn-1:0),originalLine:a.originalLine,originalColumn:a.originalColumn,name:c};this.__generatedMappings.push(l),"number"==typeof l.originalLine&&this.__originalMappings.push(l)}f(this.__generatedMappings,s.compareByGeneratedPositionsDeflated),f(this.__originalMappings,s.compareByOriginalPositions)},r.IndexedSourceMapConsumer=a},{"./array-set":2,"./base64-vlq":3,"./binary-search":5,"./quick-sort":6,"./util":8}],8:[function(e,n,r){function t(e,n,r){if(n in e)return e[n];if(3===arguments.length)return r;throw new Error('"'+n+'" is a required argument.')}function o(e){var n=e.match(v);return n?{scheme:n[1],auth:n[2],host:n[3],port:n[4],path:n[5]}:null}function i(e){var n="";return e.scheme&&(n+=e.scheme+":"),n+="//",e.auth&&(n+=e.auth+"@"),e.host&&(n+=e.host),e.port&&(n+=":"+e.port),e.path&&(n+=e.path),n}function a(e){var n=e,t=o(e);if(t){if(!t.path)return e;n=t.path}for(var a,s=r.isAbsolute(n),u=n.split(/\/+/),c=0,l=u.length-1;l>=0;l--)a=u[l],"."===a?u.splice(l,1):".."===a?c++:c>0&&(""===a?(u.splice(l+1,c),c=0):(u.splice(l,2),c--));return n=u.join("/"),""===n&&(n=s?"/":"."),t?(t.path=n,i(t)):n}function s(e,n){""===e&&(e="."),""===n&&(n=".");var r=o(n),t=o(e);if(t&&(e=t.path||"/"),r&&!r.scheme)return t&&(r.scheme=t.scheme),i(r);if(r||n.match(_))return n;if(t&&!t.host&&!t.path)return t.host=n,i(t);var s="/"===n.charAt(0)?n:a(e.replace(/\/+$/,"")+"/"+n);return t?(t.path=s,i(t)):s}function u(e,n){""===e&&(e="."),e=e.replace(/\/$/,"");for(var r=0;0!==n.indexOf(e+"/");){var t=e.lastIndexOf("/");if(t<0)return n;if(e=e.slice(0,t),e.match(/^([^\/]+:\/)?\/*$/))return n;++r}return Array(r+1).join("../")+n.substr(e.length+1)}function c(e){return e}function l(e){return p(e)?"$"+e:e}function f(e){return p(e)?e.slice(1):e}function p(e){if(!e)return!1;var n=e.length;if(n<9)return!1;if(95!==e.charCodeAt(n-1)||95!==e.charCodeAt(n-2)||111!==e.charCodeAt(n-3)||116!==e.charCodeAt(n-4)||111!==e.charCodeAt(n-5)||114!==e.charCodeAt(n-6)||112!==e.charCodeAt(n-7)||95!==e.charCodeAt(n-8)||95!==e.charCodeAt(n-9))return!1;for(var r=n-10;r>=0;r--)if(36!==e.charCodeAt(r))return!1;return!0}function g(e,n,r){var t=e.source-n.source;return 0!==t?t:(t=e.originalLine-n.originalLine,0!==t?t:(t=e.originalColumn-n.originalColumn,0!==t||r?t:(t=e.generatedColumn-n.generatedColumn,0!==t?t:(t=e.generatedLine-n.generatedLine,0!==t?t:e.name-n.name))))}function h(e,n,r){var t=e.generatedLine-n.generatedLine;return 0!==t?t:(t=e.generatedColumn-n.generatedColumn,0!==t||r?t:(t=e.source-n.source,0!==t?t:(t=e.originalLine-n.originalLine,0!==t?t:(t=e.originalColumn-n.originalColumn,0!==t?t:e.name-n.name))))}function m(e,n){return e===n?0:e>n?1:-1}function d(e,n){var r=e.generatedLine-n.generatedLine;return 0!==r?r:(r=e.generatedColumn-n.generatedColumn,0!==r?r:(r=m(e.source,n.source),0!==r?r:(r=e.originalLine-n.originalLine,0!==r?r:(r=e.originalColumn-n.originalColumn,0!==r?r:m(e.name,n.name)))))}r.getArg=t;var v=/^(?:([\w+\-.]+):)?\/\/(?:(\w+:\w+)@)?([\w.]*)(?::(\d+))?(\S*)$/,_=/^data:.+\,.+$/;r.urlParse=o,r.urlGenerate=i,r.normalize=a,r.join=s,r.isAbsolute=function(e){return"/"===e.charAt(0)||!!e.match(v)},r.relative=u;var y=function(){var e=Object.create(null);return!("__proto__"in e)}();r.toSetString=y?c:l,r.fromSetString=y?c:f,r.compareByOriginalPositions=g,r.compareByGeneratedPositionsDeflated=h,r.compareByGeneratedPositionsInflated=d},{}],9:[function(n,r,t){!function(o,i){"use strict";"function"==typeof e&&e.amd?e("stack-generator",["stackframe"],i):"object"==typeof t?r.exports=i(n("stackframe")):o.StackGenerator=i(o.StackFrame)}(this,function(e){return{backtrace:function(n){var r=[],t=10;"object"==typeof n&&"number"==typeof n.maxStackSize&&(t=n.maxStackSize);for(var o=arguments.callee;o&&r.length<t;){for(var i=new Array(o.arguments.length),a=0;a<i.length;++a)i[a]=o.arguments[a];/function(?:\s+([\w$]+))+\s*\(/.test(o.toString())?r.push(new e({functionName:RegExp.$1||void 0,args:i})):r.push(new e({args:i}));try{o=o.caller}catch(s){break}}return r}}})},{stackframe:10}],10:[function(n,r,t){!function(n,o){"use strict";"function"==typeof e&&e.amd?e("stackframe",[],o):"object"==typeof t?r.exports=o():n.StackFrame=o()}(this,function(){"use strict";function e(e){return!isNaN(parseFloat(e))&&isFinite(e)}function n(e){return e[0].toUpperCase()+e.substring(1)}function r(e){return function(){return this[e]}}function t(e){if(e instanceof Object)for(var r=o.concat(i.concat(a.concat(s))),t=0;t<r.length;t++)e.hasOwnProperty(r[t])&&void 0!==e[r[t]]&&this["set"+n(r[t])](e[r[t]])}var o=["isConstructor","isEval","isNative","isToplevel"],i=["columnNumber","lineNumber"],a=["fileName","functionName","source"],s=["args"];t.prototype={getArgs:function(){return this.args},setArgs:function(e){if("[object Array]"!==Object.prototype.toString.call(e))throw new TypeError("Args must be an Array");this.args=e},getEvalOrigin:function(){return this.evalOrigin},setEvalOrigin:function(e){if(e instanceof t)this.evalOrigin=e;else{if(!(e instanceof Object))throw new TypeError("Eval Origin must be an Object or StackFrame");this.evalOrigin=new t(e)}},toString:function(){var n=this.getFunctionName()||"{anonymous}",r="("+(this.getArgs()||[]).join(",")+")",t=this.getFileName()?"@"+this.getFileName():"",o=e(this.getLineNumber())?":"+this.getLineNumber():"",i=e(this.getColumnNumber())?":"+this.getColumnNumber():"";return n+r+t+o+i}};for(var u=0;u<o.length;u++)t.prototype["get"+n(o[u])]=r(o[u]),t.prototype["set"+n(o[u])]=function(e){return function(n){this[e]=Boolean(n)}}(o[u]);for(var c=0;c<i.length;c++)t.prototype["get"+n(i[c])]=r(i[c]),t.prototype["set"+n(i[c])]=function(n){return function(r){if(!e(r))throw new TypeError(n+" must be a Number");this[n]=Number(r)}}(i[c]);for(var l=0;l<a.length;l++)t.prototype["get"+n(a[l])]=r(a[l]),t.prototype["set"+n(a[l])]=function(e){return function(n){this[e]=String(n)}}(a[l]);return t})},{}],11:[function(n,r,t){!function(o,i){"use strict";"function"==typeof e&&e.amd?e("stacktrace-gps",["source-map","stackframe"],i):"object"==typeof t?r.exports=i(n("source-map/lib/source-map-consumer"),n("stackframe")):o.StackTraceGPS=i(o.SourceMap||o.sourceMap,o.StackFrame)}(this,function(e,n){"use strict";function r(e){return new Promise(function(n,r){var t=new XMLHttpRequest;t.open("get",e),t.onerror=r,t.onreadystatechange=function(){4===t.readyState&&(t.status>=200&&t.status<300||"file://"===e.substr(0,7)&&t.responseText?n(t.responseText):r(new Error("HTTP status: "+t.status+" retrieving "+e)))},t.send()})}function t(e){if("undefined"!=typeof window&&window.atob)return window.atob(e);throw new Error("You must supply a polyfill for window.atob in this environment")}function o(e){if("undefined"!=typeof JSON&&JSON.parse)return JSON.parse(e);throw new Error("You must supply a polyfill for JSON.parse in this environment")}function i(e,n){for(var r=[/['"]?([$_A-Za-z][$_A-Za-z0-9]*)['"]?\s*[:=]\s*function\b/,/function\s+([^('"`]*?)\s*\(([^)]*)\)/,/['"]?([$_A-Za-z][$_A-Za-z0-9]*)['"]?\s*[:=]\s*(?:eval|new Function)\b/,/\b(?!(?:if|for|switch|while|with|catch)\b)(?:(?:static)\s+)?(\S+)\s*\(.*?\)\s*\{/,/['"]?([$_A-Za-z][$_A-Za-z0-9]*)['"]?\s*[:=]\s*\(.*?\)\s*=>/],t=e.split("\n"),o="",i=Math.min(n,20),a=0;a<i;++a){var s=t[n-a-1],u=s.indexOf("//");if(u>=0&&(s=s.substr(0,u)),s){o=s+o;for(var c=r.length,l=0;l<c;l++){var f=r[l].exec(o);if(f&&f[1])return f[1]}}}}function a(){if("function"!=typeof Object.defineProperty||"function"!=typeof Object.create)throw new Error("Unable to consume source maps in older browsers")}function s(e){if("object"!=typeof e)throw new TypeError("Given StackFrame is not an object");if("string"!=typeof e.fileName)throw new TypeError("Given file name is not a String");if("number"!=typeof e.lineNumber||e.lineNumber%1!==0||e.lineNumber<1)throw new TypeError("Given line number must be a positive integer");if("number"!=typeof e.columnNumber||e.columnNumber%1!==0||e.columnNumber<0)throw new TypeError("Given column number must be a non-negative integer");return!0}function u(e){var n=/\/\/[#@] ?sourceMappingURL=([^\s'"]+)\s*$/m.exec(e);if(n&&n[1])return n[1];throw new Error("sourceMappingURL not found")}function c(e,r,t){return new Promise(function(o,i){var a=r.originalPositionFor({line:e.lineNumber,column:e.columnNumber});if(a.source){var s=r.sourceContentFor(a.source);s&&(t[a.source]=s),o(new n({functionName:a.name||e.functionName,args:e.args,fileName:a.source,lineNumber:a.line,columnNumber:a.column}))}else i(new Error("Could not get original source for given stackframe and source map"))})}return function l(f){return this instanceof l?(f=f||{},this.sourceCache=f.sourceCache||{},this.sourceMapConsumerCache=f.sourceMapConsumerCache||{},this.ajax=f.ajax||r,this._atob=f.atob||t,this._get=function(e){return new Promise(function(n,r){var t="data:"===e.substr(0,5);if(this.sourceCache[e])n(this.sourceCache[e]);else if(f.offline&&!t)r(new Error("Cannot make network requests in offline mode"));else if(t){var o=/^data:application\/json;([\w=:"-]+;)*base64,/,i=e.match(o);if(i){var a=i[0].length,s=e.substr(a),u=this._atob(s);this.sourceCache[e]=u,n(u)}else r(new Error("The encoding of the inline sourcemap is not supported"))}else{var c=this.ajax(e,{method:"get"});this.sourceCache[e]=c,c.then(n,r)}}.bind(this))},this._getSourceMapConsumer=function(n,r){return new Promise(function(t,i){if(this.sourceMapConsumerCache[n])t(this.sourceMapConsumerCache[n]);else{var a=new Promise(function(t,i){return this._get(n).then(function(n){"string"==typeof n&&(n=o(n.replace(/^\)\]\}'/,""))),"undefined"==typeof n.sourceRoot&&(n.sourceRoot=r),t(new e.SourceMapConsumer(n))},i)}.bind(this));this.sourceMapConsumerCache[n]=a,t(a)}}.bind(this))},this.pinpoint=function(e){return new Promise(function(n,r){this.getMappedLocation(e).then(function(e){function r(){n(e)}this.findFunctionName(e).then(n,r)["catch"](r)}.bind(this),r)}.bind(this))},this.findFunctionName=function(e){return new Promise(function(r,t){s(e),this._get(e.fileName).then(function(t){var o=e.lineNumber,a=e.columnNumber,s=i(t,o,a);r(s?new n({functionName:s,args:e.args,fileName:e.fileName,lineNumber:o,columnNumber:a}):e)},t)["catch"](t)}.bind(this))},void(this.getMappedLocation=function(e){return new Promise(function(n,r){a(),s(e);var t=this.sourceCache,o=e.fileName;this._get(o).then(function(r){var i=u(r),a="data:"===i.substr(0,5),s=o.substring(0,o.lastIndexOf("/")+1);return"/"===i[0]||a||/^https?:\/\/|^\/\//i.test(i)||(i=s+i),this._getSourceMapConsumer(i,s).then(function(r){return c(e,r,t).then(n)["catch"](function(){n(e)})})}.bind(this),r)["catch"](r)}.bind(this))})):new l(f)}})},{"source-map/lib/source-map-consumer":7,stackframe:10}],12:[function(n,r,t){!function(o,i){"use strict";"function"==typeof e&&e.amd?e("stacktrace",["error-stack-parser","stack-generator","stacktrace-gps"],i):"object"==typeof t?r.exports=i(n("error-stack-parser"),n("stack-generator"),n("stacktrace-gps")):o.StackTrace=i(o.ErrorStackParser,o.StackGenerator,o.StackTraceGPS)}(this,function(e,n,r){function t(e,n){var r={};return[e,n].forEach(function(e){for(var n in e)e.hasOwnProperty(n)&&(r[n]=e[n]);return r}),r}function o(e){return e.stack||e["opera#sourceloc"]}function i(e,n){return"function"==typeof n?e.filter(n):e}var a={filter:function(e){return(e.functionName||"").indexOf("StackTrace$$")===-1&&(e.functionName||"").indexOf("ErrorStackParser$$")===-1&&(e.functionName||"").indexOf("StackTraceGPS$$")===-1&&(e.functionName||"").indexOf("StackGenerator$$")===-1},sourceCache:{}},s=function(){try{throw new Error}catch(e){return e}};return{get:function(e){var n=s();return o(n)?this.fromError(n,e):this.generateArtificially(e)},getSync:function(r){r=t(a,r);var u=s(),c=o(u)?e.parse(u):n.backtrace(r);return i(c,r.filter)},fromError:function(n,o){o=t(a,o);var s=new r(o);return new Promise(function(r){var t=i(e.parse(n),o.filter);r(Promise.all(t.map(function(e){return new Promise(function(n){function r(){n(e)}s.pinpoint(e).then(n,r)["catch"](r)})})))}.bind(this))},generateArtificially:function(e){e=t(a,e);var r=n.backtrace(e);return"function"==typeof e.filter&&(r=r.filter(e.filter)),Promise.resolve(r)},instrument:function(e,n,r,t){if("function"!=typeof e)throw new Error("Cannot instrument non-function object");if("function"==typeof e.__stacktraceOriginalFn)return e;var i=function(){try{return this.get().then(n,r)["catch"](r),e.apply(t||this,arguments)}catch(i){throw o(i)&&this.fromError(i).then(n,r)["catch"](r),i}}.bind(this);return i.__stacktraceOriginalFn=e,i},deinstrument:function(e){if("function"!=typeof e)throw new Error("Cannot de-instrument non-function object");return"function"==typeof e.__stacktraceOriginalFn?e.__stacktraceOriginalFn:e},report:function(e,n,r,t){return new Promise(function(o,i){var a=new XMLHttpRequest;if(a.onerror=i,a.onreadystatechange=function(){4===a.readyState&&(a.status>=200&&a.status<400?o(a.responseText):i(new Error("POST to "+n+" failed with status: "+a.status)))},a.open("post",n),a.setRequestHeader("Content-Type","application/json"),t&&"object"==typeof t.headers){var s=t.headers;for(var u in s)s.hasOwnProperty(u)&&a.setRequestHeader(u,s[u])}var c={stack:e};void 0!==r&&null!==r&&(c.message=r),a.send(JSON.stringify(c))})}}})},{"error-stack-parser":1,"stack-generator":9,"stacktrace-gps":11}]},{},[12])(12)});

(function(t,e){"object"==typeof exports&&"undefined"!=typeof module?e(exports):"function"==typeof define&&define.amd?define(["exports"],e):e(t.RSVP={})})(this,function(t){"use strict";function e(t){var e=t._promiseCallbacks;e||(e=t._promiseCallbacks={});return e}function r(t,e){if(2!==arguments.length)return dt[t];dt[t]=e}function n(){setTimeout(function(){for(var t=0;t<vt.length;t++){var e=vt[t],r=e.payload;r.guid=r.key+r.id;r.childGuid=r.key+r.childId;r.error&&(r.stack=r.error.stack);dt.trigger(e.name,e.payload)}vt.length=0},50)}function o(t,e,r){1===vt.push({name:t,payload:{key:e._guidKey,id:e._id,eventName:t,detail:e._result,childId:r&&r._id,label:e._label,timeStamp:Date.now(),error:dt["instrument-with-stack"]?new Error(e._label):null}})&&n()}function i(t,e){var r=this;if(t&&"object"==typeof t&&t.constructor===r)return t;var n=new r(c,e);_(n,t);return n}function s(){return new TypeError("A promises callback cannot return that same promise.")}function u(t){var e=typeof t;return null!==t&&("object"===e||"function"===e)}function c(){}function a(t){try{return t.then}catch(e){gt.error=e;return gt}}function f(){try{var t=jt;jt=null;return t.apply(this,arguments)}catch(e){gt.error=e;return gt}}function l(t){jt=t;return f}function h(t,e,r){dt.async(function(t){var n=!1,o=l(r).call(e,function(r){if(!n){n=!0;e===r?v(t,r):_(t,r)}},function(e){if(!n){n=!0;m(t,e)}},"Settle: "+(t._label||" unknown promise"));if(!n&&o===gt){n=!0;var i=gt.error;gt.error=null;m(t,i)}},t)}function p(t,e){if(e._state===bt)v(t,e._result);else if(e._state===wt){e._onError=null;m(t,e._result)}else b(e,void 0,function(r){e===r?v(t,r):_(t,r)},function(e){return m(t,e)})}function y(t,e,r){var n=e.constructor===t.constructor&&r===O&&t.constructor.resolve===i;if(n)p(t,e);else if(r===gt){var o=gt.error;gt.error=null;m(t,o)}else"function"==typeof r?h(t,e,r):v(t,e)}function _(t,e){t===e?v(t,e):u(e)?y(t,e,a(e)):v(t,e)}function d(t){t._onError&&t._onError(t._result);w(t)}function v(t,e){if(t._state===mt){t._result=e;t._state=bt;0===t._subscribers.length?dt.instrument&&o("fulfilled",t):dt.async(w,t)}}function m(t,e){if(t._state===mt){t._state=wt;t._result=e;dt.async(d,t)}}function b(t,e,r,n){var o=t._subscribers,i=o.length;t._onError=null;o[i]=e;o[i+bt]=r;o[i+wt]=n;0===i&&t._state&&dt.async(w,t)}function w(t){var e=t._subscribers,r=t._state;dt.instrument&&o(r===bt?"fulfilled":"rejected",t);if(0!==e.length){for(var n=void 0,i=void 0,s=t._result,u=0;u<e.length;u+=3){n=e[u];i=e[u+r];n?g(r,n,i,s):i(s)}t._subscribers.length=0}}function g(t,e,r,n){var o="function"==typeof r,i=void 0;i=o?l(r)(n):n;if(e._state!==mt);else if(i===e)m(e,s());else if(i===gt){var u=gt.error;gt.error=null;m(e,u)}else o?_(e,i):t===bt?v(e,i):t===wt&&m(e,i)}function j(t,e){var r=!1;try{e(function(e){if(!r){r=!0;_(t,e)}},function(e){if(!r){r=!0;m(t,e)}})}catch(n){m(t,n)}}function O(t,e,r){var n=this,i=n._state;if(i===bt&&!t||i===wt&&!e){dt.instrument&&o("chained",n,n);return n}n._onError=null;var s=new n.constructor(c,r),u=n._result;dt.instrument&&o("chained",n,s);if(i===mt)b(n,s,t,e);else{var a=i===bt?t:e;dt.async(function(){return g(i,s,a,u)})}return s}function A(t,e,r){this._remaining--;t===bt?this._result[e]={state:"fulfilled",value:r}:this._result[e]={state:"rejected",reason:r}}function E(t,e){return Array.isArray(t)?new Ot(this,t,(!0),e).promise:this.reject(new TypeError("Promise.all must be called with an array"),e)}function T(t,e){var r=this,n=new r(c,e);if(!Array.isArray(t)){m(n,new TypeError("Promise.race must be called with an array"));return n}for(var o=0;n._state===mt&&o<t.length;o++)b(r.resolve(t[o]),void 0,function(t){return _(n,t)},function(t){return m(n,t)});return n}function P(t,e){var r=this,n=new r(c,e);m(n,t);return n}function S(){throw new TypeError("You must pass a resolver function as the first argument to the promise constructor")}function R(){throw new TypeError("Failed to construct 'Promise': Please use the 'new' operator, this object constructor cannot be called as a function.")}function x(t,e){for(var r={},n=t.length,o=new Array(n),i=0;i<n;i++)o[i]=t[i];for(var s=0;s<e.length;s++){var u=e[s];r[u]=o[s+1]}return r}function k(t){for(var e=t.length,r=new Array(e-1),n=1;n<e;n++)r[n-1]=t[n];return r}function M(t,e){return{then:function(r,n){return t.call(e,r,n)}}}function C(t,e){var r=function(){for(var r=arguments.length,n=new Array(r+1),o=!1,i=0;i<r;++i){var s=arguments[i];if(!o){o=N(s);if(o===gt){var u=gt.error;gt.error=null;var a=new Tt(c);m(a,u);return a}o&&o!==!0&&(s=M(o,s))}n[i]=s}var f=new Tt(c);n[r]=function(t,r){t?m(f,t):void 0===e?_(f,r):e===!0?_(f,k(arguments)):Array.isArray(e)?_(f,x(arguments,e)):_(f,r)};return o?I(f,n,t,this):F(f,n,t,this)};r.__proto__=t;return r}function F(t,e,r,n){var o=l(r).apply(n,e);if(o===gt){var i=gt.error;gt.error=null;m(t,i)}return t}function I(t,e,r,n){return Tt.all(e).then(function(e){return F(t,e,r,n)})}function N(t){return null!==t&&"object"==typeof t&&(t.constructor===Tt||a(t))}function U(t,e){return Tt.all(t,e)}function V(t,e){if(!t)throw new ReferenceError("this hasn't been initialised - super() hasn't been called");return!e||"object"!=typeof e&&"function"!=typeof e?t:e}function D(t,e){if("function"!=typeof e&&null!==e)throw new TypeError("Super expression must either be null or a function, not "+typeof e);t.prototype=Object.create(e&&e.prototype,{constructor:{value:t,enumerable:!1,writable:!0,configurable:!0}});e&&(Object.setPrototypeOf?Object.setPrototypeOf(t,e):t.__proto__=e)}function K(t,e){return Array.isArray(t)?new Pt(Tt,t,e).promise:Tt.reject(new TypeError("Promise.allSettled must be called with an array"),e)}function q(t,e){return Tt.race(t,e)}function G(t,e){if(!t)throw new ReferenceError("this hasn't been initialised - super() hasn't been called");return!e||"object"!=typeof e&&"function"!=typeof e?t:e}function L(t,e){if("function"!=typeof e&&null!==e)throw new TypeError("Super expression must either be null or a function, not "+typeof e);t.prototype=Object.create(e&&e.prototype,{constructor:{value:t,enumerable:!1,writable:!0,configurable:!0}});e&&(Object.setPrototypeOf?Object.setPrototypeOf(t,e):t.__proto__=e)}function W(t,e){return null===t||"object"!=typeof t?Tt.reject(new TypeError("Promise.hash must be called with an object"),e):new Rt(Tt,t,e).promise}function Y(t,e){if(!t)throw new ReferenceError("this hasn't been initialised - super() hasn't been called");return!e||"object"!=typeof e&&"function"!=typeof e?t:e}function $(t,e){if("function"!=typeof e&&null!==e)throw new TypeError("Super expression must either be null or a function, not "+typeof e);t.prototype=Object.create(e&&e.prototype,{constructor:{value:t,enumerable:!1,writable:!0,configurable:!0}});e&&(Object.setPrototypeOf?Object.setPrototypeOf(t,e):t.__proto__=e)}function z(t,e){return null===t||"object"!=typeof t?Tt.reject(new TypeError("RSVP.hashSettled must be called with an object"),e):new xt(Tt,t,(!1),e).promise}function B(t){setTimeout(function(){throw t});throw t}function H(t){var e={resolve:void 0,reject:void 0};e.promise=new Tt(function(t,r){e.resolve=t;e.reject=r},t);return e}function J(t,e){if(!t)throw new ReferenceError("this hasn't been initialised - super() hasn't been called");return!e||"object"!=typeof e&&"function"!=typeof e?t:e}function Q(t,e){if("function"!=typeof e&&null!==e)throw new TypeError("Super expression must either be null or a function, not "+typeof e);t.prototype=Object.create(e&&e.prototype,{constructor:{value:t,enumerable:!1,writable:!0,configurable:!0}});e&&(Object.setPrototypeOf?Object.setPrototypeOf(t,e):t.__proto__=e)}function X(t,e,r){return Array.isArray(t)?"function"!=typeof e?Tt.reject(new TypeError("RSVP.map expects a function as a second argument"),r):new kt(Tt,t,e,r).promise:Tt.reject(new TypeError("RSVP.map must be called with an array"),r)}function Z(t,e){return Tt.resolve(t,e)}function tt(t,e){return Tt.reject(t,e)}function et(t,e){if(!t)throw new ReferenceError("this hasn't been initialised - super() hasn't been called");return!e||"object"!=typeof e&&"function"!=typeof e?t:e}function rt(t,e){if("function"!=typeof e&&null!==e)throw new TypeError("Super expression must either be null or a function, not "+typeof e);t.prototype=Object.create(e&&e.prototype,{constructor:{value:t,enumerable:!1,writable:!0,configurable:!0}});e&&(Object.setPrototypeOf?Object.setPrototypeOf(t,e):t.__proto__=e)}function nt(t,e,r){return"function"!=typeof e?Tt.reject(new TypeError("RSVP.filter expects function as a second argument"),r):Tt.resolve(t,r).then(function(t){if(!Array.isArray(t))throw new TypeError("RSVP.filter must be called with an array");return new Ct(Tt,t,e,r).promise})}function ot(t,e){qt[Ft]=t;qt[Ft+1]=e;Ft+=2;2===Ft&&Gt()}function it(){var t=process.nextTick,e=process.versions.node.match(/^(?:(\d+)\.)?(?:(\d+)\.)?(\*|\d+)$/);Array.isArray(e)&&"0"===e[1]&&"10"===e[2]&&(t=setImmediate);return function(){return t(ft)}}function st(){return"undefined"!=typeof It?function(){It(ft)}:at()}function ut(){var t=0,e=new Vt(ft),r=document.createTextNode("");e.observe(r,{characterData:!0});return function(){return r.data=t=++t%2}}function ct(){var t=new MessageChannel;t.port1.onmessage=ft;return function(){return t.port2.postMessage(0)}}function at(){return function(){return setTimeout(ft,1)}}function ft(){for(var t=0;t<Ft;t+=2){var e=qt[t],r=qt[t+1];e(r);qt[t]=void 0;qt[t+1]=void 0}Ft=0}function lt(){try{var t=require,e=t("vertx");It=e.runOnLoop||e.runOnContext;return st()}catch(r){return at()}}function ht(t,e,r){e in t?Object.defineProperty(t,e,{value:r,enumerable:!0,configurable:!0,writable:!0}):t[e]=r;return t}function pt(){dt.on.apply(dt,arguments)}function yt(){dt.off.apply(dt,arguments)}var _t={mixin:function(t){t.on=this.on;t.off=this.off;t.trigger=this.trigger;t._promiseCallbacks=void 0;return t},on:function(t,r){if("function"!=typeof r)throw new TypeError("Callback must be a function");var n=e(this),o=void 0;o=n[t];o||(o=n[t]=[]);o.indexOf(r)&&o.push(r)},off:function(t,r){var n=e(this),o=void 0,i=void 0;if(r){o=n[t];i=o.indexOf(r);i!==-1&&o.splice(i,1)}else n[t]=[]},trigger:function(t,r,n){var o=e(this),i=void 0,s=void 0;if(i=o[t])for(var u=0;u<i.length;u++){s=i[u];s(r,n)}}},dt={instrument:!1};_t.mixin(dt);var vt=[],mt=void 0,bt=1,wt=2,gt={error:null},jt=void 0,Ot=function(){function t(t,e,r,n){this._instanceConstructor=t;this.promise=new t(c,n);this._abortOnReject=r;this._isUsingOwnPromise=t===Tt;this._isUsingOwnResolve=t.resolve===i;this._init.apply(this,arguments)}t.prototype._init=function(t,e){var r=e.length||0;this.length=r;this._remaining=r;this._result=new Array(r);this._enumerate(e)};t.prototype._enumerate=function(t){for(var e=this.length,r=this.promise,n=0;r._state===mt&&n<e;n++)this._eachEntry(t[n],n,!0);this._checkFullfillment()};t.prototype._checkFullfillment=function(){0===this._remaining&&v(this.promise,this._result)};t.prototype._settleMaybeThenable=function(t,e,r){var n=this._instanceConstructor;if(this._isUsingOwnResolve){var o=a(t);if(o===O&&t._state!==mt){t._onError=null;this._settledAt(t._state,e,t._result,r)}else if("function"!=typeof o)this._settledAt(bt,e,t,r);else if(this._isUsingOwnPromise){var i=new n(c);y(i,t,o);this._willSettleAt(i,e,r)}else this._willSettleAt(new n(function(e){return e(t)}),e,r)}else this._willSettleAt(n.resolve(t),e,r)};t.prototype._eachEntry=function(t,e,r){null!==t&&"object"==typeof t?this._settleMaybeThenable(t,e,r):this._setResultAt(bt,e,t,r)};t.prototype._settledAt=function(t,e,r,n){var o=this.promise;if(o._state===mt)if(this._abortOnReject&&t===wt)m(o,r);else{this._setResultAt(t,e,r,n);this._checkFullfillment()}};t.prototype._setResultAt=function(t,e,r,n){this._remaining--;this._result[e]=r};t.prototype._willSettleAt=function(t,e,r){var n=this;b(t,void 0,function(t){return n._settledAt(bt,e,t,r)},function(t){return n._settledAt(wt,e,t,r)})};return t}(),At="rsvp_"+Date.now()+"-",Et=0,Tt=function(){function t(e,r){this._id=Et++;this._label=r;this._state=void 0;this._result=void 0;this._subscribers=[];dt.instrument&&o("created",this);if(c!==e){"function"!=typeof e&&S();this instanceof t?j(this,e):R()}}t.prototype._onError=function(t){var e=this;dt.after(function(){e._onError&&dt.trigger("error",t,e._label)})};t.prototype["catch"]=function(t,e){return this.then(void 0,t,e)};t.prototype["finally"]=function(t,e){var r=this,n=r.constructor;return r.then(function(e){return n.resolve(t()).then(function(){return e})},function(e){return n.resolve(t()).then(function(){throw e})},e)};return t}();Tt.all=E;Tt.race=T;Tt.resolve=i;Tt.reject=P;Tt.prototype._guidKey=At;Tt.prototype.then=O;var Pt=function(t){function e(e,r,n){return V(this,t.call(this,e,r,!1,n))}D(e,t);return e}(Ot);Pt.prototype._setResultAt=A;var St=Object.prototype.hasOwnProperty,Rt=function(t){function e(e,r){var n=!(arguments.length>2&&void 0!==arguments[2])||arguments[2],o=arguments[3];return G(this,t.call(this,e,r,n,o))}L(e,t);e.prototype._init=function(t,e){this._result={};this._enumerate(e);0===this._remaining&&v(this.promise,this._result)};e.prototype._enumerate=function(t){var e=this.promise,r=[];for(var n in t)St.call(t,n)&&r.push({position:n,entry:t[n]});var o=r.length;this._remaining=o;for(var i=void 0,s=0;e._state===mt&&s<o;s++){i=r[s];this._eachEntry(i.entry,i.position)}};return e}(Ot),xt=function(t){function e(e,r,n){return Y(this,t.call(this,e,r,!1,n))}$(e,t);return e}(Rt);xt.prototype._setResultAt=A;var kt=function(t){function e(e,r,n,o){return J(this,t.call(this,e,r,!0,o,n))}Q(e,t);e.prototype._init=function(t,e,r,n,o){var i=e.length||0;this.length=i;this._remaining=i;this._result=new Array(i);this._mapFn=o;this._enumerate(e)};e.prototype._setResultAt=function(t,e,r,n){if(n){var o=l(this._mapFn)(r,e);o===gt?this._settledAt(wt,e,o.error,!1):this._eachEntry(o,e,!1)}else{this._remaining--;this._result[e]=r}};return e}(Ot),Mt={},Ct=function(t){function e(e,r,n,o){return et(this,t.call(this,e,r,!0,o,n))}rt(e,t);e.prototype._init=function(t,e,r,n,o){var i=e.length||0;this.length=i;this._remaining=i;this._result=new Array(i);this._filterFn=o;this._enumerate(e)};e.prototype._checkFullfillment=function(){if(0===this._remaining){this._result=this._result.filter(function(t){return t!==Mt});v(this.promise,this._result)}};e.prototype._setResultAt=function(t,e,r,n){if(n){this._result[e]=r;var o=l(this._filterFn)(r,e);o===gt?this._settledAt(wt,e,o.error,!1):this._eachEntry(o,e,!1)}else{this._remaining--;r||(this._result[e]=Mt)}};return e}(Ot),Ft=0,It=void 0,Nt="undefined"!=typeof window?window:void 0,Ut=Nt||{},Vt=Ut.MutationObserver||Ut.WebKitMutationObserver,Dt="undefined"==typeof self&&"undefined"!=typeof process&&"[object process]"==={}.toString.call(process),Kt="undefined"!=typeof Uint8ClampedArray&&"undefined"!=typeof importScripts&&"undefined"!=typeof MessageChannel,qt=new Array(1e3),Gt=void 0;Gt=Dt?it():Vt?ut():Kt?ct():void 0===Nt&&"function"==typeof require?lt():at();var Lt;dt.async=ot;dt.after=function(t){return setTimeout(t,0)};var Wt=function(t,e){return dt.async(t,e)};if("undefined"!=typeof window&&"object"==typeof window.__PROMISE_INSTRUMENTATION__){var Yt=window.__PROMISE_INSTRUMENTATION__;r("instrument",!0);for(var $t in Yt)Yt.hasOwnProperty($t)&&pt($t,Yt[$t])}var zt=(Lt={asap:ot,Promise:Tt,EventTarget:_t,all:U,allSettled:K,race:q,hash:W,hashSettled:z,rethrow:B,defer:H,denodeify:C,configure:r,on:pt,off:yt,resolve:Z,reject:tt,map:X},ht(Lt,"async",Wt),ht(Lt,"filter",nt),Lt);t["default"]=zt;t.asap=ot;t.Promise=Tt;t.EventTarget=_t;t.all=U;t.allSettled=K;t.race=q;t.hash=W;t.hashSettled=z;t.rethrow=B;t.defer=H;t.denodeify=C;t.configure=r;t.on=pt;t.off=yt;t.resolve=Z;t.reject=tt;t.map=X;t.async=Wt;t.filter=nt;Object.defineProperty(t,"__esModule",{value:!0})});
