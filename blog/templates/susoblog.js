var Suso = {
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
            removeCookie();
            Cookies.remove('PHPSESSID');
            window.location = "./";
        }

        this._initializeCKEditor();
    },
    _initializeCKEditor: function () {
        var plugins = ['videoembed', 'niftytimers', 'chart', 'autocorrect', 'autogrow', 'emojione', 'floating-tools', 'lineheight', 'wordcount'];
        var dependencies = ['widgetselection', 'lineutils', 'widget'];

        CKEDITOR.config.removePlugins = "print,newpage,preview,div,flash,forms,smiley,maximize,language";

        CKEDITOR.scayt_multiLanguageMode = true;
        CKEDITOR.config.scayt_sLang = "de_DE";
        CKEDITOR.config.scayt_autoStartup = true;

        CKEDITOR.config.niftyTimer = {widgetKey: '2e27b278-02ad-4e86-96bb-074dd2cad79c'};

        var baseDir = '/blog/ckeditor/';

        var pluginStr = '';
        dependencies.forEach(function (value) {
            var url = baseDir + value + '/';
            CKEDITOR.plugins.addExternal(value, url, 'plugin.js');
        });

        plugins.forEach(function (value) {
            var url = baseDir + value + '/';
            CKEDITOR.plugins.addExternal(value, url, 'plugin.js');

            if (pluginStr !== '') {
                pluginStr += ',';
            }

            pluginStr += value;

        });


        this._initializeOnClick();

        CKEDITOR.replace('createText', {
            extraPlugins: pluginStr, language: 'de', extraAllowedContent: 'br'
        });
        CKEDITOR.replace('editText', {
            extraPlugins: pluginStr, language: 'de', extraAllowedContent: 'br'
        });
    },
    /** Modals **/
    confirmDeleteModal: function (id) {
        $('#confirmdelete #deletebtn').attr('onclick', 'Suso.deletePost(' + id + ')');
        $('#confirmdelete').modal('open');
    },
    editModal: function (id) {
        SusoBlogAPI.fetchPost(function (data) {

            var subject = data.subject;
            var body = data.body;

            $('#editPost #editBtn').attr('onclick', 'Suso.editPost(' + id + ')');
            $('#editPost #editTitle').val(subject);
            CKEDITOR.instances.editText.setData(body);

            $('#editPost').modal('open');

            Materialize.updateTextFields();
        }, {postId: id});
    },
    /** Utility **/
    _initializeOnClick: function () {
        $('.suso-replace#login_form').attr('onSubmit', 'Suso._createTokenLogin()');
        $('.suso-replace#login_btn').attr('onClick', 'Suso._createTokenLogin()');
        $('.suso-replace#createForm').attr('onSubmit', 'Suso.createPost()');
        $('.suso-replace#delete').attr('onClick', 'Suso.deletePost()');
        $('.suso-replace#edit').attr('onClick', 'Suso.editPost()');
        $('.suso-replace#logout').attr('onClick', 'Suso.logout()');
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
    loadPage: function () {
        this._checkOrCreateToken();
        this._fetchPosts();
        $('#entry').hide();
        $('#newdate').hide();
        $('[permission]').hide();
        $('.modal').modal();
        $('.collapsible').collapsible();
    },
    logout: function () {
        this._removeCookie();
        Cookies.remove('PHPSESSID');
        Materialize.toast('Erfolgreich abgemeldet!', 2000);

        this._checkOrCreateToken();
    },
    _toMYSQLDate: function (d) {

        a = d.getFullYear();
        b = d.getMonth();
        if (b < 10) {
            b = '0' + b;
        }
        c = d.getDate();
        if (c < 10) {
            c = '0' + c;
        }
        e = d.getHours();
        if (e < 10) {
            e = '0' + e;
        }
        f = d.getMinutes();
        if (f < 10) {
            f = '0' + f;
        }
        g = d.getSeconds();
        if (g < 10) {
            g = '0' + g;
        }
        return a + '-' + b + '-' + c + ' ' + e + ':' + f + ':' + g;
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
                    $('#newdate' + element.id + ' #date').text(date.getDate() + '. ' + (date.getMonth() + 1) + '. ' + date.getFullYear() + ':');

                    $('#newdate' + element.id).show();

                }

                var card = $('#entry').clone();
                card.attr('id', 'entry' + element.id);
                placeHolder.append(card);

                var body = instance._putDivAroundIframe(element.body);

                if (element.authorObject.displayName === null || element.authorObject.displayName === "") {
                    $('#entry' + element.id + ' #author').text("Unknown");
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
                $('#entry' + element.id + ' #delete').attr('onclick', 'Suso.confirmDeleteModal(' + element.id + ')');
                $('#entry' + element.id + ' #edit').attr('onclick', 'Suso.editModal(' + element.id + ')');

                $('#entry' + element.id).show();

                lastDate = date;

            });
        })
    },
    createPost: function () {
        var title = $('#createTitle').val();
        var text = CKEDITOR.instances.createText.getSnapshot();
        var releaseDate = this._toMYSQLDate(new Date());

        SusoBlogAPI.addPost(function () {
            Materialize.toast('Post hinzugefügt', 2000);
            $('#createForm').find("input[type=text], textarea").val("");
            if (CKEDITOR.instances.createText) {

                CKEDITOR.instances.createText.setData('');
            }
            $('.collapsible').collapsible('close', 0);
            Suso._fetchPosts();
        }, {subject: title, body: text, releaseDate: releaseDate});
    },
    editPost: function (id) {
        var title = $('#editPost#editTitle').val();
        var text = '';
        CKEDITOR.instances.editText.getSnapshot();

        SusoBlogAPI.editPost(function () {
            Materialize.toast('Post erfolgreich bearbeitet', 2000);
            Suso._fetchPosts();
        }, {subject: title, body: text, postId: id});
    },
    deletePost: function (id) {
        SusoBlogAPI.deletePost(function () {
            Materialize.toast('Post erfolgreich gelöscht', 2000);
            Suso._fetchPosts();
        }, {postId: id});
    },
    _checkOrCreateToken: function () {

        var token = this._getAuthToken();
        if (token === null) {
            SusoBlogAPI.createTokenFromSession(function (data) {
                var token = data.authToken;
                var expire = new Date(data.expire);
                Suso._setAuthToken(token, expire, false);
            }, true);
        } else {
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

            Suso.loadPage();
        }, {username: usr, password: pwd}, true);
    },
    _managePermissionToSee: function () {
        $('[permission]').hide();
        var permissions = [];

        SusoBlogAPI.getPermissions(function (perms) {
            for (var key in perms) {
                if (perms.hasOwnProperty(key)) {
                    permissions[key] = perms[key];
                }
            }

            SusoBlogAPI.getUserInfo(function (data) {
                if (data.code === 200) {
                    $('.loginbtn').hide();
                    $('.logoutbtn').show().text(data.username);

                    var userPermission = data.payload.permission;

                    for (var permKey in permissions) {
                        var permVal = permissions[permKey];
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