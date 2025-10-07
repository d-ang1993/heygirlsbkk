<?php
/**
 * WooCommerce My Account Dashboard
 */

// Check if user is logged in
if (!is_user_logged_in()) {
    wp_redirect(wc_get_page_permalink('myaccount'));
    exit;
}

$current_user = wp_get_current_user();
?>

<div class="account-layout">
  <!-- Left Navigation Panel -->
  <div class="account-nav">
    <div class="nav-header">
      <h3>My Account</h3>
    </div>
    <nav class="nav-menu">
      <a href="#" class="nav-item active" data-section="dashboard">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
          <polyline points="9,22 9,12 15,12 15,22"></polyline>
        </svg>
        Dashboard
      </a>
      <a href="#" class="nav-item" data-section="orders">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"></path>
          <rect x="8" y="2" width="8" height="4" rx="1" ry="1"></rect>
        </svg>
        Orders
      </a>
      <a href="#" class="nav-item" data-section="addresses">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
          <circle cx="12" cy="10" r="3"></circle>
        </svg>
        Addresses
      </a>
      <a href="#" class="nav-item" data-section="account">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
          <circle cx="12" cy="7" r="4"></circle>
        </svg>
        Account Details
      </a>
      <a href="#" class="nav-item" data-section="downloads">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
          <polyline points="7,10 12,15 17,10"></polyline>
          <line x1="12" y1="15" x2="12" y2="3"></line>
        </svg>
        Downloads
      </a>
      <a href="#" class="nav-item" data-section="payment">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect>
          <line x1="1" y1="10" x2="23" y2="10"></line>
        </svg>
        Payment Methods
      </a>
    </nav>
  </div>

  <!-- Right Content Panel -->
  <div class="account-content">
    <!-- Welcome Header -->
    <div class="dashboard-header">
      <div class="welcome-section">
        <h1>Welcome back!</h1>
        <p>Hello <strong><?php echo esc_html($current_user->display_name ?: 'Customer'); ?></strong>, manage your account and orders here.</p>
      </div>
      <div class="logout-section">
        <a href="<?php echo esc_url(wc_logout_url()); ?>" class="logout-btn">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
            <polyline points="16,17 21,12 16,7"></polyline>
            <line x1="21" y1="12" x2="9" y2="12"></line>
          </svg>
          Logout
        </a>
      </div>
    </div>

    <!-- Dashboard Content -->
    <div class="dashboard-content">
      <?php 
      // Check for URL parameters to determine what to show
      $edit_address = isset($_GET['edit-address']) ? sanitize_text_field($_GET['edit-address']) : '';
      $current_endpoint = WC()->query->get_current_endpoint();
      
      if ($current_endpoint === 'edit-address' || !empty($edit_address)): 
        // Include the address form content
        include get_template_directory() . '/woocommerce/myaccount/form-edit-address.php';
      else:
        // Show default dashboard content
      ?>
        <div class="dashboard-info">
          <h2>Account Overview</h2>
          <p>Use the navigation menu on the left to manage different aspects of your account.</p>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<style>
/* Left Navigation Account Layout */
.account-layout {
  display: grid;
  grid-template-columns: 250px 1fr;
  gap: 2rem;
  max-width: 1200px;
  margin: 0 auto;
  padding: 2rem;
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
  min-height: 60vh;
}

/* Left Navigation Panel */
.account-nav {
  background: #ffffff;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  padding: 1.5rem;
  height: fit-content;
  position: sticky;
  top: 2rem;
}

.nav-header {
  margin-bottom: 1.5rem;
  padding-bottom: 1rem;
  border-bottom: 1px solid #e5e7eb;
}

.nav-header h3 {
  font-size: 1.125rem;
  font-weight: 600;
  color: #1f2937;
  margin: 0;
}

.nav-menu {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.nav-item {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.75rem 1rem;
  color: #6b7280;
  text-decoration: none;
  border-radius: 6px;
  font-size: 0.875rem;
  font-weight: 500;
  transition: all 0.15s ease;
}

.nav-item:hover {
  background: #f3f4f6;
  color: #374151;
  text-decoration: none;
}

.nav-item.active {
  background: #1f2937;
  color: #ffffff;
}

.nav-item svg {
  flex-shrink: 0;
}

/* Right Content Panel */
.account-content {
  background: #ffffff;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  padding: 2rem;
}

/* Dashboard Header */
.dashboard-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 3rem;
  padding-bottom: 2rem;
  border-bottom: 1px solid #e5e7eb;
}

.welcome-section h1 {
  font-size: 2.5rem;
  font-weight: 600;
  color: #1f2937;
  margin-bottom: 0.5rem;
  letter-spacing: -0.02em;
}

.welcome-section p {
  color: #6b7280;
  font-size: 1.1rem;
  margin: 0;
}

.logout-btn {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem 1.5rem;
  background: #f3f4f6;
  color: #6b7280;
  text-decoration: none;
  border-radius: 12px;
  font-weight: 500;
  transition: all 0.2s ease;
}

.logout-btn:hover {
  background: #e5e7eb;
  color: #374151;
  text-decoration: none;
}

/* Dashboard Content */
.dashboard-content {
  margin-top: 2rem;
}

.dashboard-info {
  text-align: center;
  padding: 3rem 2rem;
  background: #f9fafb;
  border-radius: 12px;
  border: 1px solid #e5e7eb;
}

.dashboard-info h2 {
  font-size: 1.5rem;
  font-weight: 600;
  color: #1f2937;
  margin-bottom: 1rem;
}

.dashboard-info p {
  color: #6b7280;
  font-size: 1rem;
  margin: 0;
  line-height: 1.5;
}

/* Coming Soon Cards */
.coming-soon-card {
  background: #ffffff;
  border: 1px solid #e5e7eb;
  border-radius: 16px;
  padding: 3rem 2rem;
  text-align: center;
  max-width: 500px;
  margin: 2rem auto;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
  position: relative;
  overflow: hidden;
}

.coming-soon-card::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 4px;
  background: linear-gradient(90deg, #f271ba, #ff6b9d);
}

.coming-soon-icon {
  width: 80px;
  height: 80px;
  border-radius: 16px;
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto 1.5rem;
  color: #64748b;
}

.coming-soon-card h3 {
  font-size: 1.5rem;
  font-weight: 600;
  color: #1e293b;
  margin-bottom: 1rem;
}

.coming-soon-card p {
  color: #64748b;
  font-size: 1rem;
  line-height: 1.6;
  margin: 0;
}

/* Section Styling */
.orders-section,
.account-section,
.downloads-section,
.payment-section {
  max-width: 800px;
  margin: 0 auto;
}

.page-header {
  text-align: center;
  margin-bottom: 2rem;
  padding-bottom: 1.5rem;
  border-bottom: 1px solid #e5e7eb;
}

.page-header h1 {
  font-size: 2rem;
  font-weight: 600;
  color: #1e293b;
  margin-bottom: 0.5rem;
}

.page-header p {
  color: #64748b;
  font-size: 1rem;
  margin: 0;
}

/* Responsive Design */
@media (max-width: 768px) {
  .account-layout {
    grid-template-columns: 1fr;
    gap: 1rem;
    padding: 1rem;
  }
  
  .account-nav {
    position: static;
    order: 2;
  }
  
  .account-content {
    order: 1;
    padding: 1.5rem;
  }
  
  .nav-menu {
    flex-direction: row;
    flex-wrap: wrap;
    gap: 0.5rem;
  }
  
  .nav-item {
    flex: 1;
    min-width: 120px;
    justify-content: center;
    text-align: center;
  }
  
  .dashboard-header {
    flex-direction: column;
    gap: 1rem;
    text-align: center;
  }
  
  .welcome-section h1 {
    font-size: 2rem;
  }
  
  .dashboard-info {
    padding: 2rem 1rem;
  }
  
  .dashboard-info h2 {
    font-size: 1.25rem;
  }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Account navigation functionality
    const navItems = document.querySelectorAll('.nav-item[data-section]');
    const contentPanel = document.querySelector('.dashboard-content');
    const loadingHtml = '<div class="loading-spinner"><div class="spinner"></div><p>Loading...</p></div>';
    
    // Add loading styles
    const style = document.createElement('style');
    style.textContent = `
        .loading-spinner {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 3rem;
            color: #6b7280;
        }
        .spinner {
            width: 32px;
            height: 32px;
            border: 3px solid #e5e7eb;
            border-top: 3px solid #f271ba;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-bottom: 1rem;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    `;
    document.head.appendChild(style);
    
    navItems.forEach(item => {
        item.addEventListener('click', function(e) {
            e.preventDefault();
            
            const section = this.getAttribute('data-section');
            
            // Update active nav item
            navItems.forEach(nav => nav.classList.remove('active'));
            this.classList.add('active');
            
            // Update URL with parameter
            const url = new URL(window.location);
            url.searchParams.set('section', section);
            window.history.pushState({}, '', url);
            
            // Show loading
            contentPanel.innerHTML = loadingHtml;
            
            // Load content via AJAX
            loadAccountSection(section);
        });
    });
    
    // Check for URL parameters on page load
    const urlParams = new URLSearchParams(window.location.search);
    const sectionParam = urlParams.get('section');
    const editAddressParam = urlParams.get('edit-address');
    
    if (sectionParam) {
        // Find and activate the corresponding nav item
        const activeNav = document.querySelector(`[data-section="${sectionParam}"]`);
        if (activeNav) {
            navItems.forEach(nav => nav.classList.remove('active'));
            activeNav.classList.add('active');
            
            // Load the section content
            contentPanel.innerHTML = loadingHtml;
            loadAccountSection(sectionParam);
        }
    } else if (editAddressParam) {
        // If we have an edit-address parameter, load addresses section first, then the form
        const addressesNav = document.querySelector('[data-section="addresses"]');
        if (addressesNav) {
            navItems.forEach(nav => nav.classList.remove('active'));
            addressesNav.classList.add('active');
            
            contentPanel.innerHTML = loadingHtml;
            loadAccountSection('addresses').then(() => {
                // After addresses load, load the specific form
                loadAddressForm(editAddressParam);
            });
        }
    }
    
    function loadAccountSection(section) {
        const formData = new FormData();
        formData.append('action', 'load_account_section');
        formData.append('section', section);
        formData.append('nonce', '<?php echo wp_create_nonce("account_section_nonce"); ?>');
        
        return fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                contentPanel.innerHTML = data.data;
                
                // Re-initialize any JavaScript in the loaded content
                initializeLoadedContent(section);
                return data;
            } else {
                contentPanel.innerHTML = '<div class="error-message"><p>Error loading content. Please try again.</p></div>';
                throw new Error('Failed to load content');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            contentPanel.innerHTML = '<div class="error-message"><p>Error loading content. Please try again.</p></div>';
            throw error;
        });
    }
    
    function initializeLoadedContent(section) {
        // Handle different sections
        if (section === 'addresses') {
            // Initialize address form interactions
            const editButtons = document.querySelectorAll('.edit-btn');
            editButtons.forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    const addressType = this.getAttribute('data-address-type');
                    loadAddressForm(addressType);
                });
            });
            
            const addButtons = document.querySelectorAll('.add-address-btn');
            addButtons.forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    const addressType = this.getAttribute('data-address-type');
                    loadAddressForm(addressType);
                });
            });
            
            const backButtons = document.querySelectorAll('.back-btn');
            backButtons.forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    loadAccountSection('addresses');
                });
            });
        }
    }
    
    function loadAddressForm(addressType) {
        const formData = new FormData();
        formData.append('action', 'load_address_form');
        formData.append('address_type', addressType);
        formData.append('nonce', '<?php echo wp_create_nonce("address_form_nonce"); ?>');
        
        // Update URL with address type parameter
        const url = new URL(window.location);
        url.searchParams.set('edit-address', addressType);
        window.history.pushState({}, '', url);
        
        const contentPanel = document.querySelector('.dashboard-content');
        contentPanel.innerHTML = '<div class="loading-spinner"><div class="spinner"></div><p>Loading...</p></div>';
        
        fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                contentPanel.innerHTML = data.data;
                
                // Initialize form interactions
                const backButtons = document.querySelectorAll('.back-btn');
                backButtons.forEach(btn => {
                    btn.addEventListener('click', function(e) {
                        e.preventDefault();
                        // Remove address parameter and go back to addresses
                        const url = new URL(window.location);
                        url.searchParams.delete('edit-address');
                        url.searchParams.set('section', 'addresses');
                        window.history.pushState({}, '', url);
                        
                        // Update nav and load addresses
                        navItems.forEach(nav => nav.classList.remove('active'));
                        const addressesNav = document.querySelector('[data-section="addresses"]');
                        if (addressesNav) {
                            addressesNav.classList.add('active');
                        }
                        
                        loadAccountSection('addresses');
                    });
                });
            } else {
                contentPanel.innerHTML = '<div class="error-message"><p>Error loading form. Please try again.</p></div>';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            contentPanel.innerHTML = '<div class="error-message"><p>Error loading form. Please try again.</p></div>';
        });
    }
});
</script>
