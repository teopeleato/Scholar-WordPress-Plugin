<?php # -*- coding: utf-8 -*-
/*
 * Plugin Name: Scholar Scraper
 * Description: A plugin that allows you to integrate users' Google Scholar papers into your website.
 * Version: 1.0.0
 * Author: Guillaume ELAMBERT <guillaume.elambert@yahoo.fr>
 * Author URI: https://elambert-guillau.me
 * Text Domain: scholar-scraper
 */

defined( 'ABSPATH' ) || exit;

/**
 * Le fichier principal du plugin.
 * @since 1.0.0
 */
const PLUGIN_FILE = __FILE__;

/**
 * Le dossier du plugin.
 * @since 1.0.0
 */
const PLUGIN_DIR = __DIR__ . "/";

/**
 * La priorité du plugin.
 * @since 1.0.0
 */
const PLUGIN_PRIORITY = 100;

/**
 * Chemin vers le fichier de configuration du plugin.
 * @since 1.0.0
 */
const PLUGIN_CONFIG = PLUGIN_DIR . "config.php";

// Le fichier de configuration doit être inclus après les fichiers du dossier "src" car la définition
// de certaines constantes utilisent des fonction définies dans les fichiers du dossier "src".
require_once __DIR__ . "/src/init_plugin.php";
