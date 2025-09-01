// Create a DataTablesManager Object only if one does not already exist.
// We create the methods in a closure to avoid creating global variables.
if (typeof DataTablesManager !== "object") {
    DataTablesManager = {};
}

(function () {
    // Set the global error handling for DataTables
    DataTable.ext.errMode = function (settings, techNote, message) {
        if (mode === 'prod' || mode === 'uat') {
            window.location.href = logoutUrl;
        }
    };

    //main function to run this Object
    DataTablesManager.run = function () {
        DataTablesManager.populateTable();
        DataTablesManager.navigatePaginator();
        DataTablesManager.recordPreviewer();
        DataTablesManager.navigateTableKeyboardShortcuts();
    }

    DataTablesManager.fetchOperationDelayMs = 1000; //prevent sending multiple fetches at approximately the same time
    DataTablesManager.fetchOperationID = null; //prevent sending multiple fetches at approximately the same time
    DataTablesManager.fetchOperationIsInProgress = false; //if the fetch is in progress, prevent other operations
    DataTablesManager.autoRefresh = false; //auto-refresh the DataTables data

    DataTablesManager.refreshTime = null;
    DataTablesManager.totalRefreshTime = null;
    DataTablesManager.timerId = null;

    //timer that shows when the refresh will happen
    DataTablesManager.updateTimer = function () {
        let overlay = document.querySelector('.timer-overlay');
        if (overlay === null) {
            return;
        }

        // Calculate the percentage of completion
        let percentage = (1 - (DataTablesManager.refreshTime / DataTablesManager.totalRefreshTime));
        let endAngle = percentage * 360;

        // Apply the conic-gradient background to represent the pie chart effect
        overlay.style.background = `conic-gradient(#e6e6e6 ${endAngle}deg, transparent ${endAngle}deg 360deg)`;

        if (DataTablesManager.refreshTime <= 0) {

        } else {
            DataTablesManager.refreshTime -= 100; // Update the countdown by 100ms
            DataTablesManager.timerId = setTimeout(DataTablesManager.updateTimer, 100);
        }
    }

    DataTablesManager.populateTable = function () {
        let options = {
            serverSide: true,
            stateSave: true,
            ajax: {
                type: "POST",
                headers: {
                    'X-CSRF-Token': csrfToken,
                    Accept: "text/plain; charset=utf-8", "Content-Type": "text/plain; charset=utf-8"
                },
                beforeSend: function () {
                    if (DataTablesManager.autoRefresh) {
                        $('.timer-holder').removeClass('d-none');
                    }

                    //initiate the timer variables
                    DataTablesManager.refreshTime = DataTablesManager.autoRefresh;
                    DataTablesManager.totalRefreshTime = DataTablesManager.autoRefresh;
                    clearTimeout(DataTablesManager.timerId);
                    DataTablesManager.timerId = null;

                    DataTablesManager.updateTimer();
                },
                complete: function (e) {
                }
            },
            initComplete: function () {
                let api = this.api();

                // For each column
                api
                    .columns()
                    .eq(0)
                    .each(function (colIdx) {
                        let filterSelector = '.filters th';
                        let filterHeaderSelector = '.filter-headers th';

                        let columnIndex = $(api.column(colIdx).header()).index();

                        // Set the header cell to contain the input element
                        let cell = $(filterSelector).eq(columnIndex);

                        let cellHeader = $(filterHeaderSelector).eq(columnIndex);
                        let title = $(cellHeader).text();
                        let dataType = $(cellHeader).attr("data-db-type");

                        if (title !== 'Actions') {
                            let placeholder = '';
                            if (dataType === 'boolean') {
                                placeholder = 'y or n';
                            } else if (dataType === 'integer' || dataType === 'float') {
                                placeholder = '#';
                            } else if (dataType === 'datetime' || dataType === 'date') {
                                placeholder = 'y-m-d';
                            } else {
                                placeholder = 'search text';
                            }
                            $(cell).html('<input class="form-control form-control-sm" type="text" placeholder="' + placeholder + '" />');
                        }

                        //on every keypress in this input
                        let cursorPosition;
                        $('input', $(filterSelector).eq(columnIndex))
                            .off('keyup change')
                            .on('change', function (e) {
                                // Get the search value
                                $(this).attr('title', $(this).val());
                                let regexr = '{search}'; //$(this).parents('th').find('select').val();

                                cursorPosition = this.selectionStart;
                                // Search the column for that value
                                api
                                    .column(colIdx)
                                    .search(
                                        this.value !== ''
                                            ? regexr.replace('{search}', this.value)
                                            : '',
                                        this.value !== '',
                                        this.value === ''
                                    )
                                    .draw();
                            })
                            .on('keyup', function (e) {
                                e.stopPropagation();
                                $(this).trigger('change');
                                $(this)
                                    .focus()[0]
                                    .setSelectionRange(cursorPosition, cursorPosition);
                            });
                    });

                // Restore state of the column filters
                let state = api.state.loaded();
                if (state) {
                    api.columns().eq(0).each(function (colIdx) {
                        let colSearch = state.columns[colIdx].search;
                        if (colSearch.search) {
                            //console.log(colIdx, colSearch.search);
                            let filterSelector = '.filters th input';
                            $(filterSelector).eq(colIdx).val(colSearch.search);
                        }
                    });
                }
            },
        };

        let table = $('.dataset').DataTable(options);

        if (DataTablesManager.autoRefresh) {
            let autoUpdateId = setInterval(function () {
                table.ajax.reload(null, false); // user paging is not reset on reload
            }, DataTablesManager.autoRefresh);
        }

        table.off('draw.tooltips').on('draw.tooltips', function () {
            //enable popovers & tooltips
            $('[data-bs-toggle="tooltip"]').tooltip();
            $('[data-bs-toggle="popover"]').popover();
        });
    }

    DataTablesManager.navigatePaginator = function () {
        $("body").keydown(function (e) {
            // left arrow
            if (e.which === 37) {
                $('li.paginate_button.page-item.previous').trigger('click');
            }
            // right arrow
            if (e.which === 39) {
                $('li.paginate_button.page-item.next').trigger('click');
            }
        })
    }

    DataTablesManager.recordPreviewer = function () {
        $('#previewRecord')
            .on('hide.bs.modal', function (event) {
                $('.loader-content').removeClass('d-none');
                $('.record-content').addClass('d-none').empty();
            })
            .on('show.bs.modal', function (event) {
                var button = $(event.relatedTarget)
                var recordTitle = button.data('record-title')
                var recordId = button.data('record-id')

                var modal = $(this)
                modal.find('.modal-title').text(recordTitle)
                modal.find('.modal-body input').val(recordTitle)

                var targetUrl = controllerUrl + 'preview/' + recordId + '/html';

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
                        $('.record-content').html(response).removeClass('d-none');
                    },
                    error: function (e) {
                    }
                })

            });
    }

    DataTablesManager.navigateTableKeyboardShortcuts = function () {
        $("body").keydown(function (e) {
            // left arrow
            if (e.which === 37) {
                $('button.dt-paging-button.previous').trigger('click');
            }
            // right arrow
            if (e.which === 39) {
                $('button.dt-paging-button.next').trigger('click');
            }
        });
    }

}());
