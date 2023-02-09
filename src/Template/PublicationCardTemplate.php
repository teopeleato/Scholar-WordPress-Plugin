<?php
if ( ! isset( $publication ) ) {
	return;
}
?>

<!-- On crée une sorte de carte pour les publications scientifiques -->
<div class="scholar-scraper-publication-card">

    <div class="scholar-scraper-publication-card-top">

		<?php
		if ( isset( $publication->title ) ) { ?>
            <!-- On affiche le titre de la publication -->
            <h3 class="scholar-scraper-publication-card-title">
				<?php echo $publication->title; ?>
            </h3>

		<?php }

		if ( isset( $publication->author ) ) { ?>
            <!-- On affiche l'auteur de la publication -->
            <p class="scholar-scraper-publication-card-author">
                <strong><u>Authors :</u></strong> <?php echo $publication->author; ?>
            </p>

		<?php }

		if ( isset( $publication->pub_year ) ||
		     isset( $publication->pages ) ||
		     isset( $publication->venue ) ||
		     isset( $publication->journal ) ||
		     isset( $publication->volume ) ||
		     isset( $publication->number ) ||
		     isset( $publication->publisher )
		) { ?>
            <!-- On affiche la date de publication -->
            <p class="scholar-scraper-publication-card-date">
				<?php
				$listToShow = [];

				if ( isset( $publication->pub_year ) ) {
					$listToShow[] = $publication->pub_year . " ";
				}

				if ( isset( $publication->venue ) ) {
					$listToShow[] = $publication->venue . "venue ";
				}

				if ( isset( $publication->journal ) ) {
					$tmpString = $publication->journal;

					if ( isset( $publication->volume ) ) {
						$tmpString .= ", vol. " . $publication->volume;

						if ( isset( $publication->pages ) ) {
							$tmpString .= ", p. " . $publication->pages;
						}

					} else if ( isset( $publication->pages ) ) {
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

		if ( isset( $publication->abstract ) ) { ?>
            <!-- On affiche l'abstract de publication -->
            <div class="scholar-scraper-publication-card-abstract">
                <strong><u>Abstract :</u></strong>
                <p class="scholar-scraper-publication-card-abstract-content">
					<?php echo $publication->abstract; ?>
                </p>
            </div>

		<?php } ?>

    </div>

	<?php
	if ( isset( $publication->pub_url ) ) { ?>
        <!-- On affiche le lien vers la publication -->
        <div class="wp-block-button scholar-scraper-publication-card-link">
            <a class="wp-block-button__link wp-element-button" style="border-radius:15px"
               href="<?php echo $publication->pub_url; ?>" target="_blank" rel="noopener noreferrer">Click to access the
                publication</a>
        </div>
	<?php } ?>
</div>