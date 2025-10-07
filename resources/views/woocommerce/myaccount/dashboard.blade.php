<div class="account-dashboard">
  <!-- Welcome Header -->
  <div class="dashboard-header">
    <div class="welcome-section">
      <h1>Welcome back!</h1>
      <p>Hello <strong>{{ wp_get_current_user()->display_name ?? 'Customer' }}</strong>, manage your account and orders here.</p>
    </div>
    <div class="logout-section">
      <a href="{{ esc_url( wc_logout_url() ) }}" class="logout-btn">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
          <polyline points="16,17 21,12 16,7"></polyline>
          <line x1="21" y1="12" x2="9" y2="12"></line>
        </svg>
        Logout
      </a>
    </div>
  </div>

  <!-- Dashboard Grid -->
  <div class="dashboard-grid">
    
    <!-- Orders -->
    <a href="{{ wc_get_account_endpoint_url( 'orders' ) }}" class="dashboard-card orders-card">
      <div class="card-icon">
        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M16 11V7a4 4 0 0 0-8 0v4"></path>
          <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
        </svg>
      </div>
      <div class="card-content">
        <h3>My Orders</h3>
        <p>Track and view your order history</p>
      </div>
      <div class="card-arrow">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <polyline points="9,18 15,12 9,6"></polyline>
        </svg>
      </div>
    </a>

    <!-- Addresses -->
    <a href="{{ wc_get_account_endpoint_url( 'edit-address' ) }}" class="dashboard-card addresses-card">
      <div class="card-icon">
        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
          <circle cx="12" cy="10" r="3"></circle>
        </svg>
      </div>
      <div class="card-content">
        <h3>Addresses</h3>
        <p>Manage your shipping and billing addresses</p>
      </div>
      <div class="card-arrow">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <polyline points="9,18 15,12 9,6"></polyline>
        </svg>
      </div>
    </a>

    <!-- Account Details -->
    <a href="{{ wc_get_account_endpoint_url( 'edit-account' ) }}" class="dashboard-card account-card">
      <div class="card-icon">
        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
          <circle cx="12" cy="7" r="4"></circle>
        </svg>
      </div>
      <div class="card-content">
        <h3>Account Details</h3>
        <p>Update your personal information</p>
      </div>
      <div class="card-arrow">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <polyline points="9,18 15,12 9,6"></polyline>
        </svg>
      </div>
    </a>

    <!-- Downloads -->
    <a href="{{ wc_get_account_endpoint_url( 'downloads' ) }}" class="dashboard-card downloads-card">
      <div class="card-icon">
        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
          <polyline points="7,10 12,15 17,10"></polyline>
          <line x1="12" y1="15" x2="12" y2="3"></line>
        </svg>
      </div>
      <div class="card-content">
        <h3>Downloads</h3>
        <p>Access your downloadable files</p>
      </div>
      <div class="card-arrow">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <polyline points="9,18 15,12 9,6"></polyline>
        </svg>
      </div>
    </a>

    <!-- Payment Methods -->
    <a href="{{ wc_get_account_endpoint_url( 'payment-methods' ) }}" class="dashboard-card payment-card">
      <div class="card-icon">
        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect>
          <line x1="1" y1="10" x2="23" y2="10"></line>
        </svg>
      </div>
      <div class="card-content">
        <h3>Payment Methods</h3>
        <p>Manage your saved payment options</p>
      </div>
      <div class="card-arrow">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <polyline points="9,18 15,12 9,6"></polyline>
        </svg>
      </div>
    </a>

    <!-- Wishlist -->
    <a href="#" class="dashboard-card wishlist-card">
      <div class="card-icon">
        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
        </svg>
      </div>
      <div class="card-content">
        <h3>Wishlist</h3>
        <p>View your saved favorite items</p>
      </div>
      <div class="card-arrow">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <polyline points="9,18 15,12 9,6"></polyline>
        </svg>
      </div>
    </a>

  </div>
</div>

<style>
/* Account Dashboard Styles */
.account-dashboard {
  max-width: 1200px;
  margin: 0 auto;
  padding: 2rem;
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
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

/* Dashboard Grid */
.dashboard-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
  gap: 1.5rem;
}

/* Dashboard Cards */
.dashboard-card {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1.5rem;
  background: #ffffff;
  border: 1px solid #e5e7eb;
  border-radius: 16px;
  text-decoration: none;
  color: inherit;
  transition: all 0.3s ease;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  position: relative;
  overflow: hidden;
}

.dashboard-card::before {
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

.dashboard-card:hover::before {
  transform: scaleX(1);
}

.dashboard-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
  text-decoration: none;
  color: inherit;
}

/* Card Icons */
.card-icon {
  flex-shrink: 0;
  width: 60px;
  height: 60px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.3s ease;
}

.orders-card .card-icon {
  background: linear-gradient(135deg, #3b82f6, #1d4ed8);
  color: white;
}

.addresses-card .card-icon {
  background: linear-gradient(135deg, #10b981, #059669);
  color: white;
}

.account-card .card-icon {
  background: linear-gradient(135deg, #8b5cf6, #7c3aed);
  color: white;
}

.downloads-card .card-icon {
  background: linear-gradient(135deg, #f59e0b, #d97706);
  color: white;
}

.payment-card .card-icon {
  background: linear-gradient(135deg, #ef4444, #dc2626);
  color: white;
}

.wishlist-card .card-icon {
  background: linear-gradient(135deg, #ec4899, #db2777);
  color: white;
}

.dashboard-card:hover .card-icon {
  transform: scale(1.1);
}

/* Card Content */
.card-content {
  flex: 1;
}

.card-content h3 {
  font-size: 1.25rem;
  font-weight: 600;
  color: #1f2937;
  margin: 0 0 0.25rem 0;
}

.card-content p {
  color: #6b7280;
  font-size: 0.9rem;
  margin: 0;
  line-height: 1.4;
}

/* Card Arrow */
.card-arrow {
  color: #d1d5db;
  transition: all 0.3s ease;
}

.dashboard-card:hover .card-arrow {
  color: #6b7280;
  transform: translateX(4px);
}

/* Responsive Design */
@media (max-width: 768px) {
  .account-dashboard {
    padding: 1rem;
  }
  
  .dashboard-header {
    flex-direction: column;
    gap: 1rem;
    text-align: center;
  }
  
  .welcome-section h1 {
    font-size: 2rem;
  }
  
  .dashboard-grid {
    grid-template-columns: 1fr;
    gap: 1rem;
  }
  
  .dashboard-card {
    padding: 1.25rem;
  }
  
  .card-icon {
    width: 50px;
    height: 50px;
  }
  
  .card-content h3 {
    font-size: 1.1rem;
  }
  
  .card-content p {
    font-size: 0.85rem;
  }
}
</style>
