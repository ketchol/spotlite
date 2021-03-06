function welcome(bodyText) {
    var $modal = popupHTML(title, bodyText, null, "lg");
    $modal.modal();
}

/**
 * simulate alert popup
 * @param title
 * @param bodyText
 * @param callback
 */
function alertP(title, bodyText, callback) {

    var $footer = $("<button>").addClass("btn btn-primary btn-flat").attr({
        "type": "button",
        "data-dismiss": "modal"
    }).text("OK");

    var $modal = popupHTML(title, bodyText, $footer, "sm");
    $modal.modal();

    if (typeof callback != 'undefined') {
        $modal.on("hidden.bs.modal", function () {
            if ($.isFunction(callback)) {
                callback();
                $modal.remove();
                $("body").css("padding-right", "");
            }
        })
    }
}

/**
 * simulate confirm popup
 * @param title
 * @param bodyText
 * @param btnOpts
 */
function confirmP(title, bodyText, btnOpts) {
    var $footer = $("<div>").append(
        $("<button>")
            .addClass("btn")
            .addClass(typeof btnOpts.affirmative.class == 'undefined' ? "" : btnOpts.affirmative.class)
            .on("click", function () {
                if (typeof btnOpts.affirmative.callback != 'undefined' && $.isFunction(btnOpts.affirmative.callback)) {
                    btnOpts.affirmative.callback();
                }
            })
            .attr("data-dismiss", function () {
                return typeof btnOpts.affirmative.dismiss != 'undefined' && btnOpts.affirmative.dismiss == true ? "modal" : "";
            })
            .text(typeof btnOpts.affirmative.text != 'undefined' ? btnOpts.affirmative.text : 'OK'),
        $("<button>")
            .addClass("btn")
            .addClass(typeof btnOpts.negative.class == 'undefined' ? "" : btnOpts.negative.class)
            .on("click", function () {
                if (typeof btnOpts.negative.callback != 'undefined' && $.isFunction(btnOpts.negative.callback)) {
                    btnOpts.negative.callback();
                }
            })
            .attr("data-dismiss", function () {
                return typeof btnOpts.negative.dismiss != 'undefined' && btnOpts.negative.dismiss == true ? "modal" : "";
            })
            .text(typeof btnOpts.negative.text != 'undefined' ? btnOpts.negative.text : 'Cancel')
    );
    var $modal = popupHTML(title, bodyText, $footer, "sm");
    $modal.modal();

    $modal.on("hidden.bs.modal", function () {
        $("body").css("padding-right", "");
        $modal.remove();
    })
}

/**
 * Create popup HTML content
 * @param title
 * @param $content
 * @param $footer
 * @param dialogSize
 * @returns {*|jQuery}
 */
function popupHTML(title, $content, $footer, dialogSize) {
    var $header = $("<div>").append(
        $("<button>").addClass("close").attr({
            "type": "button",
            "data-dismiss": "modal",
            "aria-label": "Close"
        }).append(
            $("<span>").attr({
                "aria-hidden": "true"
            }).html("&times;")
        ),
        $("<h4>").addClass("modal-title").text(title)
    );


    if (typeof $footer == 'undefined' || $footer == null) {
        $footer = $("<button>").addClass("btn btn-primary btn-flat").attr({
            "type": "button",
            "data-dismiss": "modal"
        }).text("OK");
    }
    if (typeof dialogSize == "undefined") {
        dialogSize = "";
    } else {
        switch (dialogSize) {
            case "lg":
                dialogSize = "modal-lg";
                break;
            case "md":
                dialogSize = "";
                break;
            case "sm":
                dialogSize = "modal-sm";
                break;
            default:
        }
    }
    var $modal = popupFrame($header, $content, $footer);
    $modal.find(".modal-dialog").addClass(dialogSize);
    return $modal;
}

function popupFrame($header, $content, $footer) {
    return $("<div>").attr("id", randomString(10)).addClass("modal fade popup").append(
        $("<div>").addClass("modal-dialog").append(
            $("<div>").addClass("modal-content").append(
                typeof $header != 'undefined' ?
                    $("<div>").addClass("modal-header").append(
                        $header
                    ) : '',
                typeof $content != 'undefined' ?
                    $("<div>").addClass("modal-body").append(
                        $content
                    ) : '',
                typeof $footer != 'undefined' ?
                    $("<div>").addClass("modal-footer").append(
                        $footer
                    ) : ''
            )
        )
    );
}

/**
 * Generate random string
 * @param length
 * @param chars
 * @returns {string}
 */
function randomString(length, chars) {
    if (typeof chars == 'undefined') {
        chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    }
    var result = '';
    for (var i = length; i > 0; --i) result += chars[Math.floor(Math.random() * chars.length)];
    return result;
}

function showLoading(text) {
    if ($(".spinner").length == 0) {
        var $spinner = $("<div>").addClass("spinner").append(
            $("<div>").addClass("spinner-backdrop"),
            $("<div>").addClass("spinner-core"),
            $("<div>").addClass("spinner-text").text(function () {
                if (typeof text != 'undefined' && text.length > 0) {
                    return text;
                }
            })
        );
        $spinner.find(".spinner-backdrop").hide();
        $spinner.find(".spinner-core").hide();
        $spinner.find(".spinner-text").hide();
        $("body").append($spinner);
        $(".spinner-backdrop, .spinner-core, .spinner-text").fadeIn();
    }
}

function hideLoading() {
    var $spinnerElement = $(".spinner-backdrop, .spinner-core");
    $spinnerElement.fadeOut(function () {
        $(this).closest(".spinner").remove();
    });
}

Number.prototype.formatMoney = function (c, d, t) {
    var n = this,
        c = isNaN(c = Math.abs(c)) ? 2 : c,
        d = d == undefined ? "." : d,
        t = t == undefined ? "," : t,
        s = n < 0 ? "-" : "",
        i = parseInt(n = Math.abs(+n || 0).toFixed(c)) + "",
        j = (j = i.length) > 3 ? j % 3 : 0;
    return s + (j ? i.substr(0, j) + t : "") + i.substr(j).replace(/(\d{3})(?=\d)/g, "$1" + t) + (c ? d + Math.abs(n - i).toFixed(c).slice(2) : "");
};

function getDomainFromURL(url) {
    var a = document.createElement('a');
    a.href = url;
    return a.hostname;
}

function stripDomainFromURL(url) {
    return url.replace(/^.*\/\/[^\/]+/, '');
}

function strtotime(str) {
    return new Date(str).getTime() / 1000
}

$.urlParam = function (name, uri) {
    if (typeof uri == 'undefined') {
        uri = window.location.href;
    }
    // Thanks to R.B.: http://stackoverflow.com/questions/19491336/get-url-parameter-jquery
    var results = new RegExp('[\?&]' + name + '=([^&#]*)').exec(uri);
    if (results == null) {
        return null;
    } else {
        return results[1] || 0;
    }
};

/**
 * Replication of PHP datetime format
 *
 * @param timestamp
 * @param format
 * @returns {string}
 */
function timestampToDateTimeByFormat(timestamp, format) {
    var days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
    var months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
    var date = new Date(timestamp * 1000);
    var d = '',
        D = '',
        j = '',
        l = '',
        N = '',
        S = '',
        w = '',
        F = '',
        m = '',
        M = '',
        n = '',
        L = '',
        Y = '',
        y = '',
        a = '',
        A = '',
        g = '',
        G = '',
        h = '',
        H = '',
        i = '',
        s = '';
    if (format.indexOf('d') > -1) {
        d = (date.getDate() < 10) ? '0' + date.getDate() : date.getDate();
    }
    if (format.indexOf('j') > -1) {
        j = date.getDate();
    }
    if (format.indexOf('D') > -1) {
        D = days[date.getDay()].substring(0, 3);
    }
    if (format.indexOf('l') > -1) {
        l = days[date.getDay()];
    }
    if (format.indexOf('N') > -1) {
        N = (date.getDay() == 0) ? 7 : date.getDay();
    }
    if (format.indexOf('S') > -1) {
        switch ((date.getDate() + '').slice(-1)) {
            case '1':
                if ((date.getDate() + '').slice(-2) != "11") {
                    S = 'st';
                } else {
                    S = 'th';
                }
                break;
            case '2':
                if ((date.getDate() + '').slice(-2) != "12") {
                    S = 'nd';
                } else {
                    S = 'th';
                }
                break;
            case '3':
                if ((date.getDate() + '').slice(-2) != "13") {
                    S = 'rd';
                } else {
                    S = 'th';
                }
                break;
            default:
                S = 'th';
                break;
        }
    }
    if (format.indexOf('w') > -1) {
        w = date.getDay();
    }
    if (format.indexOf('F') > -1) {
        F = months[date.getMonth()];
    }
    if (format.indexOf('m') > -1) {
        m = date.getMonth() + 1;
        m = (m < 10) ? '0' + m : m;
    }
    if (format.indexOf('M') > -1) {
        M = months[date.getMonth()].substring(0, 3);
    }
    if (format.indexOf('n') > -1) {
        n = date.getMonth() + 1;
    }
    if (format.indexOf('L') > -1) {
        L = (date.getFullYear() % 4 == 0) ? 1 : 0;
    }
    if (format.indexOf('Y') > -1) {
        Y = date.getFullYear();
    }
    if (format.indexOf('y') > -1) {
        y = (date.getFullYear() + '').slice(-2);
    }
    if (format.indexOf('a') > -1) {
        a = (date.getHours() > 11) ? 'pm' : 'am';
    }
    if (format.indexOf('A') > -1) {
        A = (date.getHours() > 11) ? 'PM' : 'AM';
    }
    if (format.indexOf('g') > -1) {
        g = (date.getHours() > 12) ? date.getHours() - 12 : date.getHours();
    }
    if (format.indexOf('G') > -1) {
        G = date.getHours();
    }
    if (format.indexOf('h') > -1) {
        h = (date.getHours() > 12) ? date.getHours() - 12 : date.getHours();
        h = h < 10 ? '0' + h : h;
    }
    if (format.indexOf('H') > -1) {
        H = date.getHours();
        H = H < 10 ? '0' + H : H;
    }
    if (format.indexOf('i') > -1) {
        i = date.getMinutes();
        i = i < 10 ? '0' + i : i;
    }
    if (format.indexOf('s') > -1) {
        s = date.getSeconds();
        s = s < 10 ? '0' + s : s;
    }
    var formattedDate = '';
    for (var counter = 0; counter < format.length; counter++) {
        switch (format[counter]) {
            case 'd':
                formattedDate += d;
                break;
            case 'D':
                formattedDate += D;
                break;
            case 'j':
                formattedDate += j;
                break;
            case 'l':
                formattedDate += l;
                break;
            case 'N':
                formattedDate += N;
                break;
            case 'S':
                formattedDate += S;
                break;
            case 'w':
                formattedDate += w;
                break;
            case 'F':
                formattedDate += F;
                break;
            case 'm':
                formattedDate += m;
                break;
            case 'M':
                formattedDate += M;
                break;
            case 'n':
                formattedDate += n;
                break;
            case 'L':
                formattedDate += L;
                break;
            case 'Y':
                formattedDate += Y;
                break;
            case 'y':
                formattedDate += y;
                break;
            case 'a':
                formattedDate += a;
                break;
            case 'A':
                formattedDate += A;
                break;
            case 'g':
                formattedDate += g;
                break;
            case 'G':
                formattedDate += G;
                break;
            case 'h':
                formattedDate += h;
                break;
            case 'H':
                formattedDate += H;
                break;
            case 'i':
                formattedDate += i;
                break;
            case 's':
                formattedDate += s;
                break;
            default:
                formattedDate += format[counter];
                break;
        }
    }
    return formattedDate;
}

// String.prototype.startsWith = function(needle)
// {
//     return(this.indexOf(needle) == 0);
// };
//
//
// var Preloader = new function () {
//     var _this,
//         _pContainer,
//         _preloader;
//     this.init = function () {
//         _this = this;
//         _preloader = $('.coll-site-preloader');
//
//         // prepare remove
//         $wndw.load(_this.load)
//
//     };
//     this.load = function () {
//
//         $('.wrapper.common').css('visibility', 'visible')
//
//         _preloader.animate({
//             opacity: 0
//         }, 500, "linear", function () {
//             $(this).remove();
//         });
//     };
// };
// Preloader.init();

function capitalise(string) {
    return string.charAt(0).toUpperCase() + string.slice(1).toLowerCase();
}

function camelize(str) {
    return str.replace(/(?:^\w|[A-Z]|\b\w)/g, function (letter, index) {
        return index == 0 ? letter.toLowerCase() : letter.toUpperCase();
    }).replace(/\s+/g, '');
}

function describeServerRespondedError(errorCode) {
    switch (errorCode) {
        case 403:
            alertP("Oops. Something went wrong.", "You have no permission to perform this action.");
            break;
        case 404:
            alertP("Oops. Something went wrong.", "The content could not be found.");
            break;
        case 500:
            alertP("Oops. Something went wrong.", "Our support team is already working to fix this technical issue. Please try again later.");
            break;
    }
}

function localStorageAvailable() {
    var test = 'test';
    try {
        localStorage.setItem(test, test);
        localStorage.removeItem(test);
        return true;
    } catch (e) {
        return false;
    }
}

function setCookie(name, value, days) {
    if (days) {
        var date = new Date();
        date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
        //noinspection JSDuplicatedDeclaration
        var expires = "; expires=" + date.toGMTString();
    }
    else { //noinspection JSDuplicatedDeclaration
        var expires = "";
    }
    document.cookie = name + "=" + value + expires + "; path=/";
}

function getCookie(name) {
    var nameEQ = name + "=";
    var ca = document.cookie.split(';');
    for (var i = 0; i < ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0) == ' ') c = c.substring(1, c.length);
        if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length, c.length);
    }
    return null;
}

function removeCookie(name) {
    setCookie(name, "", -1);
}

function setLocalStorageOrCookie(key, value) {
    if (localStorageAvailable()) {
        localStorage.setItem(key, value);
    } else {
        setCookie(key, value, 365);
    }
}

function getLocalStorageOrCookie(key) {
    if (localStorageAvailable()) {
        return localStorage.getItem(key);
    } else {
        return getCookie(key);
    }
}

function removeLocalStorageOrCookie(key) {
    if (localStorageAvailable()) {
        localStorage.removeItem(key);
    } else {
        removeCookie(key);
    }
}

function getCookies() {
    var pairs = document.cookie.split(";");
    var cookies = {};
    for (var i = 0; i < pairs.length; i++) {
        var pair = pairs[i].split("=");
        cookies[pair[0]] = unescape(pair[1]);
    }
    return cookies;
}

/**
 *
 * @param title
 * @param headingContent
 * @param subHeadingContent
 * @param listContent
 * @param btnOpts
 */
function deletePopup(title, headingContent, subHeadingContent, listContent, btnOpts) {
    var $footer = $("<div>").append(
        $("<button>")
            .prop("disabled", true)
            .addClass("btn btn-confirm-delete")
            .addClass(typeof btnOpts.affirmative.class == 'undefined' ? "" : btnOpts.affirmative.class)
            .on("click", function () {
                if (typeof btnOpts.affirmative.callback != 'undefined' && $.isFunction(btnOpts.affirmative.callback)) {
                    btnOpts.affirmative.callback();
                }
            })
            .attr("data-dismiss", function () {
                return typeof btnOpts.affirmative.dismiss != 'undefined' && btnOpts.affirmative.dismiss == true ? "modal" : "";
            })
            .text(typeof btnOpts.affirmative.text != 'undefined' ? btnOpts.affirmative.text : 'OK'),
        $("<button>")
            .addClass("btn")
            .addClass(typeof btnOpts.negative.class == 'undefined' ? "" : btnOpts.negative.class)
            .on("click", function () {
                if (typeof btnOpts.negative.callback != 'undefined' && $.isFunction(btnOpts.negative.callback)) {
                    btnOpts.negative.callback();
                }
            })
            .attr("data-dismiss", function () {
                return typeof btnOpts.negative.dismiss != 'undefined' && btnOpts.negative.dismiss == true ? "modal" : "";
            })
            .text(typeof btnOpts.negative.text != 'undefined' ? btnOpts.negative.text : 'CANCEL')
    );
    var $warningList = $("<div>").addClass("warning-list");
    $.each(listContent, function (index, listItem) {
        $warningList.append(
            $("<div>").append(
                $("<i>").addClass("fa fa-times text-danger"),
                "&nbsp;&nbsp;&nbsp;&nbsp;",
                listItem
            )
        );
    });

    var $bodyContent = $("<div>").append(
        $("<button>").addClass("close").attr({
            "type": "button",
            "data-dismiss": "modal",
            "aria-label": "Close"
        }).append(
            $("<span>").attr("aria-hidden", "true").html("&times;")
        ),
        $("<div>").addClass("delete-popup-content-container").append(
            $("<h3>").text(headingContent).addClass("text-center delete-popup-heading m-b-20"),
            $("<p>").text(subHeadingContent).addClass("delete-popup-subheading m-b-20"),
            $("<div>").addClass("row delete-popup-list m-b-20").append(
                $("<div>").addClass("col-sm-12").append(
                    $warningList
                )
            ),
            $("<div>").addClass("checkbox").append(
                $("<label>").append(
                    $("<input>").addClass("chk-confirm-delete").attr({
                        "type": "checkbox",
                        "onclick": "updateDeletePopupButtonStatus(this);"
                    }),
                    "&nbsp;&nbsp;I have read and understood that this action cannot be undone."
                )
            )
        )
    ).html();

    var $modal = popupHTMLNoHeader(title, $bodyContent, $footer);
    $modal.modal();

    $modal.on("hidden.bs.modal", function () {
        $("body").css("padding-right", "");
        $modal.remove();
    })
}

function updateDeletePopupButtonStatus(el) {
    var $modal = $(el).closest(".modal");
    var $chk = $modal.find(".chk-confirm-delete");
    var $button = $modal.find(".btn-confirm-delete");
    $button.prop("disabled", !$chk.is(":checked"));
}

/**
 *
 * @param title
 * @param $content
 * @param $footer
 * @param dialogSize
 * @returns {*|jQuery}
 */
function popupHTMLNoHeader(title, $content, $footer, dialogSize) {
    if (typeof $footer == 'undefined' || $footer == null) {
        $footer = $("<button>").addClass("btn").attr({
            "type": "button",
            "data-dismiss": "modal"
        }).text("OK");
    }
    if (typeof dialogSize == "undefined") {
        dialogSize = "";
    } else {
        switch (dialogSize) {
            case "lg":
                dialogSize = "modal-lg";
                break;
            case "md":
                dialogSize = "";
                break;
            case "sm":
                dialogSize = "modal-sm";
                break;
            default:
        }
    }
    var $modal = popupFrameNoHeader($content, $footer);
    $modal.find(".modal-dialog").addClass(dialogSize);
    return $modal;
}

/**
 *
 * @param $content
 * @param $footer
 * @returns {*|jQuery}
 */
function popupFrameNoHeader($content, $footer) {
    return $("<div>").attr("id", randomString(10)).addClass("modal fade popup").append(
        $("<div>").addClass("modal-dialog").append(
            $("<div>").addClass("modal-content").append(
                typeof $content != 'undefined' ?
                    $("<div>").addClass("modal-body").append(
                        $content
                    ) : '',
                typeof $footer != 'undefined' ?
                    $("<div>").addClass("modal-footer").append(
                        $footer
                    ) : ''
            )
        )
    );
}


function serialize(obj) {
    var str = [];
    for(var p in obj)
        if (obj.hasOwnProperty(p)) {
            str.push(encodeURIComponent(p) + "=" + encodeURIComponent(obj[p]));
        }
    return str.join("&");
}