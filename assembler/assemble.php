<?php

// || -- || PHP Assembler for Multi-page Website || -- ||

/*

  Assembles endpoint files at /dist from /src.
  
  Project structure at project-structure.txt.
  
  In this file:
  
  - Define constants.
  - Ensure necessary folders exist.
  - Ensure there is a .htacess file blocking /logs*.
  - Get generic content for the page endpoints.
  - Get main content for every page:
  -- Get main file and support files.
  -- Get view content.
  -- Merge content files.
  -- Create endfile at /dist.
  - Make api.
  - Copy necessary files to /dist.

*/

echo 'Assembling endpoint files for php website.';

define( 'ROOT',         dirname( __DIR__ ) . DIRECTORY_SEPARATOR );
define( 'DIR_DIST',     ROOT         . 'dist'     . DIRECTORY_SEPARATOR );
define( 'DIR_DIST_API', DIR_DIST     . 'api'      . DIRECTORY_SEPARATOR );
define( 'DIR_DIST_CSS', DIR_DIST     . 'css'      . DIRECTORY_SEPARATOR );
define( 'DIR_DIST_JS',  DIR_DIST     . 'js'       . DIRECTORY_SEPARATOR );
define( 'DIR_LOGS',     DIR_DIST     . 'logs'     . DIRECTORY_SEPARATOR );
define( 'DIR_SRC',      ROOT         . 'src'      . DIRECTORY_SEPARATOR );
define( 'DIR_API',      DIR_SRC      . 'api'      . DIRECTORY_SEPARATOR );
define( 'DIR_CORE',     DIR_SRC      . 'core'     . DIRECTORY_SEPARATOR );
define( 'DIR_FRONTEND', DIR_SRC      . 'frontend' . DIRECTORY_SEPARATOR );
define( 'DIR_PARTIALS', DIR_FRONTEND . 'partials' . DIRECTORY_SEPARATOR );
define( 'DIR_SCRIPTS',  DIR_FRONTEND . 'scripts'  . DIRECTORY_SEPARATOR );
define( 'DIR_PAGES',    DIR_SRC      . 'pages'    . DIRECTORY_SEPARATOR );

// Ensure necessary folders exist.
foreach ( [
  DIR_API,
  DIR_CORE,
  DIR_DIST,
  DIR_DIST_API,
  DIR_DIST_CSS,
  DIR_DIST_JS,
  DIR_FRONTEND,
  DIR_LOGS,
  DIR_PARTIALS,
  DIR_PAGES,
  DIR_SCRIPTS,
  DIR_SRC
] as $dir_path )
{
  if ( ! is_dir( $dir_path ) )
  {
    mkdir( $dir_path );
    ECHO_after_newline( 'mkdir: ' . $dir_path );
  }
}

// Ensure there's an .htaccess blocking requests to /logs*.
ensure_htaccess_blocking_logs();

// --
// -- Get generic content for endfiles --

// Get core functions files.
$_CORE_content = '';
require_once 'get-core-files.php';

$endfile_start = '// --- CORE ---' . $_CORE_content;

// -- Arrays for asset file names --

// All files to be copied to /dist.
$_CSS_files = [];
$_JS_files  = [];

// Layout asset files to be included in each page.
$_layout_css = [];
$_layout_js  = [];

$_layout_content = get_view_with_partials_and_register_assets( DIR_FRONTEND . 'layout.php', $_layout_css, $_layout_js );

// --
// -- Get all pages --

$_PAGES = [];
require_once 'get-pages.php';

// Mount endfile per page.
$i = 0;
foreach ( $_PAGES as $page )
{
  $i++;

  // Include functions files if present.
  if ( $page['functions'] )
  {
    $functions_content = "\n// --- FUNCTIONS ---\n";
    foreach ( $page['functions'] as $fn_file )
    {
      $functions_content .= file_get_contents( $fn_file );
    }
  }

  $checks_content = $page['checks'] ? "\n// --- CHECKS ---" . file_get_contents( $page['checks'] ) : '';
  $post_content   = $page['post']   ? "\n// --- POST ---"   . file_get_contents( $page['post']   ) : '';
  $main_content   = "\n\n// --- MAIN ---" . file_get_contents( $page['main_page_file'] );
  $view_content   = "\n\n<?php // --- VIEW --- \\\\ ?>\n\n" . get_view_content( $page );

  $endfile_content = $endfile_start . ( $functions_content ?? '' ) . $checks_content . $post_content . $main_content;

  $endfile_content = "<?php\n\n" . str_replace( [ "<?php\r\n", '?>' ], '', $endfile_content ) . "\n?>" . $view_content;

  // Create endpoint file.
  file_put_contents( DIR_DIST . $page['name'] . '.php', $endfile_content );
}

// Output number of generated files.
ECHO_after_newline( $i . ' page files generated' );

// Make API.
require_once 'make-api.php';

// Add layout assets to global asset arrays.
$_CSS_files = array_merge( $_layout_css, array_unique( $_CSS_files ) );
$_JS_files  = array_merge( $_layout_js,  array_unique( $_JS_files ) );

// -- Copy necessary files into /dist --
require_once 'copy-files.php';

// --
// -- Functions --

function ECHO_after_newline( $msg )
{
  echo '<br><br>' . $msg;
}

function ensure_htaccess_blocking_logs()
{
  $path = DIR_SRC . '/.htaccess';
  $code = "RewriteEngine On\nRewriteRule ^logs - [R=404,L]\n";

  if ( ! file_exists( $path ) )
  {
    file_put_contents( $path, $code );
    ECHO_after_newline( '.htaccess file created, blocking access to /logs*.' );
  }
  else
  {
    ECHO_after_newline( '.htaccess file exists.' );
    
    $content = file_get_contents( $path );
    if ( strpos( $content, 'RewriteRule ^logs' ) === false )
    {
      file_put_contents( $path, $code . "\n" . $content );
      ECHO_after_newline( 'blocking access to /logs* - prepended to .htaccess.' );
    }
  }
}

function get_view_content( $page )
{
  /*

    1- Get generic layout content and partials.
    2- Register layout asset file names.
    3- Get main content and its partials.
    4- Register page-required asset file names.
    5- Register scripts and remove __SCRIPT_...__ lines.
    6- Turn CSS and JS file names in array to <link> and <script>.
    7- Add <link>s and <script>s to layout content.
    8- Join layout and main contents.
    9- Return.

  */

  global $_CSS_files;
  global $_JS_files;
  global $_layout_content;
  global $_layout_css;
  global $_layout_js;

  // -- Get generic content --

  // Get generic layout content to which the page content will be added.
  $layout_content = $_layout_content;

  // Asset file names for later transformation into html tags.
  $css = [];
  $js  = [];
  
  // Get CSS files for this page.
  foreach ( $page['css_files'] as $css_file )
  {
    $css[] = $css_file;
  }

  // Get JS file for this page.
  if ( $page['js_view_file'] ) $js[] = $page['js_view_file'];

  // -- Add generic partials and assets --

  $main_view_content = get_view_with_partials_and_register_assets( $page['main_view_file'], $css, $js );

  // -- Prepare CSS and JS files --

  // Add standalone js scripts.
  // These can be present inside partial views, hence this comes after adding the partials.
  foreach ( preg_split( '/\r\n|\r|\n/', $main_view_content ) as $line )
  {
    if ( str_starts_with( $line, '__SCRIPT_' ) )
    {
      // __SCRIPT_{NAME_OF_SCRIPT}__
      $script_name = str_replace( '_', '-', strtolower( substr( $line, 9, -2 ) ) ) . '.js';
      $script_file = get_js_script_file( $script_name );
      if ( is_file( $script_file ) )
      {
        $js[] = $script_file;
      }
      else echo '<br><b>__SCRIPT__ file not found:</b> ' . basename( $script_name ) . ' <b>from page</b> ' . $page['name'] . '</b>';

      // Remove __SCRIPT_...__ line.
      $main_view_content = str_replace( $line, '', $main_view_content );
    }
  }
  
  /*
    Layout is the same across pages, so its assets will be merged with the global arrays after all pages are processed.
    Thus, each page gets the layout assets after registering its own assets in the global arrays, preventing merging duplicates.
  */
  
  // Register assets in global arrays.
  $_CSS_files = array_merge( $_CSS_files, $css );
  $_JS_files  = array_merge( $_JS_files,  $js );
  
  // Get layout asset files.
  $css = array_merge( $_layout_css, $css );
  $js  = array_merge( $_layout_js,  $js );

  // -- Get html tags for the required assets --

  $css_tags = [];
  foreach ( $css as $filename )
  {
    $css_tags[] = '<link rel="stylesheet" href="css/' . basename( $filename ) . '">';
  }

  $js_tags = [];
  foreach ( $js as $filename )
  {
    $js_tags[] = '<script src="js/' . basename( $filename ) . '"></script>';
  }

  // -- Build layout content --

  // Replace layout placeholder for page_name.
  $layout_content = str_replace( '__PAGE_NAME__', $page['name'], $layout_content );

  // Replace layout placeholders for CSS and JS assets.
  $layout_content = str_replace( '__CSS__', implode( "\n", $css_tags ), $layout_content );
  $layout_content = str_replace( '__JS__',  implode( "\n", $js_tags  ), $layout_content );

  // -- Assemble final view content --
  $view_content = str_replace( '__MAIN_VIEW__', $main_view_content, $layout_content );

  return $view_content;
}

// Add partials to target_string and register assets in arrays.
function get_view_with_partials_and_register_assets( $file, & $css_files, & $js_files )
{
  /*
    1- Search __PARTIAL_...__ lines.
    2- Replace lines with partial file content.
    3- Register the CSS and JS asset file names.
    4- Run again for the replaced lines before continuing to the following ones.
  */

  $queue = file( $file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES );
  $final_lines = [];

  /*
    Iterate over every line.
    If line starts with __PARTIAL_ prefix -> get file content and run again.
    If line doesn't start with the prefix -> add to the final array.
  */
  while ( $queue )
  {
    $line = trim( array_shift( $queue ) );
    if ( ! str_starts_with( $line, '__PARTIAL_' ) )
    {
      $final_lines[] = $line;
    }
    else
    {
      // __PARTIAL_{NAME_OF_PARTIAL}__
      $partial_name = str_replace( '_', '-', strtolower( substr( $line, 10, -2 ) ) );

      // DIR_PARTIALS/partial-name/partial-name.{php / html}
      $partial_path_wo_ext = DIR_PARTIALS . $partial_name . DIRECTORY_SEPARATOR . $partial_name;

      // Get partial content.
      foreach ( [ '.php', '.html' ] as $ext )
      {
        $partial_file = $partial_path_wo_ext . $ext;
        if ( is_file( $partial_file ) )
        {
          // Get partial file lines into the queue.
          array_unshift( $queue, ...file( $partial_file ) );

          // Register asset file names.
          foreach ( [ '.css', '.js' ] as $asset_ext )
          {
            $partial_asset_file = $partial_path_wo_ext . $asset_ext;
            if ( is_file( $partial_asset_file ) )
            {
              // Add file path to CSS or JS array for later inclusion in stylesheet links.
              // ${css / js}_files
              ${ ltrim( $asset_ext, '.' ) . '_files' }[] = $partial_asset_file;
            }
          }

          // In case the partial is php, prevent running for html.
          break;
        }
      }
    }
  }
  
  return implode( "\n", $final_lines );
}

function get_js_script_file( $file_name, $path = DIR_SCRIPTS )
{
  /*
    Searches file inside DIR_SCRIPTS, recursively, and returns the first match.
  */
  
  // Return filepath if found.
  $file = $path . $file_name;
  if ( is_file( $file ) ) return $file;
  
  // Search inside subfolders.
  foreach ( glob( $path .'*', GLOB_ONLYDIR ) as $subdir )
  {
    $subfile = get_js_script_file( $file_name, $subdir . DIRECTORY_SEPARATOR );
    if ( $subfile )
    {
      return $subfile;
    }
  }
  
  return false;
}
