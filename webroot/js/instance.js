/**
 * This JS file is used by the /instance/configure page.
 * Functions to configure this particular instance of the application.
 */
/**
 * @var csrfToken
 */
/**
 * @var dataObjectsUrl
 */
/**
 * @var homeUrl
 */
$(document).ready(function () {

    var instanceUrl;
    populateInstanceUrl();

    function populateInstanceUrl() {
        var subDirAction = window.location.pathname.split("/instance")[1];
        instanceUrl = homeUrl + "instance" + subDirAction;
    }

    var driverState = false;
    var instanceState = false;

    var ajaxDelayId = null;
    var ajaxPromise = null;

    $('#database-driver-selection').change(function () {
        var databaseDriver = $(this).val();

        $('.server').addClass('d-none');

        if (databaseDriver === 'Sqlserver') {
            $('.server-sql').removeClass('d-none');
        } else if (databaseDriver === 'Mysql') {
            $('.server-mysql').removeClass('d-none');
        } else if (databaseDriver === 'Sqlite') {
            $('.server-sqlite').removeClass('d-none');
        }
    });

    $('form#database-driver').on('keyup', function () {
        checkDatabaseDriver(false);
    })

    $('button.driver-submit').on('click', function () {
        checkDatabaseDriver(true);
    })


    function checkDatabaseDriver(dump) {
        var delay = 2000;
        var targetUrl = instanceUrl;
        var formData = new FormData();
        formData.append("is-ajax", '1');
        formData.append("database-driver-selection", $('#database-driver-selection').find(":selected").attr('value'));
        formData.append("dump", dump);

        if (dump === true) {
            delay = 0;
        }

        $('form#database-driver').find('input').each(function () {
            var fieldName = $(this).attr('name');
            var fieldValue = $(this).val();
            formData.append(fieldName, fieldValue);
        });

        if (ajaxDelayId !== null) {
            clearTimeout(ajaxDelayId);
            ajaxDelayId = null;
        }

        if (ajaxPromise) {
            ajaxPromise.abort();
        }

        //make sure submit button is disabled
        $('button.driver-submit').attr('disabled', 'disabled');

        ajaxDelayId = setTimeout(function () {
            ajaxPromise = $.ajax({
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

                beforeSend: function () {
                },
                success: function (response) {
                    // console.log(response);
                    if (response.connected === true) {
                        $('button.driver-submit').attr('disabled', null);
                    }

                    if (response.connected === true && response.dump === true) {
                        $('form#database-driver').remove();
                        $('#database').append("<p>The database configuration has been saved</p>");
                        driverState = true;
                    }

                    if (instanceState && driverState) {
                        showUpdateLink();
                    }
                },
                error: function (e) {
                    //alert("An error occurred: " + e.responseText.message);
                    //console.log(e);
                    //updateCsrfToken();
                },
                complete: function (e) {
                }
            })
        }, delay);

    }

    $('button.instance-submit').on('click', function () {
        applyInstance();
    })

    function applyInstance() {
        var targetUrl = instanceUrl;
        var formData = new FormData();
        formData.append("is-ajax", '1');
        formData.append("instance-configuration-selection", $('#instance-configuration-selection').find(":selected").attr('value'));

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

            beforeSend: function () {
            },
            success: function (response) {
                // console.log(response);
                if (response === true) {
                    $('form#instance-configuration').remove();
                    $('#instance').append("<p>The instance configuration has been saved</p>");
                    instanceState = true;
                }

                if (instanceState && driverState) {
                    showUpdateLink();
                }
            },
            error: function (e) {
                //alert("An error occurred: " + e.responseText.message);
                //console.log(e);
                //updateCsrfToken();
            },
            complete: function (e) {
            }
        });

    }

    function showUpdateLink() {
        $(".database-holder").addClass('d-none');
        $(".instance-holder").addClass('d-none');
        $(".hr-1").addClass('d-none');
        $(".updates-holder").removeClass('d-none');
    }


});
