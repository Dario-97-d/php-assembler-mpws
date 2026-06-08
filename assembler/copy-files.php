<?php

/*

  This script copies all necessary files at /dist.
  
  Copies:
  - All files at /src.
  - All files at /src/frontend.
  -- For JS and CSS files, they are copied into their respective folder inside /dist.
  - All CSS files registered in the $_CSS_files array.
  - All JS files registered in the $_JS_files array.

*/

// Expected global variables.
$_CSS_files;
$_JS_files;

// Copy whichever files are at /src.
foreach ( glob( DIR_SRC . '{*,.*}', GLOB_BRACE ) as $src_item )
{
  if ( is_file( $src_item ) ) copy( $src_item, DIR_DIST . basename( $src_item ) );
}

// Copy whichever files are at /src/frontend.
foreach ( glob( DIR_FRONTEND . '*' ) as $frontend_file )
{
  if ( ! is_file( $frontend_file ) ) continue;

  $ext = pathinfo( $frontend_file, PATHINFO_EXTENSION );

  if ( in_array( $ext, [ 'css', 'js' ] ) )
  {
    copy( $frontend_file, DIR_DIST . $ext . DIRECTORY_SEPARATOR . basename( $frontend_file ) );
  }
  else
  {
    copy( $frontend_file, DIR_DIST . basename( $frontend_file ) );
  }
}

// Copy required CSS files.
foreach ( $_CSS_files as $css_file )
{
  copy( $css_file, DIR_DIST_CSS . basename( $css_file ) );
}

// Copy required JS files.
foreach ( $_JS_files as $js_file )
{
  copy( $js_file, DIR_DIST_JS . basename( $js_file ) );
}
