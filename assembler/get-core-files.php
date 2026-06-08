<?php

// -- Get backend core files --

/*
  
  This code gets all scripts from the core folder and adds them to every page endpoint file.
  The scripts are included from top-level to nested directories, files before folders, and alphabetically.
  
  Example:
  
  core/
  | backstart.php                           <- 1st
  | functions/
  | | logger-functions.php                  <- 2nd
  | | result-functions.php                  <- 3rd
  | | sql-functions.php                     <- 4th
  | | request/
  | | | auth-functions.php                  <- 5th
  | | | input-handling-functions.php        <- 6th
  | | | url-functions.php                   <- 7th
  | | response/
  | | | dialog-functions.php                <- 8th
  | | | die-functions.php                   <- 9th
  | | | redirect-functions.php              <- 10th
  
  In a hypothetical folder inside /core/functions/request,
  the scripts inside this folder would appear before any script inside /core/functions/response.
  
*/

// Expected global variable.
$_CORE_content;

foreach ( get_nested_core_files( DIR_CORE ) as $file )
{
  $_CORE_content .= "\n" . file_get_contents( $file );
}

function get_nested_core_files( $path, & $files = [] )
{
  // GLOB_MARK is necessary to prevent nested matching of current dir.
  $dir_items = glob( $path . '*', GLOB_MARK );
  
  /*
    Get higher level files before recursing.
  */
  
  $sub_files = array_filter( $dir_items, fn( $item ) => is_file( $item ) );
  $sub_dirs  = array_filter( $dir_items, fn( $item ) => is_dir(  $item ) );
  
  // Add current level files to final array.
  $files = array_merge( $files, $sub_files );
  
  // Recurse in all dirs.
  foreach ( $sub_dirs as $sub_dir )
  {
    get_nested_core_files( $sub_dir, $files );
  }
  
  return $files;
}
