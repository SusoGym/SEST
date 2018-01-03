var SusoAdmin = {
        initialize: function () {

            if (!$('#admin-choosemodal').length) { // check if already exists
                $.get('templates/module-administration.html', function (data) {
                    $('body').append(data);
                    $.loadScript("./templates/jquery.autocomplete.min.js", function () {
                        SusoAdmin.loadAdminArea();
                    });
                });
            } else {
                SusoAdmin.loadAdminArea();
            }
        },
        loadAdminArea: function () {
            //nav-mobile | right-nav  nav-placeholder-mobile | nav-placeholder

            if (!$('#open_adminarea').length) {
                $('#right-nav').prepend("<li id='open_adminarea' style='display: list-item;' permission='PERMISSION_CHANGE_PERMISSION PERMISSION_CHANGE_ALL_PERMISSION PERMISSION_CHANGE_DISPLAYNAME PERMISSION_CHANGE_DISPLAYNAME_OTHER'><a href='javascript:void(0);' onclick='SusoAdmin.open()'><span>Admin Area</span><i class='material-icons right'>adb</i> </a></li>");
            }
            /*$('#admin-autocomplete-name').devbridgeAutocomplete({serviceUrl:"index.php?console&action=searchUsers&raw=true&auth_token=" + SusoBlogAPI.accessToken, type:'GET', onSelect: function () {

            }, showNoSuggestionNotice: true, noSuggestionNotice:"Sad"})*/

            ajaxAutoComplete({
                ajaxURL: "index.php?console&action=searchUsers&auth_token=" + SusoBlogAPI.accessToken + "&query=",
                inputId: 'admin-autocomplete-name',
                minLength: 2
            });
            $('#admin-autocomplete-name').keyup(function (event) {
                if (event.keyCode === 13) {
                    $('#chooseuserbtn').click();
                }
            });

        },
        open: function () {
            SusoBlogAPI.getUserInfo(function (data) {
                var perms = data.permission;

                function hasPermission(perm) {
                    return (perms & perm) === perm;
                }

                if (hasPermission(1) || hasPermission(64) || hasPermission(256)) { // may edit other users
                    $('#admin-choosemodal').modal('open');

                } else {
                    $('#admin-autocomplete-name').val(data.username);
                    SusoAdmin.editUser();
                }
            }, {auth_token: SusoBlogAPI.accessToken});
        },
        editUser: function () {

            var name = $('#admin-autocomplete-name').val();
            $('#admin-autocomplete-name').val("");

            SusoBlogAPI.searchUsers(function (data) {
                if (typeof data === 'undefined' || data === null || data.length === 0) {
                    Materialize.toast("Der angegebende Nutzer konnte nicht gefunden werden.", 2000);
                    return;
                }

                var user = data[0];
                SusoAdmin.setupEditModal(user, function () {
                    $('#admin-choosemodal').modal('close');
                    $('#admin-editusermodal').modal('open');
                });

            }, {query: name});
        },
        setupEditModal: function (user, callback) {

            var name = (user.displayname === null || user.displayname === "") ? user.username : user.displayname;
            $('#edituser-username').text(name);

            if (user.displayname !== null && user.displayname !== "") {
                $('#displayname-field').val(user.displayname);
                $('#displayname-field-label').addClass('active')
            } else {
                $('#displayname-field').val("");
                $('#displayname-field-label').removeClass('active')
            }

            SusoBlogAPI.getPermissions(function (permissions) {

                var grid = $('#userpermissions');
                $('#hiddenusername').text(user.username);

                var userPerm = user.permission;


                SusoBlogAPI.getUserInfo(function (data) {

                    var execPerm = data.permission;

                    var canChangeDisplayName = (execPerm & 1) === 1 || (execPerm & 128) === 128 || (execPerm & 256) === 256;

                    $('#displayname-field').attr('disabled', !canChangeDisplayName);

                    grid.empty();

                    for (var perm in permissions) {

                        var name = perm.substring(11).replaceAll("_", " ");
                        grid.append('<div class="col s6"><input type="checkbox" id="' + perm + '" value="' + perm + '" /><label class="black-text" for="' + perm + '">' + name + '</label></div>');

                        var permVal = permissions[perm];

                        if ((userPerm & permVal) === permVal) {
                            $('#' + perm).prop('checked', true);
                        }

                        if ((execPerm & permVal) !== permVal && ((execPerm & 64) !== 64) && ((execPerm & 1) !== 1)) {
                            $('#' + perm).attr('disabled', true);
                        }

                    }


                    callback();

                }, {auth_token: SusoBlogAPI.accessToken})

            });

        },
        sendEditUser: function () {
            var username = $('#hiddenusername').text();

            SusoBlogAPI.getUserInfo(function (data) {
                SusoBlogAPI.getPermissions(function (perms) {
                    for (var perm in perms) {
                        var val = perms[perm];
                        if ((data.permission & val) === val || (data.permission & 1) === 1) {
                            var allowed = $('#' + perm)[0].checked;
                            allowed = allowed ? 1 : 0;
                            SusoBlogAPI.changePermission(function () {

                            }, {username: username, permission: val, value: allowed})
                        }
                    }

                    var displayname = $('#displayname-field').val();

                    if (displayname !== "") {
                        if ((username !== data.username && (data.permission & 256) === 256) || (username === data.username && ((data.permission & 128) === 128 || (data.permission & 256) === 256))
                            || (data.permission & 1) === 1) {
                            SusoBlogAPI.changeDisplayName(function (data) {
                                //done
                                $('#admin-editusermodal').modal('close');
                                Suso.loadPage(true); // reload page
                            }, {displayName: displayname, username: username});
                        }
                    } else {
                        $('#admin-editusermodal').modal('close');
                    }

                });
            }, {auth_token: SusoBlogAPI.accessToken});
        }

    }
;