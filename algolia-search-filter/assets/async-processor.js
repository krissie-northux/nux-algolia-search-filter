jQuery(document).ready(function($) {
    $('#total-progress').hide();
    //check if an existing process is running
    var batch_id;
    var timeoutRunning;
    var checkStatus = function(batchId) {
        console.log('checking status');
        // Check status periodically
        let statusCheck = setInterval(function() {
            console.log('interval check');
            $.ajax({
                type: 'POST',
                url: ajax_object.ajax_url,
                data: {
                    action: 'check_status',
                    post_type: postType,
                    batch_id: batchId,
                },
                success: function(response) {
                    console.log('checked status', response)
                    if (response.data.status === 'complete') {
                        completeUI(response.data.progress,response.data.timestamp);
                        clearInterval(statusCheck);
                    } else {
                        processingUI(response.data);
                    }
                },
                error: function() {
                    // Enable the button in case of an error
                    $('#start-processing').prop('disabled', false);
                }
            });
        }, 1000);
        timeoutRunning = statusCheck;
    }
    var processingUI = function( data ) {
        var percentComplete = Math.round((data.progress / data.total_posts) * 100);
        $('#total-progress').show();
        $('#status-message').text('Processing... You may leave this page and come back later to check on progress.');
        $('#start-processing').prop('disabled', true);
        $('#progress-bar').width(percentComplete + '%');
        $('#status-message').text('Processing... (' + percentComplete + '%) You may leave this page and come back later to check on progress.');
    }
    var completeUI = function(recordCount,timeStamp) {
        $('#total-progress').show();
        $('#status-message').text('Complete! ' + recordCount + ' records synced to Algolia on '+ timeStamp +'.');
        $('#start-processing').prop('disabled', false);
        $('#progress-bar').width('100%');
    }
    var idleUI = function() {
        $('#status-message').text('');
        $('#start-processing').prop('disabled', false);
        $('#total-progress').hide();
    }
    $.ajax({
        type: 'POST',
        url: ajax_object.ajax_url,
        data: {
            action: 'check_latest_status',
            post_type: postType
        },
        success: function(response) {
            console.log('checked latest status', response)
            if (response.data.status === 'processing') {
                //set batch_id
                batch_id = response.data.batch_id;
                // Show progress bar and status message
                processingUI(response.data);
                // Start periodic checks
                checkStatus(response.data.batch_id);
            } else if (response.data.status === 'complete') {
                // Show completion message
                completeUI(response.data.progress, response.data.timestamp);
            } else {
                // Show idle message
                idleUI();
            }
        },
    });
    $('#reset-index').on('click', function() {
        if ($(this).is(':checked')) {
            var confirmation = confirm('Are you sure you want to reset the index? This will delete all records in Algolia and reindex them once you click start processing.');
            if (!confirmation) {
                $(this).prop('checked', false);
            }
        }
    }
    );

    $('#start-processing').on('click', function() {
        var resetIndex = $('#reset-index').is(':checked') ? true : false;
        if (resetIndex) {
            var confirmation = confirm('Are you sure you want to proceed? Resetting the index will delete all records in Algolia and reindex them. Press cancel to stop. Ok to proceed.');
            if (!confirmation) {
                $(this).prop('disabled', false);
                return;
            } else {
                processIndex( postType, resetIndex);
            }
        } else {
            processIndex( postType, resetIndex);
        }
        
    });

    var processIndex = function(postType, resetIndex) {
        $(this).prop('disabled', true);
        $.ajax({
            type: 'POST',
            url: ajax_object.ajax_url,
            data: {
                action: 'start_processing',
                post_type: postType,
                reset_index: resetIndex
            },
            success: function(response) {
                console.log('triggered processing', response);
                let data = {status: 'processing', progress: 0, total_posts: 100};
                processingUI(data);
                checkStatus(response.data.batch_id);

                
            },
        });
    }

    $('#clear-processing').on('click', function() {
        $(this).prop('disabled', true);
        $.ajax({
            type: 'POST',
            url: ajax_object.ajax_url,
            data: {
                action: 'clear_processing',
                post_type: postType,
                batch_id: batch_id
            },
            success: function(response) {
                console.log('cleared processing', response);
                idleUI();
                clearInterval(timeoutRunning);

                
            },
        });
    });
});