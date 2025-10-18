/**
 * Infinite Related Products Carousel
 * - Seamless looping both directions
 * - Flicker-free transition
 * - Dots animate on every click (left/right)
 * - Responsive + touch
 */

class RelatedProductsCarousel {
    constructor(carouselElement) {
      this.carousel = carouselElement;
      this.track = this.carousel.querySelector('.related-products-track');
      this.prevButton = this.carousel.querySelector('.related-carousel-arrow--prev');
      this.nextButton = this.carousel.querySelector('.related-carousel-arrow--next');
      this.dotsContainer = this.carousel.parentElement.querySelector('.related-carousel-dots');
  
      this.cards = Array.from(this.track.querySelectorAll('.related-product-card'));
      this.visibleCards = 1;
      this.cloneBuffer = 0;
      this.currentIndex = 0;
      this.isAnimating = false;
      this.isSnapping = false;
      this.resizeTimeout = null;
  
      this.dotIndex = 0; // 🟢 keeps track of which dot is active manually
  
      this.updateVisibleCards();
      this.cloneEdges();
      this.init();
    }
  
    updateVisibleCards() {
      const w = window.innerWidth;
      if (w <= 480) this.visibleCards = 1;
      else if (w <= 768) this.visibleCards = 2;
      else if (w <= 1024) this.visibleCards = 2;
      else this.visibleCards = 3;

      this.realCount = this.cards.length;
      this.totalSlides = Math.ceil(this.realCount / this.visibleCards);
    }
  
    cloneEdges() {
      this.track.querySelectorAll('.is-clone').forEach(n => n.remove());
      this.cloneBuffer = this.visibleCards;
      const originals = Array.from(this.track.querySelectorAll('.related-product-card'));
  
      const firstClones = originals.slice(0, this.cloneBuffer).map(c => {
        const n = c.cloneNode(true);
        n.classList.add('is-clone');
        return n;
      });
      const lastClones = originals.slice(-this.cloneBuffer).map(c => {
        const n = c.cloneNode(true);
        n.classList.add('is-clone');
        return n;
      });
  
      lastClones.reverse().forEach(n => this.track.prepend(n));
      firstClones.forEach(n => this.track.append(n));
  
      this.cards = Array.from(this.track.querySelectorAll('.related-product-card'));
      this.realCount = this.cards.length - 2 * this.cloneBuffer;
      this.currentIndex = this.cloneBuffer;
    }
  
    init() {
      this.track.classList.add('no-transition');
      this.createDots();
      this.addListeners();
      this.updateCarousel(false);
  
      requestAnimationFrame(() => {
        this.track.classList.remove('no-transition');
      });
    }
  
    addListeners() {
      this.prevButton.addEventListener('click', () => this.prev());
      this.nextButton.addEventListener('click', () => this.next());
      window.addEventListener('resize', () => this.debouncedResize());
      this.addTouchSupport();
    }
  
    createDots() {
      if (!this.dotsContainer) return;
      this.dotsContainer.innerHTML = '';
      for (let i = 0; i < this.totalSlides; i++) {
        const dot = document.createElement('button');
        dot.className = 'related-carousel-dot';
        dot.setAttribute('aria-label', `Go to slide ${i + 1}`);
        this.dotsContainer.appendChild(dot);
      }
      this.dots = Array.from(this.dotsContainer.querySelectorAll('.related-carousel-dot'));
      this.dotIndex = 0;
      this.updateDots();
    }
  
    getGap() {
      const gap = parseFloat(getComputedStyle(this.track).gap);
      return isNaN(gap) ? 24 : gap;
    }
  
    updateCarousel(animate = true) {
      const firstCard = this.cards[0];
      if (!firstCard) return;
  
      const cardWidth = firstCard.offsetWidth || 0;
      const offset = this.currentIndex * (cardWidth + this.getGap());
  
      this.track.style.transition = animate ? 'transform 0.5s ease' : 'none';
      this.track.style.transform = `translateX(-${offset}px)`;
  
      if (animate) {
        this.isAnimating = true;
        this.track.addEventListener(
          'transitionend',
          () => {
            this.isAnimating = false;
  
            if (this.currentIndex >= this.cloneBuffer + this.realCount) {
              this.snapTo(this.currentIndex - this.realCount);
            } else if (this.currentIndex < this.cloneBuffer) {
              this.snapTo(this.currentIndex + this.realCount);
            }
          },
          { once: true }
        );
      }
  
      this.updateDots();
    }
  
    snapTo(index) {
      this.isSnapping = true;
      this.track.style.transition = 'none';
      this.currentIndex = index;
      this.updateCarousel(false);
      void this.track.offsetHeight;
      this.track.style.transition = '';
      this.isSnapping = false;
    }
  
    updateDots() {
      if (!this.dots?.length) return;
  
      this.dots.forEach((dot, i) => dot.classList.toggle('active', i === this.dotIndex));
    }
  
    next() {
      if (this.isAnimating || this.isSnapping) return;
      this.currentIndex++;
      this.updateCarousel(true);
  
      // 🟢 move dot forward
      this.dotIndex = (this.dotIndex + 1) % this.totalSlides;
      this.updateDots();
    }
  
    prev() {
      if (this.isAnimating || this.isSnapping) return;
      this.currentIndex--;
      this.updateCarousel(true);
  
      // 🟢 move dot backward
      this.dotIndex = (this.dotIndex - 1 + this.totalSlides) % this.totalSlides;
      this.updateDots();
    }
  
    goToSlide(slideIndex) {
      if (this.isAnimating || this.isSnapping) return;
      const firstReal = this.cloneBuffer;
      const target = firstReal + slideIndex * this.visibleCards;
      this.currentIndex = target;
      this.updateCarousel(true);
      this.dotIndex = slideIndex;
      this.updateDots();
    }
  
    debouncedResize() {
      clearTimeout(this.resizeTimeout);
      this.resizeTimeout = setTimeout(() => this.handleResize(), 150);
    }
  
    handleResize() {
      const prevVisible = this.visibleCards;
      this.updateVisibleCards();
  
      if (prevVisible !== this.visibleCards) {
        this.track.classList.add('no-transition');
        this.cloneEdges();
        this.createDots();
        this.updateCarousel(false);
  
        requestAnimationFrame(() => {
          this.track.classList.remove('no-transition');
        });
      } else {
        this.updateCarousel(false);
      }
    }
  
    addTouchSupport() {
      let sx = 0, ex = 0;
      const threshold = 50;
  
      this.track.addEventListener('touchstart', e => {
        sx = e.changedTouches[0].screenX;
      }, { passive: true });
  
      this.track.addEventListener('touchend', e => {
        ex = e.changedTouches[0].screenX;
        const d = sx - ex;
        if (Math.abs(d) > threshold) (d > 0 ? this.next() : this.prev());
      }, { passive: true });
    }
  }
  
  function initRelatedProductsCarousels() {
    document.querySelectorAll('.related-products-carousel').forEach(el => {
      if (!el.hasAttribute('data-carousel-initialized')) {
        el.setAttribute('data-carousel-initialized', 'true');
        new RelatedProductsCarousel(el);
      }
    });
  }
  
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initRelatedProductsCarousels);
  } else {
    initRelatedProductsCarousels();
  }
  
  // Color swatch functionality
document.addEventListener('DOMContentLoaded', function() {
  // Add click handlers for color swatches
  document.querySelectorAll('.related-product-card .color-swatch').forEach(swatch => {
    swatch.addEventListener('click', function() {
      // Remove selected class from siblings
      const siblings = this.parentElement.querySelectorAll('.color-swatch');
      siblings.forEach(s => s.classList.remove('selected'));
      
      // Add selected class to clicked swatch
      this.classList.add('selected');
    });
  });
});

// Wishlist functionality
function toggleWishlist(productId) {
  const heartIcon = event.target.closest('.heart-icon');
  if (heartIcon) {
    heartIcon.classList.toggle('liked');
    
    // Here you would typically make an AJAX call to save to wishlist
    console.log('Toggled wishlist for product:', productId);
  }
}

export { RelatedProductsCarousel, initRelatedProductsCarousels, toggleWishlist };
  