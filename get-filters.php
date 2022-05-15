<?php
    include_once 'common-functions.php';

    // Get the supplied ID from the URL
    $tableCode = $_GET['code'];

    // Get the records...
    $sql = 'SELECT code, name
            FROM filters
            WHERE table_code=?';

    // We should get an array the records
    $tables = getRecords( $sql, 's', [$tableCode] );

    // Pass back a JSON encoded string of data
    echo json_encode( $tables );
?>