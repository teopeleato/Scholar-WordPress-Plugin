<?php

// Planification de l'événement cron en utilisant la valeur enregistrée
function scholar_scrapper_schedule_event() {
    $frequency = get_option('scholar_scrapper_cron_frequency', 'daily');
    wp_schedule_event(time(), $frequency, 'scholar_scrapper_cron_hook');
}
add_action('init', 'scholar_scrapper_schedule_event');


// Fonction pour annuler l'événement cron
function scholar_scrapper_unschedule_event() {
    wp_clear_scheduled_hook('scholar_scrapper_cron_hook');
}

function scholar_scrapper_modify_schedule() {
    // Récupération de la nouvelle fréquence d'exécution
    $new_frequency = get_option('scholar_scrapper_cron_frequency', 'daily');
    // Annulation de l'événement cron existant
    wp_clear_scheduled_hook('scholar_scrapper_cron_hook');
    // Planification de l'événement cron avec la nouvelle fréquence
    wp_schedule_event(time(), $new_frequency, 'scholar_scrapper_cron_hook');
}