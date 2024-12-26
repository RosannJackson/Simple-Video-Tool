jQuery(document).ready(function($) {
    $('#video-upload-form').on('submit', function(e) {
        e.preventDefault();
        var formData = new FormData(this);
        formData.append('action', 'upload_video');
        formData.append('nonce', videoToolAjax.nonce);
        $.ajax({
            url: videoToolAjax.ajaxurl,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            beforeSend: function() {
                $('.upload-button').prop('disabled', true).text('Uploading...');
            },
            success: function(response) {
                if (response.success) {
                    alert('Video uploaded successfully!');
                } else {
                    alert('Upload failed: ' + response.data);
                }
            },
            error: function() {
                alert('Upload failed. Please try again.');
            },
            complete: function() {
                $('.upload-button').prop('disabled', false).text('Upload Video');
            }
        });
    });
});
