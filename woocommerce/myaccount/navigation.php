<?php
/**
 * WooCommerce My Account Navigation
 */

// Get current user and navigation items
$current_user = wp_get_current_user();
$endpoints = wc_get_account_menu_items();
$current_endpoint = WC()->query->get_current_endpoint();
?>

<nav class="woocommerce-MyAccount-navigation">
  <div class="navigation-header">
    <div class="user-info">
      <div class="user-avatar">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
          <circle cx="12" cy="7" r="4"></circle>
        </svg>
      </div>
      <div class="user-details">
        <span class="user-name"><?php echo esc_html($current_user->display_name ?: 'Customer'); ?></span>
        <span class="user-email"><?php echo esc_html($current_user->user_email); ?></span>
      </div>
    </div>
  </div>

  <ul class="navigation-menu">
    <?php foreach ($endpoints as $endpoint => $label) : ?>
      <?php $is_active = $current_endpoint === $endpoint; ?>
      <li class="navigation-item <?php echo $is_active ? 'is-active' : ''; ?>">
        <a href="<?php echo esc_url(wc_get_account_endpoint_url($endpoint)); ?>" class="navigation-link">
          <span class="navigation-icon">
            <?php
            // Display appropriate icon based on endpoint
            switch ($endpoint) {
              case 'dashboard':
                echo '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>';
                break;
              case 'orders':
                echo '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 11V7a4 4 0 0 0-8 0v4"></path><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect></svg>';
                break;
              case 'downloads':
                echo '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7,10 12,15 17,10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>';
                break;
              case 'edit-address':
                echo '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>';
                break;
              case 'payment-methods':
                echo '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect><line x1="1" y1="10" x2="23" y2="10"></line></svg>';
                break;
              case 'edit-account':
                echo '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>';
                break;
              case 'customer-logout':
                echo '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16,17 21,12 16,7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>';
                break;
              default:
                echo '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"></circle><path d="M12 1v6m0 6v6m11-7h-6m-6 0H1"></path></svg>';
            }
            ?>
          </span>
          <span class="navigation-label"><?php echo esc_html($label); ?></span>
        </a>
      </li>
    <?php endforeach; ?>
  </ul>
</nav>

<style>
/* Navigation Styles */
.woocommerce-MyAccount-navigation {
  background: #ffffff;
  border-radius: 16px;
  padding: 1.5rem;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
  border: 1px solid #e5e7eb;
  margin-bottom: 2rem;
}

.navigation-header {
  margin-bottom: 1.5rem;
  padding-bottom: 1.5rem;
  border-bottom: 1px solid #f3f4f6;
}

.user-info {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.user-avatar {
  width: 48px;
  height: 48px;
  background: linear-gradient(135deg, #f271ba, #ff6b9d);
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
}

.user-details {
  display: flex;
  flex-direction: column;
}

.user-name {
  font-weight: 600;
  color: #1f2937;
  font-size: 1rem;
}

.user-email {
  color: #6b7280;
  font-size: 0.875rem;
}

.navigation-menu {
  list-style: none;
  margin: 0;
  padding: 0;
}

.navigation-item {
  margin-bottom: 0.5rem;
}

.navigation-item:last-child {
  margin-bottom: 0;
}

.navigation-link {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.75rem 1rem;
  border-radius: 12px;
  text-decoration: none;
  color: #6b7280;
  font-weight: 500;
  transition: all 0.2s ease;
}

.navigation-link:hover {
  background: #f8fafc;
  color: #374151;
  text-decoration: none;
}

.navigation-item.is-active .navigation-link {
  background: linear-gradient(135deg, #f271ba, #ff6b9d);
  color: white;
}

.navigation-icon {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 20px;
  height: 20px;
  flex-shrink: 0;
}

.navigation-label {
  font-size: 0.9rem;
}

/* Responsive Design */
@media (max-width: 768px) {
  .woocommerce-MyAccount-navigation {
    padding: 1rem;
    margin-bottom: 1rem;
  }
  
  .navigation-header {
    margin-bottom: 1rem;
    padding-bottom: 1rem;
  }
  
  .user-avatar {
    width: 40px;
    height: 40px;
  }
  
  .user-name {
    font-size: 0.9rem;
  }
  
  .user-email {
    font-size: 0.8rem;
  }
  
  .navigation-link {
    padding: 0.625rem 0.75rem;
  }
  
  .navigation-label {
    font-size: 0.85rem;
  }
}
</style>
