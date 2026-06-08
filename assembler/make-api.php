<?php

$_API_core_content = '';

// Get functions files from /src/core/functions.
foreach ( glob( DIR_CORE . 'functions/*' ) as $dir_item )
{
  if ( is_file( $dir_item ) ) $_API_core_content .= file_get_contents( $dir_item );
}

foreach ( get_nested_api_files( DIR_API ) as $api_file )
{
  $slug = str_replace( DIR_API, '', $api_file );
  file_put_contents( DIR_DIST_API . $slug, '<?php' . str_replace( [ '<?php', '?>'], '', "\n\n// --- API core content ---" . $_API_core_content . "\n\n// --- Main API content ---" . file_get_contents( $api_file ) ) );
}

function get_nested_api_files( $path, & $files = [] )
{
  // GLOB_MARK is necessary to prevent nested matching of current dir.
  foreach ( glob( $path . '*', GLOB_MARK ) as $dir_item )
  {
    if ( is_file( $dir_item ) ) $files[] = $dir_item;
    else get_nested_api_files( $dir_item, $files );
  }

  return $files;
}
