<?php

/**
 * Fonction pour afficher le formulaire de paramètres du plugin.
 * @return void
 * @since 1.0.0
 */
function scholar_scraper_display_settings_form() {
	?>
    <div class="wrap">
        <h1><?php echo get_admin_page_title() ?></h1>
        <form method="post" action="options.php" id="scholar-scraper-settings-form">
			<?php
			// Affiche les champs cachés nécessaires pour la validation
			settings_fields( OPTION_GROUP );

			// Affiche la section de paramètres avec les champs de formulaire
			scholar_scraper_do_settings_sections_tabs( PLUGIN_SLUG );

			submit_button();
			?>
        </form>
    </div>
	<?php
}

/**
 * Fonction pour afficher les sections de paramètres avec les champs de formulaire.
 *
 * @param $page string Le slug de la page de paramètres.
 *
 * @return void
 * @since 1.0.0
 */
function scholar_scraper_do_settings_sections_tabs( $page ) {

	global $wp_settings_sections, $wp_settings_fields;

	if ( ! isset( $wp_settings_sections[ $page ] ) ) :
		return;
	endif;

	$content       = '';
	$activeSection = null;
	$activeContent = null;

	echo '<h2 class="nav-tab-wrapper">';

	foreach ( $wp_settings_sections[ $page ] as $section ) :
		// Check if the section is the first one
		$activeSection = $activeSection === null ? ' nav-tab-active' : '';
		$activeContent = $activeContent === null ? ' section-content-active' : '';

		echo '<a class="nav-tab' . $activeSection . '" data-section="' . $section['id'] . '">' . $section['title'] . '</a>';


		$content .= '<div id="content-' . $section['id'] . '" class="section-content' . $activeContent . '" data-section="' . $section['id'] . '">';
		$content .= '<table class="form-table" role="presentation">';

		// Grab the content echo from do_settings_fields
		ob_start();

		// Call the section callback function
		if ( $section['callback'] ) {

			call_user_func( $section['callback'], $section );
		}

		do_settings_fields( $page, $section['id'] );
		$content .= ob_get_clean();
		$content .= '</table></div>';

	endforeach;

	echo '</h2>';

	echo '<div class="tab-content">';
	echo $content;
	echo '</div>';

}


/**
 * Fonction pour afficher la section "Scholar Scraper" du formulaire de paramètres du plugin.
 * @return void
 * @since 1.0.0
 */
function scholar_scraper_display_scholar_scraper_settings_section() {
	?>
    <h4 class="section-description">Here are listed the different settings for the Scholar Scraper execution.</h4>
	<?php
}


/**
 * Fonction qui permet le champs "Reasearchers Role" du formulaire de paramètres du plugin.
 * @return void
 * @since 1.0.0
 */
function scholar_scraper_display_researchers_roles_field() {
	scholar_scraper_display_select_field( 'RESEARCHERS_ROLES' );
}


/**
 * Fonction qui permet le champs "Scholar ID Meta Key" du formulaire de paramètres du plugin.
 * @return void
 * @since 1.0.0
 */
function scholar_scraper_display_meta_key_scholar_id_field() {
	scholar_scraper_display_input_field( 'META_KEY_SCHOLAR_ID' );
}


/**
 * Fonction qui permet le champs "Python API threads" du formulaire de paramètres du plugin.
 * @return void
 * @since 1.0.0
 */
function scholar_scraper_display_threads_field() {
	scholar_scraper_display_input_field( 'PYTHON_API_THREADS' );
}


/**
 * Fonction pour afficher la section "Cron" du formulaire de paramètres du plugin.
 * @return void
 * @since 1.0.0
 */
function scholar_scraper_display_cron_section() {
	?>
    <h4 class="section-description">Here are listed the different settings for the cron job.</h4>
	<?php
}


/**
 * Fonction pour afficher le champ de formulaire "Cron" à choix multiple
 *
 * @return void
 * @since 1.0.0
 */
function scholar_scraper_display_cron_field() {
	scholar_scraper_display_select_field( 'CRON_FREQUENCY' );
}


/**
 * Fonction pour afficher la section "Retry after" du formulaire de paramètres du plugin.
 * @return void
 */
function scholar_scraper_display_retry_field() {
	scholar_scraper_display_input_field( 'CRON_RETRY_AFTER' );
}


/**
 * Fonction pour afficher la section "Python" du formulaire de paramètres du plugin.
 * @return void
 * @since 1.0.0
 */
function scholar_scraper_display_python_section() {
	?>
    <h4 class="section-description">Here are listed the different settings related to the Python configuration.</h4>
	<?php
}


/**
 * Fonction pour afficher le champ de formulaire "Python Path"
 * @return void
 * @since 1.0.0
 */
function scholar_scraper_display_python_path_field() {
	scholar_scraper_display_input_field( 'PYTHON_PATH' );
}


/**
 * Fonction pour afficher le champ de formulaire "Pip Path"
 * @return void
 * @since 1.0.0
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
 * @since 1.0.0
 */
function scholar_scraper_display_input_field( string $settingAcronym, bool $withLabel = false ) {

	// Si l'acronyme n'existe pas dans le tableau des paramètres du plugin, on arrête le script
	if ( ! array_key_exists( $settingAcronym, PLUGIN_SETTINGS ) ) {
		return;
	}

	$setting = PLUGIN_SETTINGS[ $settingAcronym ];
	$value   = scholar_scraper_get_setting_value( $settingAcronym );

	echo scholar_scraper_html_input_field(
		scholar_scraper_get_setting_name( $settingAcronym ),
		$setting['type'] ?? 'text',
		$value,
		$setting['pattern'] ?? null,
		$setting['placeholder'] ?? null,
			$setting['id'] ?? isset( $setting['name'] ) ? strtolower( $setting['name'] ) . '_field' : null,
		( $withLabel && ! empty( $setting['label'] ) ) ? $setting['label'] : null,
		$setting['min'] ?? null,
		$setting['max'] ?? null
	);
}


/**
 * Fonction pour afficher un champ de formulaire de type input
 *
 * @param string $settingName Le nom de l'option.
 * @param string|null $type Le type de l'input.
 * @param string|null $value La valeur de l'option.
 * @param string|null $pattern Le pattern de validation.
 * @param string|null $placeholder Le placeholder de l'input.
 * @param string|null $id L'id de l'input.
 * @param string|null $label Le label de l'input.
 * @param int|null $min La valeur minimale de l'input.
 * @param int|null $max La valeur maximale de l'input.
 *
 * @return string Le code HTML du champ de formulaire.
 * @since 1.0.0
 */
function scholar_scraper_html_input_field( string $settingName, string $type = null, string $value = null, string $pattern = null, string $placeholder = null, string $id = null, string $label = null, int $min = null, int $max = null ): string {

	$htmlField = '';

	if ( empty( $id ) ) {
		$id = $settingName;
	}

	if ( ! isset( $placeholder ) ) {
		$placeholder = '';
	}

	if ( ! empty( $label ) ) {
		$htmlField .= sprintf( '<label for="%s">%s</label>', $id, $label );
	}

	$htmlField .= sprintf(
		'<input name="%s" id="%s" %s %s %s %s %s %s>',
		esc_attr( $settingName ),
		esc_attr( $id ),
		! is_null( $type ) ? 'type="' . esc_attr( $type ) . '"' : '',
		! is_null( $value ) ? 'value="' . esc_attr( $value ) . '"' : '',
		! is_null( $placeholder ) ? 'placeholder="' . esc_attr( $placeholder ) . '"' : '',
		! is_null( $pattern ) ? 'pattern="' . esc_attr( $pattern ) . '"' : '',
		! is_null( $min ) ? 'min="' . esc_attr( $min ) . '"' : '',
		! is_null( $max ) ? 'max="' . esc_attr( $max ) . '"' : ''
	);

	return $htmlField;
}

/**
 * Fonction qui affiche un champ de formulaire de type input contenu dans les paramètres du plugin.
 *
 * @param string $settingAcronym L'acronyme du paramètre à afficher.
 *
 * @return void
 * @since 1.0.0
 */
function scholar_scraper_display_select_field( string $settingAcronym, bool $withLabel = false ) {

	// Si l'acronyme n'existe pas dans le tableau des paramètres du plugin, on arrête le script
	if ( ! array_key_exists( $settingAcronym, PLUGIN_SETTINGS ) ) {
		return;
	}

	$setting = PLUGIN_SETTINGS[ $settingAcronym ];
	$value   = scholar_scraper_get_setting_value( $settingAcronym );


	if ( ! is_array( $value ) ) {
		$value = [ $value ];
	}

	echo scholar_scraper_html_select_field(
		scholar_scraper_get_setting_name( $settingAcronym ),
		$setting['options'],
		$value,
		$setting['type'] === 'multiselect',
			$setting['id'] ?? isset( $setting['name'] ) ? strtolower( $setting['name'] ) . '_field' : null,
		( $withLabel && ! empty( $setting['label'] ) ) ? $setting['label'] : null
	);
}


/**
 * Fonction pour afficher un champ de formulaire de type select.
 *
 * @param string $settingName Le nom de l'option.
 * @param array $values Les valeurs du select.
 * @param array $selectedValues Les valeurs sélectionnées.
 * @param string|null $id L'id du select.
 * @param bool $isMultiple Si le select est multiple ou non.
 * @param string|null $label Le label du select.
 *
 * @return string Le code HTML du champ de formulaire.
 * @since 1.0.0
 */
function scholar_scraper_html_select_field( string $settingName, array $values, array $selectedValues, bool $isMultiple = false, string $id = null, string $label = null ): string {

	$htmlField = '';

	if ( empty( $id ) ) {
		$id = $settingName;
	}

	if ( ! empty( $label ) ) {
		$htmlField .= sprintf( '<label for="%s">%s</label>', $id, $label );
	}

	$htmlField .= sprintf(
		'<select name="%s" id="%s" %s>',
		esc_attr( $isMultiple ? $settingName . '[]' : $settingName ),
		esc_attr( $id ),
		esc_attr( $isMultiple ? 'multiple' : '' ),
	);

	foreach ( $values as $value => $label ) {
		$htmlField .= sprintf(
			'<option value="%s" %s>%s</option>',
			esc_attr( $value ),
			in_array( $value, $selectedValues ) ? 'selected' : '',
			esc_attr( $label )
		);
	}

	$htmlField .= '</select>';

	return $htmlField;
}