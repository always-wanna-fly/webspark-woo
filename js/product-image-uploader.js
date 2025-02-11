jQuery(document).ready(function ($) {
    var mediaUploader;

    // Trigger the media uploader on button click
    $('#product_image_button').click(function (e) {
        e.preventDefault();

        // If media uploader is already open, just open it again
        if (mediaUploader) {
            mediaUploader.open();
            return;
        }

        // Create the media uploader
        mediaUploader = wp.media.frames.file_frame = wp.media({
            title: 'Select Image',
            button: {
                text: 'Use this image'
            },
            multiple: false // Single image selection
        });

        // When an image is selected, update the preview and hidden field
        mediaUploader.on('select', function () {
            var attachment = mediaUploader.state().get('selection').first().toJSON();
            $('#product_image_display').attr('src', attachment.url);
            $('#product_image_preview').show();
            $('#product_image_button').hide();
            $('#product_image_id').val(attachment.id); // Set the hidden input field with the selected image ID
        });

        mediaUploader.open();
    });

    // Remove the image when the "Remove Image" button is clicked
    $('#product_image_remove').click(function (e) {
        e.preventDefault();
        $('#product_image_display').attr('src', '');
        $('#product_image_preview').hide();
        $('#product_image_button').show();
        $('#product_image_id').val(''); // Clear the hidden input field
    });
});
