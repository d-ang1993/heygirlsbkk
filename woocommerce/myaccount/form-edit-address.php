<?php
/**
 * Template: Edit Address (simplified custom)
 */

defined('ABSPATH') || exit;

if (!is_user_logged_in()) {
  wp_redirect(wc_get_page_permalink('myaccount'));
  exit;
}

$current_user = wp_get_current_user();

// Check for address parameter from URL (supports both 'address' and 'edit-address' parameters)
$load_address = '';
if (isset($_GET['address']) && in_array($_GET['address'], ['billing','shipping'])) {
    $load_address = sanitize_text_field($_GET['address']);
} elseif (isset($_GET['edit-address']) && in_array($_GET['edit-address'], ['billing','shipping'])) {
    $load_address = sanitize_text_field($_GET['edit-address']);
}

?>

<div class="address-management">
  <?php if (empty($load_address)): ?>
    <!-- Address Overview -->
    <div class="address-overview">
      <div class="page-header">
        <h1>Manage Addresses</h1>
        <p>Update your billing and shipping addresses</p>
      </div>
      
      <div class="address-cards">
        <!-- Billing Address Card -->
        <div class="address-card">
          <div class="card-header">
            <div class="card-icon billing">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M20 9v11a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V9"></path>
                <path d="M9 22V12h6v10M2 10.6L12 2l10 8.6"></path>
              </svg>
            </div>
            <div class="card-title">
              <h3>Billing Address</h3>
              <p class="card-subtitle">Your billing information</p>
            </div>
          </div>
          <div class="card-content">
            <?php 
            $billing_address = array(
              'first_name' => get_user_meta($current_user->ID, 'billing_first_name', true),
              'last_name' => get_user_meta($current_user->ID, 'billing_last_name', true),
              'address_1' => get_user_meta($current_user->ID, 'billing_address_1', true),
              'city' => get_user_meta($current_user->ID, 'billing_city', true),
              'postcode' => get_user_meta($current_user->ID, 'billing_postcode', true),
              'country' => get_user_meta($current_user->ID, 'billing_country', true),
            );
            
            $has_billing = !empty(array_filter($billing_address));
            ?>
            <?php if ($has_billing): ?>
              <div class="address-details">
                <p><strong><?php echo esc_html($billing_address['first_name'] . ' ' . $billing_address['last_name']); ?></strong></p>
                <?php if (!empty($billing_address['address_1'])): ?>
                  <p><?php echo esc_html($billing_address['address_1']); ?></p>
                <?php endif; ?>
                <?php if (!empty($billing_address['city'])): ?>
                  <p><?php echo esc_html($billing_address['city'] . ', ' . $billing_address['postcode']); ?></p>
                <?php endif; ?>
                <?php if (!empty($billing_address['country'])): ?>
                  <p><?php echo esc_html($billing_address['country']); ?></p>
                <?php endif; ?>
              </div>
            <?php else: ?>
              <div class="no-address">
                <p>No billing address set</p>
              </div>
            <?php endif; ?>
          </div>
          <div class="card-actions">
            <?php if ($has_billing): ?>
              <a href="#" class="edit-btn" data-address-type="billing">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                  <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                </svg>
                Edit
              </a>
            <?php else: ?>
              <a href="#" class="add-address-btn" data-address-type="billing">
                Add Billing Address
              </a>
            <?php endif; ?>
          </div>
        </div>

        <!-- Shipping Address Card -->
        <div class="address-card">
          <div class="card-header">
            <div class="card-icon shipping">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                <circle cx="12" cy="10" r="3"></circle>
              </svg>
            </div>
            <div class="card-title">
              <h3>Shipping Address</h3>
              <p class="card-subtitle">Your shipping information</p>
            </div>
          </div>
          <div class="card-content">
            <?php 
            $shipping_address = array(
              'first_name' => get_user_meta($current_user->ID, 'shipping_first_name', true),
              'last_name' => get_user_meta($current_user->ID, 'shipping_last_name', true),
              'address_1' => get_user_meta($current_user->ID, 'shipping_address_1', true),
              'city' => get_user_meta($current_user->ID, 'shipping_city', true),
              'postcode' => get_user_meta($current_user->ID, 'shipping_postcode', true),
              'country' => get_user_meta($current_user->ID, 'shipping_country', true),
            );
            
            $has_shipping = !empty(array_filter($shipping_address));
            ?>
            <?php if ($has_shipping): ?>
              <div class="address-details">
                <p><strong><?php echo esc_html($shipping_address['first_name'] . ' ' . $shipping_address['last_name']); ?></strong></p>
                <?php if (!empty($shipping_address['address_1'])): ?>
                  <p><?php echo esc_html($shipping_address['address_1']); ?></p>
                <?php endif; ?>
                <?php if (!empty($shipping_address['city'])): ?>
                  <p><?php echo esc_html($shipping_address['city'] . ', ' . $shipping_address['postcode']); ?></p>
                <?php endif; ?>
                <?php if (!empty($shipping_address['country'])): ?>
                  <p><?php echo esc_html($shipping_address['country']); ?></p>
                <?php endif; ?>
              </div>
            <?php else: ?>
              <div class="no-address">
                <p>No shipping address set</p>
              </div>
            <?php endif; ?>
          </div>
          <div class="card-actions">
            <?php if ($has_shipping): ?>
              <a href="#" class="edit-btn" data-address-type="shipping">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                  <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                </svg>
                Edit
              </a>
            <?php else: ?>
              <a href="#" class="add-address-btn" data-address-type="shipping">
                Add Shipping Address
              </a>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>

  <?php else: ?>
    <!-- Address Edit Form -->
    <div class="address-edit">
      <div class="page-header">
        <h1><?php echo $load_address === 'billing' ? 'Edit Billing Address' : 'Edit Shipping Address'; ?></h1>
        <p>Update your <?php echo esc_html($load_address); ?> details below.</p>
        <a href="<?php echo esc_url(wc_get_account_endpoint_url('edit-address')); ?>" class="back-btn">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <polyline points="15,18 9,12 15,6"></polyline>
          </svg>
          Back to Addresses
        </a>
      </div>

      <form method="post" class="woocommerce-EditAddressForm edit-address-form">
        <?php
          do_action("woocommerce_before_edit_address_form_{$load_address}");
          foreach (wc()->countries->get_address_fields('', $load_address . '_') as $key => $field) {
            woocommerce_form_field($key, $field, wc_get_post_data_by_key($key, get_user_meta(get_current_user_id(), $key, true)));
          }
          do_action("woocommerce_after_edit_address_form_{$load_address}");
        ?>
        <?php wp_nonce_field('woocommerce-edit_address', 'woocommerce-edit-address-nonce'); ?>
        <input type="hidden" name="address_type" value="<?php echo esc_attr($load_address); ?>">
        <button type="submit" name="save_address" class="btn-primary"><?php esc_html_e('Save address', 'woocommerce'); ?></button>
      </form>
    </div>
  <?php endif; ?>
</div>

<style>
/* Address Management Styles */
.address-management {
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}

.page-header {
  text-align: center;
  margin-bottom: 2rem;
  padding-bottom: 1.5rem;
  border-bottom: 1px solid #e5e7eb;
}

.page-header h1 {
  color: #1f2937;
  font-size: 1.75rem;
  font-weight: 600;
  margin-bottom: 0.5rem;
}

.page-header p {
  color: #6b7280;
  font-size: 1rem;
  margin-bottom: 1rem;
}

.back-btn {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  color: #6b7280;
  text-decoration: none;
  font-size: 0.875rem;
  padding: 0.5rem 1rem;
  border: 1px solid #e5e7eb;
  border-radius: 6px;
  transition: all 0.2s ease;
}

.back-btn:hover {
  background: #f9fafb;
  color: #374151;
  text-decoration: none;
}

/* Address Cards */
.address-cards {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 2rem;
  margin-top: 2rem;
  max-width: 1000px;
  margin-left: auto;
  margin-right: auto;
}

.address-card {
  background: #ffffff;
  border: 1px solid #e5e7eb;
  border-radius: 16px;
  padding: 2rem;
  transition: all 0.3s ease;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
  position: relative;
  overflow: hidden;
}

.address-card::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 4px;
  background: linear-gradient(90deg, #f271ba, #ff6b9d);
  transform: scaleX(0);
  transition: transform 0.3s ease;
}

.address-card:hover::before {
  transform: scaleX(1);
}

.address-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
}

.card-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 1.5rem;
  padding-bottom: 1rem;
  border-bottom: 1px solid #f3f4f6;
}

.card-icon {
  width: 56px;
  height: 56px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  background: #f8fafc;
  border: 1px solid #e2e8f0;
}

.card-icon.billing {
  color: #64748b;
}

.card-icon.shipping {
  color: #64748b;
}

.card-title {
  flex: 1;
  margin-left: 1rem;
}

.card-title h3 {
  font-size: 1.125rem;
  font-weight: 600;
  color: #1e293b;
  margin: 0 0 0.25rem 0;
}

.card-subtitle {
  font-size: 0.875rem;
  color: #64748b;
  margin: 0;
}

.card-content {
  margin-bottom: 1.5rem;
}

.address-details p {
  margin: 0 0 0.5rem 0;
  color: #475569;
  font-size: 0.9rem;
  line-height: 1.5;
}

.address-details p:first-child {
  font-weight: 600;
  color: #1e293b;
  margin-bottom: 0.75rem;
}

.address-details p:last-child {
  margin-bottom: 0;
}

.no-address {
  text-align: center;
  padding: 3rem 1rem;
  color: #94a3b8;
  font-size: 0.9rem;
}

.card-actions {
  display: flex;
  gap: 0.75rem;
  align-items: center;
}

.edit-btn {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  background: #f8fafc;
  color: #475569;
  text-decoration: none;
  padding: 0.75rem 1.25rem;
  border-radius: 8px;
  font-weight: 500;
  font-size: 0.875rem;
  border: 1px solid #e2e8f0;
  transition: all 0.2s ease;
  flex: 1;
  justify-content: center;
}

.edit-btn:hover {
  background: #f1f5f9;
  color: #334155;
  text-decoration: none;
  border-color: #cbd5e1;
  transform: translateY(-1px);
}

.add-address-btn {
  background: #1e293b;
  color: white;
  border: none;
  padding: 0.75rem 1.5rem;
  border-radius: 8px;
  font-weight: 500;
  font-size: 0.875rem;
  cursor: pointer;
  transition: all 0.2s ease;
  width: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
}

.add-address-btn:hover {
  background: #334155;
  transform: translateY(-1px);
}

/* Address Edit Form */
.address-edit {
  max-width: 600px;
  margin: 0 auto;
}

.edit-address-form {
  background: #ffffff;
  border: 1px solid #e5e7eb;
  border-radius: 12px;
  padding: 2rem;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.btn-primary {
  display: inline-block;
  background: #f271ba;
  color: #fff;
  padding: 0.75rem 2rem;
  border-radius: 8px;
  border: none;
  cursor: pointer;
  font-weight: 500;
  font-size: 1rem;
  transition: all 0.2s ease;
  margin-top: 1rem;
}

.btn-primary:hover {
  background: #e252a2;
  transform: translateY(-1px);
}

/* Form Field Styles */
.woocommerce-EditAddressForm .form-row {
  margin-bottom: 1.5rem;
}

.woocommerce-EditAddressForm label {
  display: block;
  font-weight: 500;
  color: #374151;
  margin-bottom: 0.5rem;
  font-size: 0.875rem;
}

.woocommerce-EditAddressForm input,
.woocommerce-EditAddressForm select {
  width: 100%;
  padding: 0.75rem;
  border: 1px solid #d1d5db;
  border-radius: 6px;
  font-size: 1rem;
  transition: border-color 0.2s ease;
}

.woocommerce-EditAddressForm input:focus,
.woocommerce-EditAddressForm select:focus {
  outline: none;
  border-color: #f271ba;
  box-shadow: 0 0 0 3px rgba(242, 113, 186, 0.1);
}

/* Responsive Design */
@media (max-width: 768px) {
  .address-cards {
    grid-template-columns: 1fr;
    gap: 1.5rem;
    margin-top: 1.5rem;
  }
  
  .address-card {
    padding: 1.5rem;
  }
  
  .card-header {
    gap: 0.75rem;
  }
  
  .card-icon {
    width: 48px;
    height: 48px;
  }
  
  .card-title h3 {
    font-size: 1rem;
  }
  
  .card-subtitle {
    font-size: 0.8rem;
  }
  
  .edit-address-form {
    padding: 1.5rem;
  }
  
  .page-header h1 {
    font-size: 1.5rem;
  }
  
  .card-actions {
    flex-direction: column;
    gap: 0.5rem;
  }
  
  .edit-btn,
  .add-address-btn {
    width: 100%;
    justify-content: center;
  }
}
</style>
