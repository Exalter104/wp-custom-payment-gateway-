<?php

// Step 1: Add shortcode for CSV data uploader
add_shortcode("csv_data_uploader", "csv_data_uploader_function");

function csv_data_uploader_function() {
    ob_start();
    ?>
<div class="csv-uploader-wrapper">
    <form class="csv-uploader-form" action="" method="post">
        <h2>Upload Your CSV File</h2>
        <input type="file" name="csv_file" id="csv_file" required>
        <input type="submit" name="submit" value="Upload CSV">
    </form>
</div>
<?php
    return ob_get_clean();
}

?>