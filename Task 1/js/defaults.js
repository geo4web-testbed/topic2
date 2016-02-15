// Console fallback
if (!window.console) console = {};
console.log = console.log || function(name, data) {};
console.clear = console.clear || function() {};

// Host
var host = location.protocol + '//' + location.host + '/';

// Screen heights
var mobileHeight = 480;
var tabletHeight = 768;
var hdHeight = 1080;

// Javascript functions
function setLocation(location, targetTop) {
        if (location) (targetTop ? top : window).location = location;
}

function openWindow(location, target) {
        if (location) window.open(location, target || '_self');
}

function updateQueryString(key, value, options) {
        if (!options) options = {};

        var url = options.url || location.href;
        var re = new RegExp("([?&])" + key + "=.*?(&|#|$)(.*)", "gi"), hash;

        hash = url.split('#');
        url = hash[0];
        if (re.test(url)) {
                if (typeof value !== 'undefined' && value !== null) {
                        url = url.replace(re, '$1' + key + "=" + value + '$2$3');
                } else {
                        url = url.replace(re, '$1$3').replace(/(&|\?)$/, '');
                }
        } else if (typeof value !== 'undefined' && value !== null) {
                var separator = url.indexOf('?') !== -1 ? '&' : '?';
                url = url + separator + key + '=' + value;
        }

        if ((typeof options.hash === 'undefined' || options.hash) &&
            typeof hash[1] !== 'undefined' && hash[1] !== null)
                url += '#' + hash[1];
        return url;
}

function toJSON(object) {
        var seen = [];
        var json = JSON.stringify(object, function(key, val) {
                if (typeof val === 'object') {
                        if (seen.indexOf(val) >= 0) return;
                        seen.push(val);
                }
                return val;
        });

        return json;
}

function getObjects(obj, key, val) {
        var objects = [];
        for (var i in obj) {
                if (!obj.hasOwnProperty(i)) continue;
                if (typeof obj[i] === 'object') {
                        objects = objects.concat(getObjects(obj[i], key, val));
                } else if (i === key && obj[key] === val) {
                        objects.push(obj);
                }
        }
        return objects;
}

function removeBySubValue(array, key, value) {
        var itemCount = array.length;
        for (var i = 0; i < itemCount; i++) {
                if (array[i][key] === value) {
                        array.splice(i, 1);
                        break;
                }
        }
};

function createCookie(name, value, days) {
        var expires = '';
        if (days) {
                var date = new Date();
                date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
                expires = '; expires=' + date.toGMTString();
        }
        document.cookie = name + '=' + value + expires + '; path=/';
}
function getCookie(name) {
        var nameEQ = name + '=';
        var ca = document.cookie.split(';');
        for (var i = 0; i < ca.length; i++) {
                var c = ca[i];
                while (c.charAt(0) === ' ') c = c.substring(1, c.length);
                if (c.indexOf(nameEQ) === 0) return c.substring(nameEQ.length, c.length);
        }
        return null;
}
function deleteCookie(name) {
        createCookie(name, '', -1);
}

// Prototype functions

String.prototype.capitalize = function() {
        return this.charAt(0).toUpperCase() + this.slice(1);
};

String.prototype.replaceAll = function(find, replace) {
        find = find.replace(/([.*+?^=!:${}()|\[\]\/\\])/g, "\\$1");
        return this.replace(new RegExp(find, 'g'), replace);
};

String.prototype.decodeHTML = function() {
        return this.replace(/&(#(?:x[0-9a-f]+|\d+)|[a-z]+);/gi, function($0) {
                var tempElement = document.createElement('span');
                tempElement.innerHTML = $0;
                return (tempElement.textContent || tempElement.innerText);
        });
};

// jQuery functions
(function($) {
        $.fn.hasValue = function() {
                return this.length && this.val().length;
        };

        $.fn.attributes = function() {
                var attributes = {};

                if (this.length) {
                        $.each(this[0].attributes, function(index, attr) {
                                attributes[attr.name] = attr.value;
                        });
                }

                return attributes;
        };

        $.fn.serializeObject = function() {
                var o = {};
                var a = this.serializeArray();
                $.each(a, function() {
                        if (o[this.name] !== undefined) {
                                if (!o[this.name].push) {
                                        o[this.name] = [o[this.name]];
                                }
                                o[this.name].push(this.value || '');
                        } else {
                                o[this.name] = this.value || '';
                        }
                });
                return o;
        };
})(jQuery);

/**
 * jQuery Plugin to use Local Storage or Session Storage without worrying
 * about HTML5 support. It uses Cookies for backward compatibility.
 *
 * @author Alberto Varela Sánchez (http://www.berriart.com)
 * @version 1.0 (17th January 2013)
 *
 * Released under the MIT License (http://opensource.org/licenses/MIT)
 *
 * Copyright (c) 2013 Alberto Varela Sánchez (alberto@berriart.com)
 *
 * Permission is hereby granted, free of charge, to any person obtaining a
 * copy of this software and associated documentation files (the "Software"),
 * to deal in the Software without restriction, including without limitation
 * the rights to use, copy, modify, merge, publish, distribute, sublicense,
 * and/or sell copies of the Software, and to permit persons to whom the
 * Software is furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */
(function(window, $) {
        "use strict";

        var types = ['localStorage','sessionStorage'],
            support = [];

        $.each(types, function(i, type) {
                try {
                        support[type] = type in window && window[type] !== null;
                } catch (e) {
                        support[type] = false;
                }

                $[type] = {
                        settings: {
                                cookiePrefix : 'html5fallback:' + type + ':',
                                cookieOptions : {
                                        path : '/',
                                        domain : document.domain,
                                        expires : ('localStorage' === type) ? { expires: 365 } : undefined
                                }
                        },

                        getItem: function(key) {
                                var response;
                                if (support[type]) {
                                        response = window[type].getItem(key);
                                } else {
                                        response = $.cookie(this.settings.cookiePrefix + key);
                                }

                                return response;
                        },

                        setItem: function(key, value) {
                                if (support[type]) {
                                        return window[type].setItem(key, value);
                                } else {
                                        return $.cookie(this.settings.cookiePrefix + key, value, this.settings.cookieOptions);
                                }
                        },

                        removeItem: function(key) {
                                if (support[type]) {
                                        return window[type].removeItem(key);
                                } else {
                                        var options = $.extend(this.settings.cookieOptions, {
                                                expires: -1
                                        });
                                        return $.cookie(this.settings.cookiePrefix + key, null, options);
                                }
                        },

                        clear: function() {
                                if (support[type]) {
                                        return window[type].clear();
                                } else {
                                        var reg = new RegExp('^' + this.settings.cookiePrefix, ''),
                                            options = $.extend(this.settings.cookieOptions, {
                                                        expires: -1
                                            });

                                        if (document.cookie && document.cookie !== '') {
                                                $.each(document.cookie.split(';'), function(i, cookie) {
                                                        if (reg.test(cookie = $.trim(cookie))) {
                                                                $.cookie(cookie.substr(0,cookie.indexOf('=')), null, options);
                                                        }
                                                });
                                        }
                                }
                        }
                };
        });
})(window, jQuery);