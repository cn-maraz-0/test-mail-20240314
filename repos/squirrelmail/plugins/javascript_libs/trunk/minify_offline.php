#!/usr/bin/php5
<?php
/*
 * Javascript libraries management framework for Squirrelmail Plugins.
 *
 * Licensed under the GNU GPL. For full terms see the file COPYING that came
 * with the Squirrelmail distribution.
 *
 * Also view plugins/README.plugins for more information.
 *
 *
 * Minify individual files under modules-min.
 *
 * Only to be called from PHP-CLI.
 *
 *
 * @version $Id$
 * @author Alexandros Vellis <avel@users.sourceforge.net>
 * @copyright 2007-2008 The SquirrelMail Project Team
 * @package plugins
 * @subpackage javascript_libs
 */

// Bail out if not running from CLI
if(isset($_SERVER['REMOTE_ADDR'])) exit(1);

/** Minify libraries directory */
define('MINIFY_MIN_DIR', getcwd() . '/lib/min');

/** Load minify config */
require MINIFY_MIN_DIR . '/config.php';

set_include_path($min_libPath . PATH_SEPARATOR . get_include_path());

require('Minify.php');

define('MINIFY_CACHE_DIR', './modules-min');

$minifyJS  = new Minify(Minify::TYPE_JS);

$sources = array();

$d = dir('./modules');
//echo "Handle: " . $d->handle . "\n";
//echo "Path: " . $d->path . "\n";

while (false !== ($entry = $d->read())) {
    if($entry != '.' && $entry != '..' && $entry != '.svn' && is_dir($d->path . '/'.$entry)) {
        $e = dir($d->path.'/'.$entry);
        
        @mkdir(MINIFY_CACHE_DIR.'/'.$entry);
         
        while (false !== ($file = $e->read())) {
            if($file != '.' && $file != '..' && $file != '.svn') {
                if(strtolower(filename_extension($file)) == 'js') {
                    // Minify these
                    echo "minifying: ".$e->path.'/'.$file." -> ".MINIFY_CACHE_DIR.'/'.$entry.'/'.$file."\n";
                    $buffer = $minifyJS->combine(array($e->path.'/'.$file));
                    file_put_contents(MINIFY_CACHE_DIR.'/'.$entry.'/'.$file, $buffer);
                } else {
                    // copy these
                    echo "copying: ".$e->path.'/'.$file." -> ".MINIFY_CACHE_DIR.'/'.$entry.'/'.$file."\n";
                    copy($e->path.'/'.$file,  MINIFY_CACHE_DIR.'/'.$entry.'/'.$file);
                }
            }
        }
        
    }
}
$d->close();


function filename_extension($filename)  {
    if (($pos = strrpos($filename, '.')) === false) {
        return '';
    } else { 
        $basename = substr($filename, 0, $pos); 
        $extension = substr($filename, $pos+1); 
        return $extension;
    } 
}

function gzip_contents($buffer, $destfilename) {
    $gzip_compression_level = 9;
    $prefix = "\x1f\x8b\x08\x00\x00\x00\x00\x00";
    $size = strlen($buffer);
    $crc = crc32($buffer);
    $buffer = gzcompress($buffer, $gzip_compression_level);
    $buffer = $prefix
             . substr($buffer, 0, strlen($buffer) - 4) 
             . pack('V', $crc) 
             . pack('V', $size);
    echo "Saving gzip'ed $destfilename . \n";
    file_put_contents($destfilename, $buffer);
}
