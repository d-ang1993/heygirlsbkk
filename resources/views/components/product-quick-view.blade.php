@props(['product' => null])

@php
    if (!$product) {
        return;
    }
    
    $product_id = is_object($product) ? $product->get_id() : $product;
    $product = wc_get_product($product_id);
    
    if (!$product) {
        return;
    }
    
    $product_type = $product->get_type();
    $is_variable = $product->is_type('variable');
    
    // Get product images
    $image_ids = [];
    $featured_image_id = $product->get_image_id();
    if ($featured_image_id) {
        $image_ids[] = $featured_image_id;
    }
    $gallery_ids = $product->get_gallery_image_ids();
    if (!empty($gallery_ids)) {
        $image_ids = array_merge($image_ids, $gallery_ids);
    }
    
    // Get product variations if variable
    $variations = [];
    $attributes = [];
    $color_variations = [];
    $size_variations = [];
    $sizes = [];
    
    if ($is_variable) {
        $variations = $product->get_available_variations();
        $attributes = $product->get_variation_attributes();
        
        foreach ($variations as $variation) {
            $variation_attributes = $variation['attributes'];
            
            // Color variations
            $color_key = $variation_attributes['attribute_pa_color'] ?? $variation_attributes['attribute_color'] ?? '';
            if ($color_key && !isset($color_variations[$color_key])) {
                $color_variations[$color_key] = $variation;
            }
            
            // Size variations - check multiple possible attribute names
            $size_key = '';
            if (isset($variation_attributes['attribute_pa_sizes'])) {
                $size_key = $variation_attributes['attribute_pa_sizes'];
            } elseif (isset($variation_attributes['attribute_pa_size'])) {
                $size_key = $variation_attributes['attribute_pa_size'];
            } elseif (isset($variation_attributes['attribute_sizes'])) {
                $size_key = $variation_attributes['attribute_sizes'];
            } elseif (isset($variation_attributes['attribute_size'])) {
                $size_key = $variation_attributes['attribute_size'];
            }
            
            if ($size_key && !in_array($size_key, $sizes)) {
                $sizes[] = $size_key;
            }
        }
        
        // Debug: Check if sizes were found
        // Uncomment for debugging: 
        // error_log('Quick View - Found sizes: ' . print_r($sizes, true));
        // error_log('Quick View - Attributes: ' . print_r(array_keys($attributes), true));
    }
    
    // Get price
    $price_html = $product->get_price_html();
    $regular_price = $product->get_regular_price();
    $sale_price = $product->get_sale_price();
    $is_on_sale = $product->is_on_sale();
    
    // Get product rating
    $rating = $product->get_average_rating();
    $review_count = $product->get_review_count();
@endphp

<el-dialog id="quick-view-modal" class="quick-view-modal">
  <dialog id="quick-view-dialog" class="relative z-10 m-0 p-0 backdrop:bg-transparent">
    <el-dialog-backdrop class="fixed inset-0 hidden bg-gray-500/75 transition-opacity data-[closed]:opacity-0 data-[enter]:duration-300 data-[leave]:duration-200 data-[enter]:ease-out data-[leave]:ease-in md:block"></el-dialog-backdrop>

    <div tabindex="0" class="fixed inset-0 z-10 w-screen overflow-y-auto focus:outline focus:outline-0">
      <div class="flex min-h-full items-stretch justify-center text-center md:items-center md:px-2 lg:px-4">
        <span aria-hidden="true" class="hidden md:inline-block md:h-screen md:align-middle">&#8203;</span>
        
        <el-dialog-panel class="flex w-full transform text-left text-base transition data-[closed]:translate-y-4 data-[closed]:opacity-0 data-[enter]:duration-300 data-[leave]:duration-200 data-[enter]:ease-out data-[leave]:ease-in md:my-8 md:max-w-2xl md:px-4 data-[closed]:md:translate-y-0 data-[closed]:md:scale-95 lg:max-w-4xl">
          <div class="relative flex w-full items-center overflow-hidden bg-white px-4 pb-8 pt-14 shadow-2xl sm:px-6 sm:pt-8 md:p-6 lg:p-8">
            <button type="button" command="close" commandfor="quick-view-dialog" class="quick-view-close absolute right-4 top-4 text-gray-400 hover:text-gray-500 sm:right-6 sm:top-8 md:right-6 md:top-6 lg:right-8 lg:top-8">
              <span class="sr-only">Close</span>
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" data-slot="icon" aria-hidden="true" class="size-6">
                <path d="M6 18 18 6M6 6l12 12" stroke-linecap="round" stroke-linejoin="round" />
              </svg>
            </button>

            <div class="grid w-full grid-cols-1 items-start gap-x-6 gap-y-8 sm:grid-cols-12 lg:items-center lg:gap-x-8">
              <!-- Product Image -->
              <div class="quick-view-image-container sm:col-span-4 lg:col-span-5">
                @if(!empty($image_ids))
                  <img src="{{ wp_get_attachment_image_url($image_ids[0], 'woocommerce_single') }}" 
                       alt="{{ $product->get_name() }}" 
                       class="quick-view-main-image aspect-[2/3] w-full rounded-lg bg-gray-100 object-cover" />
                @else
                  <img src="{{ wc_placeholder_img_src('woocommerce_single') }}" 
                       alt="{{ $product->get_name() }}" 
                       class="quick-view-main-image aspect-[2/3] w-full rounded-lg bg-gray-100 object-cover" />
                @endif
              </div>
              
              <!-- Product Details -->
              <div class="sm:col-span-8 lg:col-span-7">
                <h2 class="text-xl font-medium text-gray-900 sm:pr-12">{{ $product->get_name() }}</h2>

                <section aria-labelledby="information-heading" class="mt-1">
                  <h3 id="information-heading" class="sr-only">Product information</h3>

                  <div class="quick-view-price font-medium text-gray-900">
                    {!! $price_html !!}
                  </div>

                  <!-- Reviews -->
                  @if($rating > 0)
                  <div class="mt-4">
                    <h4 class="sr-only">Reviews</h4>
                    <div class="flex items-center">
                      <p class="text-sm text-gray-700">
                        {{ number_format($rating, 1) }}
                        <span class="sr-only"> out of 5 stars</span>
                      </p>
                      <div class="ml-1 flex items-center">
                        @for($i = 1; $i <= 5; $i++)
                          @if($i <= floor($rating))
                            <svg viewBox="0 0 20 20" fill="currentColor" data-slot="icon" aria-hidden="true" class="size-5 shrink-0 text-yellow-400">
                              <path d="M10.868 2.884c-.321-.772-1.415-.772-1.736 0l-1.83 4.401-4.753.381c-.833.067-1.171 1.107-.536 1.651l3.62 3.102-1.106 4.637c-.194.813.691 1.456 1.405 1.02L10 15.591l4.069 2.485c.713.436 1.598-.207 1.404-1.02l-1.106-4.637 3.62-3.102c.635-.544.297-1.584-.536-1.65l-4.752-.382-1.831-4.401Z" clip-rule="evenodd" fill-rule="evenodd" />
                            </svg>
                          @elseif($i - 0.5 <= $rating)
                            <svg viewBox="0 0 20 20" fill="currentColor" data-slot="icon" aria-hidden="true" class="size-5 shrink-0 text-yellow-400">
                              <path d="M10.868 2.884c-.321-.772-1.415-.772-1.736 0l-1.83 4.401-4.753.381c-.833.067-1.171 1.107-.536 1.651l3.62 3.102-1.106 4.637c-.194.813.691 1.456 1.405 1.02L10 15.591l4.069 2.485c.713.436 1.598-.207 1.404-1.02l-1.106-4.637 3.62-3.102c.635-.544.297-1.584-.536-1.65l-4.752-.382-1.831-4.401Z" clip-rule="evenodd" fill-rule="evenodd" />
                            </svg>
                          @else
                            <svg viewBox="0 0 20 20" fill="currentColor" data-slot="icon" aria-hidden="true" class="size-5 shrink-0 text-gray-200">
                              <path d="M10.868 2.884c-.321-.772-1.415-.772-1.736 0l-1.83 4.401-4.753.381c-.833.067-1.171 1.107-.536 1.651l3.62 3.102-1.106 4.637c-.194.813.691 1.456 1.405 1.02L10 15.591l4.069 2.485c.713.436 1.598-.207 1.404-1.02l-1.106-4.637 3.62-3.102c.635-.544.297-1.584-.536-1.65l-4.752-.382-1.831-4.401Z" clip-rule="evenodd" fill-rule="evenodd" />
                            </svg>
                          @endif
                        @endfor
                      </div>
                      @if($review_count > 0)
                      <div class="ml-4 hidden lg:flex lg:items-center">
                        <span aria-hidden="true" class="text-gray-300">&middot;</span>
                        <a href="{{ $product->get_permalink() }}#reviews" class="ml-4 text-sm font-medium quick-view-link">
                          See all {{ $review_count }} {{ $review_count === 1 ? 'review' : 'reviews' }}
                        </a>
                      </div>
                      @endif
                    </div>
                  </div>
                  @endif
                </section>

                <section aria-labelledby="options-heading" class="mt-8">
                  <h3 id="options-heading" class="sr-only">Product options</h3>

                  <form class="quick-view-form" method="post" action="{{ wc_get_cart_url() }}" enctype="multipart/form-data" data-product_id="{{ $product_id }}">
                    @csrf
                    <input type="hidden" name="add-to-cart" value="{{ $product_id }}">
                    <input type="hidden" name="product_id" value="{{ $product_id }}">
                    <input type="hidden" name="variation_id" value="0" id="quick-view-variation-id">
                    <input type="hidden" name="quantity" value="1" id="quick-view-quantity">
                    
                    @if($is_variable)
                      <!-- Color picker -->
                      @if(!empty($color_variations))
                      <fieldset aria-label="Choose a color" class="quick-view-color-fieldset">
                        <legend class="text-sm font-medium text-gray-900">Color</legend>

                        <div class="mt-2 flex items-center gap-x-3 flex-wrap">
                          @foreach($color_variations as $color_key => $variation)
                            @php
                              $color_name = str_replace(['-', '_'], ' ', $color_key);
                              $color_name = ucwords($color_name);
                              
                              $color_term = get_term_by('slug', $color_key, 'pa_color');
                              $dot_color = '#cccccc';
                              
                              if ($color_term) {
                                $color_meta = get_term_meta($color_term->term_id, 'product_attribute_color', true);
                                if (!empty($color_meta)) {
                                  $dot_color = $color_meta;
                                }
                              }
                              
                              // Check if this color has any stock
                              $color_has_stock = false;
                              foreach ($variations as $check_variation) {
                                $check_attributes = $check_variation['attributes'];
                                $check_color_key = $check_attributes['attribute_pa_color'] ?? $check_attributes['attribute_color'] ?? '';
                                if ($check_color_key === $color_key) {
                                  $check_variation_obj = wc_get_product($check_variation['variation_id']);
                                  if ($check_variation_obj && $check_variation_obj->is_in_stock()) {
                                    $color_has_stock = true;
                                    break;
                                  }
                                }
                              }
                            @endphp
                            <div>
                              <input type="radio" 
                                     name="attribute_pa_color" 
                                     value="{{ $color_key }}" 
                                     id="quick-view-color-{{ $color_key }}"
                                     aria-label="{{ $color_name }}"
                                     data-color="{{ $color_key }}"
                                     data-color-name="{{ $color_name }}"
                                     data-variation-id="{{ $variation['variation_id'] }}"
                                     class="quick-view-color-input"
                                     style="background-color: {{ $dot_color }};"
                                     {{ $loop->first ? 'checked' : '' }}
                                     {{ !$color_has_stock ? 'disabled' : '' }} />
                            </div>
                            @if(!$color_has_stock)
                              <input type="hidden" name="attribute_pa_color" value="" disabled>
                            @endif
                          @endforeach
                        </div>
                      </fieldset>
                      @endif

                      <!-- Size picker -->
                      @php
                        // Try to get sizes from attributes if not found in variations
                        if (empty($sizes) && isset($attributes['pa_sizes'])) {
                            $sizes = $attributes['pa_sizes'];
                        } elseif (empty($sizes) && isset($attributes['pa_size'])) {
                            $sizes = $attributes['pa_size'];
                        } elseif (empty($sizes) && isset($attributes['sizes'])) {
                            $sizes = $attributes['sizes'];
                        } elseif (empty($sizes) && isset($attributes['size'])) {
                            $sizes = $attributes['size'];
                        }
                      @endphp
                      @if(!empty($sizes) && is_array($sizes))
                      <fieldset aria-label="Choose a size" class="quick-view-size-fieldset">
                        <div class="flex items-center justify-between">
                          <div class="text-sm font-medium text-gray-900">Size</div>
                          <a href="{{ $product->get_permalink() }}" class="text-sm font-medium quick-view-link">Size guide</a>
                        </div>
                        <div class="quick-view-size-buttons">
                          @php
                            $size_order = ['xs', 's', 'm', 'l', 'xl', 'xxl', 'xxxl'];
                            $sorted_sizes = [];
                            foreach ($size_order as $ordered_size) {
                              foreach ($sizes as $size) {
                                $normalized_size = strtolower(str_replace(['-', '_'], '', $size));
                                if ($normalized_size === $ordered_size) {
                                  $sorted_sizes[] = $size;
                                  break;
                                }
                              }
                            }
                            foreach ($sizes as $size) {
                              if (!in_array($size, $sorted_sizes)) {
                                $sorted_sizes[] = $size;
                              }
                            }
                            
                            // Determine the correct attribute name for form submission
                            $size_attr_name = 'attribute_pa_sizes';
                            if (isset($attributes['pa_size'])) {
                                $size_attr_name = 'attribute_pa_size';
                            } elseif (isset($attributes['sizes'])) {
                                $size_attr_name = 'attribute_sizes';
                            } elseif (isset($attributes['size'])) {
                                $size_attr_name = 'attribute_size';
                            }
                          @endphp
                          @foreach($sorted_sizes as $size)
                            @php
                              $size_name = str_replace(['-', '_'], ' ', $size);
                              $size_name = strtoupper($size_name);
                            @endphp
                            <label aria-label="{{ $size_name }}" class="quick-view-size-label">
                              <input type="radio" 
                                     name="{{ $size_attr_name }}" 
                                     value="{{ $size }}" 
                                     id="quick-view-size-{{ $size }}"
                                     class="quick-view-size-input" />
                              <span>{{ $size_name }}</span>
                            </label>
                          @endforeach
                        </div>
                      </fieldset>
                      @endif
                      
                      @foreach($attributes as $attr_name => $attr_values)
                        @php
                          // Skip color and size attributes as they're already handled
                          $skip_attrs = ['pa_color', 'pa_sizes', 'pa_size', 'sizes', 'size', 'color'];
                          $should_skip = in_array($attr_name, $skip_attrs) || 
                                        in_array(str_replace('pa_', '', $attr_name), $skip_attrs);
                        @endphp
                        @if(!$should_skip)
                          <input type="hidden" name="attribute_{{ $attr_name }}" value="" id="quick-view-attr-{{ $attr_name }}">
                        @endif
                      @endforeach
                    @endif

                    <button type="submit" class="quick-view-add-to-cart" {{ $is_variable ? 'disabled' : '' }}>
                      Add to bag
                    </button>

                    <p class="absolute left-4 top-4 text-center sm:static sm:mt-8">
                      <a href="{{ $product->get_permalink() }}" class="font-medium quick-view-link">View full details</a>
                    </p>
                  </form>
                </section>
              </div>
            </div>
          </div>
        </el-dialog-panel>
      </div>
    </div>
  </dialog>
</el-dialog>
