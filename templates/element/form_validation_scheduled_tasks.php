<script>
    $(document).ready(function () {
        var scheduleField = $("input[name*='schedule']");
        var scheduleValue;
        var scheduleStatus = true;

        var parametersField = $("textarea[name*='parameters']");
        var parametersValue;
        var parametersStatus = true;

        var delayTimer;

        checkScheduleField();
        checkParametersField();

        scheduleField.on('keyup', function () {
            //clear previous timer
            clearTimeout(delayTimer);
            //delay the execution of checkCronExpression function by 750ms
            delayTimer = setTimeout(checkScheduleField, 750);
        });

        parametersField.on('keyup', function () {
            //clear previous timer
            clearTimeout(delayTimer);
            //delay the execution of checkJsonObject function by 750ms
            delayTimer = setTimeout(checkParametersField, 750);
        });

        function enableDisableSubmitButton() {
            if (scheduleStatus && parametersStatus) {
                $(".submit-scheduled-task").prop("disabled", false);
            } else {
                $(".submit-scheduled-task").prop("disabled", true);
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

        function checkScheduleField() {
            scheduleValue = scheduleField.val();

            var targetUrl = homeUrl + 'scheduled-tasks/check-cron-expression';
            var formData = new FormData();
            formData.append("schedule", scheduleValue);

            $.ajax({
                headers: {
                    'X-CSRF-Token': csrfToken
                },
                type: "POST",
                url: targetUrl,
                async: true,
                data: formData,
                cache: false,
                contentType: false,
                processData: false,
                timeout: 60000,

                success: function (response) {
                    var resultBox = $('.cron-expression-result');
                    if (response['is_valid']) {
                        resultBox.html(`<strong>Net Run Time:&nbsp;</strong>${response['next_run_time_local']}`);
                        resultBox.removeClass('alert alert-info').removeClass('alert alert-danger').addClass('alert alert-success');
                        scheduleStatus = true;
                    } else {
                        if (response['schedule'] === '') {
                            resultBox.text(`Enter a cron expression.`);
                            resultBox.removeClass('alert alert-danger').removeClass('alert alert-success').addClass('alert alert-info');
                            scheduleStatus = false;
                        } else {
                            resultBox.text(`Invalid cron expression!`);
                            resultBox.removeClass('alert alert-info').removeClass('alert alert-success').addClass('alert alert-danger');
                            scheduleStatus = false;
                        }
                    }

                    enableDisableSubmitButton();
                },
                error: function (e) {
                    // Handle error
                }
            });
        }


        $('.expiration-date-clear').on('click', function () {
            $('input#expiration').val('');
        })

        $('.activation-date-clear').on('click', function () {
            $('input#activation').val('');
        })

    });
</script>

