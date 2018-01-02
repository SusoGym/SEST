var SusoEditor = {
    visibility: "publish",

    initialize: function () {

        $.get('templates/module-editor.html', function (data) {
            $('body').append(data);
            $.get('templates/module-editor-ckeditor.html', function (data) {
                $('.container').prepend(data);
                SusoEditor._initializeCKEditor();
                SusoEditor._initializeOnClick();
                SusoEditor.loadPage(false);
            });
        });


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
        CKEDITOR.replace('editDraftText', {
            extraPlugins: pluginStr, language: 'de', extraAllowedContent: 'br'
        });
    },
    /** Modals **/
    confirmDeleteModal: function (id) {
        $('#confirmdelete #deletebtn').attr('onclick', 'SusoEditor.deletePost(' + id + ')');
        $('#confirmdelete').modal('open');
    },
    editModal: function (id) {
        SusoBlogAPI.fetchPost(function (data) {

            var subject = data.subject;
            var body = data.body;

            $('#editPost #editBtn').attr('onclick', 'SusoEditor.editPost(' + id + ')');
            $('#editPost #editTitle').val(subject);
            CKEDITOR.instances.editText.setData(body);

            $('#editPost').modal('open');

            Materialize.updateTextFields();
        }, {postId: id});
    },
    confirmDeleteDraftModal: function (id) {
        $('#confirmdeletedraft #deletedraftbtn').attr('onclick', 'SusoEditor.deleteDraft(' + id + ')');
        $('#confirmdeletedraft').modal('open');
    },
    editDraftModal: function (id) {
        SusoBlogAPI.fetchDraft(function (data) {

            var subject = data.subject;
            var body = data.body;

            $('#editDraft #editDraftBtn').attr('onclick', 'SusoEditor.editDraft(' + id + ')');
            $('#editDraft #editDraftTitle').val(subject);
            CKEDITOR.instances.editDraftText.setData(body);

            $('#editDraft').modal('open');

            Materialize.updateTextFields();
        }, {draft_id: id});
    },
    publishDraftModal: function (id) {
        $('#publishdraft #publishdraftbtn').attr('onClick', 'SusoEditor.publishDraft(' + id + ')');
        $('#publishdraft').modal('open');
    },
    _initializeOnClick: function () {

        $('.suso-replace #createDraft').attr('onClick', 'SusoEditor.createDraft();');
        $('.suso-replace#createForm').attr('onSubmit', 'SusoEditor.createPost();');
        $('.suso-replace#delete').attr('onClick', 'SusoEditor.deletePost()');
        $(".suso-replace.visibility-changer#publish").attr('onClick', 'SusoEditor.changeVisibility("publish")');
        $(".suso-replace.visibility-changer#draft").attr('onClick', 'SusoEditor.changeVisibility("draft")');
    },
    changeVisibility: function (invoker) {
        $('#createPost').collapsible('close', 0);
        if (invoker === "publish") {
            this.visibility = "draft";
        } else {
            this.visibility = "publish";
        }

        SusoEditor.loadPage(true);
    },
    handleVisibilityButton: function () {
        $(".suso-replace.visibility-changer").hide();
        $(".suso-replace.visibility-changer#" + this.visibility).show();
        window.scrollTo(0, 0);
    },
    loadPage: function (minifiy) {
        Suso._checkOrCreateToken(minifiy);
        this.handleVisibilityButton();

        switch (this.visibility) {
            case "draft":
                this._fetchDrafts();
                break;
            default:
                Suso.loadPage();
                return;
                break;
        }

        if (minifiy === undefined || minifiy == false) {
            $('#entry').hide();
            $('#newdate').hide();
            $('[permission]').hide();
            $('.modal').modal();
            $('.collapsible').collapsible();
        }

    },
    _fetchDrafts: function () {
        var instance = Suso;
        SusoBlogAPI.fetchDrafts(function (posts) {
            var placeHolder = $('#blog-placeholder');
            placeHolder.empty();


            if (posts === null) {
                Materialize.toast('Es wurden keine Einträge gefunden.');
                return;
            }

            posts.reverse();


            posts.forEach(function (element) {

                var card = $('#entry').clone();
                card.attr('id', 'entry' + element.id);
                placeHolder.append(card);

                var jTitle = $('#entry' + element.id + ' #title');

                var body = element.body;
                var title = element.subject;

                if (body === null || body === "" || body === "<p><br></p>") {
                    body = "<p class='red-text'>Inhalt nicht gesetzt</p>";
                }

                if (title === null) {
                    title = "Titel nicht gesetzt";
                    jTitle.addClass("red-text");
                }

                body = instance._putDivAroundIframe(body);


                if (element.authorId === null) {
                    var subtitle = $('#entry' + element.id + ' #subtitle');
                    subtitle.text("Author nicht gesetzt");
                    subtitle.removeClass("grey-text");
                    subtitle.addClass("red-text");
                } else {
                    $('#entry' + element.id + ' #subtitle').text(element.authorObject.username);
                }

                jTitle.text(title);
                var submit = $('#entry' + element.id + ' #publish');

                $('#entry' + element.id + ' #body').html(body);

                $('#entry' + element.id + ' #delete').attr('onclick', 'SusoEditor.confirmDeleteDraftModal(' + element.id + ')');
                $('#entry' + element.id + ' #edit').attr('onclick', 'SusoEditor.editDraftModal(' + element.id + ')');
                submit.attr('onclick', 'SusoEditor.publishDraftModal(' + element.id + ')');

                submit.show();

                $('#entry' + element.id).show();

            });

            placeHolder.append('<div class="watermark" style="font-size: 400%">Entwurf</div>');

        })
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
            SusoEditor.loadPage(true);
        }, {subject: title, body: text, releaseDate: releaseDate});
    },
    editPost: function (id) {
        var title = $('#editPost #editTitle').val();
        var text = CKEDITOR.instances.editText.getSnapshot();
        SusoBlogAPI.editPost(function () {
            Materialize.toast('Post erfolgreich bearbeitet', 2000);
            SusoEditor.loadPage(true);
        }, {subject: title, body: text, postId: id});
    },
    deletePost: function (id) {
        SusoBlogAPI.deletePost(function () {
            Materialize.toast('Post erfolgreich gelöscht', 2000);
            SusoEditor.loadPage(true);
        }, {postId: id});
    },
    createDraft: function () {
        var title = $('#createTitle').val();
        var text = CKEDITOR.instances.createText.getSnapshot();
        var releaseDate = this._toMYSQLDate(new Date());

        SusoBlogAPI.addDraft(function () {
            Materialize.toast('Entwurf gespeichert', 2000);
            $('#createForm').find("input[type=text], textarea").val("");
            if (CKEDITOR.instances.createText) {

                CKEDITOR.instances.createText.setData('');
            }
            $('.collapsible').collapsible('close', 0);
            SusoEditor.loadPage(true);
        }, {subject: title, body: text, releaseDate: releaseDate});
    },

    deleteDraft: function (id) {
        SusoBlogAPI.deleteDraft(function (data) {
            Materialize.toast('Entwurf erfolgreich gelöscht', 2000);
            SusoEditor.loadPage(true);
        }, {draft_id: id});
    },
    editDraft: function (id) {
        var title = $('#editDraft #editDraftTitle').val();
        var text = CKEDITOR.instances.editDraftText.getSnapshot();

        var data = {draft_id: id, subject: title, body: text};

        SusoBlogAPI.editDraft(function (data) {
            Materialize.toast('Entwurf erfolgreich bearbeitet', 2000);
            SusoEditor.loadPage(true);
        }, data);
    },
    publishDraft: function (id) {
        SusoBlogAPI.publishDraft(function (data) {
            Materialize.toast('Entwurf erfolgreich veröffentlicht', 2000);
            SusoEditor.loadPage(true);
        }, {draft_id: id});

    }


};