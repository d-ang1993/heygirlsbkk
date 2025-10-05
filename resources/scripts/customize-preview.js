(function($) {
    'use strict';

    // Hero background customization live preview
    wp.customize('hero_bg_position', function(value) {
        value.bind(function(newval) {
            $('.hero__bg').css('background-position', newval);
        });
    });

    wp.customize('hero_bg_size', function(value) {
        value.bind(function(newval) {
            $('.hero__bg').css('background-size', newval);
        });
    });

    wp.customize('hero_bg_repeat', function(value) {
        value.bind(function(newval) {
            $('.hero__bg').css('background-repeat', newval);
        });
    });

    wp.customize('hero_bg_color', function(value) {
        value.bind(function(newval) {
            $('.hero__bg').css('background-color', newval);
            $('.hero__bg--fallback').css('background-color', newval);
        });
    });

    wp.customize('hero_bg_attachment', function(value) {
        value.bind(function(newval) {
            $('.hero__bg').css('background-attachment', newval);
        });
    });

    // Hero text customization live preview
    wp.customize('hero_heading', function(value) {
        value.bind(function(newval) {
            $('.hero__title').text(newval);
        });
    });

    wp.customize('hero_subheading', function(value) {
        value.bind(function(newval) {
            $('.hero__subtitle').text(newval);
        });
    });

    wp.customize('hero_cta_text', function(value) {
        value.bind(function(newval) {
            $('.hero__cta').text(newval);
        });
    });

    wp.customize('hero_align', function(value) {
        value.bind(function(newval) {
            $('.hero__inner').removeClass('hero__inner--left hero__inner--center hero__inner--right')
                           .addClass('hero__inner--' + newval);
        });
    });

    wp.customize('hero_height', function(value) {
        value.bind(function(newval) {
            $('.hero').css('--hero-height', newval);
        });
    });

    wp.customize('hero_overlay', function(value) {
        value.bind(function(newval) {
            $('.hero__overlay').css('opacity', newval);
        });
    });

})(jQuery);