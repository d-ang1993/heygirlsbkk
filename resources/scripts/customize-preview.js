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

    // New Drops Carousel live preview
    wp.customize('new_drops_title', function(value) {
        value.bind(function(newval) {
            $('.new-drops-title').text(newval);
        });
    });

    wp.customize('new_drops_subtitle', function(value) {
        value.bind(function(newval) {
            $('.new-drops-subtitle').text(newval);
        });
    });

    wp.customize('new_drops_height', function(value) {
        value.bind(function(newval) {
            $('.new-drops-carousel-container').css('height', newval);
        });
    });

    // Button text and position live preview
    for (var i = 1; i <= 10; i++) {
        (function(slideIndex) {
            wp.customize('new_drops_slide_' + slideIndex + '_button_text', function(value) {
                value.bind(function(newval) {
                    $('.new-drops-slide[data-slide="' + (slideIndex - 1) + '"] .new-drops-button').text(newval);
                });
            });

            wp.customize('new_drops_slide_' + slideIndex + '_button_position', function(value) {
                value.bind(function(newval) {
                    var buttonContainer = $('.new-drops-slide[data-slide="' + (slideIndex - 1) + '"] .new-drops-button-container');
                    buttonContainer.removeClass('new-drops-button-top new-drops-button-center new-drops-button-bottom')
                                  .addClass('new-drops-button-' + newval);
                });
            });

            wp.customize('new_drops_slide_' + slideIndex + '_show_button', function(value) {
                value.bind(function(newval) {
                    var buttonContainer = $('.new-drops-slide[data-slide="' + (slideIndex - 1) + '"] .new-drops-button-container');
                    if (newval) {
                        buttonContainer.show();
                    } else {
                        buttonContainer.hide();
                    }
                });
            });
        })(i);
    }

})(jQuery);