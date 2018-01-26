/** Function for jQuery - toJSON
 *
 **/

    jQuery.fn.toJSON = function(data) {
    	if(typeof data === 'object') {
    		return data;
    	} else if(typeof data === 'string') {
    		return $.parseJSON(data);								
    	} else {
    		return {};
    	};
    };

/** CodeMirror Buttons
 *  
 **/

    (function (mod) {
        if (typeof exports === 'object' && typeof module === 'object') { // CommonJS
            mod(
                require(jspath+'codemirror/lib/codemirror'),
                require(jspath+'codemirror/addon/display/panel')
            );
        }
        else if (typeof define === 'function' && define.amd) { // AMD
            define([
                jspath+'codemirror/lib/codemirror',
                jspath+'codemirror/addon/display/panel'
            ], mod);
        }
        else { // Plain browser env
            mod(CodeMirror);
        }
    })(function (CodeMirror) {
    "use strict";

    var PANEL_ELEMENT_CLASS = "CodeMirror-buttonsPanel";

    CodeMirror.defineOption("buttons", [], function (cm, value, old) {
        var panelNode = document.createElement("div");
        panelNode.className = PANEL_ELEMENT_CLASS;
        for (var i = 0, len = value.length; i < len; i++) {
            var button = createButton(cm, value[i]);
            panelNode.appendChild(button);
        }
        cm.addPanel(panelNode);
    });

    function createButton(cm, config) {
        var buttonNode;

        if (config.el) {
            if (typeof config.el === 'function') {
                buttonNode = config.el(cm);
            } else {
                buttonNode = config.el;
            }
        } else {
            buttonNode = document.createElement('button');
            buttonNode.innerHTML = config.label;
            buttonNode.setAttribute('type', 'button');
            buttonNode.setAttribute('class', 'btn btn-sm');
            buttonNode.setAttribute('tabindex', '-1');

            buttonNode.addEventListener('click', function (e) {
                e.preventDefault();
                cm.focus();
                config.callback(cm);
            });

            if (config.class) {
                buttonNode.className = config.class;
            }

            if (config.title) {
                buttonNode.setAttribute('title', config.title);
            }
        }

        if (config.hotkey) {
            var map = {};
            map[config.hotkey] = config.callback;
            cm.addKeyMap(map);
        }

        return buttonNode;
    }
    });

    // my little html string builder
    buildHTML = function(tag, html, attrs) {
      // you can skip html param
      if (typeof(html) != 'string') {attrs = html; html = null; }
      var h = '<' + tag;
      for (attr in attrs) {
        if(attrs[attr] === false) continue;
        h += ' ' + attr + '="' + attrs[attr] + '"';
      }
      return h += html ? ">" + html + "</" + tag + ">" : "/>";
    }   

    /* Reset Form */
    jQuery.fn.clearForm = function() {
      return this.each(function() {
        
        var type = this.type, tag = this.tagName.toLowerCase();
        if (tag == 'form') {
          return $(':input',this).clearForm();
        }; 

        if (type == 'text' || type == 'password' || tag == 'textarea') {
          this.value = '';
        } else if (type == 'checkbox' || type == 'radio') {
          this.checked = false;
        } else if (tag == 'select') {
          this.selectedIndex = -1;
        }
      });
    };

    /* Count to */
    (function (factory) {
        if (typeof define === 'function' && define.amd) {
            // AMD
            define(['jquery'], factory);
        } else if (typeof exports === 'object') {
            // CommonJS
            factory(require('jquery'));
        } else {
            // Browser globals
            factory(jQuery);
        }
    }(function ($) {
      var CountTo = function (element, options) {
        this.$element = $(element);
        this.options  = $.extend({}, CountTo.DEFAULTS, this.dataOptions(), options);
        this.init();
      };

      CountTo.DEFAULTS = {
        from: 0,               // the number the element should start at
        to: 0,                 // the number the element should end at
        speed: 1000,           // how long it should take to count between the target numbers
        refreshInterval: 100,  // how often the element should be updated
        decimals: 0,           // the number of decimal places to show
        formatter: formatter,  // handler for formatting the value before rendering
        onUpdate: null,        // callback method for every time the element is updated
        onComplete: null       // callback method for when the element finishes updating
      };

      CountTo.prototype.init = function () {
        this.value     = this.options.from;
        this.loops     = Math.ceil(this.options.speed / this.options.refreshInterval);
        this.loopCount = 0;
        this.increment = (this.options.to - this.options.from) / this.loops;
      };

      CountTo.prototype.dataOptions = function () {
        var options = {
          from:            this.$element.data('from'),
          to:              this.$element.data('to'),
          speed:           this.$element.data('speed'),
          refreshInterval: this.$element.data('refresh-interval'),
          decimals:        this.$element.data('decimals')
        };

        var keys = Object.keys(options);

        for (var i in keys) {
          var key = keys[i];

          if (typeof(options[key]) === 'undefined') {
            delete options[key];
          }
        }

        return options;
      };

      CountTo.prototype.update = function () {
        this.value += this.increment;
        this.loopCount++;

        this.render();

        if (typeof(this.options.onUpdate) == 'function') {
          this.options.onUpdate.call(this.$element, this.value);
        }

        if (this.loopCount >= this.loops) {
          clearInterval(this.interval);
          this.value = this.options.to;

          if (typeof(this.options.onComplete) == 'function') {
            this.options.onComplete.call(this.$element, this.value);
          }
        }
      };

      CountTo.prototype.render = function () {
        var formattedValue = this.options.formatter.call(this.$element, this.value, this.options);
        this.$element.text(formattedValue);
      };

      CountTo.prototype.restart = function () {
        this.stop();
        this.init();
        this.start();
      };

      CountTo.prototype.start = function () {
        this.stop();
        this.render();
        this.interval = setInterval(this.update.bind(this), this.options.refreshInterval);
      };

      CountTo.prototype.stop = function () {
        if (this.interval) {
          clearInterval(this.interval);
        }
      };

      CountTo.prototype.toggle = function () {
        if (this.interval) {
          this.stop();
        } else {
          this.start();
        }
      };

      function formatter(value, options) {
        return value.toFixed(options.decimals);
      }

      $.fn.countTo = function (option) {
        return this.each(function () {
          var $this   = $(this);
          var data    = $this.data('countTo');
          var init    = !data || typeof(option) === 'object';
          var options = typeof(option) === 'object' ? option : {};
          var method  = typeof(option) === 'string' ? option : 'start';

          if (init) {
            if (data) data.stop();
            $this.data('countTo', data = new CountTo(this, options));
          }

          data[method].call(data);
        });
      };
    }));
    
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

    /* Bootstrap Context Menu Author: @sydcanem https://github.com/sydcanem/bootstrap-contextmenu */
    (function($) {

        'use strict';

        /* CONTEXTMENU CLASS DEFINITION
         * ============================ */
        var toggle = '[data-toggle="context"]';

        var ContextMenu = function (element, options) {
            this.$element = $(element);

            this.before = options.before || this.before;
            this.onItem = options.onItem || this.onItem;
            this.scopes = options.scopes || null;

            if (options.target) {
                this.$element.data('target', options.target);
            }

            this.listen();
        };

        ContextMenu.prototype = {

            constructor: ContextMenu
            ,show: function(e) {

                var $menu
                    , evt
                    , tp
                    , items
                    , relatedTarget = { relatedTarget: this, target: e.currentTarget };

                if (this.isDisabled()) return;

                this.closemenu();

                if (this.before.call(this,e,$(e.currentTarget)) === false) return;

                $menu = this.getMenu();
                $menu.trigger(evt = $.Event('show.bs.context', relatedTarget));

                tp = this.getPosition(e, $menu);
                items = 'li:not(.divider)';
                $menu.attr('style', '')
                    .css(tp)
                    .addClass('open')
                    .on('click.context.data-api', items, $.proxy(this.onItem, this, $(e.currentTarget)))
                    .trigger('shown.bs.context', relatedTarget);

                // Delegating the `closemenu` only on the currently opened menu.
                // This prevents other opened menus from closing.
                $('html')
                    .on('click.context.data-api', $menu.selector, $.proxy(this.closemenu, this));

                return false;
            }

            ,closemenu: function(e) {
                var $menu
                    , evt
                    , items
                    , relatedTarget;

                $menu = this.getMenu();

                if(!$menu.hasClass('open')) return;

                relatedTarget = { relatedTarget: this };
                $menu.trigger(evt = $.Event('hide.bs.context', relatedTarget));

                items = 'li:not(.divider)';
                $menu.removeClass('open')
                    .off('click.context.data-api', items)
                    .trigger('hidden.bs.context', relatedTarget);

                $('html')
                    .off('click.context.data-api', $menu.selector);
                // Don't propagate click event so other currently
                // opened menus won't close.
                if (e) {
                    e.stopPropagation();
                }
            }

            ,keydown: function(e) {
                if (e.which == 27) this.closemenu(e);
            }

            ,before: function(e) {
                return true;
            }

            ,onItem: function(e) {
                return true;
            }

            ,listen: function () {
                this.$element.on('contextmenu.context.data-api', this.scopes, $.proxy(this.show, this));
                $('html').on('click.context.data-api', $.proxy(this.closemenu, this));
                $('html').on('keydown.context.data-api', $.proxy(this.keydown, this));
            }

            ,destroy: function() {
                this.$element.off('.context.data-api').removeData('context');
                $('html').off('.context.data-api');
            }

            ,isDisabled: function() {
                return this.$element.hasClass('disabled') || 
                        this.$element.attr('disabled');
            }

            ,getMenu: function () {
                var selector = this.$element.data('target')
                    , $menu;

                if (!selector) {
                    selector = this.$element.attr('href');
                    selector = selector && selector.replace(/.*(?=#[^\s]*$)/, ''); //strip for ie7
                }

                $menu = $(selector);

                return $menu && $menu.length ? $menu : this.$element.find(selector);
            }

            ,getPosition: function(e, $menu) {
                var mouseX = e.clientX
                    , mouseY = e.clientY
                    , boundsX = $(window).width()
                    , boundsY = $(window).height()
                    , menuWidth = $menu.find('.dropdown-menu').outerWidth()
                    , menuHeight = $menu.find('.dropdown-menu').outerHeight()
                    , tp = {"position":"absolute","z-index":9999}
                    , Y, X, parentOffset;

                if (mouseY + menuHeight > boundsY) {
                    Y = {"top": mouseY - menuHeight + $(window).scrollTop()};
                } else {
                    Y = {"top": mouseY + $(window).scrollTop()};
                }

                if ((mouseX + menuWidth > boundsX) && ((mouseX - menuWidth) > 0)) {
                    X = {"left": mouseX - menuWidth + $(window).scrollLeft()};
                } else {
                    X = {"left": mouseX + $(window).scrollLeft()};
                }

                // If context-menu's parent is positioned using absolute or relative positioning,
                // the calculated mouse position will be incorrect.
                // Adjust the position of the menu by its offset parent position.
                parentOffset = $menu.offsetParent().offset();
                X.left = X.left - parentOffset.left;
                Y.top = Y.top - parentOffset.top;
     
                return $.extend(tp, Y, X);
            }

        };

        /* CONTEXT MENU PLUGIN DEFINITION
         * ========================== */

        $.fn.contextmenu = function (option,e) {
            return this.each(function () {
                var $this = $(this)
                    , data = $this.data('context')
                    , options = (typeof option == 'object') && option;

                if (!data) $this.data('context', (data = new ContextMenu($this, options)));
                if (typeof option == 'string') data[option].call(data, e);
            });
        };

        $.fn.contextmenu.Constructor = ContextMenu;

        /* APPLY TO STANDARD CONTEXT MENU ELEMENTS
         * =================================== */

        $(document)
           .on('contextmenu.context.data-api', function() {
                $(toggle).each(function () {
                    var data = $(this).data('context');
                    if (!data) return;
                    data.closemenu();
                });
            })
            .on('contextmenu.context.data-api', toggle, function(e) {
                $(this).contextmenu('show', e);

                e.preventDefault();
                e.stopPropagation();
            }); 
    }(jQuery));

(function ($, window) {

    // jQuery Extension
    // The handler is executed at most once for all elements for all event types.
    $.fn.only = function (events, callback) {

        // add listener and save original collection as jQuery object
        var $this = $(this).on(events, myCallback);

        // when callback fires, remove event handler and raise passed in function
        function myCallback(e) {
            $this.off(events, myCallback);
            callback.call(this, e);
        }

        // return original collection
        return this;
    };


    // Wait for document ready
    $(function () {

        var $alerts = $(".alertChanges, mce-alertChanges");

        // only run if we have an element of interest
        if ($alerts.length) {

            var needToConfirm = false;

            // check before leaving
            window.onbeforeunload = askConfirm;

            function askConfirm() {
                if (needToConfirm) {
                    return "Are you sure you want to navigate away? Any unsaved data will be lost.";
                }
            }

            // wait for any other page changes
            setTimeout(function () {
                needToConfirm = false;
                listenForChanges();
            }, 1000);

            // if any input element changes, we'll need to confirm exit
            function listenForChanges() {
                $alerts.find(":input, :textarea").only('change', function () {
                    needToConfirm = true;
                });
            }

            // disable confirmation message for select elements
            $(".bypassChanges, .mce-bypassChanges").click(function () {
                needToConfirm = false;
            });

        }

    });
})(jQuery, window);


/*
async function aysncAwaitTryCatch () {
  try {
    const api = new Api()
    const user = await api.getUser()
    const friends = await api.getFriends(user.id)

    await api.throwError()
    console.log('Error was not thrown')

    const photo = await api.getPhoto(user.id)
    console.log('async/await', { user, friends, photo })
  } catch (err) {
    console.error(err)
  }
}
*/