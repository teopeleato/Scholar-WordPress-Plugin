<?php # -*- coding: utf-8 -*-
/* Plugin Name: Scholar Scrapper */

defined( 'ABSPATH' ) || exit;

define("PLUGIN_PATH", __DIR__ . "/");

add_shortcode( 'scholar_scrapper', 'scholar_scrapper' );
function scholar_scrapper( $attributes )
{
    global $wpdb;
    if(!isset($wpdb)) return "Error: No database connection";
    

    $data = shortcode_atts(
        [
            'file' => 'ScholarPythonAPI/__init__.py'
        ],
        $attributes
    );

    $scholarUsers = array("1iQtvdsAAAAJ", "dAKCYJgAAAAJ");

    if(!count($scholarUsers)) return "<h3>Sorry there is no result...</h3>";

    $toReturn = "";
    $metaValues = "";

    foreach($scholarUsers as $result){
        $metaValues .= $result;
    }



    foreach($scholarUsers as $result){

        $handle = popen( "python " . __DIR__ . '/' . $data['file'] .  ' ' . $result . ' 2>&1', 'r' );

        while ( ! feof( $handle ) )
        {
            $toReturn .= fread( $handle, 2096 );
        }

        pclose( $handle );
    }

    return $toReturn;
}