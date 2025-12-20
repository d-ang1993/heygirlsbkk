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
      this.cardWidth = 0;
      this.gap = 0;
  
      this.dotIndex = 0; // 🟢 keeps track of which dot is active manually
  
      this.updateVisibleCards();
      this.cloneEdges();
      this.init();
    }
  
    updateVisibleCards() {
      const w = window.innerWidth;
      this.visibleCards = w <= 480 ? 1 : w <= 1024 ? 2 : 3;
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
      this.updateCardDimensions();
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
  
    updateCardDimensions() {
      if (!this.cards[0]) return;
      this.cardWidth = this.cards[0].offsetWidth || 0;
      
      if (this.gap === 0) {
      const gap = parseFloat(getComputedStyle(this.track).gap);
        this.gap = isNaN(gap) ? 24 : gap;
      }
    }
  
    updateCarousel(animate = true) {
      if (!this.cards.length) return;
  
      if (!this.cardWidth) this.updateCardDimensions();
  
      const offset = this.currentIndex * (this.cardWidth + this.gap);
      this.track.style.transition = animate ? 'transform 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94)' : 'none';
      this.track.style.transform = `translate3d(-${offset}px, 0, 0)`;
  
      if (animate) {
        this.isAnimating = true;
        this.track.addEventListener('transitionend', () => {
            this.isAnimating = false;
            if (this.currentIndex >= this.cloneBuffer + this.realCount) {
              this.snapTo(this.currentIndex - this.realCount);
            } else if (this.currentIndex < this.cloneBuffer) {
              this.snapTo(this.currentIndex + this.realCount);
            }
        }, { once: true });
      }
    }
  
    snapTo(index) {
      this.isSnapping = true;
      this.track.style.transition = 'none';
      this.currentIndex = index;
      this.updateCarousel(false);
      this.track.offsetHeight; // Force reflow
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
      this.dotIndex = (this.dotIndex + 1) % this.totalSlides;
      this.updateCarousel(true);
      this.updateDots();
    }
  
    prev() {
      if (this.isAnimating || this.isSnapping) return;
      this.currentIndex--;
      this.dotIndex = (this.dotIndex - 1 + this.totalSlides) % this.totalSlides;
      this.updateCarousel(true);
      this.updateDots();
    }
  
    goToSlide(slideIndex) {
      if (this.isAnimating || this.isSnapping) return;
      this.currentIndex = this.cloneBuffer + slideIndex * this.visibleCards;
      this.dotIndex = slideIndex;
      this.updateCarousel(true);
      this.updateDots();
    }
  
    debouncedResize() {
      clearTimeout(this.resizeTimeout);
      this.resizeTimeout = setTimeout(() => this.handleResize(), 150);
    }
  
    handleResize() {
      const prevVisible = this.visibleCards;
      this.updateVisibleCards();
      this.cardWidth = 0; // Force recalculation
  
      const needsReclone = prevVisible !== this.visibleCards;
      
      if (needsReclone) {
        this.track.classList.add('no-transition');
        this.cloneEdges();
        this.createDots();
      }
      
      this.updateCardDimensions();
        this.updateCarousel(false);
  
      if (needsReclone) {
        requestAnimationFrame(() => this.track.classList.remove('no-transition'));
      }
    }
  
    addTouchSupport() {
      let startX = 0;
      const threshold = 50;
  
      this.track.addEventListener('touchstart', e => {
        startX = e.changedTouches[0].screenX;
      }, { passive: true });
  
      this.track.addEventListener('touchend', e => {
        const delta = startX - e.changedTouches[0].screenX;
        if (Math.abs(delta) > threshold) {
          delta > 0 ? this.next() : this.prev();
        }
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
  
  // Color swatch functionality - use event delegation for better performance
  document.addEventListener('click', (e) => {
    if (e.target.classList.contains('color-swatch')) {
      const swatches = e.target.parentElement.querySelectorAll('.color-swatch');
      swatches.forEach(s => s.classList.remove('selected'));
      e.target.classList.add('selected');
    }
});

// Wishlist functionality
function toggleWishlist(productId) {
    const heartIcon = event?.target?.closest('.heart-icon');
  if (heartIcon) {
    heartIcon.classList.toggle('liked');
    console.log('Toggled wishlist for product:', productId);
  }
}

export { RelatedProductsCarousel, initRelatedProductsCarousels, toggleWishlist };
  