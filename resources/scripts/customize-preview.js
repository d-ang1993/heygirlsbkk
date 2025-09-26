(function($) {
    'use strict';

    // Hero section live preview
    wp.customize('hero_heading', function(value) {
        value.bind(function(newval) {
            $('.hero__title').text(newval);
        });
    });

    wp.customize('hero_subheading', function(value) {
        value.bind(function(newval) {
            if (newval) {
                $('.hero__subtitle').text(newval).show();
            } else {
                $('.hero__subtitle').hide();
            }
        });
    });

    wp.customize('hero_cta_text', function(value) {
        value.bind(function(newval) {
            $('.hero__cta').text(newval);
        });
    });

    wp.customize('hero_overlay', function(value) {
        value.bind(function(newval) {
            $('.hero__overlay').css('opacity', newval);
        });
    });

    wp.customize('hero_enable', function(value) {
        value.bind(function(newval) {
            if (newval) {
                $('.hero').show();
            } else {
                $('.hero').hide();
            }
        });
    });

    // Carousel functionality
    let carouselIndex = 0;
    let carouselInterval;

    function showCarouselSlide(index) {
        $('.hero__carousel-item').removeClass('active');
        $('.hero__carousel-dot').removeClass('active');
        
        $('.hero__carousel-item[data-slide="' + index + '"]').addClass('active');
        $('.hero__carousel-dot[data-slide="' + index + '"]').addClass('active');
        
        carouselIndex = index;
    }

    function startCarousel() {
        const slides = $('.hero__carousel-item');
        if (slides.length > 1) {
            carouselInterval = setInterval(function() {
                const nextIndex = (carouselIndex + 1) % slides.length;
                showCarouselSlide(nextIndex);
            }, 4000);
        }
    }

    function stopCarousel() {
        if (carouselInterval) {
            clearInterval(carouselInterval);
        }
    }

    // Initialize carousel on page load
    $(document).ready(function() {
        console.log('Hero carousel script loaded');
        if ($('.hero__carousel').length > 0) {
            console.log('Hero carousel found, starting...');
            startCarousel();
        } else {
            console.log('No hero carousel found');
        }
    });

    // Carousel dot navigation
    $(document).on('click', '.hero__carousel-dot', function() {
        const slideIndex = $(this).data('slide');
        showCarouselSlide(slideIndex);
        stopCarousel();
        startCarousel(); // Restart auto-rotation
    });

    // Arrow navigation
    window.changeSlide = function(direction) {
        const slides = $('.hero__carousel-item');
        if (slides.length > 1) {
            let newIndex = carouselIndex + direction;
            if (newIndex >= slides.length) newIndex = 0;
            if (newIndex < 0) newIndex = slides.length - 1;
            showCarouselSlide(newIndex + 1);
            stopCarousel();
            startCarousel(); // Restart auto-rotation
        }
    };

    // Pause carousel on hover
    $('.hero__carousel').hover(
        function() { stopCarousel(); },
        function() { startCarousel(); }
    );

})(jQuery);