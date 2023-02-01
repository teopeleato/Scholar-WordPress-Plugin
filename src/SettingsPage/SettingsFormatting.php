<?php

/**
 * Fonction pour afficher le formulaire de paramètres du plugin.
 * @return void
 */
function scholar_scrapper_display_settings_form() {
	?>
    <div class="wrap">
        <h1><?php echo get_admin_page_title() ?></h1>
        <form method="post" action="options.php">
			<?php
			// Affiche les champs cachés nécessaires pour la validation
			settings_fields( OPTION_GROUP );

			// Affiche la section de paramètres avec les champs de formulaire
			do_settings_sections( PLUGIN_SLUG );

			submit_button();
			?>
        </form>
    </div>
	<?php
}


/**
 * Fonction pour afficher le champ de formulaire "Cron" à choix multiple
 *
 * @return void
 */
function scholar_scrapper_display_cron_field() {

	$settingName = SETTINGS['CRON_FREQUENCY']['name'];
	$value       = get_option( $settingName );
	?>

    <select name="<?php echo $settingName; ?>">
		<?php foreach ( SETTINGS['CRON_FREQUENCY']['options'] as $key => $label ): ?>
            <option value="<?php echo $key; ?>" <?php selected( $value, $key ); ?>><?php echo $label; ?></option>
		<?php endforeach; ?>
    </select>
	<?php
}


/**
 * Fonction pour afficher le champ de formulaire "Python Path"
 * @return void
 */
function scholar_scrapper_display_python_path_field() {
	$settingName = SETTINGS['PYTHON_PATH']['name'];
	$value       = get_option( $settingName );
	?>

    <input type="text" name="<?php echo $settingName; ?>" value="<?php echo esc_attr( $value ); ?>">
	<?php
}
