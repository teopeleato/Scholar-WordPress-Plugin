<?php
if ( ! isset( $publication ) || empty( $publication->title ) ) {
	return;
}
?>

<!-- On crée une sorte de carte pour les publications scientifiques -->
<div class="scholar-scraper-publication-card">

    <div class="scholar-scraper-publication-card-top">

        <!-- On affiche le titre de la publication -->
        <h3 class="scholar-scraper-publication-card-title">
			<?php echo $publication->title; ?>
        </h3>


		<?php if ( ! empty( $publication->author ) ) {
			// Count the number of authors using the explode function
			$count = count( explode( " and ", $publication->author ) );
			?>
            <!-- On affiche l'auteur de la publication -->
            <p class="scholar-scraper-publication-card-author">
                <strong><u>Author<?php echo $count > 1 ? 's' : ''; ?>:</u></strong> <?php echo $publication->author; ?>
            </p>

		<?php }

		if ( ! empty( $publication->pub_year ) ||
		     ! empty( $publication->pages ) ||
		     ! empty( $publication->venue ) ||
		     ! empty( $publication->journal ) ||
		     ! empty( $publication->volume ) ||
		     ! empty( $publication->number ) ||
		     ! empty( $publication->publisher )
		) { ?>
            <!-- On affiche la date de publication -->
            <p class="scholar-scraper-publication-card-date">
				<?php
				$listToShow = [];

				if ( ! empty( $publication->pub_year ) ) {
					$listToShow[] = $publication->pub_year . " ";
				}

				if ( ! empty( $publication->venue ) ) {
					$listToShow[] = $publication->venue . "venue ";
				}

				if ( ! empty( $publication->journal ) ) {
					$tmpString = $publication->journal;

					if ( ! empty( $publication->volume ) ) {
						$tmpString .= ", vol. " . $publication->volume;

						if ( ! empty( $publication->pages ) ) {
							$tmpString .= ", p. " . $publication->pages;
						}

					} else if ( ! empty( $publication->pages ) ) {
						$tmpString .= ", p. " . $publication->pages;
					}

					$listToShow[] = $tmpString;
				}

				// Create a string where each element of the array is separated by a dash (-)
				echo implode( " - ", $listToShow );
				?>
            </p>

			<?php
		}

		if ( ! empty( $publication->abstract ) ) {

			$specificClass         = '';
			$styleString           = '';
			$shouldDisplayAbstract = true;

			// Entrée : Le nombre de lignes de l'abstract à afficher est limité
			//       => On fait le nécessaire pour afficher le nombre lignes demandées
			if ( isset( $numberLinesAbstract ) ) {

				$shouldDisplayAbstract = $numberLinesAbstract > 0;

				$specificClass = 'content-limited';
				$styleString   = "style=\"--number-lines:$numberLinesAbstract;\"";
			}

			if ( $shouldDisplayAbstract ) {
				?>
                <!-- On affiche l'abstract de publication -->
                <div class="scholar-scraper-publication-card-abstract">
                    <strong><u>Abstract :</u></strong>
                    <p class="scholar-scraper-publication-card-abstract-content <?php echo $specificClass; ?>" <?php echo $styleString; ?>>
						<?php echo $publication->abstract; ?>
                    </p>
                </div>

			<?php }
		} ?>

    </div>

	<?php
	if ( ! empty( $publication->pub_url ) ) {
		$url   = $publication->pub_url;
		$title = "View the publication on the publisher's website";
	} else if ( ! empty( $publication->author_pub_id ) ) {
		$url   = 'https://scholar.google.com/citations?view_op=view_citation&citation_for_view=' . $publication->author_pub_id;
		$title = "View the publication on Google Scholar";
	}

	if ( ! empty( $url ) && ! empty( $title ) ) {
		?>
        <!-- On affiche le lien vers la publication -->
        <div class="wp-block-button scholar-scraper-publication-card-link">
            <a class="wp-block-button__link wp-element-button" style="border-radius:15px"
               href="<?php echo $url ?>" target="_blank" rel="noopener noreferrer" title="<?php echo $title; ?>">Click
                to access the
                publication</a>
        </div>
	<?php } ?>
</div>