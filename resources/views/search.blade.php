@extends('layouts.app')

@section('content')
  <div class="search-results-page">
    @include('partials.page-header')

    <div class="search-content">
      @if (! have_posts())
        <div class="no-results">
          <div class="no-results-icon">
            <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1">
              <circle cx="11" cy="11" r="8"></circle>
              <path d="m21 21-4.35-4.35"></path>
            </svg>
          </div>
          <h2>No results found</h2>
          <p>Sorry, we couldn't find anything matching your search. Try different keywords or browse our categories.</p>
          
          <div class="search-again">
            @include('forms.search')
          </div>
        </div>
      @else
        <div class="search-info">
          @php
            global $wp_query;
            $found_posts = $wp_query->found_posts ?? 0;
          @endphp
          <p>Found {{ $found_posts }} result{{ $found_posts !== 1 ? 's' : '' }} for "{{ get_search_query() }}"</p>
        </div>

        <div class="search-results">
          @while(have_posts()) @php(the_post())
            @include('partials.content-search')
          @endwhile
        </div>

        <div class="search-pagination">
          {!! get_the_posts_navigation() !!}
        </div>
      @endif
    </div>
  </div>
@endsection
