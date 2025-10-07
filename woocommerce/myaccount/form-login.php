<?php
/**
 * WooCommerce My Account Login / Register Page
 */

$action = isset($_GET['action']) ? sanitize_text_field($_GET['action']) : '';
$is_logged_in = is_user_logged_in();

if ($is_logged_in) {
    wp_redirect(wc_get_page_permalink('myaccount'));
    exit;
}
?>

<div class="auth-container">
  <div class="auth-card">
    <div class="auth-header">
      <?php if ($action === 'register') : ?>
        <h1>Join HeyGirlsBKK</h1>
        <p>Create your account and start shopping your favorites</p>
      <?php else : ?>
        <h1>Welcome Back</h1>
        <p>Sign in to continue your HeyGirlsBKK experience</p>
      <?php endif; ?>
    </div>

    <?php if ($action === 'register') : ?>
      <?php
      // Display any registration errors
      $show_error = false;
      $error_message = '';
      
      // Check URL parameters
      if (isset($_GET['registration_error'])) {
          $show_error = true;
          $error_message = sanitize_text_field($_GET['registration_error']);
      }
      
      // Check for WooCommerce notices
      if (wc_notice_count('error') > 0) {
          $show_error = true;
          $notices = wc_get_notices('error');
          if (!empty($notices)) {
              $error_message = $notices[0]['notice'];
          }
      }
      
      // Check for WordPress errors
      if (isset($_POST['register']) && !empty($GLOBALS['wp_error']->errors)) {
          $show_error = true;
          $wp_error = $GLOBALS['wp_error'];
          if ($wp_error->get_error_message('email_exists')) {
              $error_message = 'email_exists';
          } else {
              $error_message = $wp_error->get_error_message();
          }
      }
      
      if ($show_error) {
          echo '<div class="auth-error-message">';
          if ($error_message === 'email_exists' || strpos($error_message, 'already registered') !== false || (strpos($error_message, 'email') !== false && strpos($error_message, 'exists') !== false)) {
              echo '<p><strong>Email already exists!</strong> This email is already registered.</p>';
              echo '<p>Please <a href="' . esc_url(add_query_arg('action', 'login', wc_get_page_permalink('myaccount'))) . '" class="auth-error-link">try logging in</a> or use a different email address.</p>';
          } else {
              // Clean up the error message and allow safe HTML
              $clean_message = str_replace(['**Error:**', '<strong>', '</strong>'], ['', '', ''], $error_message);
              echo '<p><strong>Registration Error:</strong> ' . wp_kses($clean_message, array('a' => array('href' => array()))) . '</p>';
          }
          echo '</div>';
      }
      
      ?>
      
      <form method="post" class="woocommerce-form woocommerce-form-register register">
        <?php do_action('woocommerce_register_form_start'); ?>

        <div class="form-group">
          <label for="reg_first_name"><?php esc_html_e('First Name', 'woocommerce'); ?><span class="required">*</span></label>
          <input type="text" name="first_name" id="reg_first_name" class="woocommerce-Input input-text"
                 value="<?php echo esc_attr(wp_unslash($_POST['first_name'] ?? '')); ?>" placeholder="Enter your first name" />
        </div>

        <div class="form-group">
          <label for="reg_last_name"><?php esc_html_e('Last Name', 'woocommerce'); ?><span class="required">*</span></label>
          <input type="text" name="last_name" id="reg_last_name" class="woocommerce-Input input-text"
                 value="<?php echo esc_attr(wp_unslash($_POST['last_name'] ?? '')); ?>" placeholder="Enter your last name" />
        </div>

        <div class="form-group">
          <label for="reg_email"><?php esc_html_e('Email address', 'woocommerce'); ?><span class="required">*</span></label>
          <input type="email" name="email" id="reg_email" class="woocommerce-Input input-text"
                 value="<?php echo esc_attr(wp_unslash($_POST['email'] ?? '')); ?>" placeholder="Enter your email" />
        </div>

        <div class="form-group">
          <label for="reg_password"><?php esc_html_e('Password', 'woocommerce'); ?><span class="required">*</span></label>
          <input type="password" name="password" id="reg_password" class="woocommerce-Input input-text" placeholder="Create a password" />
        </div>

        <div class="form-group">
          <label for="reg_password2"><?php esc_html_e('Confirm password', 'woocommerce'); ?><span class="required">*</span></label>
          <input type="password" name="password2" id="reg_password2" class="woocommerce-Input input-text" placeholder="Confirm your password" />
        </div>

        <?php do_action('woocommerce_register_form'); ?>

        <div class="form-group">
          <label class="privacy-notice">
            <input type="checkbox" name="privacy_policy" required />
            <span>I agree to the <a href="#">Privacy Policy</a> and <a href="#">Terms of Service</a></span>
          </label>
        </div>

        <?php wp_nonce_field('woocommerce-register', 'woocommerce-register-nonce'); ?>
        <button type="submit" class="auth-button" name="register">Create Account</button>

        <?php do_action('woocommerce_register_form_end'); ?>
      </form>

      <div class="auth-footer">
        <p>Already have an account? <a href="<?php echo esc_url(add_query_arg('action', 'login', wc_get_page_permalink('myaccount'))); ?>" class="auth-link">Sign in here</a></p>
      </div>
    <?php else : ?>
      <form method="post" class="woocommerce-form woocommerce-form-login login">
        <?php do_action('woocommerce_login_form_start'); ?>

        <div class="form-group">
          <label for="username"><?php esc_html_e('Email', 'woocommerce'); ?><span class="required">*</span></label>
          <input type="text" name="username" id="username" class="woocommerce-Input input-text"
                 value="<?php echo esc_attr(wp_unslash($_POST['username'] ?? '')); ?>" placeholder="Enter your email or username" />
        </div>

        <div class="form-group">
          <label for="password"><?php esc_html_e('Password', 'woocommerce'); ?><span class="required">*</span></label>
          <input type="password" name="password" id="password" class="woocommerce-Input input-text" placeholder="Enter your password" />
        </div>

        <div class="form-options">
          <label class="remember-me">
            <input type="checkbox" name="rememberme" id="rememberme" value="forever" />
            <span>Remember me</span>
          </label>
          <a href="<?php echo esc_url(wp_lostpassword_url()); ?>" class="forgot-password">Forgot password?</a>
        </div>

        <?php wp_nonce_field('woocommerce-login', 'woocommerce-login-nonce'); ?>
        <button type="submit" class="auth-button" name="login">Sign In</button>

        <?php do_action('woocommerce_login_form_end'); ?>
      </form>

      <div class="auth-footer">
        <p>Don’t have an account? <a href="<?php echo esc_url(add_query_arg('action', 'register', wc_get_page_permalink('myaccount'))); ?>" class="auth-link">Create one here</a></p>
      </div>
    <?php endif; ?>
  </div>
</div>

<style>
/* --- HeyGirlsBKK Auth Styles --- */
.auth-container {
  min-height: 90vh;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 2rem;
  background: linear-gradient(135deg, #fff7fb 0%, #ffe0f2 100%);
}

.auth-card {
  background: #fff;
  border-radius: 20px;
  padding: 3rem;
  width: 90%;             /* makes it responsive */
  max-width: 600px;       /* bigger card on desktops */
  box-shadow: 0 20px 50px rgba(0, 0, 0, 0.08);
  border: 1px solid rgba(0, 0, 0, 0.04);
  transition: all 0.2s ease;
}

@media (max-width: 768px) {
  .auth-card {
    width: 95%;
    max-width: 480px;
    padding: 2.5rem;
  }
}


.auth-header {
  text-align: center;
  margin-bottom: 2rem;
}

.auth-header h1 {
  font-family: "Poppins", sans-serif;
  font-weight: 500;
  font-size: 2rem;
  color: #111;
  margin-bottom: 0.25rem;
}

.auth-header p {
  color: #6b6b6b;
  font-size: 1rem;
}

.form-group {
  margin-bottom: 1.5rem;
}

.form-group label {
  display: block;
  font-weight: 500;
  font-size: 0.875rem;
  color: #333;
  margin-bottom: 0.5rem;
}

.woocommerce-Input {
  width: 100%;
  padding: 0.9rem 1rem;
  border: 1px solid #ddd;
  border-radius: 12px;
  font-size: 1rem;
  background-color: #fff;
  transition: all 0.2s ease;
}

.woocommerce-Input:focus {
  border-color: #f271ba;
  box-shadow: 0 0 0 3px rgba(242, 113, 186, 0.15);
  outline: none;
}

.form-options {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
  flex-wrap: wrap;
  gap: 0.5rem;
}

.remember-me {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.85rem;
  color: #777;
}

.remember-me input[type="checkbox"] {
  accent-color: #f271ba;
}

.forgot-password {
  color: #f271ba;
  text-decoration: none;
  font-size: 0.85rem;
  font-weight: 500;
}

.forgot-password:hover {
  text-decoration: underline;
}

.privacy-notice {
  font-size: 0.85rem;
  color: #666;
  display: flex;
  align-items: flex-start;
  gap: 0.4rem;
}

.privacy-notice input[type="checkbox"] {
  accent-color: #f271ba;
  margin-top: 0.2rem;
}

.privacy-notice a {
  color: #f271ba;
  font-weight: 500;
  text-decoration: none;
}

.privacy-notice a:hover {
  text-decoration: underline;
}

.auth-button {
  width: 100%;
  padding: 1rem 2rem;
  background: #f271ba;
  border: none;
  border-radius: 12px;
  color: #fff;
  font-weight: 600;
  font-size: 1rem;
  cursor: pointer;
  transition: all 0.25s ease;
}

.auth-button:hover {
  background: #e252a2;
  transform: translateY(-2px);
  box-shadow: 0 6px 14px rgba(242, 113, 186, 0.3);
}

.auth-footer {
  text-align: center;
  margin-top: 2rem;
  font-size: 0.9rem;
  color: #666;
}

.auth-link {
  color: #f271ba;
  font-weight: 600;
  text-decoration: none;
}

.auth-link:hover {
  text-decoration: underline;
}

@media (max-width: 480px) {
  .auth-card {
    padding: 2rem;
  }
}
</style>
