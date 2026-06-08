<?php

/*

  Each page is defined by its path, folder and files, as in:

  Page (example): Player Overview
  - main page file (required): /src/pages/player/overview/player-overview.php
  - expectable files: /src/pages/player/overview/
  -- player-overview.php                       <- Main page file (required)
  -- player-overview-checks.php                <- Checks made before the main file
  -- player-overview-fn.php                    <- Support functions for this feature
  -- player-overview-post.php                  <- HTTP handling (all verbs except GET)
  -- view-player-overview.css                  <- CSS file
  -- view-player-overview.js                   <- JS file
  -- view-player-overview.php                  <- Main view file

  Order of file insertion:
  1- feature-fn.php            <- Functions
  2- feature-checks.php        <- Checks
  3- feature-post.php          <- Post
  4- feature.php               <- Main
  5- view files                <- View

  Inherited files: for functions and CSS, all ancestor files are included (except for index).

  It works if, at least, the main page file is included.
  The expectable files may be used to separate concerns.

*/

// Initialize pages array.
$_PAGES = [];

// -- Define functions --

function get_index_page()
{
  global $_PAGES;

  $_PAGES[] = [
    'name'           => 'index',
    'main_page_file' => DIR_PAGES . 'index.php',
    'functions'      => [],
    'checks'         => '',
    'post'           => '',
    'main_view_file' => DIR_PAGES . 'view-index.php',
    'css_files'      => [],
    'js_view_file'   => ''
  ];
}

// Get all pages (except index).
function get_pages_files( $current_path )
{
  /*

    1st - Determine page name for the current directory and check whether its main page file exists.
    2nd - Get ancestor files for php functions and CSS.
    3rd - Get the other files.
    4th - Add page info to the global array.
    5th - Recurse.

    Example:

      /src/pages/feature/
      - feature.php                     <- page file, will include fn file.
      - feature-fn.php
      /src/pages/feature/subfeature/
      -- feature-subfeature.php         <- page file, will include view and feature-fn.php
      -- view-feature-subfeature.php

  */

  global $_PAGES;

  foreach ( glob( $current_path . '*', GLOB_ONLYDIR ) as $sub_dir )
  {
    $nested_path = $sub_dir . DIRECTORY_SEPARATOR;

    // Get page name.
    // /src/pages/page/name/ -> page/name -> page-name
    $page_path = str_replace( DIR_PAGES, '', $sub_dir );
    $page_name = str_replace( DIRECTORY_SEPARATOR, '-', $page_path );

    $main_page_file = $nested_path . $page_name . '.php';

    $functions_files = [];
    $css_files = [];

    // Process this directory's files only if the main page file is present.
    if ( is_file( $main_page_file ) )
    {
      // -- Get ancestor files for this page --
      $parts = explode( '-', $page_name );
      foreach ( $parts as $i => $part )
      {
        /*

          -- Example --

          page name: player-overview
          relevant ancestor files:

            (DIR_PAGES)/player/
              player-fn.php
              view-player.css

            (DIR_PAGES)/player/overview/
              player-overview-fn.php
              view-player-overview.css

        */

        $file_name_parts = array_slice( $parts, 0, $i + 1 );
        $path = DIR_PAGES . implode( DIRECTORY_SEPARATOR, $file_name_parts ) . DIRECTORY_SEPARATOR;

        $name = implode( '-', $file_name_parts );

        $fn_name  =           $name . '-fn.php';
        $css_name = 'view-' . $name . '.css';

        $fn_file  = $path . $fn_name;
        $css_file = $path . $css_name;

        if ( is_file( $fn_file  ) ) $functions_files[] = $fn_file;
        if ( is_file( $css_file ) ) $css_files[]       = $css_file;
      }

      // -- Get other files --

      $checks_file = $nested_path . $page_name . '-checks.php';
      $post_file   = $nested_path . $page_name . '-post.php';

      $view_name = 'view-' . $page_name;

      $main_view_file = $nested_path . $view_name . '.php';
      $js_view_file   = $nested_path . $view_name . '.js';

      // -- Add page --

      // Add page and info on its files.
      $_PAGES[] = [
        'name'           => $page_name,
        'main_page_file' => $main_page_file,
        'functions'      => $functions_files,
        'checks'         => is_file( $checks_file )  ? $checks_file  : '',
        'post'           => is_file( $post_file )    ? $post_file    : '',
        'main_view_file' => $main_view_file,
        'css_files'      => $css_files,
        'js_view_file'   => is_file( $js_view_file ) ? $js_view_file : ''
      ];
    }

    get_pages_files( $nested_path );
  }
}


// -- Call functions --

get_index_page();
get_pages_files( DIR_PAGES );
