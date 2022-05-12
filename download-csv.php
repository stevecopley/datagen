<?php

    require_once 'common-session.php';

    $dataFile = fopen( $_SESSION['tempDataFilePath'], 'r' );

    header( 'Content-Type: text/csv; charset=utf-8' );
    header( 'Content-Disposition: attachment; filename=datagen.csv' );

    fpassthru( $dataFile );

    fclose( $dataFile );
    
    unset( $_SESSION['tempDataFilePath'] );

?>
