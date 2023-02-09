<?php # -*- coding: utf-8 -*-
/*
 * Plugin Name: Scholar Scraper
 * Description: A plugin for scraping Google Scholar articles.
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

// Le fichier de configuration doit être inclus après les fichiers du dossier "src" car la définition
// de certaines constantes utilisent des fonction définies dans les fichiers du dossier "src".
require_once __DIR__ . "/config.php";
require_once __DIR__ . "/src/index.php";
