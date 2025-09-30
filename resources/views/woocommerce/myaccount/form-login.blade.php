{{-- WooCommerce My Account Login & Register --}}

<div class="u-columns col2-set" id="customer_login">

  {{-- LOGIN --}}
  <div class="u-column1 col-1">
    <h2>{{ __('Login', 'woocommerce') }}</h2>

    <form class="woocommerce-form woocommerce-form-login login" method="post">

      @do_action('woocommerce_login_form_start')

      <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
        <label for="username">{{ __('Username or email address', 'woocommerce') }}&nbsp;<span class="required">*</span></label>
        <input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="username" id="username" autocomplete="username" value="{{ esc_attr( wp_unslash($_POST['username'] ?? '') ) }}" />
      </p>

      <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
        <label for="password">{{ __('Password', 'woocommerce') }}&nbsp;<span class="required">*</span></label>
        <input class="woocommerce-Input woocommerce-Input--text input-text" type="password" name="password" id="password" autocomplete="current-password" />
      </p>

      @do_action('woocommerce_login_form')

      <p class="form-row">
        <label class="woocommerce-form__label woocommerce-form__label-for-checkbox woocommerce-form-login__rememberme">
          <input class="woocommerce-form__input woocommerce-form__input-checkbox" name="rememberme" type="checkbox" id="rememberme" value="forever" /> <span>{{ __('Remember me', 'woocommerce') }}</span>
        </label>
        @php wp_nonce_field('woocommerce-login', 'woocommerce-login-nonce') @endphp
        <button type="submit" class="woocommerce-Button button" name="login" value="{{ __('Log in', 'woocommerce') }}">{{ __('Log in', 'woocommerce') }}</button>
      </p>

      <p class="woocommerce-LostPassword lost_password">
        <a href="{{ esc_url(wp_lostpassword_url()) }}">{{ __('Lost your password?', 'woocommerce') }}</a>
      </p>

      @do_action('woocommerce_login_form_end')

    </form>
  </div>

  {{-- REGISTER --}}
  <div class="u-column2 col-2">
    <h2>{{ __('Register', 'woocommerce') }}</h2>

    <form method="post" class="woocommerce-form woocommerce-form-register register">

      @do_action('woocommerce_register_form_start')

      {{-- Email --}}
      <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
        <label for="reg_email">{{ __('Email address', 'woocommerce') }}&nbsp;<span class="required">*</span></label>
        <input type="email" class="woocommerce-Input woocommerce-Input--text input-text" name="email" id="reg_email" autocomplete="email" value="{{ esc_attr( wp_unslash($_POST['email'] ?? '') ) }}" />
      </p>

      {{-- Password --}}
      <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
        <label for="reg_password">{{ __('Password', 'woocommerce') }}&nbsp;<span class="required">*</span></label>
        <input type="password" class="woocommerce-Input woocommerce-Input--text input-text" name="password" id="reg_password" autocomplete="new-password" />
      </p>

      {{-- Confirm Password --}}
      <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
        <label for="reg_password2">{{ __('Confirm password', 'woocommerce') }}&nbsp;<span class="required">*</span></label>
        <input type="password" class="woocommerce-Input woocommerce-Input--text input-text" name="password2" id="reg_password2" autocomplete="new-password" />
      </p>

      @do_action('woocommerce_register_form')

      <p class="woocommerce-FormRow form-row">
        @php wp_nonce_field('woocommerce-register', 'woocommerce-register-nonce') @endphp
        <button type="submit" class="woocommerce-Button button" name="register" value="{{ __('Register', 'woocommerce') }}">{{ __('Register', 'woocommerce') }}</button>
      </p>

      @do_action('woocommerce_register_form_end')

    </form>
  </div>

</div>
