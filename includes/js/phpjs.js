/* Phpjs.Js */

/* array() */
;function array(){try{this.php_js=this.php_js||{}}catch(e){this.php_js={}}var arrInst,e,__,that=this,PHPJS_Array=function(){};return mainArgs=arguments,p=this.php_js,_indexOf=function(e,t,n){for(var r=t||0,i=!n,s=this.length;r<s;){if(this[r]===e||i&&this[r]==e)return r;r++}return-1},p.Relator||(p.Relator=function(){function e(e){for(var t=0,n=this.length;t<n;){if(this[t]===e)return t;t++}return-1}function t(){var n=[],r=[];return n.indexOf||(n.indexOf=e),{$:function(){return t()},constructor:function(e){var t=n.indexOf(e);return~t?r[t]:r[n.push(e)-1]={},this.method(e).that=e,this.method(e)},method:function(e){return r[n.indexOf(e)]}}}return t()}()),p&&p.ini&&"on"===p.ini["phpjs.return_phpjs_arrays"].local_value.toLowerCase()?(p.PHPJS_Array||(__=p.ArrayRelator=p.ArrayRelator||p.Relator.$(),p.PHPJS_Array=function(){var e,t,n=__.constructor(this),r=arguments,i=0;for(r=1===r.length&&r[0]&&"object"==typeof r[0]&&r[0].length&&!r[0].propertyIsEnumerable("length")?r[0]:r,n.objectChain||(n.objectChain=r,n.object={},n.keys=[],n.values=[]),e=r.length;i<e;i++)for(t in r[i]){this[t]=n.object[t]=r[i][t],n.keys[n.keys.length]=t,n.values[n.values.length]=r[i][t];break}},e=p.PHPJS_Array.prototype,e.change_key_case=function(e){for(var t,n,r=__.method(this),i=0,s=r.keys.length,a=e&&"CASE_LOWER"!==e?"toUpperCase":"toLowerCase";i<s;)(t=r.keys[i])!==(n=r.keys[i]=r.keys[i][a]())&&(this[t]=r.object[t]=r.objectChain[i][t]=null,delete this[t],delete r.object[t],delete r.objectChain[i][t],this[n]=r.object[n]=r.objectChain[i][n]=r.values[i]),i++;return this},e.flip=function(){for(var e=__.method(this),t=0,n=e.keys.length;t<n;)oldkey=e.keys[t],newkey=e.values[t],oldkey!==newkey&&(this[oldkey]=e.object[oldkey]=e.objectChain[t][oldkey]=null,delete this[oldkey],delete e.object[oldkey],delete e.objectChain[t][oldkey],this[newkey]=e.object[newkey]=e.objectChain[t][newkey]=oldkey,e.keys[t]=newkey),t++;return this},e.walk=function(funcname,userdata){var _=__.method(this),obj,func,ini,i=0,kl=0;try{if("function"==typeof funcname)for(i=0,kl=_.keys.length;i<kl;i++)arguments.length>1?funcname(_.values[i],_.keys[i],userdata):funcname(_.values[i],_.keys[i]);else if("string"==typeof funcname)if(this.php_js=this.php_js||{},this.php_js.ini=this.php_js.ini||{},!(ini=this.php_js.ini["phpjs.no-eval"])||0===parseInt(ini.local_value,10)||ini.local_value.toLowerCase&&"off"===ini.local_value.toLowerCase())if(arguments.length>1)for(i=0,kl=_.keys.length;i<kl;i++)eval(funcname+"(_.values[i], _.keys[i], userdata)");else for(i=0,kl=_.keys.length;i<kl;i++)eval(funcname+"(_.values[i], _.keys[i])");else if(arguments.length>1)for(i=0,kl=_.keys.length;i<kl;i++)this.window[funcname](_.values[i],_.keys[i],userdata);else for(i=0,kl=_.keys.length;i<kl;i++)this.window[funcname](_.values[i],_.keys[i]);else{if(!funcname||"object"!=typeof funcname||2!==funcname.length)return!1;if(obj=funcname[0],func=funcname[1],arguments.length>1)for(i=0,kl=_.keys.length;i<kl;i++)obj[func](_.values[i],_.keys[i],userdata);else for(i=0,kl=_.keys.length;i<kl;i++)obj[func](_.values[i],_.keys[i])}}catch(e){return!1}return this},e.keys=function(e,t){var n,r=__.method(this),i=[],s=!!t;if(!(void 0!==e))return r.keys;for(;-1!==(n=_indexOf(r.values,n,s));)i[i.length]=r.keys[n];return i},e.values=function(){return __.method(this).values},e.search=function(e,t){var n,r,i,s,a=__.method(this),o=!!t,l=a.values;if("object"==typeof e&&e.exec){for(o||(s="i"+(e.global?"g":"")+(e.multiline?"m":"")+(e.sticky?"y":""),e=new RegExp(e.source,s)),n=0,r=l.length;n<r;n++)if(i=l[n],e.test(i))return a.keys[n];return!1}for(n=0,r=l.length;n<r;n++)if(i=l[n],o&&i===e||!o&&i==e)return a.keys[n];return!1},e.sum=function(){for(var e=__.method(this),t=0,n=0,r=e.keys.length;n<r;)isNaN(parseFloat(e.values[n]))||(t+=parseFloat(e.values[n])),n++;return t},e.foreach=function(e){for(var t=__.method(this),n=0,r=t.keys.length;n<r;)1===e.length?e(t.values[n]):e(t.keys[n],t.values[n]),n++;return this},e.list=function(){for(var e,t=__.method(this),n=0,r=arguments.length;n<r;)(e=t.keys[n])&&e.length===parseInt(e,10).toString().length&&parseInt(e,10)<r&&(that.window[arguments[e]]=t.values[e]),n++;return this},e.forEach=function(e){for(var t=__.method(this),n=0,r=t.keys.length;n<r;)e(t.values[n],t.keys[n],this),n++;return this},e.$object=function(){return __.method(this).object},e.$objectChain=function(){return __.method(this).objectChain}),PHPJS_Array.prototype=p.PHPJS_Array.prototype,arrInst=new PHPJS_Array,p.PHPJS_Array.apply(arrInst,mainArgs),arrInst):Array.prototype.slice.call(mainArgs)}

/* array_merge() */
;function array_merge(){var r,e=Array.prototype.slice.call(arguments),t=e.length,o={},a="",n=0,c=0,l=0,f=0,i=Object.prototype.toString,y=!0;for(l=0;l<t;l++)if("[object Array]"!==i.call(e[l])){y=!1;break}if(y){for(y=[],l=0;l<t;l++)y=y.concat(e[l]);return y}for(l=0,f=0;l<t;l++)if(r=e[l],"[object Array]"===i.call(r))for(c=0,n=r.length;c<n;c++)o[f++]=r[c];else for(a in r)r.hasOwnProperty(a)&&(parseInt(a,10)+""===a?o[f++]=r[a]:o[a]=r[a]);return o}

/* array_merge_recursive() */
;function array_merge_recursive(e,t){var r="";if(e&&"[object Array]"===Object.prototype.toString.call(e)&&t&&"[object Array]"===Object.prototype.toString.call(t))for(r in t)e.push(t[r]);else if(e&&e instanceof Object&&t&&t instanceof Object)for(r in t)r in e&&"object"==typeof e[r]&&"object"==typeof t?e[r]=this.array_merge(e[r],t[r]):e[r]=t[r];return e}

/* array_key_exists() */
;function array_key_exists(r,t){return!(!t||t.constructor!==Array&&t.constructor!==Object)&&r in t}

/* count() */
;function count(r,t){var n,o=0;if(null===r||void 0===r)return 0;if(r.constructor!==Array&&r.constructor!==Object)return 1;"COUNT_RECURSIVE"===t&&(t=1),1!=t&&(t=0);for(n in r)r.hasOwnProperty(n)&&(o++,1!=t||!r[n]||r[n].constructor!==Array&&r[n].constructor!==Object||(o+=this.count(r[n],1)));return o}

/* each() */
;function each(r){this.php_js=this.php_js||{},this.php_js.pointers=this.php_js.pointers||[];var t=this.php_js.pointers;t.indexOf||(t.indexOf=function(r){for(var t=0,e=this.length;t<e;t++)if(this[t]===r)return t;return-1}),-1===t.indexOf(r)&&t.push(r,0);var e=t.indexOf(r),n=t[e+1],i=0;if("[object Array]"!==Object.prototype.toString.call(r)){var h=0;for(var p in r){if(h===n)return t[e+1]+=1,each.returnArrayOnly?[p,r[p]]:{1:r[p],value:r[p],0:p,key:p};h++}return!1}return 0!==r.length&&n!==r.length&&(i=n,t[e+1]+=1,each.returnArrayOnly?[i,r[i]]:{1:r[i],value:r[i],0:i,key:i})}

/* array_column() */
;function array_column(r,n,l){var a={},o=r.length;l=l||null;for(var t=0;t<o;t++)"object"!==(r[t],!1)&&(null===l&&r[t].hasOwnProperty(n)?a[t]=r[t][n]:r[t].hasOwnProperty(l)&&(null===n?a[r[t][l]]=r[t]:r[t].hasOwnProperty(n)&&(a[r[t][l]]=r[t][n])));return a}

/* array_flip() */
;function array_flip(r){var e,n={};if(r&&"object"==typeof r&&r.change_key_case)return r.flip();for(e in r)r.hasOwnProperty(e)&&(n[r[e]]=e);return n}

/* array_keys() */
;function array_keys(e,r,n){var t=void 0!==r,a=[],o=!!n,y=!0,c="";if(e&&"object"==typeof e&&e.change_key_case)return e.keys(r,n);for(c in e)e.hasOwnProperty(c)&&(y=!0,t&&(o&&e[c]!==r?y=!1:e[c]!=r&&(y=!1)),y&&(a[a.length]=c));return a}

/* array_search() */
;function array_search(e,r,t){var n=!!t,i="";if(r&&"object"==typeof r&&r.change_key_case)return r.search(e,t);if("object"==typeof e&&e.exec){if(!n){var a="i"+(e.global?"g":"")+(e.multiline?"m":"")+(e.sticky?"y":"");e=new RegExp(e.source,a)}for(i in r)if(r.hasOwnProperty(i)&&e.test(r[i]))return i;return!1}for(i in r)if(r.hasOwnProperty(i)&&(n&&r[i]===e||!n&&r[i]==e))return i;return!1};

/* array_replace() */
;function array_replace(r){var e={},a=0,n="",o=arguments.length;if(o<2)throw new Error("There should be at least 2 arguments passed to array_replace()");for(n in r)e[n]=r[n];for(a=1;a<o;a++)for(n in arguments[a])e[n]=arguments[a][n];return e}

/* array_replace_recursive() */
;function array_replace_recursive(r){var e={},a=0,t="",o=arguments.length;if(o<2)throw new Error("There should be at least 2 arguments passed to array_replace_recursive()");for(t in r)e[t]=r[t];for(a=1;a<o;a++)for(t in arguments[a])e[t]&&"object"==typeof e[t]?e[t]=this.array_replace_recursive(e[t],arguments[a][t]):e[t]=arguments[a][t];return e}

/* array_intersect */
function array_intersect(n){var r={},i=arguments.length,t=i-1,e="",o={},f=0,c="";n:for(e in n)r:for(f=1;f<i;f++){o=arguments[f];for(c in o)if(o[c]===n[e]){f===t&&(r[e]=n[e]);continue r}continue n}return r};

/* array_intersect_key */
function array_intersect_key(n){var r={},e=arguments.length,t=e-1,i="",o={},f=0,a="";n:for(i in n)if(n.hasOwnProperty(i))r:for(f=1;f<e;f++){o=arguments[f];for(a in o)if(o.hasOwnProperty(a)&&a===i){f===t&&(r[i]=n[i]);continue r}continue n}return r};

/* explode() */
;function explode(e,t,n){if(arguments.length<2||void 0===e||void 0===t)return null;if(""===e||!1===e||null===e)return!1;if("function"==typeof e||"object"==typeof e||"function"==typeof t||"object"==typeof t)return{0:""};!0===e&&(e="1"),e+="";var o=(t+="").split(e);return void 0===n?o:(0===n&&(n=1),n>0?n>=o.length?o:o.slice(0,n-1).concat([o.slice(n-1).join(e)]):-n>=o.length?[]:(o.splice(o.length+n),o))}

/* implode() */
;function implode(t,r){var e="",o="",n="";if(1===arguments.length&&(r=t,t=""),"object"==typeof r){if("[object Array]"===Object.prototype.toString.call(r))return r.join(t);for(e in r)o+=n+r[e],n=t;return o}return r}

/* str_replace() */
;function str_replace(t,o,r,c){var e=0,n=0,i="",a="",l=0,p=0,y=[].concat(t),f=[].concat(o),g=r,j="[object Array]"===Object.prototype.toString.call(f),b="[object Array]"===Object.prototype.toString.call(g);if(g=[].concat(g),"object"==typeof t&&"string"==typeof o){for(i=o,o=new Array,e=0;e<t.length;e+=1)o[e]=i;i="",f=[].concat(o),j="[object Array]"===Object.prototype.toString.call(f)}for(c&&(this.window[c]=0),e=0,l=g.length;e<l;e++)if(""!==g[e])for(n=0,p=y.length;n<p;n++)i=g[e]+"",a=j?void 0!==f[n]?f[n]:"":f[0],g[e]=i.split(y[n]).join(a),c&&(this.window[c]+=i.split(y[n]).length-1);return b?g:g[0]}

/* trim() */
;function trim(r,n){var t,e=0,f=0;for(r+="",t=n?(n+="").replace(/([\[\]\(\)\.\?\/\*\{\}\+\$\^\:])/g,"$1"):" \n\r\t\f\v            ​\u2028\u2029　",e=r.length,f=0;f<e;f++)if(-1===t.indexOf(r.charAt(f))){r=r.substring(f);break}for(f=(e=r.length)-1;f>=0;f--)if(-1===t.indexOf(r.charAt(f))){r=r.substring(0,f+1);break}return-1===t.indexOf(r.charAt(0))?r:""}

/* stripos() */
;function strripos(e,r,s){e=(e+"").toLowerCase(),r=(r+"").toLowerCase();var t=-1;return s?-1!==(t=(e+"").slice(s).lastIndexOf(r))&&(t+=s):t=(e+"").lastIndexOf(r),t>=0&&t}

/* substr() */
;function substr(t,s,e){var u=0,i=!0,r=0,h=0,F=0,a="",c=(t+="").length;switch(this.php_js=this.php_js||{},this.php_js.ini=this.php_js.ini||{},this.php_js.ini["unicode.semantics"]&&this.php_js.ini["unicode.semantics"].local_value.toLowerCase()){case"on":for(u=0;u<t.length;u++)if(/[\uD800-\uDBFF]/.test(t.charAt(u))&&/[\uDC00-\uDFFF]/.test(t.charAt(u+1))){i=!1;break}if(!i){if(s<0)for(u=c-1,r=s+=c;u>=r;u--)/[\uDC00-\uDFFF]/.test(t.charAt(u))&&/[\uD800-\uDBFF]/.test(t.charAt(u-1))&&(s--,r--);else for(var n=/[\uD800-\uDBFF][\uDC00-\uDFFF]/g;null!=n.exec(t)&&n.lastIndex-2<s;)s++;if(s>=c||s<0)return!1;if(e<0){for(u=c-1,h=c+=e;u>=h;u--)/[\uDC00-\uDFFF]/.test(t.charAt(u))&&/[\uD800-\uDBFF]/.test(t.charAt(u-1))&&(c--,h--);return!(s>c)&&t.slice(s,c)}for(F=s+e,u=s;u<F;u++)a+=t.charAt(u),/[\uD800-\uDBFF]/.test(t.charAt(u))&&/[\uDC00-\uDFFF]/.test(t.charAt(u+1))&&F++;return a}case"off":default:return s<0&&(s+=c),c=void 0===e?c:e<0?e+c:e+s,!(s>=t.length||s<0||s>c)&&t.slice(s,c)}}

/* strstr() */
;function strstr(r,s,t){var n=0;return r+="",-1!=(n=r.indexOf(s))&&(t?r.substr(0,n):r.slice(n))}

/* stristr() */
;function stristr(r,e,s){var t=0;return r+="",-1!=(t=r.toLowerCase().indexOf((e+"").toLowerCase()))&&(s?r.substr(0,t):r.slice(t))}

/* strip_tags() */
;function strip_tags(a,e){e=(((e||"")+"").toLowerCase().match(/<[a-z][a-z0-9]*>/g)||[]).join("");var r=/<\/?([a-z][a-z0-9]*)\b[^>]*>/gi,t=/<!--[\s\S]*?-->|<\?(?:php)?[\s\S]*?\?>/gi;return a.replace(t,"").replace(r,function(a,r){return e.indexOf("<"+r.toLowerCase()+">")>-1?a:""})}

/* ucwords() */
;function ucwords(u){return(u+"").replace(/^([a-z\u00E0-\u00FC])|\s+([a-z\u00E0-\u00FC])/g,function(u){return u.toUpperCase()})}

/* file_get_contents() */
;function file_get_contents(e,t,s,a,r){var n,i=[],o=[],p=0,l=0,c="",h=-1,u=0,_=null,d=!1;this.php_js=this.php_js||{},this.php_js.ini=this.php_js.ini||{};var f=this.php_js.ini;s=s||this.php_js.default_streams_context||null,t||(t=0);var v={FILE_USE_INCLUDE_PATH:1,FILE_TEXT:32,FILE_BINARY:64};if("number"==typeof t)u=t;else for(t=[].concat(t),l=0;l<t.length;l++)v[t[l]]&&(u|=v[t[l]]);if(u&v.FILE_BINARY&&u&v.FILE_TEXT)throw"You cannot pass both FILE_BINARY and FILE_TEXT to file_get_contents()";if(u&v.FILE_USE_INCLUDE_PATH&&f.include_path&&f.include_path.local_value){var T=-1!==f.include_path.local_value.indexOf("/")?"/":"\\";e=f.include_path.local_value+T+e}else/^(https?|file):/.test(e)||(c=this.window.location.href,h=0===e.indexOf("/")?c.indexOf("/",8)-1:c.lastIndexOf("/"),e=c.slice(0,h+1)+e);var g;if(s&&(d=!!(g=s.stream_options&&s.stream_options.http)),!s||!s.stream_options||d){var m=this.window.ActiveXObject?new ActiveXObject("Microsoft.XMLHTTP"):new XMLHttpRequest;if(!m)throw new Error("XMLHttpRequest not supported");var E=d?g.method:"GET",x=!!(s&&s.stream_params&&s.stream_params["phpjs.async"]);if(f["phpjs.ajaxBypassCache"]&&f["phpjs.ajaxBypassCache"].local_value&&(e+=(null==e.match(/\?/)?"?":"&")+(new Date).getTime()),m.open(E,e,x),x){var y=s.stream_params.notification;"function"==typeof y&&(m.onreadystatechange=function(e){var t,s={responseText:m.responseText,responseXML:m.responseXML,status:m.status,statusText:m.statusText,readyState:m.readyState,evt:e};switch(m.readyState){case 0:case 1:case 2:y.call(s,0,0,"",0,0,0);break;case 3:t=2*m.responseText.length,y.call(s,7,0,"",0,t,0);break;case 4:m.status>=200&&m.status<400?(t=2*m.responseText.length,y.call(s,8,0,"",m.status,t,0)):403===m.status?y.call(s,10,2,"",m.status,0,0):y.call(s,9,2,"",m.status,0,0);break;default:throw"Unrecognized ready state for file_get_contents()"}})}if(d){var I=g.header&&g.header.split(/\r?\n/)||[],L=!1;for(l=0;l<I.length;l++){var j=I[l],A=j.search(/:\s*/),b=j.substring(0,A);m.setRequestHeader(b,j.substring(A+1)),"User-Agent"===b&&(L=!0)}if(!L){var w=g.user_agent||f.user_agent&&f.user_agent.local_value;w&&m.setRequestHeader("User-Agent",w)}_=g.content||null}if(u&v.FILE_TEXT){var F="text/html";if(g&&g["phpjs.override"])F=g["phpjs.override"];else{var X=f["unicode.stream_encoding"]&&f["unicode.stream_encoding"].local_value||"UTF-8";g&&g.header&&/^content-type:/im.test(g.header)&&(F=g.header.match(/^content-type:\s*(.*)$/im)[1]),/;\s*charset=/.test(F)||(F+="; charset="+X)}m.overrideMimeType(F)}else u&v.FILE_BINARY&&m.overrideMimeType("text/plain; charset=x-user-defined");try{g&&g["phpjs.sendAsBinary"]?m.sendAsBinary(_):m.send(_)}catch(e){return!1}if(n=m.getAllResponseHeaders()){for(n=n.split("\n"),p=0;p<n.length;p++)""!==n[p].substring(1)&&o.push(n[p]);for(n=o,l=0;l<n.length;l++)i[l]=n[l];this.$http_response_header=i}return a||r?r?m.responseText.substr(a||0,r):m.responseText.substr(a):m.responseText}return!1}

/* uniqid() */
;function uniqid(t,i){void 0===t&&(t="");var e,n=function(t,i){return t=parseInt(t,10).toString(16),i<t.length?t.slice(t.length-i):i>t.length?Array(i-t.length+1).join("0")+t:t};return this.php_js||(this.php_js={}),this.php_js.uniqidSeed||(this.php_js.uniqidSeed=Math.floor(123456789*Math.random())),this.php_js.uniqidSeed++,e=t,e+=n(parseInt((new Date).getTime()/1e3,10),8),e+=n(this.php_js.uniqidSeed,5),i&&(e+=(10*Math.random()).toFixed(8).toString()),e}

/* isset()  */
;function isset(){var r=arguments,t=r.length,n=0;if(0===t)throw new Error("Empty isset");for(;n!==t;){if(void 0===r[n]||null===r[n])return!1;n++}return!0}

/* empty() */
;function empty(r){var n,t,e,f=[void 0,null,!1,0,"","0"];for(t=0,e=f.length;t<e;t++)if(r===f[t])return!0;if("object"==typeof r){for(n in r)return!1;return!0}return!1}

/*  unset()  */
;function unset(){var i=0,arg="",win="",winRef=/^(?:this)?window[.[]/,arr=[],accessor="",bracket=/\[['"]?(\d+)['"]?\]$/;for(i=0;i<arguments.length;i++)arg=arguments[i],winRef.lastIndex=0,bracket.lastIndex=0,win=winRef.test(arg)?"":"this.window.",bracket.test(arg)?(accessor=arg.match(bracket)[1],(arr=eval(win+arg.replace(bracket,""))).splice(accessor,1)):eval("delete "+win+arg)}

/*  is_numeric  */
;function is_numeric(mixed_var) {
  var type = typeof mixed_var, valid_number = /^[+\-]?(?:0x[\da-f]+|(?:(?:\d+(?:\.\d*)?|\.\d+))(e[+\-]?\d+)?)$/i;
  return !isNaN(mixed_var) && (type === 'number' || (type === 'string' && valid_number.test(mixed_var)));
}

/* rawurldecode() */
;function rawurldecode(e){return decodeURIComponent((e+"").replace(/%(?![\da-f]{2})/gi,function(){return"%25"}))}

/* rawurlencode() */
;function rawurlencode(e){return e=(e+"").toString(),encodeURIComponent(e).replace(/!/g,"%21").replace(/'/g,"%27").replace(/\(/g,"%28").replace(/\)/g,"%29").replace(/\*/g,"%2A")}

/*  parse_url()  */
;function parse_url(e,r){try{this.php_js=this.php_js||{}}catch(e){this.php_js={}}for(var p=this.php_js&&this.php_js.ini||{},s=p["phpjs.parse_url.mode"]&&p["phpjs.parse_url.mode"].local_value||"php",t=["source","scheme","authority","userInfo","user","pass","host","port","relative","path","directory","file","query","fragment"],h={php:/^(?:([^:\/?#]+):)?(?:\/\/()(?:(?:()(?:([^:@\/]*):?([^:@\/]*))?@)?([^:\/?#]*)(?::(\d*))?))?()(?:(()(?:(?:[^?#\/]*\/)*)()(?:[^?#]*))(?:\?([^#]*))?(?:#(.*))?)/,strict:/^(?:([^:\/?#]+):)?(?:\/\/((?:(([^:@\/]*):?([^:@\/]*))?@)?([^:\/?#]*)(?::(\d*))?))?((((?:[^?#\/]*\/)*)([^?#]*))(?:\?([^#]*))?(?:#(.*))?)/,loose:/^(?:(?![^:@]+:[^:@\/]*@)([^:\/?#.]+):)?(?:\/\/\/?)?((?:(([^:@\/]*):?([^:@\/]*))?@)?([^:\/?#]*)(?::(\d*))?)(((\/(?:[^?#](?![^?#\/]*\.[^?#\/.]+(?:[?#]|$)))*\/?)?([^?#\/]*))(?:\?([^#]*))?(?:#(.*))?)/},a=h[s].exec(e),u={},o=14;o--;)a[o]&&(u[t[o]]=a[o]);if(r)return u[r.replace("PHP_URL_","").toLowerCase()];if("php"!==s){var l=p["phpjs.parse_url.queryKey"]&&p["phpjs.parse_url.queryKey"].local_value||"queryKey";h=/(?:^|&)([^&=]*)=?([^&]*)/g,u[l]={},(u[t[12]]||"").replace(h,function(e,r,p){r&&(u[l][r]=p)})}return delete u.source,u}

/* base64_decode() */
;function base64_decode(e){var r,n,o,d,t,a,i="ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=",c=0,f=0,h="",C=[];if(!e)return e;e+="";do{r=(a=i.indexOf(e.charAt(c++))<<18|i.indexOf(e.charAt(c++))<<12|(d=i.indexOf(e.charAt(c++)))<<6|(t=i.indexOf(e.charAt(c++))))>>16&255,n=a>>8&255,o=255&a,C[f++]=64==d?String.fromCharCode(r):64==t?String.fromCharCode(r,n):String.fromCharCode(r,n,o)}while(c<e.length);return h=C.join(""),decodeURIComponent(escape(h.replace(/\0+$/,"")))}

/* base64_encode() */
;function base64_encode(e){var c,r,t,n,a,h="ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=",o=0,A=0,d="",i=[];if(!e)return e;e=unescape(encodeURIComponent(e));do{c=(a=e.charCodeAt(o++)<<16|e.charCodeAt(o++)<<8|e.charCodeAt(o++))>>18&63,r=a>>12&63,t=a>>6&63,n=63&a,i[A++]=h.charAt(c)+h.charAt(r)+h.charAt(t)+h.charAt(n)}while(o<e.length);d=i.join("");var l=e.length%3;return(l?d.slice(0,l-3):d)+"===".slice(l||3)}

/* json_decode() */
;function json_decode(str_json){var json=this.window.JSON;if("object"==typeof json&&"function"==typeof json.parse)try{return json.parse(str_json)}catch(t){if(!(t instanceof SyntaxError))throw new Error("Unexpected error type in json_decode()");return this.php_js=this.php_js||{},this.php_js.last_error_json=4,null}var cx=/[\u0000\u00ad\u0600-\u0604\u070f\u17b4\u17b5\u200c-\u200f\u2028-\u202f\u2060-\u206f\ufeff\ufff0-\uffff]/g,j,text=str_json;return cx.lastIndex=0,cx.test(text)&&(text=text.replace(cx,function(t){return"\\u"+("0000"+t.charCodeAt(0).toString(16)).slice(-4)})),/^[\],:{}\s]*$/.test(text.replace(/\\(?:["\\\/bfnrt]|u[0-9a-fA-F]{4})/g,"@").replace(/"[^"\\\n\r]*"|true|false|null|-?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?/g,"]").replace(/(?:^|:|,)(?:\s*\[)+/g,""))?j=eval("("+text+")"):(this.php_js=this.php_js||{},this.php_js.last_error_json=4,null)}

/* json_encode() */
;function json_encode(n){var e,t=this.window.JSON;try{if("object"==typeof t&&"function"==typeof t.stringify){if(void 0===(e=t.stringify(n)))throw new SyntaxError("json_encode");return e}var r=function(n){var e=/[\\\"\u0000-\u001f\u007f-\u009f\u00ad\u0600-\u0604\u070f\u17b4\u17b5\u200c-\u200f\u2028-\u202f\u2060-\u206f\ufeff\ufff0-\uffff]/g,t={"\b":"\\b","\t":"\\t","\n":"\\n","\f":"\\f","\r":"\\r",'"':'\\"',"\\":"\\\\"};return e.lastIndex=0,e.test(n)?'"'+n.replace(e,function(n){var e=t[n];return"string"==typeof e?e:"\\u"+("0000"+n.charCodeAt(0).toString(16)).slice(-4)})+'"':'"'+n+'"'},o=function(n,e){var t="",u=0,i="",f="",c=0,s=t,a=[],l=e[n];switch(l&&"object"==typeof l&&"function"==typeof l.toJSON&&(l=l.toJSON(n)),typeof l){case"string":return r(l);case"number":return isFinite(l)?String(l):"null";case"boolean":case"null":return String(l);case"object":if(!l)return"null";if(this.PHPJS_Resource&&l instanceof this.PHPJS_Resource||window.PHPJS_Resource&&l instanceof window.PHPJS_Resource)throw new SyntaxError("json_encode");if(t+="    ",a=[],"[object Array]"===Object.prototype.toString.apply(l)){for(c=l.length,u=0;u<c;u+=1)a[u]=o(u,l)||"null";return f=0===a.length?"[]":t?"[\n"+t+a.join(",\n"+t)+"\n"+s+"]":"["+a.join(",")+"]",t=s,f}for(i in l)Object.hasOwnProperty.call(l,i)&&(f=o(i,l))&&a.push(r(i)+(t?": ":":")+f);return f=0===a.length?"{}":t?"{\n"+t+a.join(",\n"+t)+"\n"+s+"}":"{"+a.join(",")+"}",t=s,f;case"undefined":case"function":default:throw new SyntaxError("json_encode")}};return o("",{"":n})}catch(n){if(!(n instanceof SyntaxError))throw new Error("Unexpected error type in json_encode()");return this.php_js=this.php_js||{},this.php_js.last_error_json=4,null}}

/* json_last_error() */
;function json_last_error(){return this.php_js&&this.php_js.last_error_json?this.php_js.last_error_json:0}

/* foreach(arr, handler) */
;function foreach(e,r){var o,t;if(e&&"object"==typeof e&&e.change_key_case)return e.foreach(r);if(void 0!==this.Iterator){var f=this.Iterator(e);if(1===r.length)for(t in f)r(t[1]);else for(t in f)r(t[0],t[1])}else if(1===r.length)for(o in e)e.hasOwnProperty(o)&&r(e[o]);else for(o in e)e.hasOwnProperty(o)&&r(o,e[o])}

/* require(file) */
;function require(e){var t=this.window.document,i="HTML"!==t.documentElement.nodeName||!t.write,n=this.file_get_contents(e),s=t.createElementNS&&i?t.createElementNS("http://www.w3.org/1999/xhtml","script"):t.createElement("script");s.type="text/javascript";var h=navigator.userAgent.toLowerCase();if(-1!==h.indexOf("msie")&&-1===h.indexOf("opera")?s.text=n:s.appendChild(t.createTextNode(n)),void 0!==s){t.getElementsByTagNameNS&&i?t.getElementsByTagNameNS("http://www.w3.org/1999/xhtml","head")[0]?t.getElementsByTagNameNS("http://www.w3.org/1999/xhtml","head")[0].appendChild(s):t.documentElement.insertBefore(s,t.documentElement.firstChild):t.getElementsByTagName("head")[0].appendChild(s);var a={};return a[this.window.location.href]=1,this.php_js=this.php_js||{},this.php_js.includes||(this.php_js.includes=a),this.php_js.includes[e]?++this.php_js.includes[e]:(this.php_js.includes[e]=1,1)}return 0}

/* str_split() */
;function str_split(l,n){if(null===n&&(n=1),null===l||n<1)return!1;for(var r=[],t=0,u=(l+="").length;t<u;)r.push(l.slice(t,t+=n));return r};








