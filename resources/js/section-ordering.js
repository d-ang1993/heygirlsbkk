/**
 * Section Ordering JavaScript
 * Handles drag and drop reordering of homepage sections
 */

(function($) {
    'use strict';

    // Wait for customizer to be ready
    wp.customize.bind('ready', function() {
        console.log('Section Ordering: Initializing...');
        
        // Define sections with their display names
        var sections = {
            'hero': 'Homepage Hero',
            'new_drops': 'New Drops Carousel', 
            'featured_products': 'Featured Products',
            'new_arrival': 'New Arrival',
            'footer': 'Footer'
        };
        
        // Function to create the sortable list
        function createSortableList() {
            var $sectionOrderingSection = $('#accordion-section-section_ordering .accordion-section-content');
            
            if ($sectionOrderingSection.length === 0) {
                console.log('Section Ordering: Section not found, retrying...');
                setTimeout(createSortableList, 1000);
                return;
            }
            
            console.log('Section Ordering: Section found, creating list');
            
            // Remove existing list if any
            $('#section-ordering-list').remove();
            
            // Create sortable list
            var $sectionOrdering = $('<div id="section-ordering-list"></div>');
            $sectionOrderingSection.append($sectionOrdering);
            
            // Get current order
            var currentOrder = wp.customize('homepage_section_order').get();
            console.log('Section Ordering: Current order:', currentOrder);
            
            var orderArray = currentOrder ? currentOrder.split(',') : Object.keys(sections);
            
            // Create sortable list items
            orderArray.forEach(function(sectionId) {
                if (sections[sectionId]) {
                    $sectionOrdering.append(
                        '<div class="section-order-item" data-section="' + sectionId + '">' +
                        '<span class="dashicons dashicons-menu"></span> ' + sections[sectionId] +
                        '</div>'
                    );
                }
            });
            
            // Make sortable
            $sectionOrdering.sortable({
                placeholder: 'section-order-placeholder',
                update: function(event, ui) {
                    var newOrder = [];
                    $sectionOrdering.find('.section-order-item').each(function() {
                        newOrder.push($(this).data('section'));
                    });
                    console.log('Section Ordering: New order:', newOrder.join(','));
                    wp.customize('homepage_section_order').set(newOrder.join(','));
                }
            });
            
            console.log('Section Ordering: Sortable list created successfully');
        }
        
        // Create the list
        createSortableList();
    });
    
    // Add CSS
    $('<style>')
        .prop('type', 'text/css')
        .html('#section-ordering-list { margin: 10px 0; }' +
              '.section-order-item { ' +
              '  background: #fff; ' +
              '  border: 1px solid #ddd; ' +
              '  padding: 10px 15px; ' +
              '  margin: 5px 0; ' +
              '  cursor: move; ' +
              '  border-radius: 4px; ' +
              '  display: flex; ' +
              '  align-items: center; ' +
              '}' +
              '.section-order-item:hover { ' +
              '  background: #f9f9f9; ' +
              '  border-color: #999; ' +
              '}' +
              '.section-order-item .dashicons { ' +
              '  margin-right: 8px; ' +
              '  color: #666; ' +
              '}' +
              '.section-order-placeholder { ' +
              '  background: #f0f0f0; ' +
              '  border: 2px dashed #ccc; ' +
              '  height: 40px; ' +
              '  margin: 5px 0; ' +
              '  border-radius: 4px; ' +
              '}')
        .appendTo('head');

})(jQuery);
