<?php
/**
 * Exécute une commande et retourne la sortie de la commande et le code de retour de la commande.
 *
 * @param $command string La commande à exécuter.
 * @param $function FUNCTION_TYPE Le type de fonction à utiliser pour exécuter la commande.
 *
 * @return array [0] => string La sortie de la commande, [1] => int Le code de retour de la commande.
 */
function scholar_scraper_run_command( string $command, $function = FUNCTION_TYPE::EXEC ): array {

	$ret_var = - 1;
	$res     = null;

	// Select which method to use to run the command:
	try {
		switch ( $function ) {

			// Execution de la commande avec la fonction exec()
			case FUNCTION_TYPE::EXEC:
			default:
				if ( exec( $command, $res, $ret_var ) ) {
					$res = implode( "\n", $res );
				}
				break;

			// Execution de la commande avec la fonction shell_exec()
			case FUNCTION_TYPE::SHELL_EXEC:
				$res = shell_exec( $command );
				break;


			// Execution de la commande avec la fonction system()
			case FUNCTION_TYPE::SYSTEM:
				ob_start();
				system( $command, $ret_var );
				$res = ob_get_contents();
				ob_end_clean();
				break;

			// Execution de la commande avec la fonction passthru()
			case FUNCTION_TYPE::PASSTHRU:
				ob_start();
				passthru( $command, $ret_var );
				$res = ob_get_contents();
				ob_end_clean();
				break;

			// Execution de la commande avec la fonction popen()
			case FUNCTION_TYPE::POPEN:
				if ( ( $handle = popen( $command, 'r' ) ) !== false ) {
					while ( ! feof( $handle ) ) {
						$res .= fgets( $handle );
					}
					pclose( $handle );
				}
				break;

		}
	} finally {
		return array( $res, $ret_var );
	}
}


function scholar_scraper_run_command_try_all_methods( string $command ): array {
	$res     = "";
	$ret_var = - 1;

	if ( empty( $command ) ) {
		return [ $res, $ret_var ];
	}

	foreach ( FUNCTION_TYPE::cases() as $functionType ) {
		list( $res, $ret_var ) = scholar_scraper_run_command(
			$command,
			$functionType );

		# Check if the command was executed successfully, if so, break the loop
		if ( $ret_var == 0 ) {
			$log = sprintf( "%s SUCCESS :\t%-10s\t%s\n", date( "Y-m-d H:i:s" ), $functionType, str_replace( PHP_EOL, '', trim( $res ) ) );
			scholar_scraper_log( $log );
			break;
		}

		$log = sprintf( "%s ERROR :\t%-10s\t%s (returned : %d)\n", date( "Y-m-d H:i:s" ), $functionType, str_replace( PHP_EOL, '', ( $res ) ), $ret_var );
		scholar_scraper_log( $log );
	}

	return [ $res, $ret_var ];
}