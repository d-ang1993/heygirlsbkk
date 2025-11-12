/**
 * Product Quick View Modal
 * Handles opening/closing modal and variation selection
 */

class QuickView {
    constructor() {
        this.currentProductId = null;
        this.selectedAttributes = {};
        this.variations = {};
        this.init();
    }

    init() {
        // Load Tailwind Plus Elements if not already loaded
        this.loadTailwindElements();
        
        // Bind click handlers
        document.addEventListener('click', (e) => {
            if (e.target.closest('.quick-view-btn')) {
                e.preventDefault();
                const productId = e.target.closest('.quick-view-btn').dataset.productId;
                this.openModal(productId);
            }
            
            if (e.target.closest('.quick-view-close')) {
                this.closeModal();
            }
        });

        // Handle form submission
        document.addEventListener('submit', (e) => {
            if (e.target.closest('.quick-view-form')) {
                e.preventDefault();
                e.stopPropagation();
                e.stopImmediatePropagation();
                // Prevent scroll to top
                const scrollY = window.scrollY;
                this.handleAddToCart(e.target).then(() => {
                    // Restore scroll position if it changed
                    if (window.scrollY !== scrollY) {
                        window.scrollTo(0, scrollY);
                    }
                });
                return false;
            }
        });

        // Handle variation selection
        this.bindVariationHandlers();
    }

    loadTailwindElements() {
        return new Promise((resolve) => {
            if (window.customElements && customElements.get('el-dialog')) {
                // Already loaded
                resolve();
                return;
            }
            
            if (!document.querySelector('script[src*="@tailwindplus/elements"]')) {
                const script = document.createElement('script');
                script.src = 'https://cdn.jsdelivr.net/npm/@tailwindplus/elements@1';
                script.type = 'module';
                script.onload = () => {
                    // Wait a bit for custom elements to register
                    setTimeout(resolve, 200);
                };
                script.onerror = () => {
                    console.warn('Failed to load Tailwind Elements, using native dialog');
                    resolve();
                };
                document.head.appendChild(script);
            } else {
                setTimeout(resolve, 200);
            }
        });
    }

    async openModal(productId) {
        this.currentProductId = productId;
        
        // Load Tailwind Elements first
        await this.loadTailwindElements();
        
        // Show loading state
        this.showLoadingModal();

        try {
            // Fetch product data and variations in parallel once we have productId
            const [response, apiVariations] = await Promise.all([
                fetch(`/wp-admin/admin-ajax.php?action=get_quick_view&product_id=${productId}`),
                this.getProductVariations(productId)
            ]);
            
            // Update this.variations with the fetched variations array
            this.variations = apiVariations || [];
            
            // Log variations immediately after fetching
            console.log('=== QUICK VIEW MODAL OPENED ===');
            console.log('Product ID:', productId);
            console.log('Variations from REST API:', apiVariations);
            console.log('this.variations updated:', this.variations);
            
            const data = await response.json();
            console.log('Quick View Data:', data);
            
            if (!data.success) {
                throw new Error(data.data?.message || 'Failed to load product');
            }
            
            const html = data.data.html;

            // Remove existing modal if any
            const existingModal = document.querySelector('#quick-view-modal');
            if (existingModal) {
                existingModal.remove();
            }

            // Insert new modal into body
            document.body.insertAdjacentHTML('beforeend', html);

            // Wait for modal to be inserted and Tailwind Elements to initialize
            await this.waitForModal();
            
            // Log PHP variations after modal is inserted
            console.log('Variations from PHP (window.quickViewVariations):', window.quickViewVariations);
            
            // Merge/prioritize REST API variations if available
            if (apiVariations && apiVariations.length > 0) {
                // Convert API format to match expected format
                const formattedApiVariations = apiVariations.map(v => {
                    const attrs = v.attributes || {};
                    
                    return {
                        id: v.id,
                        variation_id: v.id,
                        price: v.price,
                        price_html: v.price_html || `$${v.price}`,
                        regular_price: v.regular_price,
                        sale_price: v.sale_price,
                        in_stock: v.in_stock !== false,
                        stock_quantity: v.stock_quantity || null,
                        image: v.image_url ? { src: v.image_url } : null,
                        image_url: v.image_url,
                        // Map attributes to flat format
                        color: attrs['color'] || attrs['attribute_pa_color'] || attrs['attribute_color'] || '',
                        sizes: attrs['sizes'] || attrs['size'] || attrs['attribute_pa_sizes'] || attrs['attribute_pa_size'] || attrs['attribute_sizes'] || attrs['attribute_size'] || '',
                        // Keep original attributes
                        attributes: Object.keys(attrs).reduce((acc, key) => {
                            acc[`attribute_${key}`] = attrs[key];
                            acc[`attribute_pa_${key}`] = attrs[key];
                            return acc;
                        }, {})
                    };
                });
                
                console.log('Formatted API Variations:', formattedApiVariations);
                
                // Use API variations if available, otherwise fall back to PHP variations
                if (window.quickViewVariations && window.quickViewVariations.length > 0) {
                    // Merge both sources, prioritizing API data
                    window.quickViewVariations = [...formattedApiVariations, ...window.quickViewVariations];
                } else {
                    window.quickViewVariations = formattedApiVariations;
                }
                
                // Update this.variations with the final merged variations
                this.variations = window.quickViewVariations;
                
                console.log('Final Merged Variations:', window.quickViewVariations);
                console.log('this.variations (final):', this.variations);
            }

            // Get the dialog element and open it
            const dialog = document.querySelector('#quick-view-dialog');
            if (dialog && dialog.showModal) {
                dialog.showModal();
                this.bindVariationHandlers();
            } else {
                // Fallback to basic display if native dialog not supported
                const modal = document.querySelector('#quick-view-modal');
                if (modal) {
                    modal.style.display = 'block';
                    this.bindVariationHandlers();
                }
            }
        } catch (error) {
            console.error('Error loading quick view:', error);
            alert('Unable to load product details. Please try again.');
            this.closeModal();
        }
    }

    showLoadingModal() {
        const loadingHTML = `
            <el-dialog id="quick-view-modal" class="quick-view-modal">
                <dialog id="quick-view-dialog" class="relative z-10 m-0 p-0 backdrop:bg-transparent" open>
                    <el-dialog-backdrop class="fixed inset-0 hidden bg-gray-500/75 transition-opacity md:block"></el-dialog-backdrop>
                    <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
                        <div class="flex min-h-full items-center justify-center">
                            <div class="text-center">
                                <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-gray-900"></div>
                                <p class="mt-4 text-gray-600">Loading product...</p>
                            </div>
                        </div>
                    </div>
                </dialog>
            </el-dialog>
        `;
        
        const existingModal = document.querySelector('#quick-view-modal');
        if (existingModal) {
            existingModal.remove();
        }
        document.body.insertAdjacentHTML('beforeend', loadingHTML);
    }

    waitForModal() {
        return new Promise((resolve) => {
            const checkModal = () => {
                const modal = document.querySelector('#quick-view-modal');
                if (modal) {
                    // Wait a bit for Tailwind Elements to initialize
                    setTimeout(resolve, 100);
                } else {
                    setTimeout(checkModal, 50);
                }
            };
            checkModal();
        });
    }

    closeModal() {
        const dialog = document.querySelector('#quick-view-dialog');
        if (dialog && dialog.close) {
            dialog.close();
        }
        
        // Remove modal after animation
        setTimeout(() => {
            const modal = document.querySelector('#quick-view-modal');
            if (modal) {
                modal.remove();
            }
        }, 300);
        
        // Reset state
        this.selectedAttributes = {};
        this.currentProductId = null;
    }

    bindVariationHandlers() {
        // Color selection
        document.querySelectorAll('.quick-view-color-input').forEach(input => {
            // Remove existing listeners
            const newInput = input.cloneNode(true);
            input.parentNode.replaceChild(newInput, input);
            
            newInput.addEventListener('change', (e) => {
                this.handleColorChange(e.target);
            });
        });

        // Size selection
        document.querySelectorAll('.quick-view-size-input').forEach(input => {
            // Remove existing listeners
            const newInput = input.cloneNode(true);
            input.parentNode.replaceChild(newInput, input);
            
            newInput.addEventListener('change', (e) => {
                this.handleSizeChange(e.target);
            });
        });

        // Initialize with first selected color if available
        setTimeout(() => {
            const firstColor = document.querySelector('.quick-view-color-input:checked');
            if (firstColor) {
                this.handleColorChange(firstColor);
            } else {
                // Auto-select first color if only one color available (like product-variations.js)
                const colorInputs = document.querySelectorAll('.quick-view-color-input:not([disabled])');
                if (colorInputs.length === 1) {
                    colorInputs[0].checked = true;
                    this.handleColorChange(colorInputs[0]);
                }
            }
            
            // Initialize display
            this.updateProductDisplay();
        }, 100);
    }

    handleColorChange(colorInput) {
        const colorKey = colorInput.value;
        // Use 'color' key to match product-variations.js format
        this.selectedAttributes['color'] = colorKey;
        
        // Also set attribute_pa_color for form submission
        this.selectedAttributes['attribute_pa_color'] = colorKey;
        
        // Update available sizes for this color
        this.updateAvailableSizes(colorKey);
        
        // Update display and find matching variation
        this.updateProductDisplay();
        
        // Log variation info
        const matchingVariation = this.findMatchingVariation();
        console.log('=== COLOR CLICKED ===');
        console.log('Selected Color:', colorKey);
        console.log('Selected Attributes:', this.selectedAttributes);
        if (matchingVariation) {
            console.log('Variation ID:', matchingVariation.id || matchingVariation.variation_id);
            console.log('Variation Attributes:', matchingVariation.attributes || {});
            console.log('Matching Variation:', matchingVariation);
        } else {
            console.log('No matching variation found yet (need to select size)');
        }
    }

    handleSizeChange(sizeInput) {
        const sizeValue = sizeInput.value;
        const sizeAttrName = sizeInput.name; // Get the actual attribute name from input
        
        // Use 'sizes' key to match product-variations.js format
        this.selectedAttributes['sizes'] = sizeValue;
        
        // Also set the actual attribute name for form submission (could be attribute_pa_sizes, attribute_pa_size, etc.)
        this.selectedAttributes[sizeAttrName] = sizeValue;
        
        // Update display and find matching variation
        this.updateProductDisplay();
        
        // Log variation info
        const matchingVariation = this.findMatchingVariation();
        console.log('=== SIZE CLICKED ===');
        console.log('Selected Size:', sizeValue);
        console.log('Size Attribute Name:', sizeAttrName);
        console.log('Selected Attributes:', this.selectedAttributes);
        if (matchingVariation) {
            console.log('Variation ID:', matchingVariation.id || matchingVariation.variation_id);
            console.log('Variation Attributes:', matchingVariation.attributes || {});
            console.log('Matching Variation:', matchingVariation);
        } else {
            console.log('No matching variation found');
        }
    }
    
    updateProductDisplay() {
        if (!window.quickViewVariations || window.quickViewVariations.length === 0) {
            return;
        }
        
        // Find matching variation using same logic as product-variations.js
        const matchingVariation = this.findMatchingVariation();
        
        if (matchingVariation) {
            // Update price
            const priceHtml = matchingVariation.price_html || matchingVariation.price || 'Price not available';
            const priceContainer = document.querySelector('.quick-view-price');
            if (priceContainer) {
                priceContainer.innerHTML = priceHtml;
            }
            
            // Update image if variation has different image
            this.updateVariationImage(matchingVariation);
            
            // Update variation ID input
            const variationId = matchingVariation.id || matchingVariation.variation_id;
            const variationIdInput = document.querySelector('#quick-view-variation-id');
            if (variationIdInput && variationId) {
                variationIdInput.value = variationId;
            }
            
            // Enable/disable add to cart button
            const addToCartBtn = document.querySelector('.quick-view-add-to-cart');
            if (addToCartBtn) {
                addToCartBtn.disabled = !matchingVariation.in_stock;
                addToCartBtn.textContent = matchingVariation.in_stock ? 'Add to bag' : 'Out of Stock';
            }
        } else {
            // No matching variation found
            const addToCartBtn = document.querySelector('.quick-view-add-to-cart');
            if (addToCartBtn) {
                addToCartBtn.disabled = true;
                addToCartBtn.textContent = 'Select Options';
            }
        }
    }

    updateVariationImage(variation) {
        if (!variation) return;
        
        let imageSrc = null;
        
        // Try different image source formats (matching product-variations.js format)
        if (variation.image && variation.image.src) {
            imageSrc = variation.image.src;
        } else if (variation.image && typeof variation.image === 'string') {
            imageSrc = variation.image;
        } else if (variation.image_url) {
            imageSrc = variation.image_url;
        }
        
        if (imageSrc) {
            const img = document.querySelector('.quick-view-main-image');
            if (img) {
                img.src = imageSrc;
            }
        }
    }

    updateAvailableSizes(colorKey) {
        if (!window.quickViewVariations) return;
        
        // Get all size inputs
        const sizeInputs = document.querySelectorAll('.quick-view-size-input');
        
        sizeInputs.forEach(sizeInput => {
            const sizeValue = sizeInput.value;
            const sizeLabel = sizeInput.closest('.quick-view-size-label');
            
            // Check if this size is available for selected color
            const isAvailable = this.isSizeAvailableForColor(colorKey, sizeValue);
            
            if (isAvailable) {
                sizeInput.disabled = false;
                if (sizeLabel) {
                    sizeLabel.classList.remove('opacity-50');
                }
            } else {
                sizeInput.disabled = true;
                sizeInput.checked = false;
                if (sizeLabel) {
                    sizeLabel.classList.add('opacity-50');
                }
            }
        });
    }

    isSizeAvailableForColor(colorKey, sizeValue) {
        if (!window.quickViewVariations) return false;
        
        return window.quickViewVariations.some(variation => {
            // Support both flat format (product-variations.js) and WooCommerce format
            let variationColor = '';
            let variationSize = '';
            
            // Check flat format first (color, sizes properties directly on variation)
            if (variation.color) {
                variationColor = variation.color;
            } else if (variation.attributes) {
                // WooCommerce format
                const attrs = variation.attributes;
                variationColor = attrs['attribute_pa_color'] || attrs['attribute_color'] || '';
            }
            
            if (variation.sizes) {
                variationSize = variation.sizes;
            } else if (variation.attributes) {
                // WooCommerce format - check multiple possible attribute names
                const attrs = variation.attributes;
                variationSize = attrs['attribute_pa_sizes'] || 
                               attrs['attribute_pa_size'] || 
                               attrs['attribute_sizes'] || 
                               attrs['attribute_size'] || '';
            }
            
            // Check if in stock
            const inStock = variation.in_stock !== false && variation.is_purchasable !== false;
            
            return variationColor === colorKey && variationSize === sizeValue && inStock;
        });
    }
    // 🔍 Helper function to fetch all variations from WooCommerce REST API
    async getProductVariations(productId) {
        try {
            const response = await fetch(`/wp-admin/admin-ajax.php?action=get_product_variations&product_id=${productId}`, {
                method: 'GET',
                credentials: 'same-origin',
            });
    
            const data = await response.json();
    
            if (!data.success || !data.data?.variations?.length) {
                console.warn(`⚠️ No variations returned for product ${productId}`);
                return [];
            }
    
            console.log(`✅ Retrieved ${data.data.variations.length} variations via AJAX proxy`, data.data.variations);
    
            // Normalize key structure
            return data.data.variations.map(v => ({
                id: v.variation_id || v.id,
                variation_id: v.variation_id || v.id,
                price: v.price,
                regular_price: v.regular_price,
                sale_price: v.sale_price,
                price_html: v.price_html || (v.price ? `$${v.price}` : ''),
                in_stock: v.is_in_stock || v.stock_status === 'instock',
                stock_quantity: v.stock_quantity || null,
                attributes: v.attributes,
                image_url: v.image_url,
                image: v.image_url ? { src: v.image_url } : null,
                color: v.attributes?.attribute_pa_color || v.attributes?.color || '',
                sizes: v.attributes?.attribute_pa_size || v.attributes?.attribute_pa_sizes || v.attributes?.size || '',
            }));
        } catch (err) {
            console.error('❌ getProductVariations failed:', err);
            return [];
        }
    }
    
    findMatchingVariation() {
        const variations = window.quickView?.variations || window.quickViewVariations;
        const selected = this.selectedAttributes;
    
        if (!variations || variations.length === 0) {
            console.warn('⚠️ No variations available.');
            return null;
        }
    
        // Extract color and size from selected attributes
        const selectedColor = selected.color || selected.attribute_pa_color || selected.attribute_color || '';
        const selectedSize = selected.sizes || selected.size || selected.attribute_pa_size || selected.attribute_pa_sizes || selected.attribute_size || selected.attribute_sizes || '';
    
        if (!selectedColor || !selectedSize) {
            console.warn('⚠️ Color and size must both be selected.');
            console.log('Selected color:', selectedColor);
            console.log('Selected size:', selectedSize);
            return null;
        }
    
        console.log('🔍 Finding matching variation...');
        console.log('Selected color:', selectedColor);
        console.log('Selected size:', selectedSize);
        console.log('Available variations:', variations);
    
        const match = variations.find(variation => {
            const variationId = variation.id || variation.variation_id;
            console.log('Checking variation:', variationId);
            
            // Get all possible color values from variation
            const variationColors = [];
            const variationSizes = [];
            
            // Check direct properties first
            if (variation.color) variationColors.push(variation.color);
            if (variation.sizes) variationSizes.push(variation.sizes);
            if (variation.size) variationSizes.push(variation.size);
            
            // Check attributes object - need to check ALL possible key formats
            const attrs = variation.attributes || {};
            
            // Get all attribute keys that might contain color
            Object.keys(attrs).forEach(key => {
                if (key.toLowerCase().includes('color')) {
                    variationColors.push(attrs[key]);
                }
                if (key.toLowerCase().includes('size')) {
                    variationSizes.push(attrs[key]);
                }
            });
            
            console.log('Variation colors found:', variationColors);
            console.log('Variation sizes found:', variationSizes);
            
            // Normalize and compare
            const normalizedSelectedColor = String(selectedColor).toLowerCase().trim();
            const normalizedSelectedSize = String(selectedSize).toLowerCase().trim();
            
            const colorMatch = variationColors.some(color => 
                String(color).toLowerCase().trim() === normalizedSelectedColor
            );
            
            const sizeMatch = variationSizes.some(size => 
                String(size).toLowerCase().trim() === normalizedSelectedSize
            );
            
            if (colorMatch && sizeMatch) {
                console.log(`✅ Found matching variation ${variationId} - Color: ${colorMatch}, Size: ${sizeMatch}`);
                return true;
            } else {
                console.log(`❌ Variation ${variationId} doesn't match - Color: ${colorMatch}, Size: ${sizeMatch}`);
                return false;
            }
        });
    
        if (match) {
            const variationId = match.id || match.variation_id;
            console.log('✅ Returning variation ID:', variationId);
            return match;
        } else {
            console.warn('❌ No matching variation found for color:', selectedColor, 'size:', selectedSize);
            return null;
        }
    }
    

    checkVariationComplete() {
        // This is now handled in updateProductDisplay()
        // But we keep it for compatibility
        this.updateProductDisplay();
    }

    async handleAddToCart(form) {
        // Prevent any default form behavior
        if (form && typeof form.preventDefault === 'function') {
            form.preventDefault();
        }
        
        // Save current scroll position to prevent scroll to top
        const scrollY = window.scrollY;
        const scrollX = window.scrollX;
        
        // Lock scroll position during AJAX
        const lockScroll = () => {
            window.scrollTo(scrollX, scrollY);
        };
        
        // Set up scroll lock interval
        const scrollLockInterval = setInterval(lockScroll, 10);
        
        const addToCartBtn = form.querySelector('.quick-view-add-to-cart');
        const originalText = addToCartBtn.textContent;
        
        console.log('=== QUICK VIEW ADD TO CART CALLED ===');
        
        // Find the matching variation
        const matchingVariation = this.findMatchingVariation();
        console.log('Matching variation:', matchingVariation);
        console.log('Selected attributes:', this.selectedAttributes);
        
        if (!matchingVariation) {
            clearInterval(scrollLockInterval);
            alert('Please select all required options');
            return;
        }
        
        // Disable button and show loading
        addToCartBtn.disabled = true;
        addToCartBtn.textContent = 'Adding...';
        
        try {
            // Update the form with the correct variation_id and attributes
            this.updateQuickViewForm(form, matchingVariation);
            
            // Create FormData from the updated form
            const formData = new FormData(form);
            
            // Ensure variation_id is set (double-check)
            const variationId = matchingVariation.id || matchingVariation.variation_id;
            if (variationId) {
                formData.set('variation_id', variationId);
                console.log('🔧 Set variation_id to:', variationId);
            }
            
            // Add AJAX action for WooCommerce (append to FormData, not URL)
            formData.append('wc-ajax', 'add_to_cart');
            
            // Use form action URL (cart URL) for WooCommerce AJAX
            // WooCommerce requires wc-ajax parameter to work with the cart URL
            const formAction = form.action || window.location.href;
            
            // Log form data for debugging
            console.log('📦 Form Data:', Object.fromEntries([...formData.entries()]));
            console.log('🚀 Submitting AJAX request to:', formAction);
            
            // Use WooCommerce AJAX to add to cart
            const response = await fetch(formAction, {
                method: 'POST',
                body: formData,
                credentials: 'same-origin',
                redirect: 'manual', // Prevent any redirects that might cause scroll
            });
            
            // Clear scroll lock after fetch completes
            clearInterval(scrollLockInterval);
            
            // Restore scroll position immediately after fetch (in case it changed)
            requestAnimationFrame(() => {
                window.scrollTo(scrollX, scrollY);
            });
            
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }
            
            // Get response as text first, then parse JSON (like product-variations.js)
            const responseText = await response.text();
            let data;
            try {
                data = JSON.parse(responseText);
            } catch (e) {
                console.warn('Non-JSON response, assuming success');
                data = { success: true };
            }
            
            console.log('📦 WooCommerce AJAX Response:', data);
            
            if (data.success === false || data.error) {
                throw new Error(data.data?.message || data.error_message || 'Failed to add to cart');
            }
            
            // Success - update cart fragments
            if (data.fragments) {
                // Update cart fragments if provided
                Object.entries(data.fragments).forEach(([selector, html]) => {
                    const el = document.querySelector(selector);
                    if (el) {
                        el.innerHTML = html;
                        console.log('✅ Updated fragment:', selector);
                    } else {
                        console.warn('⚠️ Fragment element not found:', selector);
                    }
                });
                
                // Trigger WooCommerce events (prevent scroll during event)
                if (typeof jQuery !== 'undefined') {
                    // Save scroll position before triggering events
                    const currentScrollY = window.scrollY;
                    const currentScrollX = window.scrollX;
                    
                    jQuery(document.body).trigger('added_to_cart', [
                        data.fragments,
                        data.cart_hash,
                        addToCartBtn,
                    ]);
                    
                    // Restore scroll position after events
                    requestAnimationFrame(() => {
                        if (window.scrollY !== currentScrollY || window.scrollX !== currentScrollX) {
                            window.scrollTo(currentScrollX, currentScrollY);
                        }
                    });
                }
            }
            
            // Ensure scroll position is maintained
            requestAnimationFrame(() => {
                if (window.scrollY !== scrollY || window.scrollX !== scrollX) {
                    window.scrollTo(scrollX, scrollY);
                }
            });
            
            // Update bag count - multiple fallback methods
            // Method 1: Check if fragment updated it
            const bagCountElement = document.querySelector('.bag-count');
            if (bagCountElement && data.fragments && data.fragments['.bag-count']) {
                console.log('✅ Bag count updated via fragment');
            }
            
            // Method 2: Always call updateBagCount as backup (with delay to let fragments process first)
            setTimeout(() => {
                if (typeof window.updateBagCount === 'function') {
                    window.updateBagCount();
                    console.log('✅ Called updateBagCount() as backup');
                } else {
                    console.warn('⚠️ updateBagCount function not available');
                }
            }, 200);
            
            // Method 3: Trigger WooCommerce fragment refresh if available
            if (typeof jQuery !== 'undefined' && data.cart_hash) {
                setTimeout(() => {
                    jQuery('body').trigger('wc_fragment_refresh');
                    console.log('✅ Triggered wc_fragment_refresh');
                }, 150);
            }
            
            // Show success message
            addToCartBtn.textContent = '✅ Added!';
            addToCartBtn.style.background = '#10b981';
            
            // Close modal after a short delay
            setTimeout(() => {
                this.closeModal();
            }, 500);
            
        } catch (error) {
            clearInterval(scrollLockInterval);
            console.error('❌ Add to cart error:', error);
            alert(error.message || 'Failed to add product to cart. Please try again.');
            addToCartBtn.disabled = false;
            addToCartBtn.textContent = originalText;
            addToCartBtn.style.background = '';
            // Restore scroll position on error
            window.scrollTo(scrollX, scrollY);
        }
    }
    
    updateQuickViewForm(form, variation) {
        // Update variation_id input
        const variationIdInput = form.querySelector('#quick-view-variation-id, input[name="variation_id"]');
        if (variationIdInput) {
            const variationId = variation.id || variation.variation_id;
            variationIdInput.value = variationId;
            console.log('🔧 Updated variation_id input to:', variationId);
        }
        
        // Update attribute inputs with selected attributes
        Object.entries(this.selectedAttributes).forEach(([key, value]) => {
            if (!value) return;
            
            // Format the attribute key to match WooCommerce format
            let attrKey;
            if (key.startsWith('attribute_')) {
                attrKey = key;
            } else if (key.startsWith('pa_')) {
                attrKey = `attribute_${key}`;
            } else {
                // For color, size, etc. - use attribute_pa_ prefix
                attrKey = `attribute_pa_${key}`;
            }
            
            // Find and update the attribute input
            const attrInput = form.querySelector(`input[name="${attrKey}"]`);
            if (attrInput) {
                attrInput.value = value;
                attrInput.disabled = false; // Ensure it's enabled
                console.log(`🔧 Updated ${attrKey} to:`, value);
            } else {
                // Try to find by the original key
                const altInput = form.querySelector(`input[name="${key}"]`);
                if (altInput) {
                    altInput.value = value;
                    altInput.disabled = false;
                    console.log(`🔧 Updated ${key} to:`, value);
                } else {
                    console.warn(`⚠️ Attribute input not found: ${attrKey} or ${key}`);
                }
            }
        });
    }
}

// Initialize when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        window.quickView = new QuickView();
    });
} else {
    window.quickView = new QuickView();
}
