@props([
    'slides' => []
])

@php
    // Default slides if none provided
    if (empty($slides)) {
        $slides = [
            [
                'title' => 'NEW ARRIVAL',
                'subtitle' => '11월 신상품 출시 / 24시간 한정 5% SALE',
                'image' => '/app/themes/heygirlsbkk/resources/images/hero-1.jpg',
                'link' => '#',
                'button_text' => 'SHOP NOW'
            ],
            [
                'title' => 'BEST SELLER',
                'subtitle' => '가장 인기 있는 아이템들을 만나보세요',
                'image' => '/app/themes/heygirlsbkk/resources/images/hero-2.jpg',
                'link' => '#',
                'button_text' => 'VIEW COLLECTION'
            ],
            [
                'title' => 'SALE',
                'subtitle' => '최대 70% 할인! 놓치지 마세요',
                'image' => '/app/themes/heygirlsbkk/resources/images/hero-3.jpg',
                'link' => '#',
                'button_text' => 'SHOP SALE'
            ]
        ];
    }
@endphp

<section class="hero-banner">
    <div class="hero-slider">
        @foreach($slides as $index => $slide)
            <div class="hero-slide {{ $index === 0 ? 'active' : '' }}" data-slide="{{ $index }}">
                <div class="hero-slide__image">
                    <img src="{{ $slide['image'] }}" alt="{{ $slide['title'] }}" />
                </div>
                <div class="hero-slide__content">
                    <div class="container">
                        <div class="hero-content">
                            <h2 class="hero-title">{{ $slide['title'] }}</h2>
                            <p class="hero-subtitle">{{ $slide['subtitle'] }}</p>
                            <a href="{{ $slide['link'] }}" class="hero-button">
                                {{ $slide['button_text'] }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
    
    <!-- Slider Navigation -->
    <div class="hero-navigation">
        <button class="hero-nav-btn prev" onclick="changeSlide(-1)">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="15,18 9,12 15,6"></polyline>
            </svg>
        </button>
        <button class="hero-nav-btn next" onclick="changeSlide(1)">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="9,18 15,12 9,6"></polyline>
            </svg>
        </button>
    </div>
    
    <!-- Slider Dots -->
    <div class="hero-dots">
        @foreach($slides as $index => $slide)
            <button class="hero-dot {{ $index === 0 ? 'active' : '' }}" data-slide="{{ $index + 1 }}"></button>
        @endforeach
    </div>
</section>

<style>
.hero-banner {
    position: relative;
    height: 500px;
    overflow: hidden;
    background: #f8f9fa;
}

.hero-slider {
    position: relative;
    width: 100%;
    height: 100%;
}

.hero-slide {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    opacity: 0;
    transition: opacity 0.5s ease-in-out;
    display: flex;
    align-items: center;
}

.hero-slide.active {
    opacity: 1;
}

.hero-slide__image {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 1;
}

.hero-slide__image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.hero-slide__content {
    position: relative;
    z-index: 2;
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
}

.hero-content {
    max-width: 500px;
    color: #fff;
    text-align: left;
}

.hero-title {
    font-size: 3rem;
    font-weight: 700;
    margin: 0 0 1rem 0;
    text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
    line-height: 1.2;
}

.hero-subtitle {
    font-size: 1.2rem;
    margin: 0 0 2rem 0;
    text-shadow: 1px 1px 2px rgba(0,0,0,0.3);
    line-height: 1.4;
}

.hero-button {
    display: inline-block;
    background: #000;
    color: #fff;
    padding: 12px 30px;
    text-decoration: none;
    border-radius: 25px;
    font-weight: 600;
    font-size: 1rem;
    transition: all 0.3s ease;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.hero-button:hover {
    background: #333;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.3);
}

.hero-navigation {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    width: 100%;
    display: flex;
    justify-content: space-between;
    padding: 0 20px;
    z-index: 3;
}

.hero-nav-btn {
    background: rgba(255,255,255,0.8);
    border: none;
    width: 50px;
    height: 50px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.3s ease;
    color: #333;
}

.hero-nav-btn:hover {
    background: rgba(255,255,255,1);
    transform: scale(1.1);
}

.hero-dots {
    position: absolute;
    bottom: 20px;
    left: 50%;
    transform: translateX(-50%);
    display: flex;
    gap: 10px;
    z-index: 3;
}

.hero-dot {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    border: none;
    background: rgba(255,255,255,0.5);
    cursor: pointer;
    transition: all 0.3s ease;
}

.hero-dot.active {
    background: #fff;
    transform: scale(1.2);
}

.hero-dot:hover {
    background: rgba(255,255,255,0.8);
}

/* Responsive */
@media (max-width: 768px) {
    .hero-banner {
        height: 400px;
    }
    
    .hero-title {
        font-size: 2rem;
    }
    
    .hero-subtitle {
        font-size: 1rem;
    }
    
    .hero-button {
        padding: 10px 25px;
        font-size: 0.9rem;
    }
    
    .hero-navigation {
        padding: 0 10px;
    }
    
    .hero-nav-btn {
        width: 40px;
        height: 40px;
    }
}
</style>

<script>
let slideIndex = 1;
const slides = document.querySelectorAll('.hero-slide');
const dots = document.querySelectorAll('.hero-dot');

function showSlide(n) {
    if (n > slides.length) { slideIndex = 1; }
    if (n < 1) { slideIndex = slides.length; }
    
    slides.forEach(slide => slide.classList.remove('active'));
    dots.forEach(dot => dot.classList.remove('active'));
    
    slides[slideIndex - 1].classList.add('active');
    dots[slideIndex - 1].classList.add('active');
}

function changeSlide(n) {
    showSlide(slideIndex += n);
}

function currentSlide(n) {
    showSlide(slideIndex = n);
}

// Add event listeners to dots
dots.forEach(dot => {
    dot.addEventListener('click', function() {
        const slideNum = parseInt(this.getAttribute('data-slide'));
        currentSlide(slideNum);
    });
});

// Auto slide
setInterval(() => {
    changeSlide(1);
}, 5000);
</script>