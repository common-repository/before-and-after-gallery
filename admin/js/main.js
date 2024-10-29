jQuery(function($){
    function openMediaUploader(e) {
        // show the media popup
        tb_show('Upload Image', 'media-upload.php?type=image&TB_iframe=1&flash=0&simple_slideshow=1');

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
            // get uploaded image url
            var imageURL = jQuery('img',html).attr('src');
            $(e).val(imageURL).siblings('img').attr('src', imageURL);

            // close popup
            tb_remove();
        };

        return false;
    }

    $('#before-after-gallery-manager .insert-media').click(function () {
        return openMediaUploader($(this).siblings('input')[0]);
    }).siblings('input').change(function(){
        $(this).siblings('img').attr('src', $(this).val());
    });
});