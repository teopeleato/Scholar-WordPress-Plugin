<?php # -*- coding: utf-8 -*-
/*
 * Plugin Name: Scholar Scrapper
 * Description: A plugin for scraping Google Scholar articles.
 * Version: 1.0
 * Author: Guillaume ELAMBERT <guillaume.elambert@yahoo.fr>
 * Author URI: https://elambert-guillau.me
 * Text Domain: scholar-scrapper
 */

defined('ABSPATH') || exit;

// TODO : CLEAN THIS FILE

function scholar_scrapper($attributes)
{
    global $wpdb;
    if (!isset($wpdb)) return "";


    $data = shortcode_atts(
        [
            'file' => 'ScholarPythonAPI/__init__.py'
        ],
        $attributes
    );

    # TODO: Get the scholar users id from the database
    $scholarUsers = array("1iQtvdsAAAAJ", "dAKCYJgAAAAJ");

    if (!count($scholarUsers)) return "";

    $metaValues = "";
    $res = "";
    $ret_var = -1;

    # Creating a string with all the scholar users id separated by a space
    foreach ($scholarUsers as $scholarUser) {
        $metaValues .= $scholarUser . " ";
    }

    foreach (FUNCTION_TYPE::cases() as $functionType) {
        list($res, $ret_var) = run_bash_command(PYTHON_PATH . " " . __DIR__ . '/' . $data['file'] . ' ' . $metaValues . ' 2>&1', $functionType);

        # Check if the command was executed successfully, if so, break the loop
        if ($ret_var == 0) break;

        # Print the error message in the log.txt file
        $logFile = fopen(PLUGIN_PATH . "log.txt", "a");
        # Format the error message with the current date, function type and the error message
        $log = sprintf("%s ERROR :\t%-10s\t%s (%d)\n", date("Y-m-d H:i:s"), $functionType, trim($res), $ret_var);
        // $res = date("Y-m-d H:i:s") . " Error : " . $functionType . " - " . $res . "(". $ret_var . ")" . PHP_EOL;
        fwrite($logFile, $log);
        fclose($logFile);
    }

    if ($ret_var != 0) {
        return "Error : " . $res;
    }

    // Parse the result to get the JSON
    //$res = json_decode($res, true);
    //var_dump($res);

    return $res;
}

abstract class FUNCTION_TYPE
{
    const EXEC = "exec";
    const SHELL_EXEC = "shell_exec";
    const SYSTEM = "system";
    const PASSTHRU = "passthru";
    const POPEN = "popen";

    public static function cases()
    {
        return array(
            self::EXEC,
            self::SHELL_EXEC,
            self::SYSTEM,
            self::PASSTHRU,
            self::POPEN
        );
    }

}

function run_bash_command($command, $function = FUNCTION_TYPE::EXEC): array
{

    $ret_var = -1;
    $res = null;

    // Select which method to use to run the command:
    try {
        switch ($function) {

            case FUNCTION_TYPE::EXEC:
            default:
                if (exec($command, $res, $ret_var)) {
                    $res = implode("\n", $res);
                }
                break;

            case FUNCTION_TYPE::SHELL_EXEC:
                $res = shell_exec($command);
                break;

            case FUNCTION_TYPE::SYSTEM:
                ob_start();
                system($command, $ret_var);
                $res = ob_get_contents();
                ob_end_clean();
                break;

            case FUNCTION_TYPE::PASSTHRU:
                ob_start();
                passthru($command, $ret_var);
                $res = ob_get_contents();
                ob_end_clean();
                break;

            case FUNCTION_TYPE::POPEN:
                if (($handle = popen($command, 'r')) !== false) {
                    while (!feof($handle)) {
                        $res .= fgets($handle);
                    }
                    pclose($handle);
                }
                break;

        }
    } finally {
        return array($res, $ret_var);
    }
}




