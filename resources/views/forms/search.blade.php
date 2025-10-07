<form role="search" method="get" class="search-form" action="{{ home_url('/') }}">
  <div class="search-form-container">
    <label class="search-label">
      <span class="sr-only">
        {{ _x('Search for:', 'label', 'sage') }}
      </span>

      <input
        type="search"
        placeholder="{!! esc_attr_x('Search products, pages &hellip;', 'placeholder', 'sage') !!}"
        value="{{ get_search_query() }}"
        name="s"
        class="search-input"          
        id="live-search"
        autocomplete="off"
        autocorrect="off"
        autocapitalize="off"
        spellcheck="false"
      >
    </label>

    <button type="submit" class="search-button">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <circle cx="11" cy="11" r="8"></circle>
        <path d="m21 21-4.35-4.35"></path>
      </svg>
      {{ _x('Search', 'submit button', 'sage') }}
    </button>
  </div>
</form>
