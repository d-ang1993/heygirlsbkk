@php
$navbar_enable = get_theme_mod('navbar_enable', true);
$navbar_logo = get_theme_mod('navbar_logo', 'HEYGIRLSBKK');

// Get WordPress menus by location
$primary_menu = null;
$shop_menu = null;

// Get menu by location - try multiple possible location names
$primary_menu_locations = get_nav_menu_locations();
$menu_locations_to_try = ['primary', 'primary_navigation', 'main-navigation', 'header-menu'];

foreach ($menu_locations_to_try as $location) {
    if (isset($primary_menu_locations[$location]) && !$primary_menu) {
        $primary_menu = wp_get_nav_menu_items($primary_menu_locations[$location]);
        break;
    }
}

// Try to get shop dropdown menu from various possible locations
$shop_locations_to_try = ['shop-dropdown', 'shop_dropdown', 'shop-dropdown-menu', 'shop_dropdown_menu'];

foreach ($shop_locations_to_try as $location) {
    if (isset($primary_menu_locations[$location]) && !$shop_menu) {
        $shop_menu = wp_get_nav_menu_items($primary_menu_locations[$location]);
        break;
    }
}

// If no shop menu found by location, try to find it by menu name
if (!$shop_menu) {
    $menus = wp_get_nav_menus();
    foreach ($menus as $menu) {
        if (stripos($menu->name, 'shop') !== false && stripos($menu->name, 'dropdown') !== false) {
            $shop_menu = wp_get_nav_menu_items($menu->term_id);
            break;
        }
    }
}
@endphp

@if($navbar_enable)
<nav class="main-navbar">
    <div class="navbar-container">
        <!-- Top Bar -->
        <div class="navbar-top">
            <div class="navbar-location">THAILAND</div>
            <div class="navbar-account">
                <a href="#" class="navbar-link">SIGN IN</a>
                <span class="navbar-separator">|</span>
                <a href="#" class="navbar-link">SIGN UP</a>
            </div>
        </div>

        <!-- Main Navigation -->
        <div class="navbar-main">
            <div class="navbar-left">
                {{-- Always show SHOP link first --}}
                @if($shop_menu && !empty($shop_menu))
                    @php
                    $has_shop_in_primary = false;
                    if ($primary_menu) {
                        foreach ($primary_menu as $item) {
                            if ($item->menu_item_parent == 0 && strtoupper($item->title) === 'SHOP') {
                                $has_shop_in_primary = true;
                                break;
                            }
                        }
                    }
                    @endphp
                    
                    @if($has_shop_in_primary)
                        {{-- Show SHOP from primary menu if it exists --}}
                        @foreach($primary_menu as $item)
                            @if($item->menu_item_parent == 0 && strtoupper($item->title) === 'SHOP')
                                <a href="{{ $item->url }}" class="navbar-link navbar-shop" data-dropdown="shop">
                                    {{ strtoupper($item->title) }}
                                    <svg class="dropdown-arrow" width="12" height="12" viewBox="0 0 12 12" fill="currentColor">
                                        <path d="M6 8L2 4h8l-4 4z"/>
                                    </svg>
                                </a>
                                @break
                            @endif
                        @endforeach
                    @else
                        {{-- Show default SHOP link --}}
                        <a href="#" class="navbar-link navbar-shop" data-dropdown="shop">
                            SHOP
                            <svg class="dropdown-arrow" width="12" height="12" viewBox="0 0 12 12" fill="currentColor">
                                <path d="M6 8L2 4h8l-4 4z"/>
                            </svg>
                        </a>
                    @endif
                @endif
                
                {{-- Show other primary menu items (excluding SHOP) --}}
                @if($primary_menu && !empty($primary_menu))
                    @foreach($primary_menu as $item)
                        @if($item->menu_item_parent == 0 && strtoupper($item->title) !== 'SHOP')
                            <a href="{{ $item->url }}" class="navbar-link">
                                {{ strtoupper($item->title) }}
                            </a>
                        @endif
                    @endforeach
                @endif
            </div>

            <div class="navbar-center">
                <a href="{{ home_url() }}" class="navbar-logo">{{ $navbar_logo }}</a>
            </div>

            <div class="navbar-right">
                <div class="navbar-search">
                    <input type="text" placeholder="FIND SOMETHING" class="search-input">
                    <span class="search-label">SEARCH</span>
                </div>
                <a href="#" class="navbar-link navbar-bag">
                    BAG(<span class="bag-count">0</span>)
                </a>
            </div>
        </div>

        <!-- Dropdown Menu -->
        @if($shop_menu && !empty($shop_menu))
        <div class="navbar-dropdown" id="shop-dropdown">
            <div class="dropdown-container">
                <!-- General Pages -->
                <div class="dropdown-section">
                    <div class="dropdown-section-title">GENERAL</div>
                    @php
                    // Get all menu items that are pages
                    $page_items = [];
                    foreach ($shop_menu as $item) {
                        if ($item->object === 'page') {
                            $page_items[] = $item;
                        }
                    }
                    @endphp
                    @foreach($page_items as $item)
                        <a href="{{ $item->url }}" class="dropdown-category">
                            {{ strtoupper($item->title) }}
                        </a>
                    @endforeach
                </div>

                <!-- Collections/Seasons -->
                <div class="dropdown-section">
                    <div class="dropdown-section-title">COLLECTIONS</div>
                    @php
                    // Get the "Collections" product category
                    $collections_parent_cat = get_term_by('name', 'Collections', 'product_cat');
                    $collection_items = [];
                    
                    if ($collections_parent_cat) {
                        // Check each menu item to see if it's linked to a product category that's a child of Collections
                        foreach ($shop_menu as $item) {
                            // Check if this menu item is linked to a product category
                            if ($item->object === 'product_cat') {
                                $category = get_term($item->object_id, 'product_cat');
                                if ($category && !is_wp_error($category)) {
                                    // Check if this category's parent is Collections
                                    if ($category->parent == $collections_parent_cat->term_id) {
                                        $collection_items[] = $item;
                                    }
                                }
                            }
                        }
                    }
                    @endphp
                    @foreach($collection_items as $item)
                        <a href="{{ $item->url }}" class="dropdown-category">
                            {{ strtoupper($item->title) }}
                        </a>
                    @endforeach
                </div>

                <!-- Categories -->
                <div class="dropdown-section">
                    <div class="dropdown-section-title">CATEGORIES</div>
                    @php
                    // Get all menu items that are linked to tags only
                    $category_items = [];
                    foreach ($shop_menu as $item) {
                        if ($item->object === 'product_tag') {
                            $category_items[] = $item;
                        }
                    }
                    @endphp
                    @foreach($category_items as $item)
                        <a href="{{ $item->url }}" class="dropdown-category {{ strtoupper($item->title) === 'SALE' ? 'dropdown-sale' : '' }}">
                            {{ strtoupper($item->title) }}
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
        @endif
    </div>
</nav>
@endif