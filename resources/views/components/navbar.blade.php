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
                @if(is_user_logged_in())
                    {{-- User is logged in - show My Account --}}
                    <a href="{{ esc_url(wc_get_page_permalink('myaccount')) }}" class="navbar-link">MY ACCOUNT</a>
                    <span class="navbar-separator">|</span>
                    <a href="{{ esc_url(wp_logout_url(home_url())) }}" class="navbar-link">LOGOUT</a>
                @else
                    {{-- User is not logged in - show Sign In/Sign Up --}}
                    <a href="{{ esc_url(add_query_arg('action', 'login', wc_get_page_permalink('myaccount'))) }}" class="navbar-link">SIGN IN</a>
                    <span class="navbar-separator">|</span>
                    <a href="{{ esc_url(add_query_arg('action', 'register', wc_get_page_permalink('myaccount'))) }}" class="navbar-link">SIGN UP</a>
                @endif
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
                <div class="navbar-search-container">
                    <form class="navbar-search" method="get" action="{{ home_url('/') }}">
                        <input type="text" 
                        placeholder="FIND SOMETHING" 
                        class="search-input" 
                        name="s" 
                        autocomplete="off"
                        autocorrect="off"
                        autocapitalize="off"
                        spellcheck="false"
                        value="{{ get_search_query() }}" oninput="this.style.width = Math.max(120, this.value.length * 8 + 20) + 'px'">
                        <button type="submit" class="search-label">SEARCH</button>
                    </form>
                    
                    <!-- Search Dropdown Results -->
                    <div class="search-dropdown" id="search-dropdown" style="display: none;">
                        <div class="search-dropdown-content">
                            <div class="search-results-list" id="search-results-list">
                                <!-- Results will be populated here -->
                            </div>
                            <div class="search-dropdown-footer">
                                <a href="#" id="view-all-results" class="view-all-link">
                                    View all results
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <a href="#" class="navbar-link navbar-bag cart-trigger" data-cart-url="{{ wc_get_cart_url() }}">
                    BAG(<span class="bag-count">{{ WC()->cart->get_cart_contents_count() }}</span>)
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

                <!-- Accessories -->
                <div class="dropdown-section">
                    <div class="dropdown-section-title">ACCESSORIES</div>
                    @php
                    // Get the "Accessories" product category
                    $accessories_parent_cat = get_term_by('name', 'Accessories', 'product_cat');
                    $accessory_items = [];
                    
                    if (!$accessories_parent_cat) {
                        // Try alternative names
                        $accessories_parent_cat = get_term_by('slug', 'accessories', 'product_cat');
                    }
                    
                    if ($accessories_parent_cat) {
                        // Get all child categories directly
                        $child_categories = get_terms(array(
                            'taxonomy' => 'product_cat',
                            'parent' => $accessories_parent_cat->term_id,
                            'hide_empty' => false
                        ));
                        
                        // Convert child categories to menu-like items
                        foreach ($child_categories as $child_cat) {
                            $accessory_items[] = (object) array(
                                'title' => $child_cat->name,
                                'url' => get_term_link($child_cat),
                                'object' => 'product_cat',
                                'object_id' => $child_cat->term_id
                            );
                        }
                    }
                    @endphp
                    @foreach($accessory_items as $item)
                        <a href="{{ $item->url }}" class="dropdown-category">
                            {{ strtoupper($item->title) }}
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
        @endif
    </div>
</nav>

<!-- Cart Drawer -->
<div class="cart-drawer" id="cart-drawer">
    <div class="cart-drawer-overlay"></div>
    <div class="cart-drawer-content">
        <div class="cart-drawer-header">
            <h3>Shopping Cart</h3>
            <button class="cart-drawer-close" type="button">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>
        
        <div class="cart-drawer-body">
            <div class="cart-loading">
                <div class="spinner"></div>
                <p>Loading cart...</p>
            </div>
            
            <div class="cart-content" style="display: none;">
                <div class="cart-items"></div>
                <div class="cart-empty" style="display: none;">
                    <div class="empty-cart-icon">
                        <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <circle cx="9" cy="21" r="1"></circle>
                            <circle cx="20" cy="21" r="1"></circle>
                            <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                        </svg>
                    </div>
                    <h4>Your cart is empty</h4>
                    <p>Add some items to get started</p>
                    <button class="btn btn-primary continue-shopping">Continue Shopping</button>
                </div>
            </div>
        </div>
        
        <div class="cart-drawer-footer" style="display: none;">
            <div class="cart-totals">
                <div class="cart-subtotal">
                    <span>Subtotal:</span>
                    <span class="cart-subtotal-amount"></span>
                </div>
                <div class="cart-total">
                    <span>Total:</span>
                    <span class="cart-total-amount"></span>
                </div>
            </div>
            <div class="cart-actions">
                <a href="/cart/" class="btn btn-secondary view-cart-btn">View Cart</a>
                <a href="/checkout/" class="btn btn-primary checkout-btn">Checkout</a>
            </div>
        </div>
    </div>
</div>

@endif