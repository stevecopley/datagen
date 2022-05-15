<?php

    require_once 'common-top.php';

    echo '<h2>Data Generators</h2>';

    echo '<div class="card-list">';

    // Let's get all of the tables...
    $sql = 'SELECT code, name, description, type
            FROM generators
            ORDER BY name ASC';

    $gens = getRecords( $sql );

    foreach( $gens as $gen ) {
        $n = null;
        $min = null;
        $max = null;
        $format = null;
        $info = null;

        // Generate appropriate example data
        switch( $gen['code'] ) {
            case 'AUTO':
                $min = 1000;
                $n = 2;
                break;

            case 'CONS':
                $format = 'Pow!';
                break;

            case 'INT':
                $min = 0;
                $max = 100;
                break;

            case 'INT-BIN':
                $min = 0;
                $max = 255;
                $n = 12;
                break;

            case 'INT-HEX':
                $min = 0;
                $max = 65535;
                $n = 6;
                break;

            case 'FLOAT':
                $min = -1000;
                $max = 1000;
                $n = 2;
                break;

            case 'NORM':
                $min = 0;
                $max = 100;
                break;

            case 'ASCII':
                $min = 33;
                $max = 47;
                $n = 10;
                break;

            case 'BOOL':
                $format = 'Left | Right';
                $info = '50 | 50';
                break;

            case 'LIST':
                $format = '#important | #today | #call';
                $info = '30 | 40 | 30';
                $min = 0;
                $max = 3;
                break;

            case 'EMAIL':
                $format = '{FORENAME}@{DOMAIN}';
                $info = ',tigerbalm.com';
                break;

            case 'USER':
                $format = null;
                $info = 'Bob,Smith';
                break;

            case 'PASS':
                $min = 8;
                $max = 12;
                break;

            case 'TEL':
                break;

            case 'DATE':
                $min = 2000;
                $max = 2021;
                $format = 'd/m/Y';
                break;

            case 'TIME':
                $min = 8;
                $max = 17;
                $format = 'g:i a';

            case 'IPV4':
                break;

            case 'CODE':
                $format = 'AA000 >>> [aaa]';
                break;

            case 'TEXT':
                $min = 1;
                $max = 1;
                break;

            case 'WORD':
                $min = 2;
                $max = 4;
                break;
        }

        echo '<div class="card fixed">';

        echo   '<header>';
        echo     '<h2>'.$gen['name'].'</h2>';
        echo     '<p>'.$gen['type'].' data</p>';
        echo   '</header>';

        echo   '<section>';
        $gen['description'] = str_replace( '{', '<em>', $gen['description'] );
        $gen['description'] = str_replace( '}', '</em>', $gen['description'] );
        echo     '<p>'.$gen['description'].'</p>';
        echo   '</section>';

        echo   '<section>';
        echo     '<h3>Example</h3>';
        echo     '<ul>';

        if( $n      !== null ) echo '<li>N: <em>'.$n.'</em>';
        if( $min    !== null ) echo '<li>MIN: <em>'.$min.'</em>';
        if( $max    !== null ) echo '<li>MAX: <em>'.$max.'</em>';
        if( $format !== null ) echo '<li>FORMAT: <em>'.$format.'</em>';
        if( $info   !== null ) echo '<li>INFO: <em>'.$info.'</em>';
        
        echo     '</ul>';
        echo   '</section>';

        echo   '<section>';
        echo     '<h3>Sample</h3>';
        echo     '<div class="results">';
        echo       '<table class="sample">';
        echo         '<thead>';
        echo           '<tr>';
        echo             '<th>#</th>';
        echo             '<th>Value</th>';
        echo           '</tr>';
        echo         '</thead>';
        echo         '<tbody>';

        // Reset counter if needed
        if( $gen['code'] == 'AUTO' ) resetCounter( $min, $n );
        // Save name fields for email / username
        if( $gen['code'] == 'USER' || $gen['code'] == 'EMAIL' ) mapFields( $format, $info );

        for( $i = 1; $i <= 5; $i++ ) {
            $formatValue = generate( $gen['code'], $n, $min, $max, $format, $info );
            processField( $formatValue );

            echo '<tr>';
            echo   '<th>'.$i.'</th>';
            echo   '<td>'.$formatValue.'</td>';
            echo '</tr>';
        } 

        echo         '</tbody>';
        echo       '</table>';
        echo     '</div>';
        echo   '</section>';
        echo '</div>';

        // if( $gen['code'] == 'EMAIL' ) break;
    }

    echo  '</div>';

    require_once 'common-bottom.php';

?>

