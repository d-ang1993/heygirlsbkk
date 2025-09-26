<header class="site-header">
  <!-- Top Bar -->
  <div class="header-top">
    <div class="container">
      <div class="header-top__left">
        <span class="welcome-text">Welcome to heygirlsbkk</span>
      </div>
      <div class="header-top__right">
        <a href="#" class="header-link">LOGIN</a>
        <a href="#" class="header-link">JOIN + 6,000P</a>
        <a href="#" class="header-link">CART</a>
        <a href="#" class="header-link">ORDER</a>
        <a href="#" class="header-link">MY PAGE</a>
      </div>
    </div>
  </div>

  <!-- Main Header -->
  <div class="header-main">
    <div class="container">
      <div class="header-main__content">
        <!-- Logo -->
        <div class="header-logo">
          <a href="{{ home_url() }}">
            <h1 class="site-title">heygirlsbkk</h1>
          </a>
        </div>

        <!-- Navigation -->
        <nav class="main-navigation">
          <ul class="nav-menu">
            <li class="nav-item">
              <a href="#" class="nav-link">NEW 5%</a>
            </li>
            <li class="nav-item">
              <a href="#" class="nav-link">BEST 60</a>
            </li>
            <li class="nav-item">
              <a href="#" class="nav-link">SALE</a>
            </li>
            <li class="nav-item dropdown">
              <a href="#" class="nav-link">ALL PRODUCTS</a>
              <div class="dropdown-menu">
                <div class="dropdown-column">
                  <h4>TOPS</h4>
                  <ul>
                    <li><a href="#">T-Shirts</a></li>
                    <li><a href="#">Blouses</a></li>
                    <li><a href="#">Knitwear</a></li>
                    <li><a href="#">Shirts</a></li>
                    <li><a href="#">Cardigans</a></li>
                  </ul>
                </div>
                <div class="dropdown-column">
                  <h4>BOTTOMS</h4>
                  <ul>
                    <li><a href="#">Pants</a></li>
                    <li><a href="#">Skirts</a></li>
                    <li><a href="#">Dresses</a></li>
                    <li><a href="#">Jumpsuits</a></li>
                  </ul>
                </div>
                <div class="dropdown-column">
                  <h4>OUTERWEAR</h4>
                  <ul>
                    <li><a href="#">Jackets</a></li>
                    <li><a href="#">Coats</a></li>
                    <li><a href="#">Padded Jackets</a></li>
                    <li><a href="#">Cardigans</a></li>
                  </ul>
                </div>
              </div>
            </li>
            <li class="nav-item dropdown">
              <a href="#" class="nav-link">NEW ARRIVALS</a>
            </li>
            <li class="nav-item dropdown">
              <a href="#" class="nav-link">BEST SELLERS</a>
            </li>
            <li class="nav-item dropdown">
              <a href="#" class="nav-link">SALE</a>
            </li>
          </ul>
        </nav>

        <!-- Header Actions -->
        <div class="header-actions">
          <div class="search-box">
            <form class="search-form">
              <input type="search" placeholder="Search products..." class="search-input">
              <button type="submit" class="search-button">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <circle cx="11" cy="11" r="8"></circle>
                  <path d="m21 21-4.35-4.35"></path>
                </svg>
              </button>
            </form>
          </div>
          <div class="header-icons">
            <a href="#" class="icon-link">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M9 12l2 2 4-4"></path>
                <path d="M21 12c-1 0-3-1-3-3s2-3 3-3 3 1 3 3-2 3-3 3"></path>
                <path d="M3 12c1 0 3-1 3-3s-2-3-3-3-3 1-3 3 2 3 3 3"></path>
                <path d="M12 3c0 1-1 3-3 3s-3-2-3-3 1-3 3-3 3 2 3 3"></path>
                <path d="M12 21c0-1 1-3 3-3s3 2 3 3-1 3-3 3-3-2-3-3"></path>
              </svg>
            </a>
            <a href="#" class="icon-link">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
                <line x1="3" y1="6" x2="21" y2="6"></line>
                <path d="M16 10a4 4 0 0 1-8 0"></path>
              </svg>
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
</header>

<style>
/* Header Styles */
.site-header {
  background: #fff;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
  position: sticky;
  top: 0;
  z-index: 1000;
}

.header-top {
  background: #f8f9fa;
  border-bottom: 1px solid #e9ecef;
  padding: 8px 0;
  font-size: 12px;
}

.header-top .container {
  display: flex;
  justify-content: space-between;
  align-items: center;
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 20px;
}

.header-top__right {
  display: flex;
  gap: 15px;
}

.header-link {
  color: #666;
  text-decoration: none;
  font-weight: 500;
  transition: color 0.2s;
}

.header-link:hover {
  color: #000;
}

.header-main {
  padding: 15px 0;
}

.header-main__content {
  display: flex;
  align-items: center;
  justify-content: space-between;
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 20px;
}

.site-title {
  font-size: 28px;
  font-weight: 700;
  color: #000;
  margin: 0;
  text-decoration: none;
}

.main-navigation {
  flex: 1;
  display: flex;
  justify-content: center;
}

.nav-menu {
  display: flex;
  list-style: none;
  margin: 0;
  padding: 0;
  gap: 30px;
}

.nav-link {
  color: #333;
  text-decoration: none;
  font-weight: 500;
  font-size: 16px;
  padding: 10px 0;
  position: relative;
  transition: color 0.2s;
}

.nav-link:hover {
  color: #000;
}

.nav-item.dropdown {
  position: relative;
}

.dropdown-menu {
  position: absolute;
  top: 100%;
  left: 0;
  background: #fff;
  box-shadow: 0 4px 12px rgba(0,0,0,0.15);
  border-radius: 8px;
  padding: 20px;
  min-width: 600px;
  display: none;
  z-index: 1000;
}

.nav-item.dropdown:hover .dropdown-menu {
  display: flex;
  gap: 40px;
}

.dropdown-column h4 {
  font-size: 14px;
  font-weight: 600;
  margin: 0 0 15px 0;
  color: #000;
}

.dropdown-column ul {
  list-style: none;
  margin: 0;
  padding: 0;
}

.dropdown-column li {
  margin-bottom: 8px;
}

.dropdown-column a {
  color: #666;
  text-decoration: none;
  font-size: 14px;
  transition: color 0.2s;
}

.dropdown-column a:hover {
  color: #000;
}

.header-actions {
  display: flex;
  align-items: center;
  gap: 20px;
}

.search-box {
  position: relative;
}

.search-form {
  display: flex;
  align-items: center;
}

.search-input {
  width: 200px;
  padding: 8px 12px;
  border: 1px solid #ddd;
  border-radius: 20px;
  font-size: 14px;
  outline: none;
  transition: border-color 0.2s;
}

.search-input:focus {
  border-color: #000;
}

.search-button {
  position: absolute;
  right: 8px;
  background: none;
  border: none;
  cursor: pointer;
  padding: 4px;
  color: #666;
  transition: color 0.2s;
}

.search-button:hover {
  color: #000;
}

.header-icons {
  display: flex;
  gap: 15px;
}

.icon-link {
  color: #666;
  transition: color 0.2s;
}

.icon-link:hover {
  color: #000;
}

/* Responsive */
@media (max-width: 768px) {
  .header-top {
    display: none;
  }
  
  .header-main__content {
    flex-direction: column;
    gap: 15px;
  }
  
  .main-navigation {
    order: 3;
    width: 100%;
  }
  
  .nav-menu {
    flex-wrap: wrap;
    gap: 15px;
    justify-content: center;
  }
  
  .search-input {
    width: 150px;
  }
}
</style>