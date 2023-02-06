<?php
if ( ! isset( $publication ) ) {
	return;
}
?>

<!-- On crée une sorte de carte pour les publications scientifiques -->
<div class="scholar-scraper-publication-card">

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
			<?php echo $publication->author; ?>
        </p>

	<?php }

	if ( isset( $publication->pub_year ) ) { ?>
        <!-- On affiche la date de publication -->
        <p class="scholar-scraper-publication-card-date">
			<?php echo $publication->pub_year; ?>
        </p>

		<?php
	}

	if ( isset( $publication->pub_url ) ) { ?>
        <!-- On affiche le lien vers la publication -->
        <p class="scholar-scraper-publication-card-link">
            <a href="<?php echo $publication->pub_url; ?>" target="_blank">
                Click to access the publication
            </a>
        </p>
	<?php }

	if ( isset( $publication->abstract ) ) { ?>
        <!-- On affiche le type de publication -->
        <p class="scholar-scraper-publication-card-type">
			<?php echo $publication->abstract; ?>
        </p>
	<?php } ?>
</div>