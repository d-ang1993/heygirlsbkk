@props([
    'product' => null
])

@php
    // Get the current WooCommerce product if none provided
    $product = $product ?? wc_get_product();
    
    if (!$product) {
        return;
    }
    
    // Extract product data
    $title = $product->get_name();
    $description = $product->get_description() ?: $product->get_short_description();
    $price = $product->get_price_html();
    
    // Get product images
    $lifestyleImage = wp_get_attachment_image_url($product->get_image_id(), 'product-hero');
    $galleryImages = $product->get_gallery_image_ids();
    $productImages = [];
    
    foreach ($galleryImages as $imageId) {
        $productImages[] = [
            'url' => wp_get_attachment_image_url($imageId, 'product-thumbnail'),
            'alt' => get_post_meta($imageId, '_wp_attachment_image_alt', true) ?: $title
        ];
    }
    
    // Get product attributes/features
    $features = [];
    $attributes = $product->get_attributes();
    foreach ($attributes as $attribute) {
        if ($attribute->is_taxonomy()) {
            $terms = wp_get_post_terms($product->get_id(), $attribute->get_name());
            foreach ($terms as $term) {
                $features[] = $term->name;
            }
        } else {
            $features[] = $attribute->get_name();
        }
    }
    
    // Get brand and origin from product meta
    $brand = get_post_meta($product->get_id(), '_brand', true) ?: get_post_meta($product->get_id(), 'brand', true);
    $origin = get_post_meta($product->get_id(), '_origin', true) ?: get_post_meta($product->get_id(), 'origin', true);
@endphp

<div class="product-hero">
    <div class="product-hero__container">
        <!-- Left side - Lifestyle Image -->
        <div class="product-hero__lifestyle">
            @if($lifestyleImage)
                <img 
                    src="{{ $lifestyleImage }}" 
                    alt="{{ $title }}" 
                    class="product-hero__lifestyle-image"
                    loading="eager"
                    decoding="sync"
                    width="800"
                    height="800"
                />
            @endif
        </div>

        <!-- Right side - Product Details -->
        <div class="product-hero__details">
            <!-- Product Title and Description -->
            <div class="product-hero__header">
                @if($title)
                    <h1 class="product-hero__title">
                        @if($brand)
                            <span class="product-hero__brand">{{ $brand }}</span>
                        @endif
                        {{ $title }}
                        @if($origin)
                            <span class="product-hero__origin">{{ $origin }}</span>
                        @endif
                    </h1>
                @endif

                @if($description)
                    <p class="product-hero__description">{{ $description }}</p>
                @endif

                @if($price)
                    <div class="product-hero__price">{{ $price }}</div>
                @endif
            </div>

            <!-- Product Images -->
            @if(!empty($productImages))
                <div class="product-hero__images">
                    @foreach($productImages as $index => $image)
                        <div class="product-hero__image-item">
                            <img 
                                src="{{ $image['url'] }}" 
                                alt="{{ $image['alt'] ?? $title }}" 
                                class="product-hero__product-image"
                                loading="lazy"
                                decoding="async"
                                width="400"
                                height="400"
                            />
                        </div>
                    @endforeach
                </div>
            @endif

            <!-- Key Features -->
            @if(!empty($features))
                <div class="product-hero__features">
                    @foreach($features as $feature)
                        <div class="product-hero__feature">
                            <span class="product-hero__feature-text">{{ $feature }}</span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>

<style>
.product-hero {
    width: 100%;
    margin: 2rem 0;
}

.product-hero__container {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 3rem;
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 1rem;
}

.product-hero__lifestyle {
    position: relative;
}

.product-hero__lifestyle-image {
    width: 100%;
    height: 600px;
    object-fit: cover;
    border-radius: 8px;
}

.product-hero__details {
    display: flex;
    flex-direction: column;
    gap: 2rem;
}

.product-hero__title {
    font-size: 2rem;
    font-weight: 600;
    line-height: 1.2;
    margin-bottom: 1rem;
    color: #1a1a1a;
}

.product-hero__brand {
    display: block;
    font-size: 0.875rem;
    font-weight: 500;
    color: #666;
    margin-bottom: 0.5rem;
}

.product-hero__origin {
    display: block;
    font-size: 0.875rem;
    font-weight: 400;
    color: #888;
    margin-top: 0.5rem;
}

.product-hero__description {
    font-size: 1rem;
    line-height: 1.6;
    color: #4a4a4a;
    margin-bottom: 1rem;
}

.product-hero__price {
    font-size: 1.5rem;
    font-weight: 600;
    color: #1a1a1a;
}

.product-hero__images {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
}

.product-hero__image-item {
    position: relative;
}

.product-hero__product-image {
    width: 100%;
    height: 200px;
    object-fit: cover;
    border-radius: 6px;
}

.product-hero__features {
    position: absolute;
    bottom: 1rem;
    right: 1rem;
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.product-hero__feature {
    background: rgba(255, 255, 255, 0.9);
    padding: 0.5rem 1rem;
    border-radius: 4px;
    backdrop-filter: blur(4px);
}

.product-hero__feature-text {
    font-size: 0.875rem;
    font-weight: 500;
    color: #1a1a1a;
}

/* Responsive Design */
@media (max-width: 768px) {
    .product-hero__container {
        grid-template-columns: 1fr;
        gap: 2rem;
    }
    
    .product-hero__lifestyle-image {
        height: 400px;
    }
    
    .product-hero__title {
        font-size: 1.5rem;
    }
    
    .product-hero__images {
        grid-template-columns: 1fr;
    }
    
    .product-hero__features {
        position: static;
        margin-top: 1rem;
    }
}
</style>