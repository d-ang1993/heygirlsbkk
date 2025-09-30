<nav class="woocommerce-MyAccount-navigation bg-gray-50 p-4 rounded border mb-8">
  <ul class="space-y-2">

    <li>
      <a href="{{ wc_get_account_endpoint_url( 'dashboard' ) }}" class="block px-3 py-2 rounded hover:bg-gray-200">
        Dashboard
      </a>
    </li>
    <li>
      <a href="{{ wc_get_account_endpoint_url( 'orders' ) }}" class="block px-3 py-2 rounded hover:bg-gray-200">
        Orders
      </a>
    </li>
    <li>
      <a href="{{ wc_get_account_endpoint_url( 'downloads' ) }}" class="block px-3 py-2 rounded hover:bg-gray-200">
        Downloads
      </a>
    </li>
    <li>
      <a href="{{ wc_get_account_endpoint_url( 'edit-address' ) }}" class="block px-3 py-2 rounded hover:bg-gray-200">
        Addresses
      </a>
    </li>
    <li>
      <a href="{{ wc_get_account_endpoint_url( 'edit-account' ) }}" class="block px-3 py-2 rounded hover:bg-gray-200">
        Account Details
      </a>
    </li>
    <li>
      <a href="{{ esc_url( wc_logout_url() ) }}" class="block px-3 py-2 rounded text-red-600 hover:bg-red-100">
        Logout
      </a>
    </li>

  </ul>
</nav>
