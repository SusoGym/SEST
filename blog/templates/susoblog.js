var Suso = {

    permissions: [],

    /** Basic stuff **/
    initialize: function () {
        String.prototype.replaceAll = function (target, replacement) {
            return this.split(target).join(replacement);
        };

        SusoBlogAPI._handleError = function (code, message, ctx) {
            Materialize.toast('[' + code + '] ' + message, 2000);
            console.error('Error while doing ' + ctx + ' [' + code + '] ' + message);
        };

        jQuery.loadScript = function (url, callback) {
            jQuery.ajax({
                url: url,
                dataType: 'script',
                success: callback,
                async: true
            });
        };

        if (new URLSearchParams(window.location.search).has("destroy")) {
            this._removeCookie();
            Cookies.remove('PHPSESSID');
            window.location = "./";
            return;
        }

        this.loadPage(true);

        console.info(SusoBlogAPI.accessToken);

        if (SusoBlogAPI.accessToken === null) {
            console.info("No accessToken");
            var blog = this;

            SusoBlogAPI.createTokenFromSession(function (data) {
                if(data.code === 200)
                {
                    SusoBlogAPI.accessToken = data.payload.authToken;
                    var expire = new Date(data.payload.expire);
                    blog._setAuthToken(SusoBlogAPI.accessToken, expire);
                    blog.loadHtmlByPermission();
                } else {
                    Suso.loadPage(false);
                }
            });
        } else {
            this.loadHtmlByPermission();
        }

        this._initializeOnClick();


    },
    /** Utility **/
    _initializeOnClick: function () {
        $('.suso-replace#login_form').attr('onSubmit', 'Suso._createTokenLogin()');
        $('.suso-replace#login_btn').attr('onClick', 'Suso._createTokenLogin()');
        $('.suso-replace#logout').attr('onClick', 'Suso.logout()');
    },
    loadHtmlByPermission: function () {
        var permissions = "1,2,4,8,16,512,1024,2048";

        SusoBlogAPI.hasPermission(function (data) {
            var success = data.success; // we have power!
            if (success) {
                $.loadScript("templates/susoblog-editor.js", function () {
                    SusoEditor.initialize();
                });
            } else {
                Suso.loadPage(false);
            }

        }, {permission: permissions});

        permissions = "1,32,64,128,256";

        SusoBlogAPI.hasPermission(function (data) {
            var success = data.success; // we have power!
            if (success) {
                $.loadScript("templates/susoblog-administration.js", function () {
                    SusoAdmin.initialize();
                });
            } else {
                Suso.loadPage(false);
            }

        }, {permission: permissions});
    },
    _putDivAroundIframe: function (txt) {

        var arr = txt.match(/(<iframe.*?<\/iframe>)/g);

        var replaced = [];

        if (typeof arr !== 'undefined' && arr !== null) { // we have an iFrame! yay
            arr.forEach(function (element, index, array) {

                if ($.inArray(element, replaced) !== -1)
                    return;

                replaced.push(element);

                txt = txt.replaceAll(element, "<div class='embed-container'>" + element + "</div>");
            });
        }

        return txt;
    },

    loadPage: function (minifiy) {
        this._checkOrCreateToken(minifiy);
        this._fetchPosts();

        if (minifiy === undefined || minifiy == false) {
            $('#entry').hide();
            $('#newdate').hide();
            $('[permission]').hide();
            $('.modal').modal();
            $('.collapsible').collapsible();
        }

    },
    logout: function () {
        this._removeCookie();
        Cookies.remove('PHPSESSID');
        Materialize.toast('Erfolgreich abgemeldet!', 2000);

        this._checkOrCreateToken();
    },
    _setAuthToken: function (token, expire) {
        if (token !== null && (typeof token) !== "undefined") {
            var cookie = {token: token, expire: expire};

            Cookies.set('auth', cookie);

            SusoBlogAPI.accessToken = token;
        }
        this._managePermissionToSee();
    },
    _getAuthToken: function () {
        var cookie = Cookies.getJSON('auth');
        var token = null;
        if (cookie !== null && !(typeof  cookie === 'undefined')) {
            if (new Date(cookie.expire) > new Date()) {
                token = cookie.token;
            } else {
                cookie = null;
                this._removeCookie();

            }
        }

        SusoBlogAPI.accessToken = token;
        return token;
    },
    _removeCookie: function () {
        Cookies.remove('auth');
    },
    /** Api calls **/
    _fetchPosts: function () {
        var instance = this;
        SusoBlogAPI.fetchPosts(function (posts) {
            $('#blog-placeholder').empty();
            if (posts === null) {
                Materialize.toast('Es wurden keine Einträge gefunden.');
                return;
            }
            var lastDate;


            posts.forEach(function (element, index, array) {
                if (index === 0) {
                    lastDate = new Date(0);

                } else {
                    if (array[index - 1].releaseDate) {
                        lastDate = new Date(array[index - 1].releaseDate);
                    }
                    else {
                        lastDate = new Date(0);
                    }
                }

                var date = new Date(element.releaseDate);
                var placeHolder = $('#blog-placeholder');
                if (lastDate.getUTCFullYear() + '-' + lastDate.getUTCMonth() + '-' + lastDate.getUTCDate() !== date.getUTCFullYear() + '-' + date.getUTCMonth() + '-' + date.getUTCDate()) {

                    var newdate = $('#newdate').clone();
                    newdate.attr('id', 'newdate' + element.id);

                    placeHolder.append(newdate);
                    $('#newdate' + element.id + ' #dateText').text(date.getDate() + '. ' + (date.getMonth() + 1) + '. ' + date.getFullYear() + ':');

                    $('#newdate' + element.id).show();

                }

                var card = $('#entry').clone();
                card.attr('id', 'entry' + element.id);
                placeHolder.append(card);

                var body = instance._putDivAroundIframe(element.body);

                if (element.authorObject == null) {
                    $('#entry' + element.id + ' #author').text("Unknown");
                } else if (element.authorObject.displayName == null) {
                    $('#entry' + element.id + ' #author').text(element.authorObject.username);
                } else {
                    $('#entry' + element.id + ' #author').text(element.authorObject.displayName);
                }

                var hours, mins;
                if (date.getHours() < 10) {
                    hours = '0' + date.getHours();
                } else {
                    hours = date.getHours();
                }
                if (date.getMinutes() < 10) {
                    mins = '0' + date.getMinutes();
                } else {
                    mins = date.getMinutes();
                }

                var datestring = hours + ':' + mins + ' Uhr';
                $('#entry' + element.id + ' #date').text(datestring);
                $('#entry' + element.id + ' #title').text(element.subject);
                $('#entry' + element.id + ' #body').html(body);
                $('#entry' + element.id + ' #delete').attr('onClick', 'SusoEditor.confirmDeleteModal(' + element.id + ')');
                $('#entry' + element.id + ' #edit').attr('onClick', 'SusoEditor.editModal(' + element.id + ')');

                $('#entry' + element.id).show();

                lastDate = date;

            });
        })
    },
    _checkOrCreateToken: function (minify) {

        var token = this._getAuthToken();
        if (token === null) {
            SusoBlogAPI.createTokenFromSession(function (data) {
                var token = data.authToken;
                var expire = new Date(data.expire);
                Suso._setAuthToken(token, expire, false);
            }, true);
        } else if (!minify === undefined || !minify) {
            this._managePermissionToSee();
        }
    },
    _createTokenLogin: function () {
        var pwd = $('#pwd_login').val();
        var usr = $('#usr_login').val().replace(/ /g, '');

        SusoBlogAPI.createToken(function (data) {
            if (data.code === 200) {
                data = data.payload;
                var token = data.authToken;
                var expire = new Date(data.expire);
                Suso._setAuthToken(token, expire);

                Materialize.toast('Erfolgreich angemeldet!', 4000);
                $('.modal').modal('close');

            } else if (data.code === 401) {

                Materialize.toast("Email-Addresse oder Passwort falsch", 4000);
                $('#pwd_login').val("");
                $('label[for="pwd_login"]').removeClass("active");

            } else {

                Materialize.toast("Unexpected response: " + data, 4000);
                console.info("Unexcpected response: ");
                console.info(data);
                $('#pwd_login').val("");

                $('label[for="pwd_login"]').removeClass("active");
            }

            Suso.loadHtmlByPermission();
        }, {username: usr, password: pwd}, true);
    },
    _managePermissionToSee: function () {
        $('[permission]').hide();

        SusoBlogAPI.getPermissions(function (perms) {
            for (var key in perms) {
                if (perms.hasOwnProperty(key)) {
                    Suso.permissions[key] = perms[key];
                }
            }

            SusoBlogAPI.getUserInfo(function (data) {
                if (data.code === 200) {
                    $('.loginbtn').hide();
                    $('.logoutbtn').show().text(data.username);

                    var userPermission = data.payload.permission;

                    for (var permKey in Suso.permissions) {
                        var permVal = Suso.permissions[permKey];
                        var hasPermission = (userPermission & permVal) === permVal;

                        if (!hasPermission) {
                            continue;
                        }

                        if (permKey === "PERMISSION_EVERYTHING") {
                            $('[permission]').show();
                        }
                        var fields = $('[permission~="' + permKey + '"]');
                        fields.show();
                        $('.modal').modal();

                    }

                } else {
                    $('.loginbtn').show();
                    $('.logoutbtn').hide();
                }

            }, {auth_token: SusoBlogAPI.accessToken}, true);
        });

    }
};