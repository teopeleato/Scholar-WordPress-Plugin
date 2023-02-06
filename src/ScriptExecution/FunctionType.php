<?php

/**
 * Classe qui permet de définir les types de fonctions utilisées pour exécuter des commandes.
 */
abstract class FUNCTION_TYPE {
	const EXEC = "exec";
	const SHELL_EXEC = "shell_exec";
	const SYSTEM = "system";
	const PASSTHRU = "passthru";
	const POPEN = "popen";

	/**
	 * Retourne la liste des types de fonctions.
	 * @return array La liste des types de fonctions.
	 */
	public static function cases(): array {
		return array(
			self::EXEC,
			self::SHELL_EXEC,
			self::SYSTEM,
			self::PASSTHRU,
			self::POPEN
		);
	}

}