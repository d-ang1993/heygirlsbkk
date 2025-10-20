@php
// Get current section order
$currentOrder = get_theme_mod('homepage_section_order', 'hero,new_drops,featured_products,new_arrival,footer');
$sections = explode(',', $currentOrder);

// Define section names
$sectionNames = [
    'hero' => 'Homepage Hero',
    'new_drops' => 'New Drops Carousel',
    'featured_products' => 'Featured Products', 
    'new_arrival' => 'New Arrival',
    'footer' => 'Footer'
];
@endphp

<div style="background: #f9f9f9; padding: 15px; border-radius: 5px; margin: 10px 0;">
    <h4 style="margin: 0 0 10px 0;">Current Section Order:</h4>
    <ol style="margin: 0; padding-left: 20px;">
        @foreach($sections as $index => $section)
            @if(isset($sectionNames[$section]))
                <li style="margin: 5px 0;">{{ $sectionNames[$section] }}</li>
            @endif
        @endforeach
    </ol>
    <p style="margin: 10px 0 0 0; font-size: 12px; color: #666;">
        <strong>Note:</strong> The drag and drop interface should appear above this list. 
        If you don't see it, try refreshing the customizer page.
    </p>
</div>
