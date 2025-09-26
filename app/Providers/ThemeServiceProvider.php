<?php

namespace App\Providers;

use Roots\Acorn\Sage\SageServiceProvider;

class ThemeServiceProvider extends SageServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        parent::register();
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        // ACF Options pages can be added here when ACF plugin is installed
        // This code is commented out to avoid linting errors when ACF is not available
        /*
        add_action('acf/init', function () {
            if (function_exists('acf_add_options_page')) {
                acf_add_options_page([
                    'page_title' => __('Theme Settings', 'sage'),
                    'menu_title' => __('Theme Settings', 'sage'),
                    'menu_slug'  => 'theme-settings',
                    'capability' => 'edit_posts',
                    'redirect'   => false,
                ]);
            }

            if (function_exists('acf_add_options_sub_page')) {
                acf_add_options_sub_page([
                    'page_title'  => __('Navbar Settings', 'sage'),
                    'menu_title'  => __('Navbar', 'sage'),
                    'parent_slug' => 'theme-settings',
                ]);
            }
        });
        */
    }
}
