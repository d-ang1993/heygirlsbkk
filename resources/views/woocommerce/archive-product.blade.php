@extends('layouts.app')

@section('content')
  @php 
    // Remove WooCommerce default breadcrumbs and controls
    remove_action('woocommerce_before_main_content', 'woocommerce_breadcrumb', 20);
    remove_action('woocommerce_before_shop_loop', 'woocommerce_result_count', 20);
    remove_action('woocommerce_before_shop_loop', 'woocommerce_catalog_ordering', 30);
    do_action('woocommerce_before_main_content') 
  @endphp

  @if (woocommerce_product_loop())
    @php do_action('woocommerce_before_shop_loop') @endphp

    @php
      // Collect all products from the current query
      $archive_products = [];
      while (have_posts()) {
        the_post();
        global $product;
        if ($product && is_a($product, 'WC_Product')) {
          $archive_products[] = $product;
        }
      }
      wp_reset_postdata();
      
      // Get current query info
      global $wp_query;
      $total_products = $wp_query->found_posts;
      
      // Get all available product categories
      $product_categories = get_terms([
        'taxonomy' => 'product_cat',
        'hide_empty' => true,
      ]);
      
      // Get all available colors from products
      $all_colors = [];
      $color_terms = get_terms([
        'taxonomy' => 'pa_color',
        'hide_empty' => true,
      ]);
      foreach ($color_terms as $term) {
        $color_meta = get_term_meta($term->term_id, 'product_attribute_color', true);
        $all_colors[] = [
          'slug' => $term->slug,
          'name' => $term->name,
          'color' => $color_meta ?: '#cccccc',
        ];
      }
      
      // Get all available sizes from products
      $all_sizes = [];
      $size_terms = get_terms([
        'taxonomy' => 'pa_sizes',
        'hide_empty' => true,
      ]);
      if (empty($size_terms) || is_wp_error($size_terms)) {
        // Try alternative size taxonomy names
        $size_terms = get_terms([
          'taxonomy' => 'pa_size',
          'hide_empty' => true,
        ]);
      }
      if (!empty($size_terms) && !is_wp_error($size_terms)) {
        foreach ($size_terms as $term) {
          $all_sizes[] = [
            'slug' => $term->slug,
            'name' => strtoupper($term->name),
          ];
        }
      }
      
      // Get current filter values from URL
      $selected_colors = isset($_GET['filter_color']) ? (array) $_GET['filter_color'] : [];
      $selected_categories = isset($_GET['filter_category']) ? (array) $_GET['filter_category'] : [];
      $selected_sizes = isset($_GET['filter_size']) ? (array) $_GET['filter_size'] : [];
      $current_orderby = $_GET['orderby'] ?? 'menu_order';
      
      // Prepare categories data for React component
      $categories_data = [];
      if (!empty($product_categories) && !is_wp_error($product_categories)) {
        foreach ($product_categories as $cat) {
          $categories_data[] = [
            'slug' => $cat->slug,
            'name' => $cat->name,
            'link' => get_term_link($cat),
          ];
        }
      }
    @endphp

    <div class="bg-white">
      <!-- Mobile filter dialog (non-React) -->
      <el-dialog>
        <dialog id="mobile-filters" class="m-0 overflow-hidden p-0 backdrop:bg-transparent lg:hidden">
          <el-dialog-backdrop class="fixed inset-0 bg-black/25 transition-opacity duration-300 ease-linear data-[closed]:opacity-0"></el-dialog-backdrop>

          <div tabindex="0" class="fixed inset-0 flex focus:outline focus:outline-0">
            <el-dialog-panel class="relative ml-auto flex size-full max-w-xs transform flex-col overflow-y-auto bg-white pb-6 pt-4 shadow-xl transition duration-300 ease-in-out data-[closed]:translate-x-full">
              <div class="flex items-center justify-between px-4">
                <h2 class="text-lg font-medium text-gray-900">Filters</h2>
                <button type="button" command="close" commandfor="mobile-filters" class="relative -mr-2 flex size-10 items-center justify-center rounded-md bg-white p-2 text-gray-400 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                  <span class="absolute -inset-0.5"></span>
                  <span class="sr-only">Close menu</span>
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" data-slot="icon" aria-hidden="true" class="size-6">
                    <path d="M6 18 18 6M6 6l12 12" stroke-linecap="round" stroke-linejoin="round" />
                  </svg>
                </button>
              </div>

              <!-- Mobile Filters Form -->
              <form method="get" class="mt-4 border-t border-gray-200">
                @if(!empty($product_categories) && !is_wp_error($product_categories))
                  <h3 class="sr-only">Categories</h3>
                  <ul role="list" class="px-2 py-3 font-medium text-gray-900">
                    @foreach($product_categories as $category)
                      <li>
                        <a href="{{ get_term_link($category) }}" class="block px-2 py-3">{{ $category->name }}</a>
                      </li>
                    @endforeach
                  </ul>
                @endif

                @if(!empty($all_colors))
                  <div class="border-t border-gray-200 px-4 py-6">
                    <h3 class="-mx-2 -my-3 flow-root">
                      <button type="button" command="--toggle" commandfor="filter-section-mobile-color" class="flex w-full items-center justify-between bg-white px-2 py-3 text-gray-400 hover:text-gray-500">
                        <span class="font-medium text-gray-900">Color</span>
                        <span class="ml-6 flex items-center">
                          <svg viewBox="0 0 20 20" fill="currentColor" data-slot="icon" aria-hidden="true" class="size-5 [[aria-expanded='true']_&]:hidden">
                            <path d="M10.75 4.75a.75.75 0 0 0-1.5 0v4.5h-4.5a.75.75 0 0 0 0 1.5h4.5v4.5a.75.75 0 0 0 1.5 0v-4.5h4.5a.75.75 0 0 0 0-1.5h-4.5v-4.5Z" />
                          </svg>
                          <svg viewBox="0 0 20 20" fill="currentColor" data-slot="icon" aria-hidden="true" class="size-5 [&:not([aria-expanded='true']_*)]:hidden">
                            <path d="M4 10a.75.75 0 0 1 .75-.75h10.5a.75.75 0 0 1 0 1.5H4.75A.75.75 0 0 1 4 10Z" clip-rule="evenodd" fill-rule="evenodd" />
                          </svg>
                        </span>
                      </button>
                    </h3>
                    <el-disclosure id="filter-section-mobile-color" hidden class="pt-6 [&:not([hidden])]:block">
                      <div class="space-y-6">
                        @foreach($all_colors as $index => $color)
                          <div class="flex gap-3">
                            <div class="flex h-5 shrink-0 items-center">
                              <div class="group grid size-4 grid-cols-1">
                                <input id="filter-mobile-color-{{ $index }}" type="checkbox" name="filter_color[]" value="{{ $color['slug'] }}" {{ in_array($color['slug'], $selected_colors) ? 'checked' : '' }} class="col-start-1 row-start-1 appearance-none rounded border border-gray-300 bg-white checked:border-indigo-600 checked:bg-indigo-600 indeterminate:border-indigo-600 indeterminate:bg-indigo-600 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 disabled:border-gray-300 disabled:bg-gray-100 disabled:checked:bg-gray-100 forced-colors:appearance-auto" />
                                <svg viewBox="0 0 14 14" fill="none" class="pointer-events-none col-start-1 row-start-1 size-3.5 self-center justify-self-center stroke-white group-has-[:disabled]:stroke-gray-950/25">
                                  <path d="M3 8L6 11L11 3.5" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="opacity-0 group-has-[:checked]:opacity-100" />
                                  <path d="M3 7H11" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="opacity-0 group-has-[:indeterminate]:opacity-100" />
                                </svg>
                              </div>
                            </div>
                            <label for="filter-mobile-color-{{ $index }}" class="min-w-0 flex-1 text-gray-500">{{ $color['name'] }}</label>
                          </div>
                        @endforeach
                      </div>
                    </el-disclosure>
                  </div>
                @endif

                @if(!empty($product_categories) && !is_wp_error($product_categories))
                  <div class="border-t border-gray-200 px-4 py-6">
                    <h3 class="-mx-2 -my-3 flow-root">
                      <button type="button" command="--toggle" commandfor="filter-section-mobile-category" class="flex w-full items-center justify-between bg-white px-2 py-3 text-gray-400 hover:text-gray-500">
                        <span class="font-medium text-gray-900">Category</span>
                        <span class="ml-6 flex items-center">
                          <svg viewBox="0 0 20 20" fill="currentColor" data-slot="icon" aria-hidden="true" class="size-5 [[aria-expanded='true']_&]:hidden">
                            <path d="M10.75 4.75a.75.75 0 0 0-1.5 0v4.5h-4.5a.75.75 0 0 0 0 1.5h4.5v4.5a.75.75 0 0 0 1.5 0v-4.5h4.5a.75.75 0 0 0 0-1.5h-4.5v-4.5Z" />
                          </svg>
                          <svg viewBox="0 0 20 20" fill="currentColor" data-slot="icon" aria-hidden="true" class="size-5 [&:not([aria-expanded='true']_*)]:hidden">
                            <path d="M4 10a.75.75 0 0 1 .75-.75h10.5a.75.75 0 0 1 0 1.5H4.75A.75.75 0 0 1 4 10Z" clip-rule="evenodd" fill-rule="evenodd" />
                          </svg>
                        </span>
                      </button>
                    </h3>
                    <el-disclosure id="filter-section-mobile-category" hidden class="pt-6 [&:not([hidden])]:block">
                      <div class="space-y-6">
                        @foreach($product_categories as $index => $category)
                          <div class="flex gap-3">
                            <div class="flex h-5 shrink-0 items-center">
                              <div class="group grid size-4 grid-cols-1">
                                <input id="filter-mobile-category-{{ $index }}" type="checkbox" name="filter_category[]" value="{{ $category->slug }}" {{ in_array($category->slug, $selected_categories) ? 'checked' : '' }} class="col-start-1 row-start-1 appearance-none rounded border border-gray-300 bg-white checked:border-indigo-600 checked:bg-indigo-600 indeterminate:border-indigo-600 indeterminate:bg-indigo-600 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 disabled:border-gray-300 disabled:bg-gray-100 disabled:checked:bg-gray-100 forced-colors:appearance-auto" />
                                <svg viewBox="0 0 14 14" fill="none" class="pointer-events-none col-start-1 row-start-1 size-3.5 self-center justify-self-center stroke-white group-has-[:disabled]:stroke-gray-950/25">
                                  <path d="M3 8L6 11L11 3.5" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="opacity-0 group-has-[:checked]:opacity-100" />
                                  <path d="M3 7H11" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="opacity-0 group-has-[:indeterminate]:opacity-100" />
                                </svg>
                              </div>
                            </div>
                            <label for="filter-mobile-category-{{ $index }}" class="min-w-0 flex-1 text-gray-500">{{ $category->name }}</label>
                          </div>
                        @endforeach
                      </div>
                    </el-disclosure>
                  </div>
                @endif

                @if(!empty($all_sizes))
                  <div class="border-t border-gray-200 px-4 py-6">
                    <h3 class="-mx-2 -my-3 flow-root">
                      <button type="button" command="--toggle" commandfor="filter-section-mobile-size" class="flex w-full items-center justify-between bg-white px-2 py-3 text-gray-400 hover:text-gray-500">
                        <span class="font-medium text-gray-900">Size</span>
                        <span class="ml-6 flex items-center">
                          <svg viewBox="0 0 20 20" fill="currentColor" data-slot="icon" aria-hidden="true" class="size-5 [[aria-expanded='true']_&]:hidden">
                            <path d="M10.75 4.75a.75.75 0 0 0-1.5 0v4.5h-4.5a.75.75 0 0 0 0 1.5h4.5v4.5a.75.75 0 0 0 1.5 0v-4.5h4.5a.75.75 0 0 0 0-1.5h-4.5v-4.5Z" />
                          </svg>
                          <svg viewBox="0 0 20 20" fill="currentColor" data-slot="icon" aria-hidden="true" class="size-5 [&:not([aria-expanded='true']_*)]:hidden">
                            <path d="M4 10a.75.75 0 0 1 .75-.75h10.5a.75.75 0 0 1 0 1.5H4.75A.75.75 0 0 1 4 10Z" clip-rule="evenodd" fill-rule="evenodd" />
                          </svg>
                        </span>
                      </button>
                    </h3>
                    <el-disclosure id="filter-section-mobile-size" hidden class="pt-6 [&:not([hidden])]:block">
                      <div class="space-y-6">
                        @foreach($all_sizes as $index => $size)
                          <div class="flex gap-3">
                            <div class="flex h-5 shrink-0 items-center">
                              <div class="group grid size-4 grid-cols-1">
                                <input id="filter-mobile-size-{{ $index }}" type="checkbox" name="filter_size[]" value="{{ $size['slug'] }}" {{ in_array($size['slug'], $selected_sizes) ? 'checked' : '' }} class="col-start-1 row-start-1 appearance-none rounded border border-gray-300 bg-white checked:border-indigo-600 checked:bg-indigo-600 indeterminate:border-indigo-600 indeterminate:bg-indigo-600 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 disabled:border-gray-300 disabled:bg-gray-100 disabled:checked:bg-gray-100 forced-colors:appearance-auto" />
                                <svg viewBox="0 0 14 14" fill="none" class="pointer-events-none col-start-1 row-start-1 size-3.5 self-center justify-self-center stroke-white group-has-[:disabled]:stroke-gray-950/25">
                                  <path d="M3 8L6 11L11 3.5" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="opacity-0 group-has-[:checked]:opacity-100" />
                                  <path d="M3 7H11" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="opacity-0 group-has-[:indeterminate]:opacity-100" />
                                </svg>
                              </div>
                            </div>
                            <label for="filter-mobile-size-{{ $index }}" class="min-w-0 flex-1 text-gray-500">{{ $size['name'] }}</label>
                          </div>
                        @endforeach
                      </div>
                    </el-disclosure>
                  </div>
                @endif

                <!-- Preserve other query parameters -->
                @if(isset($_GET['orderby']))
                  <input type="hidden" name="orderby" value="{{ $_GET['orderby'] }}" />
                @endif
                
                <div class="px-4 py-4">
                  <button type="submit" class="w-full rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">Apply Filters</button>
                </div>
              </form>
            </el-dialog-panel>
          </div>
        </dialog>
      </el-dialog>

      <main class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <!-- Header with Title, Sort, and Mobile Filter Button -->
        <div class="flex items-baseline justify-between border-b border-gray-200 pb-6 pt-24">
          <h1 class="text-4xl font-bold tracking-tight text-gray-900">{{ woocommerce_page_title(false) }}</h1>

          <div class="flex items-center">
            <el-dropdown class="relative inline-block text-left">
              <button class="group inline-flex justify-center text-sm font-medium text-gray-700 hover:text-gray-900">
                Sort
                <svg viewBox="0 0 20 20" fill="currentColor" data-slot="icon" aria-hidden="true" class="-mr-1 ml-1 size-5 shrink-0 text-gray-400 group-hover:text-gray-500">
                  <path d="M5.22 8.22a.75.75 0 0 1 1.06 0L10 11.94l3.72-3.72a.75.75 0 1 1 1.06 1.06l-4.25 4.25a.75.75 0 0 1-1.06 0L5.22 9.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd" fill-rule="evenodd" />
                </svg>
              </button>

              <el-menu anchor="bottom end" popover class="m-0 w-40 origin-top-right rounded-md bg-white p-0 shadow-2xl ring-1 ring-black/5 transition [--anchor-gap:theme(spacing.2)] [transition-behavior:allow-discrete] focus:outline-none data-[closed]:scale-95 data-[closed]:transform data-[closed]:opacity-0 data-[enter]:duration-100 data-[leave]:duration-75 data-[enter]:ease-out data-[leave]:ease-in">
                <div class="py-1">
                  @php
                    $current_url = remove_query_arg('paged');
                    $sort_options = [
                      'menu_order' => 'Default sorting',
                      'popularity' => 'Sort by popularity',
                      'rating' => 'Sort by average rating',
                      'date' => 'Sort by latest',
                      'price' => 'Sort by price: low to high',
                      'price-desc' => 'Sort by price: high to low',
                    ];
                  @endphp
                  @foreach($sort_options as $value => $label)
                    @php
                      $sort_url = add_query_arg('orderby', $value, $current_url);
                      $is_active = $current_orderby === $value;
                    @endphp
                    <a href="{{ $sort_url }}" class="block px-4 py-2 text-sm {{ $is_active ? 'font-medium text-gray-900' : 'text-gray-500' }} focus:bg-gray-100 focus:outline-none">{{ $label }}</a>
                  @endforeach
                </div>
              </el-menu>
            </el-dropdown>

            <button type="button" class="-m-2 ml-5 p-2 text-gray-400 hover:text-gray-500 sm:ml-7">
              <span class="sr-only">View grid</span>
              <svg viewBox="0 0 20 20" fill="currentColor" data-slot="icon" aria-hidden="true" class="size-5">
                <path d="M4.25 2A2.25 2.25 0 0 0 2 4.25v2.5A2.25 2.25 0 0 0 4.25 9h2.5A.75.75 0 0 0 9 6.75v-2.5A2.25 2.25 0 0 0 6.75 2h-2.5Zm0 9A2.25 2.25 0 0 0 2 13.25v2.5A2.25 2.25 0 0 0 4.25 18h2.5A2.25 2.25 0 0 0 9 15.75v-2.5A2.25 2.25 0 0 0 6.75 11h-2.5Zm9-9A2.25 2.25 0 0 0 11 4.25v2.5A2.25 2.25 0 0 0 13.25 9h2.5A2.25 2.25 0 0 0 18 6.75v-2.5A2.25 2.25 0 0 0 15.75 2h-2.5Zm0 9A2.25 2.25 0 0 0 11 13.25v2.5A2.25 2.25 0 0 0 13.25 18h2.5A2.25 2.25 0 0 0 18 15.75v-2.5A2.25 2.25 0 0 0 15.75 11h-2.5Z" clip-rule="evenodd" fill-rule="evenodd" />
              </svg>
            </button>
            <button type="button" command="show-modal" commandfor="mobile-filters" class="-m-2 ml-4 p-2 text-gray-400 hover:text-gray-500 sm:ml-6 lg:hidden">
              <span class="sr-only">Filters</span>
              <svg viewBox="0 0 20 20" fill="currentColor" data-slot="icon" aria-hidden="true" class="size-5">
                <path d="M2.628 1.601C5.028 1.206 7.49 1 10 1s4.973.206 7.372.601a.75.75 0 0 1 .628.74v2.288a2.25 2.25 0 0 1-.659 1.59l-4.682 4.683a2.25 2.25 0 0 0-.659 1.59v3.037c0 .684-.31 1.33-.844 1.757l-1.937 1.55A.75.75 0 0 1 8 18.25v-5.757a2.25 2.25 0 0 0-.659-1.591L2.659 6.22A2.25 2.25 0 0 1 2 4.629V2.34a.75.75 0 0 1 .628-.74Z" clip-rule="evenodd" fill-rule="evenodd" />
              </svg>
            </button>
          </div>
        </div>

        <section aria-labelledby="products-heading" class="pb-24 pt-6">
          <h2 id="products-heading" class="sr-only">Products</h2>

          <div class="grid grid-cols-1 gap-x-8 gap-y-10 lg:grid-cols-4">
            <!-- Desktop Filters Sidebar - React mount point -->
            <div id="archive-filters-react" class="hidden lg:block"></div>
            
            <!-- Fallback form (hidden, for non-JS users) -->
            <form method="get" class="hidden lg:block" style="display: none !important;">
              @if(!empty($product_categories) && !is_wp_error($product_categories))
                <h3 class="sr-only">Categories</h3>
                <ul role="list" class="space-y-4 border-b border-gray-200 pb-6 text-sm font-medium text-gray-900">
                  @foreach($product_categories as $category)
                    <li>
                      <a href="{{ get_term_link($category) }}" class="hover:text-gray-600">{{ $category->name }}</a>
                    </li>
                  @endforeach
                </ul>
              @endif

              @if(!empty($all_colors))
                <div class="border-b border-gray-200 py-6">
                  <h3 class="-my-3 flow-root">
                    <button type="button" command="--toggle" commandfor="filter-section-color" class="flex w-full items-center justify-between bg-white py-3 text-sm text-gray-400 hover:text-gray-500">
                      <span class="font-medium text-gray-900">Color</span>
                      <span class="ml-6 flex items-center">
                        <svg viewBox="0 0 20 20" fill="currentColor" data-slot="icon" aria-hidden="true" class="size-5 [[aria-expanded='true']_&]:hidden">
                          <path d="M10.75 4.75a.75.75 0 0 0-1.5 0v4.5h-4.5a.75.75 0 0 0 0 1.5h4.5v4.5a.75.75 0 0 0 1.5 0v-4.5h4.5a.75.75 0 0 0 0-1.5h-4.5v-4.5Z" />
                        </svg>
                        <svg viewBox="0 0 20 20" fill="currentColor" data-slot="icon" aria-hidden="true" class="size-5 [&:not([aria-expanded='true']_*)]:hidden">
                          <path d="M4 10a.75.75 0 0 1 .75-.75h10.5a.75.75 0 0 1 0 1.5H4.75A.75.75 0 0 1 4 10Z" clip-rule="evenodd" fill-rule="evenodd" />
                        </svg>
                      </span>
                    </button>
                  </h3>
                  <el-disclosure id="filter-section-color" hidden class="pt-6 [&:not([hidden])]:block">
                    <div class="space-y-4">
                      @foreach($all_colors as $index => $color)
                        <div class="flex gap-3">
                          <div class="flex h-5 shrink-0 items-center">
                            <div class="group grid size-4 grid-cols-1">
                              <input id="filter-color-{{ $index }}" type="checkbox" name="filter_color[]" value="{{ $color['slug'] }}" {{ in_array($color['slug'], $selected_colors) ? 'checked' : '' }} class="col-start-1 row-start-1 appearance-none rounded border border-gray-300 bg-white checked:border-indigo-600 checked:bg-indigo-600 indeterminate:border-indigo-600 indeterminate:bg-indigo-600 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 disabled:border-gray-300 disabled:bg-gray-100 disabled:checked:bg-gray-100 forced-colors:appearance-auto" />
                              <svg viewBox="0 0 14 14" fill="none" class="pointer-events-none col-start-1 row-start-1 size-3.5 self-center justify-self-center stroke-white group-has-[:disabled]:stroke-gray-950/25">
                                <path d="M3 8L6 11L11 3.5" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="opacity-0 group-has-[:checked]:opacity-100" />
                                <path d="M3 7H11" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="opacity-0 group-has-[:indeterminate]:opacity-100" />
                              </svg>
                            </div>
                          </div>
                          <label for="filter-color-{{ $index }}" class="text-sm text-gray-600">{{ $color['name'] }}</label>
                        </div>
                      @endforeach
                    </div>
                  </el-disclosure>
                </div>
              @endif

              @if(!empty($product_categories) && !is_wp_error($product_categories))
                <div class="border-b border-gray-200 py-6">
                  <h3 class="-my-3 flow-root">
                    <button type="button" command="--toggle" commandfor="filter-section-category" class="flex w-full items-center justify-between bg-white py-3 text-sm text-gray-400 hover:text-gray-500">
                      <span class="font-medium text-gray-900">Category</span>
                      <span class="ml-6 flex items-center">
                        <svg viewBox="0 0 20 20" fill="currentColor" data-slot="icon" aria-hidden="true" class="size-5 [[aria-expanded='true']_&]:hidden">
                          <path d="M10.75 4.75a.75.75 0 0 0-1.5 0v4.5h-4.5a.75.75 0 0 0 0 1.5h4.5v4.5a.75.75 0 0 0 1.5 0v-4.5h4.5a.75.75 0 0 0 0-1.5h-4.5v-4.5Z" />
                        </svg>
                        <svg viewBox="0 0 20 20" fill="currentColor" data-slot="icon" aria-hidden="true" class="size-5 [&:not([aria-expanded='true']_*)]:hidden">
                          <path d="M4 10a.75.75 0 0 1 .75-.75h10.5a.75.75 0 0 1 0 1.5H4.75A.75.75 0 0 1 4 10Z" clip-rule="evenodd" fill-rule="evenodd" />
                        </svg>
                      </span>
                    </button>
                  </h3>
                  <el-disclosure id="filter-section-category" hidden class="pt-6 [&:not([hidden])]:block">
                    <div class="space-y-4">
                      @foreach($product_categories as $index => $category)
                        <div class="flex gap-3">
                          <div class="flex h-5 shrink-0 items-center">
                            <div class="group grid size-4 grid-cols-1">
                              <input id="filter-category-{{ $index }}" type="checkbox" name="filter_category[]" value="{{ $category->slug }}" {{ in_array($category->slug, $selected_categories) ? 'checked' : '' }} class="col-start-1 row-start-1 appearance-none rounded border border-gray-300 bg-white checked:border-indigo-600 checked:bg-indigo-600 indeterminate:border-indigo-600 indeterminate:bg-indigo-600 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 disabled:border-gray-300 disabled:bg-gray-100 disabled:checked:bg-gray-100 forced-colors:appearance-auto" />
                              <svg viewBox="0 0 14 14" fill="none" class="pointer-events-none col-start-1 row-start-1 size-3.5 self-center justify-self-center stroke-white group-has-[:disabled]:stroke-gray-950/25">
                                <path d="M3 8L6 11L11 3.5" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="opacity-0 group-has-[:checked]:opacity-100" />
                                <path d="M3 7H11" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="opacity-0 group-has-[:indeterminate]:opacity-100" />
                              </svg>
                            </div>
                          </div>
                          <label for="filter-category-{{ $index }}" class="text-sm text-gray-600">{{ $category->name }}</label>
                        </div>
                      @endforeach
                    </div>
                  </el-disclosure>
                </div>
              @endif

              @if(!empty($all_sizes))
                <div class="border-b border-gray-200 py-6">
                  <h3 class="-my-3 flow-root">
                    <button type="button" command="--toggle" commandfor="filter-section-size" class="flex w-full items-center justify-between bg-white py-3 text-sm text-gray-400 hover:text-gray-500">
                      <span class="font-medium text-gray-900">Size</span>
                      <span class="ml-6 flex items-center">
                        <svg viewBox="0 0 20 20" fill="currentColor" data-slot="icon" aria-hidden="true" class="size-5 [[aria-expanded='true']_&]:hidden">
                          <path d="M10.75 4.75a.75.75 0 0 0-1.5 0v4.5h-4.5a.75.75 0 0 0 0 1.5h4.5v4.5a.75.75 0 0 0 1.5 0v-4.5h4.5a.75.75 0 0 0 0-1.5h-4.5v-4.5Z" />
                        </svg>
                        <svg viewBox="0 0 20 20" fill="currentColor" data-slot="icon" aria-hidden="true" class="size-5 [&:not([aria-expanded='true']_*)]:hidden">
                          <path d="M4 10a.75.75 0 0 1 .75-.75h10.5a.75.75 0 0 1 0 1.5H4.75A.75.75 0 0 1 4 10Z" clip-rule="evenodd" fill-rule="evenodd" />
                        </svg>
                      </span>
                    </button>
                  </h3>
                  <el-disclosure id="filter-section-size" hidden class="pt-6 [&:not([hidden])]:block">
                    <div class="space-y-4">
                      @foreach($all_sizes as $index => $size)
                        <div class="flex gap-3">
                          <div class="flex h-5 shrink-0 items-center">
                            <div class="group grid size-4 grid-cols-1">
                              <input id="filter-size-{{ $index }}" type="checkbox" name="filter_size[]" value="{{ $size['slug'] }}" {{ in_array($size['slug'], $selected_sizes) ? 'checked' : '' }} class="col-start-1 row-start-1 appearance-none rounded border border-gray-300 bg-white checked:border-indigo-600 checked:bg-indigo-600 indeterminate:border-indigo-600 indeterminate:bg-indigo-600 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 disabled:border-gray-300 disabled:bg-gray-100 disabled:checked:bg-gray-100 forced-colors:appearance-auto" />
                              <svg viewBox="0 0 14 14" fill="none" class="pointer-events-none col-start-1 row-start-1 size-3.5 self-center justify-self-center stroke-white group-has-[:disabled]:stroke-gray-950/25">
                                <path d="M3 8L6 11L11 3.5" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="opacity-0 group-has-[:checked]:opacity-100" />
                                <path d="M3 7H11" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="opacity-0 group-has-[:indeterminate]:opacity-100" />
                              </svg>
                            </div>
                          </div>
                          <label for="filter-size-{{ $index }}" class="text-sm text-gray-600">{{ $size['name'] }}</label>
                        </div>
                      @endforeach
                    </div>
                  </el-disclosure>
                </div>
              @endif

              <!-- Preserve orderby parameter -->
              @if(isset($_GET['orderby']))
                <input type="hidden" name="orderby" value="{{ $_GET['orderby'] }}" />
              @endif
            </form>

            <!-- Product grid -->
            <div class="lg:col-span-3 archive-product-grid">
              <div id="archive-product-grid-container">
                @include('components.product-grid', [
                  'title' => '',
                  'products' => $archive_products,
                  'columns' => 4,
                  'showDiscount' => true,
                  'showQuickView' => true,
                  'viewAllUrl' => null
                ])
              </div>
            </div>
          </div>
        </section>
      </main>
    </div>

    @php do_action('woocommerce_after_shop_loop') @endphp
  @else
    @php do_action('woocommerce_no_products_found') @endphp
  @endif

  @php do_action('woocommerce_after_main_content') @endphp

  <!-- Load Tailwind Plus Elements for filter components -->
  <script src="https://cdn.jsdelivr.net/npm/@tailwindplus/elements@1" type="module"></script>

  <!-- Pass data to React component -->
  <script>
    window.archiveFiltersData = {
      initialFilters: {
        colors: @json($selected_colors),
        categories: @json($selected_categories),
        sizes: @json($selected_sizes),
        orderby: '{{ $current_orderby }}',
      },
      filterOptions: {
        categories: @json($categories_data),
        colors: @json($all_colors),
        sizes: @json($all_sizes),
      },
      ajaxUrl: '{{ admin_url('admin-ajax.php') }}',
      nonce: '{{ wp_create_nonce('archive_filters_nonce') }}',
    };
  </script>

  <!-- Load React (entry file that renders the component) -->
  @viteReactRefresh
  @vite('resources/js/archive-filters.jsx')

  <!-- Archive Page JavaScript -->
  <script>
    // Re-initialize product grid after AJAX updates
    window.productGridInit = function() {
      // Re-observe images for lazy loading
      const imageObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
          if (entry.isIntersecting) {
            const img = entry.target;
            if (img.dataset.src) {
              img.src = img.dataset.src;
              img.removeAttribute('data-src');
              observer.unobserve(img);
            }
          }
        });
      }, {
        rootMargin: '50px 0px',
        threshold: 0.1
      });
      
      document.querySelectorAll('.product-main-image[data-src]').forEach(img => {
        imageObserver.observe(img);
      });
      
      // Re-initialize product carousel functionality
      const productCards = document.querySelectorAll('.product-card');
      productCards.forEach(card => {
        const productImage = card.querySelector('.product-image.has-carousel');
        if (!productImage) return;
        
        const images = JSON.parse(productImage.getAttribute('data-images') || '[]');
        if (images.length <= 1) return;
        
        const img = productImage.querySelector('.product-main-image');
        const indicators = productImage.querySelectorAll('.indicator');
        let currentIndex = 0;
        let intervalId = null;
        const cycleDuration = 3000;
        
        function showImage(index) {
          currentIndex = index;
          img.style.opacity = '0';
          setTimeout(() => {
            img.src = images[index];
            img.style.opacity = '1';
            indicators.forEach((ind, i) => {
              ind.classList.toggle('active', i === index);
            });
          }, 150);
        }
        
        function startCarousel() {
          if (intervalId) clearInterval(intervalId);
          intervalId = setInterval(() => {
            const nextIndex = (currentIndex + 1) % images.length;
            showImage(nextIndex);
          }, cycleDuration);
        }
        
        function stopCarousel() {
          if (intervalId) {
            clearInterval(intervalId);
            intervalId = null;
          }
        }
        
        productImage.addEventListener('mouseenter', startCarousel);
        productImage.addEventListener('mouseleave', stopCarousel);
        
        indicators.forEach((indicator, index) => {
          indicator.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            stopCarousel();
            showImage(index);
          });
        });
      });

      // If the sidebar was replaced via AJAX, safely re-mount the React filters
      if (typeof window.mountArchiveFilters === 'function') {
        window.mountArchiveFilters();
      }
    };
  </script>
@endsection
