<script>
    $(document).ready(function () {
        var parametersField = $("textarea[name*='parameters']");
        var parametersValue;
        var parametersStatus = true;

        var delayTimer;

        checkParametersField();

        parametersField.on('keyup', function () {
            //clear previous timer
            clearTimeout(delayTimer);
            //delay the execution of checkJsonObject function by 750ms
            delayTimer = setTimeout(checkParametersField, 750);
        });

        function enableDisableSubmitButton() {
            if (parametersStatus) {
                $(".submit-hot-folder").prop("disabled", false);
            } else {
                $(".submit-hot-folder").prop("disabled", true);
            }
        }

        function checkParametersField() {
            parametersValue = parametersField.val();
            var parametersValidationBox = $('.json-validation');
            var isJsonOk;
            try {
                var jsonObject = JSON.parse(parametersValue);
                isJsonOk = (typeof jsonObject === 'object' && jsonObject !== null && !Array.isArray(jsonObject));
            } catch (e) {
                isJsonOk = parametersValue.trim() === '';
            }

            if (isJsonOk) {
                parametersField.removeClass('alert alert-danger').removeClass('text-danger');
                parametersValidationBox.html('');
                parametersStatus = true;
            } else {
                parametersField.addClass('alert alert-danger');
                parametersValidationBox.html('Please enter a valid JSON Object - must start and end with curly braces e.g. {"foo": "bar"}<br>').addClass('text-danger');
                parametersStatus = false;
            }

            enableDisableSubmitButton();
        }

        $('#submit-url-enabled').off().on('change', function () {
            if ($(this).prop('checked')) {
                $('.submit-url-enabled-help-text').removeClass('d-none');
            } else {
                $('.submit-url-enabled-help-text').addClass('d-none');
            }
        });

        $('#name').off().on('change keyup', function () {
            var inputValue = $(this).val();
            var slug = homeUrl + 'hot-folders/submit/' + generateSlug(inputValue);
            $('.submit-url-text').text(slug);
        });

        function generateSlug(text) {
            return text
                .toLowerCase()
                .replace(/\s+/g, '-')     // Replace spaces with -
                .replace(/[^\w\-]+/g, '') // Remove non-word characters
                .replace(/\-\-+/g, '-')   // Replace multiple - with single -
                .replace(/^-+/, '')       // Trim - from start of text
                .replace(/-+$/, '');      // Trim - from end of text
        }


        $('.expiration-date-clear').on('click', function () {
            $('input#expiration').val('');
        })

        $('.activation-date-clear').on('click', function () {
            $('input#activation').val('');
        })

    });
</script>

