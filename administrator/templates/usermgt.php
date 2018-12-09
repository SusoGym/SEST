<?php namespace administrator;
    include("header.php");
    $data = \View::getInstance()->getDataForView();
?>

<div class="container">

    <div class="card ">
        <div class="card-content">
          <span class="card-title">
            <?php if (isset($data["backButton"])) { ?>
                <a id="backButton" class="mdl-navigation__link waves-effect waves-light teal-text"
                   href="<?php echo $data["backButton"]; ?>"><i
                            class="material-icons">chevron_left</i></a>
            <?php } ?>
              <?php echo \View::getInstance()->getTitle(); ?>
          </span>
            <form autocomplete="off" method="GET">
                <input type="hidden" name="type" value="usredit">
                <input type="text" id="autocomplete-input" name="name">
                <button class="btn waves-effect waves-light" type="submit">Suchen
                    <i class="material-icons right">search</i>
                </button>
            </form>
        </div>

    </div>

</div>

<!-- Include Javascript -->
<?php include("js.php") ?>
<script type="application/javascript">
    initAutoComplete({inputId: 'autocomplete-input', ajaxUrl: '?type=usrmgt&console&partname='});


    function initAutoComplete(options) {
        //console.info("Initiation Autocomplete with ");
        //console.info(options);
        var defaults = {
            inputId: null,
            ajaxUrl: false,
            data: {}
        };

        options = $.extend(defaults, options);
        var $input = $("#" + options.inputId);

        if (options.ajaxUrl !== false) {
            var $autocomplete = $('<ul id="myId" class="autocomplete-content dropdown-content"></ul>'),
                $inputDiv = $input.closest('.input-field'),
                //timeout,
                runningRequest = false,
                request;

            if ($inputDiv.length) {
                $inputDiv.append($autocomplete); // Set ul in body
            } else {
                $input.after($autocomplete);
            }

            var highlight = function (string, $el) {
                var img = $el.find('img');
                var matchStart = $el.text().toLowerCase().indexOf("" + string.toLowerCase() + ""),
                    matchEnd = matchStart + string.length - 1,
                    beforeMatch = $el.text().slice(0, matchStart),
                    matchText = $el.text().slice(matchStart, matchEnd + 1),
                    afterMatch = $el.text().slice(matchEnd + 1);
                $el.html("<span>" + beforeMatch + "<span class='highlight'>" + matchText + "</span>" + afterMatch + "</span>");
                if (img.length) {
                    $el.prepend(img);
                }
            };

            $autocomplete.on('click', 'li', function () {
                $input.val($(this).text().trim());
                $autocomplete.empty();
            });

            $input.on('keyup', function (e) {

                if (e.keyCode == 37 || e.keyCode == 38 || e.keyCode == 39 || e.keyCode == 40)
                    return; //arrow keys

                //if(timeout){ clearTimeout(timeout);}
                if (runningRequest) request.abort();

                if (e.which === 13) {
                    $autocomplete.find('li').first().click();
                    return;
                }

                var val = $input.val().toLowerCase();
                $autocomplete.empty();

                //timeout = setTimeout(function() {

                runningRequest = true;

                var ajaxUrl = options.ajaxUrl + val;
                request = $.ajax({
                    type: 'GET', // your request type
                    url: ajaxUrl,
                    success: function (data) {
                        if (!$.isEmptyObject(data)) {
                            // Check if the input isn't empty
                            if (val !== '') {
                                try {
                                    data = JSON.parse(data);
									//catch timeout an reload page (back to login)
									if (null != data['time']  && data['time'] === "out") {
										Materialize.toast(data['message'],"4000");
										location.reload();
									}
                                } catch (e) {
                                    return; // no valid response
                                }
                                //console.info("Data for Autocomplete: " + data);
                                data.forEach(function (key) {
                                    if (
                                        key.toLowerCase().indexOf(val) !== -1 &&
                                        key.toLowerCase() !== val) {
                                        var autocompleteOption = $('<li></li>');
                                        if (!!data[key]) {
                                            autocompleteOption.append('<img src="' + data[key] + '" class="right circle"><span>' + key + '</span>');
                                        } else {
                                            autocompleteOption.append('<span>' + key + '</span>');
                                        }
                                        $autocomplete.append(autocompleteOption);

                                        highlight(val, autocompleteOption);
                                    }
                                });

                            }
                        }
                    },
                    complete: function () {
                        runningRequest = false;
                    }
                });
                //},250);
            });
        }
        else {
            $input.autocomplete({
                source: options.data
            });
        }
    }
</script>
</body>
</html>
