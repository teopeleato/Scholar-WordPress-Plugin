<?php

/**
 * Fonction pour afficher le formulaire de paramètres du plugin.
 * @return void
 */
function scholar_scraper_display_settings_form() {
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
function scholar_scraper_display_cron_field() {

	$settingName = PLUGIN_SETTINGS['CRON_FREQUENCY']['name'];
	$value       = get_option( $settingName );

	?>

    <select name="<?php echo $settingName; ?>">
		<?php foreach ( PLUGIN_SETTINGS['CRON_FREQUENCY']['options'] as $key => $label ): ?>
            <option value="<?php echo $key; ?>" <?php selected( $value, $key ); ?>><?php echo $label; ?></option>
		<?php endforeach; ?>
    </select>
	<?php
}


/**
 * Fonction pour afficher le champ de formulaire "Python Path"
 * @return void
 */
function scholar_scraper_display_python_path_field() {
	scholar_scraper_display_input_field( 'PYTHON_PATH' );
}

/**
 * Fonction pour afficher le champ de formulaire "Pip Path"
 * @return void
 */
function scholar_scraper_display_pip_path_field() {
	scholar_scraper_display_input_field( 'PIP_PATH' );
}


/**
 * Fonction qui affiche un champ de formulaire de type input contenu dans les paramètres du plugin.
 *
 * @param string $settingAcronym L'acronyme du paramètre à afficher.
 *
 * @return void
 */
function scholar_scraper_display_input_field( string $settingAcronym, bool $withLabel = false ) {

	// Si l'acronyme n'existe pas dans le tableau des paramètres du plugin, on arrête le script
	if ( ! array_key_exists( $settingAcronym, PLUGIN_SETTINGS ) ) {
		return;
	}

	$setting = PLUGIN_SETTINGS[ $settingAcronym ];
	$value   = get_option( $setting['name'] );

	echo scholar_scraper_html_input_field(
		$setting['name'],
		$setting['type'],
		$setting['pattern'],
		$value,
		$setting['placeholder'] ?? '',
		$setting['id'] ?? null,
		( $withLabel && ! empty( $setting['label'] ) ) ? $setting['label'] : null
	);
}


/**
 * Fonction pour afficher un champ de formulaire de type input
 *
 * @param string $settingName Le nom de l'option.
 * @param string $type Le type de l'input.
 * @param string $pattern Le pattern de validation.
 * @param string $value La valeur de l'option.
 * @param string|null $placeholder Le placeholder de l'input.
 * @param string|null $id L'id de l'input.
 * @param string|null $label Le label de l'input.
 *
 * @return string Le code HTML du champ de formulaire.
 */
function scholar_scraper_html_input_field( string $settingName, string $type, string $pattern, string $value, string $placeholder = '', string $id = null, string $label = null ): string {

	$htmlField = '';

	if ( empty( $id ) ) {
		$id = $settingName;
	}

	if ( is_null( $placeholder ) ) {
		$placeholder = '';
	}

	if ( ! empty( $label ) ) {
		$htmlField .= sprintf( '<label for="%s">%s</label>', $id, $label );
	}

	$htmlField .= sprintf(
		'<input type="%s" name="%s" id="%s" value="%s" placeholder="%s" pattern="%s">',
		esc_attr( $type ),
		esc_attr( $settingName ),
		esc_attr( $id ),
		esc_attr( $value ),
		esc_attr( $placeholder ),
		esc_attr( $pattern )
	);

	return $htmlField;
}
