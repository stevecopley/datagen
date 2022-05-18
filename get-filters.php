<?php
    include_once 'common-functions.php';

    // Table ID given?
    if( !isset( $_GET['table']) || empty( $_GET['table'] ) ) echo json_encode( [] );

    // Get the supplied ID from the URL
    $tableCode = $_GET['table'];
    // Get the records...
    $sql = 'SELECT code, name FROM filters WHERE table_code=?';
    // We should get an array the records
    $tables = getRecords( $sql, 's', [$tableCode] );
    
    // Pass back a JSON encoded string of data
    echo json_encode( $tables );
?>