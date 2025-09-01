// Create a OutputProcessor Object only if one does not already exist.
// We create the methods in a closure to avoid creating global variables.
if (typeof OutputProcessor !== "object") {
    OutputProcessor = {};
}

(function () {
    //main function to run this Object
    OutputProcessor.run = function () {
        OutputProcessor.orderOutputProcessorModal();
        OutputProcessor.openCloseDocumentCardBody();

    }

    OutputProcessor.orderOutputProcessorModal = function () {
        $('#orderOutput')
            .on('hide.bs.modal', function (event) {
                $('.loader-content').removeClass('d-none');
                $('.output-processor-content').addClass('d-none').empty();
            })
            .on('show.bs.modal', function (event) {
                var button = $(event.relatedTarget)
                var recordTitle = button.data('record-title')
                var recordId = button.data('record-id')

                var modal = $(this)
                modal.find('.modal-title').text(recordTitle)
                modal.find('.modal-body input').val(recordTitle)

                var targetUrl = controllerUrl + 'output-processor-modal/' + recordId;

                $.ajax({
                    headers: {
                        'X-CSRF-Token': csrfToken
                    },
                    type: "GET",
                    url: targetUrl,
                    async: true,
                    cache: false,
                    contentType: false,
                    processData: false,
                    timeout: 60000,

                    success: function (response) {
                        $('.loader-content').addClass('d-none');
                        $('.output-processor-content').html(response).removeClass('d-none');
                        OutputProcessor.openCloseDocumentCardBody();
                        OutputProcessor.orderOutputProcessorSend();
                    },
                    error: function (e) {
                    }
                })
            });
    }

    OutputProcessor.openCloseDocumentCardBody = function () {
        $('.action-close')
            .off('click')
            .on('click', function () {
                var card = $(this).closest('.card');
                card.find('.card-body').slideUp(400, function () {
                    $(this).addClass('d-none'); // Hide after slide up completes
                });

                card.find('.action-open').removeClass('d-none');
                card.find('.action-close').addClass('d-none');
            });

        $('.action-open')
            .off('click')
            .on('click', function () {
                var card = $(this).closest('.card');
                card.find('.card-body').removeClass('d-none').hide().slideDown(400); // Slide down smoothly

                card.find('.action-close').removeClass('d-none');
                card.find('.action-open').addClass('d-none');
            });

        $('.show-all-jobs')
            .off('click')
            .on('click', function () {
                $('.action-open').trigger('click');
                $(this).addClass('d-none');
                $('.hide-all-jobs').removeClass('d-none')
            });

        $('.hide-all-jobs')
            .off('click')
            .on('click', function () {
                $('.action-close').trigger('click');
                $(this).addClass('d-none');
                $('.show-all-jobs').removeClass('d-none')
            });
    }

    OutputProcessor.orderOutputProcessorSend = function () {
        $('.output-order-send')
            .off('click')
            .on('click', function () {
                var button = $(this);
                var orderId = button.attr('data-order-id');
                var targetUrl = controllerUrl + 'output-processor/' + orderId;

                var formData = new FormData();
                formData.append("order-id", orderId);
                formData.append("output-processor-id", $('#output-processor').find(":selected").attr('value'));

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
                        $('#output-spinner').removeClass('d-none');
                    },
                    success: function (response) {
                        var status = response['status'];
                        var returnMessage = response['return_message'];
                        var alertLevel = response['alert_level'];

                        var messageHolder = $('#output-message');

                        if (status === true) {

                        } else {

                        }
                        messageHolder.find('span')
                            .removeClass('text-success')
                            .removeClass('text-warning')
                            .removeClass('text-danger')
                            .removeClass('text-info')
                            .addClass('text-' + alertLevel)
                            .text(returnMessage);

                        messageHolder.removeClass('d-none');

                        setTimeout(function () {
                            messageHolder.fadeOut('slow', function () {
                                $(this).addClass('d-none');
                                $(this).css('display', '');
                            });
                        }, 6000);


                        // $('.errand-count').text(errandCount)
                        // if (status === true) {
                        //     $('#output-success').removeClass('d-none');
                        //
                        //     setTimeout(function () {
                        //         $('#output-success').fadeOut('slow', function () {
                        //             $(this).addClass('d-none');
                        //             $(this).css('display', '');
                        //         });
                        //     }, 6000);
                        // }
                        // if (status === false) {
                        //     $('#output-warning').removeClass('d-none');
                        //
                        //     setTimeout(function () {
                        //         $('#output-warning').fadeOut('slow', function () {
                        //             $(this).addClass('d-none');
                        //             $(this).css('display', '');
                        //         });
                        //     }, 6000);
                        // }

                    },
                    error: function (e) {
                    },
                    complete: function () {
                        $('#output-spinner').addClass('d-none');
                    }
                })

            });
    }

    OutputProcessor.formEditor = function () {
        var typeSelector = $('#type');
        var pressReadyHotFolderSelector = $('#pr-csv-hf-id-wf-id');
        var fileNamingOptionsSelector = $('#file-naming-options');

        setTypeFormOptions();
        setPressReadyHotFolderFormOptions();
        setNamingOptions();

        typeSelector.change(function () {
            setTypeFormOptions();
        });

        pressReadyHotFolderSelector.change(function () {
            setPressReadyHotFolderFormOptions();
        });

        fileNamingOptionsSelector.change(function () {
            setNamingOptions();
        });

        function setTypeFormOptions() {
            var type = typeSelector.find(":selected").attr('value');
            $('.sub-options').addClass('d-none');

            if (type === 'Folder') {
                $('.folder-options').removeClass('d-none');
            }

            if (type === 'sFTP') {
                $('.sftp-options').removeClass('d-none');
            }

            if (type === 'EpsonPrintAutomate') {
                $('.epsonprintautomate-options').removeClass('d-none');
            }

            if (type === 'BackblazeBucket') {
                $('.backblaze-options').removeClass('d-none');
            }

            if (type === 'FujifilmXmfPressReadyPdfHotFolder') {
                $('.fujifilm-xmf-press-ready-pdf-hot-folders-options').removeClass('d-none');
            }

            if (type === 'FujifilmXmfPressReadyCsvHotFolder') {
                $('.fujifilm-xmf-press-ready-csv-hot-folders-options').removeClass('d-none');
            }

            if (type === 'FujifilmXmfPressReadyJdf') {
                $('.fujifilm-xmf-press-ready-jdf-options').removeClass('d-none');
            }
        }

        function setPressReadyHotFolderFormOptions() {
            var hotFolderAndWorkflow = pressReadyHotFolderSelector.find(":selected").attr('value');

            if (typeof hotFolderAndWorkflow === "undefined") {
                return;
            }

            hotFolderAndWorkflow = hotFolderAndWorkflow.split('-');
            var hotFolder = hotFolderAndWorkflow[0];
            var workflow = hotFolderAndWorkflow[1];

            $('.csv-schema').addClass('d-none');

            var subSchemaSelector = `.csv-schema-${hotFolder}`
            $(subSchemaSelector).removeClass('d-none');

            subSchemaSelector = `.csv-schema-${hotFolder}.csv-schema-fields`
            if ($(subSchemaSelector).length === 1) {
                $('#conditional-routing-message').html("There is only 1 Workflow attached to the Hot Folder. All CSV files will use this Workflow.");
            } else {
                $('#conditional-routing-message').html("There are 2 Workflows attached to the Hot Folder. Conditional routing in Press Ready will determine what Workflow the CSV  will use.");
            }
        }

        function setNamingOptions() {
            var type = fileNamingOptionsSelector.find(":selected").attr('value');

            $('.file-naming-option').addClass('d-none');

            if (type === 'builder') {
                $('#file-naming-option-builder').removeClass('d-none');
            }

            if (type === 'prefix') {
                $('#file-naming-option-prefix').removeClass('d-none');
            }
        }


        $('.prefix').on('click', function () {
            // Get the text content of the clicked span
            var textToInsert = $(this).text();

            // Get the input field
            var $input = $('#filename-builder');

            // Get the current value of the input field
            var currentInputValue = $input.val();

            // Get the current cursor position
            var cursorPos = $input.prop('selectionStart');

            // Split the text into two parts: before and after the cursor position
            var textBeforeCursor = currentInputValue.substring(0, cursorPos);
            var textAfterCursor = currentInputValue.substring(cursorPos);

            // Insert the new text at the cursor position
            var newText = textBeforeCursor + textToInsert + textAfterCursor;

            // Set the new value of the input field
            $input.val(newText);

            // Set the cursor position after the newly inserted text
            var newCursorPos = cursorPos + textToInsert.length;
            $input.focus();
            $input.prop('selectionStart', newCursorPos);
            $input.prop('selectionEnd', newCursorPos);

        });

        populateEpaPresetUsername();

        $('#epa-preset').on('change', function () {
            populateEpaPresetUsername();
        });

        function populateEpaPresetUsername() {
            var selectedOption = $('#epa-preset').find('option:selected'); // Get the selected option
            var parentOptgroupLabel = selectedOption.closest('optgroup').attr('label'); // Get the optgroup label
            $('#epa-username').val(parentOptgroupLabel);
        }

    }

}());
