<div class="woocommerce-account-dashboard py-10">

  <h2 class="text-2xl font-semibold mb-6">My Account</h2>

  <p class="mb-6 text-gray-600">
    Hello <strong>{{ Auth::user()->display_name ?? 'Customer' }}</strong> 
    (<a href="{{ esc_url( wc_logout_url() ) }}" class="underline">Log out</a>)
  </p>

  <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

    <div class="p-6 bg-white border rounded shadow">
      <h3 class="font-semibold mb-2">Orders</h3>
      <p><a href="{{ wc_get_account_endpoint_url( 'orders' ) }}" class="text-blue-600 underline">View your orders</a></p>
    </div>

    <div class="p-6 bg-white border rounded shadow">
      <h3 class="font-semibold mb-2">Addresses</h3>
      <p><a href="{{ wc_get_account_endpoint_url( 'edit-address' ) }}" class="text-blue-600 underline">Manage addresses</a></p>
    </div>

    <div class="p-6 bg-white border rounded shadow">
      <h3 class="font-semibold mb-2">Account Details</h3>
      <p><a href="{{ wc_get_account_endpoint_url( 'edit-account' ) }}" class="text-blue-600 underline">Edit account info</a></p>
    </div>

  </div>

</div>
