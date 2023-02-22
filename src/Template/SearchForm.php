<?php
if ( ! isset( $data ) ) {
    $data = [];
}
$dataString = "";
foreach ( $data as $key => $value ) {
    $value      = esc_attr( $value );
    $dataString .= "data-$key=\"$value\" ";
}
?>
<form class="box" id="scholar-scraper-search-form" <?php echo $dataString; ?>>
    <div class="icon-container">
        <span class="dashicons dashicons-search"></span>
    </div>
    <input type="search" id="search" placeholder="Search..."/>
</form>