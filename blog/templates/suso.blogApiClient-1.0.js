var SusoBlogAPI =
    {
        debug: false,
        accessToken: null,
        apiEndpoint: "",


        /** Public Methods **/

        /**
         * Creates a new post
         * @param callback function
         * @param data array {subject, body [, auth_token, releaseDate]}
         */
        addPost: function (callback, data) {

            if (this.accessToken !== null) {
                data.auth_token = this.accessToken;
            }

            this._doApiRequest("addPost", function (data) {
                callback(data.payload);
            }, data);
        },
        /**
         * Fetches all posts
         * @param callback function
         * @param data array {[startDate, endDate]} -> Date in YYYY-MM-DD HH:mm:ss
         */
        fetchPosts: function (callback, data) {

            this._doApiRequest("fetchPosts", function (data) {
                callback(data.payload);
            }, data);

        },

        /**
         * Edits an existing post
         * @param callback function
         * @param data array {postId [, auth_token, body, subject, author, releaseDate]}
         */
        editPost: function (callback, data) {
            if (this.accessToken !== null) {
                data.auth_token = this.accessToken;
            }
            this._doApiRequest("editPost", function (data) {
                callback(data.payload);
            }, data);
        },
        /**
         * Deletes a post
         * @param callback function
         * @param data array {postId [, auth_token]}
         */
        deletePost: function (callback, data) {
            if (this.accessToken !== null) {
                data.auth_token = this.accessToken;
            }
            this._doApiRequest("deletePost", function (data) {
                callback(data.payload);
            }, data);
        },
        /**
         * Returns information about a user
         * @param callback function
         * @param data array {auth_token}, {userId}, {username}
         * @param raw bool returns raw user info
         */
        getUserInfo: function (callback, data, raw) {

            data._raw = raw;

            this._doApiRequest("getUserInfo", function (data) {
                if (raw) {
                    callback(data);
                } else {
                    callback(data.payload);
                }
            }, data);

        },
        /**
         * Generates an access token from session
         * @param callback function
         * @param raw bool returns raw api response
         */
        createTokenFromSession: function (callback, raw) {
            this._doApiRequest("createTokenFromSession", function (data) {
                if (!raw) {
                    callback(data.payload);
                } else {
                    callback(data);
                }
            }, {_raw: raw});
        },
        /**
         * Generates an access token from input
         * @param callback function
         * @param data array {username, password}
         * @param raw bool returns raw api response
         */
        createToken: function (callback, data, raw) {

            data._raw = raw;

            this._doApiRequest("createToken", function (data) {
                if (!raw) {
                    callback(data.payload);
                } else {
                    callback(data);
                }
            }, data);

        },

        /**
         * Returns all permission and their bitwise value
         * @param callback function
         */
        getPermissions: function (callback) {
            this._doApiRequest("getPermissions", function (data) {
                callback(data.payload);
            })
        },

        /**
         * Returns whether or not a user has a specific permission
         * @param callback function
         * @param data array {permission, {[auth_token], [userId], [username]}}
         */
        hasPermission: function (callback, data) {
            if (this.accessToken !== null) {
                data.auth_token = this.accessToken;
            }

            this._doApiRequest("hasPermission", function (data) {
                callback(data.payload);
            }, data);
        },

        /**
         * Changes a users permission
         * @param callback function
         * @param data array {permission, value, {[user_auth_token], [userId], [username]}, [auth_token]}
         */
        changePermission: function (callback, data) {
            if (this.accessToken !== null) {
                data.auth_token = this.accessToken;
            }

            this._doApiRequest("changePermissions", function (data) {
                callback(data.payload);
            }, data);

        },
        /**
         * Changes a users display name
         * @param callback function
         * @param data array {displayName, {[user_auth_token], [userId], [username]}, [auth_token]}
         */
        changeDisplayName: function (callback, data) {
            if (this.accessToken !== null) {
                data.auth_token = this.accessToken;
            }
            this._doApiRequest("changeDisplayName", function (data) {
                callback(data.payload);
            }, data);
        },


        /** Private Methods **/

        _handleError: function (code, message, ctx) {

            if (ctx !== null) {
                ctx = "while doing " + ctx + " ";
            }

            console.error("Encountered error " + ctx + "(" + code + "): " + message);
        },

        _doApiRequest: function (action, callback, parameters) {

            if (typeof parameters === 'undefined') {
                parameters = {_raw: false};
            }

            var url = this.apiEndpoint + "?console&action=" + action;
            this.log("Accessing " + url);

            var api = this;

            $.ajax(url, {
                method: 'POST',
                data: parameters
            }).done(function (data) {
                if (data.code !== 200 && !parameters._raw) {
                    api._handleError(data.code, data.message, "APIRequest to " + url);
                } else {
                    callback(data);
                }
            });

        }
        ,

        log: function (msg) {
            if (this.debug) {
                console.log(msg);
            }

        }
    }
;
