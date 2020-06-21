<?php
defined('ABSPATH') or die('No script kiddies please!');
// Add files
class BpaxAddFile
{
    public static function addFiles($path, $filename, $ext, $state = false)
    {
        $file = $path.'/'.$filename.'.'.$ext;

        if ($state == false) :
            require WOO_WARE2GO_PLUGIN_DIR . '/' . $file;
        else :
            return plugins_url($file, dirname(__FILE__));
        endif;
    }
}
// ends
