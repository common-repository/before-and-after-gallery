jQuery(function($){
    $('.beforeAfterGallery_main').each(function(){
        var main = this,
            startThumb = $('> a', main),
            modal = $('.modal', main),
            images = $('ul > li', modal),
            bigImage = $('.twentytwenty-container', main),
            carouselInner = $('ul', modal),
            carousel = $('.beforeAfterGallery_carousel-inner', main),
            imagesLoaded = 0,
            previousRow,
            currentRow,
            currentRowIndex = 0,
            currentImageIndex = 0,
            totalRows = 0,
            carouselMain = $('.beforeAfterGallery_carousel', modal),
            imagesPerRow = 9;

        var init = function() {
            // move HTML
            modal.appendTo('body').hide();

            // build carousel
            buildCarousel();

            // remove excess HTML
            $('.modal-footer > ul', modal).remove();

            // add events handlers
            startThumb.click(showModal);
            modal.click(hideModal).find('.close').click(hideModal);
            modal.find('.modal-dialog').click(function(e){
                e.stopPropagation();
            });

            $('>a', images).click(thumb_click);

            $('.beforeAfterGallery_carousel a.left', modal).click(left_click);
            $('.beforeAfterGallery_carousel a.right', modal).click(right_click);

            $('.modal-body > .left', modal).click(leftImage_click);
            $('.modal-body > .right', modal).click(rightImage_click);
        };

        var buildCarousel = function() {
            // move images out of rows
            $('.item >', carousel).appendTo(carouselInner);

            // remove rows
            $('.item', carousel).remove();

            // hide controls?
            $('.beforeAfterGallery_carousel-control', modal).toggle($('>', carouselInner).length > imagesPerRow);
            $('.modal-body > a', modal).toggle($('>', carouselInner).length > 1);

            var carouselItem;
            totalRows = 0;
            $('>', carouselInner).each(function(i){
                // start new row if needed
                if (!(i % imagesPerRow)) {
                    ++totalRows;
                    carouselItem = $('<ul />').addClass('item').appendTo(carousel);
                }

                $(this).appendTo(carouselItem);
            });

            $('ul', carousel).first().addClass('active');

            buildCarouselJS();
        };

        var buildCarouselJS = function() {
            // reset indexes
            currentRowIndex = 0;
            currentRow = $('ul', modal).eq(currentRowIndex);
            currentImageIndex = 0;

            // select first image
            $('.item >', carousel).first().find('a').click();
        };

        var leftImage_click = function() {
            if (--currentImageIndex < 0) {
                currentImageIndex = $('a', carousel).length - 1;
            }

            selectImage();

            return false;
        };

        var rightImage_click = function() {
            if (++currentImageIndex >= $('a', carousel).length) {
                currentImageIndex = 0;
            }

            selectImage();

            return false;
        };

        var selectImage = function() {
            // move carousel row?
            previousRow = $('ul', modal).eq(currentRowIndex);
            var previousRowIndex = currentRowIndex;
            currentRowIndex = Math.ceil((currentImageIndex + 1) / imagesPerRow) - 1;
            currentRow = $('ul', modal).eq(currentRowIndex);
            if (previousRowIndex != currentRowIndex) {
                animateRows(currentRowIndex > previousRowIndex);
            }

            // select image
            $('a', carousel).eq(currentImageIndex).click();
        };

        var left_click = function() {
            previousRow = $('ul', modal).eq(currentRowIndex);

            if (--currentRowIndex < 0) {
                currentRowIndex = totalRows - 1;
            }

            currentRow = $('ul', modal).eq(currentRowIndex);

            animateRows(false);

            return false;
        };

        var right_click = function() {
            previousRow = $('ul', modal).eq(currentRowIndex);

            if (++currentRowIndex >= totalRows) {
                currentRowIndex = 0;
            }

            currentRow = $('ul', modal).eq(currentRowIndex);

            animateRows(true);

            return false;
        };

        var animateRows = function(isNext) {
            currentRow.addClass(isNext ? 'next' : 'prev');
            currentRow[0] && currentRow[0].offsetWidth; // force redraw
            currentRow.addClass(isNext ? 'left' : 'right');
            previousRow.addClass(isNext ? 'left' : 'right');

            setTimeout(function(){
                previousRow.removeClass('left right active');
                currentRow.removeClass('left right prev next').addClass('active');
            }, 500);
        };

        var showModal = function() {
            // cleanup any previous backdrop
            $('.artifex-modal-backdrop').remove();

            // add backdrop
            $('<div/>').addClass('artifex-modal-backdrop').appendTo('body');

            // show modal
            modal.removeClass('out').show();

            // add global class
            $('html').addClass('artifex-modal-open');

            // schedule modal fade in
            setTimeout(function() {
                modal.addClass('in');
                $('.artifex-modal-backdrop').addClass('in');

                // select first image
                $('.item >', carousel).first().find('a').click();
            }, 300);


            return false;
        };

        var hideModal = function() {
            // swap classes
            modal.removeClass('in').addClass('out');
            $('.artifex-modal-backdrop').removeClass('in').addClass('out');

            // remove global class
            $('html').removeClass('artifex-modal-open');

            // schedule hide of modal
            setTimeout(function(){
                modal.hide();

                // cleanup any previous backdrop
                $('.artifex-modal-backdrop').remove();
            }, 300);
        };

        var thumb_click = function() {
            // set index
            currentImageIndex = $.inArray(this, $('a', carousel));

            // clear out images
            bigImage.appendTo($('.modal-body', modal));
            bigImage.html('');
            $('.twentytwenty-wrapper', modal).remove();

            // set classes
            images.removeClass('active');
            $(this).parent().addClass('active');

            // reset image count
            imagesLoaded = 0;

            // add images
            $('<img/>').attr('src', $(this).attr('data-before-image')).appendTo(bigImage).one('load', function () {
                imagesReady();
            }).each(function() {
                if (this.complete) $(this).load();
            });

            $('<img/>').attr('src', $(this).attr('data-after-image')).appendTo(bigImage).one('load', function () {
                imagesReady();
            }).each(function() {
                if (this.complete) $(this).load();
            });

            return false;
        };

        var imagesReady = function() {
            if (++imagesLoaded >= 2) {
                // start visual diff
                $(bigImage).twentytwenty();
            }
        };

        init();
    });
});

function triggerBeforeAfterGallery(id) {
    jQuery('.beforeAfterGallery_' + id + ' > a').click();
    return false;
}