<?php # -*- coding: utf-8 -*-
/* Plugin Name: Scholar Scrapper */

defined('ABSPATH') || exit;

define("PLUGIN_PATH", __DIR__ . "/");

const PYTHON_PATH = "/Users/guillaume/.pyenv/shims/python";

add_shortcode('scholar_scrapper', 'scholar_scrapper');
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

    # Creating a string with all the scholar users id separated by a space
    foreach ($scholarUsers as $scholarUser) {
        $metaValues .= $scholarUser . " ";
    }

    foreach (FUNCTION_TYPE::cases() as $functionType) {
        list($res, $ret_var) = run_bash_command(PYTHON_PATH . " " . __DIR__ . '/' . $data['file'] . ' ' . $metaValues . ' 2>&1', $functionType);

        var_dump($res);
        var_dump($ret_var);

        if ($ret_var == 0) break;
    }

    return $res;
}

abstract class FUNCTION_TYPE
{
    const EXEC = 1;
    const SHELL_EXEC = 2;
    const SYSTEM = 3;
    const PASSTHRU = 4;
    const POPEN = 5;

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