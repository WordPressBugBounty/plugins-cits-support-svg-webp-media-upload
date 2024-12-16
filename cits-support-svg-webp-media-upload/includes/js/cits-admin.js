jQuery(document).ready(function ($) {
    // Toggle Upload Font Form
    $('#add-custom-font-btn').on('click', function () {
        $('#custom-font-form').slideToggle();
    });

    $('#show-assign-font-form').on('click', function () {
        $('#assign-font-form').slideToggle();
    });

    // WordPress Media Uploader
    $('#select_font_button').on('click', function () {
        var mediaFrame = wp.media({
            title: 'Select Font File',
            button: { text: 'Use this font' },
            multiple: false
        });

        mediaFrame.on('select', function () {
            var attachment = mediaFrame.state().get('selection').first().toJSON();
            $('#font_file_url').val(attachment.url);
            $('#font_name').val(attachment.title);
        });

        mediaFrame.open();
    });

    // Copy Font Family Name
    $(document).on('click', '.copy-font-family', function () {
        var fontFamily = $(this).data('font-family');
        navigator.clipboard.writeText(fontFamily).then(() => {
            alert('Font Family Copied: ' + fontFamily);
        });
    });
});
