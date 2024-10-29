function openMediaUploader() {
    // show the media popup
    tb_show('Upload Image', 'media-upload.php?type=image&post_id=1&TB_iframe=true&flash=0&simple_slideshow=true');

    /* The following lines append the value ‘&simple_slideshow=true’ to all the links in the media uploader pop-up.
        Based on this parameter, we can determine if the request came from the pop-up of our media uploader. */
    jQuery('iframe#TB_iframeContent').load(function () {
        jQuery(this).contents().find('#sidemenu li').each(function (i) {
            var tab_link = jQuery(this).children(":first-child");
            var href = tab_link.attr('href');
            if (href.indexOf('simple_slideshow') < 0) {
                tab_link.attr('href', href + '&simple_slideshow=true');
            }
        });
    });



    window.send_to_editor = function(html) {
        var image_url = jQuery('img',html).attr('src');
        console.log(image_url);
        tb_remove();
    };

    return false;
}