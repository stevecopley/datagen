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
        $data = null;
        $rates = null;

        // Generate appropriate example data
        switch( $gen['code'] ) {
            case 'AUTO':
                $min = 1000;
                $n = 2;
                break;

            case 'CONS':
                $data = 'Pow!';
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
                $data = 'Left | Right';
                $rates = '50 | 50';
                break;

            case 'LIST':
                $data = '#important | #today | #call';
                $rates = '30 | 40 | 30';
                $min = 0;
                $max = 3;
                break;

            case 'EMAIL':
                $data = '(No names given)';
                break;

            case 'USER':
                $data = '(No names given)';
                break;

            case 'PASS':
                $n = 8;
                break;

            case 'TEL':
                break;

            case 'DATE':
                $min = 2000;
                $max = 2021;
                $data = 'd/m/Y';
                break;

            case 'TIME':
                $min = 8;
                $max = 17;
                $data = 'g:i a';

            case 'IPV4':
                break;

            case 'CODE':
                $data = 'AA000 >>> [aaa]';
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

        if( $n     !== null ) echo '<li>N: <em>'.$n.'</em>';
        if( $min   !== null ) echo '<li>MIN: <em>'.$min.'</em>';
        if( $max   !== null ) echo '<li>MAX: <em>'.$max.'</em>';
        if( $data  !== null ) echo '<li>DATA: <em>'.$data.'</em>';
        if( $rates !== null ) echo '<li>RATES: <em>'.$rates.'</em>';
        
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

        if( $gen['code'] == 'AUTO' ) resetCounter( $min, $n );

        for( $i = 1; $i <= 5; $i++ ) {
            $dataValue = generate( $gen['code'], $n, $min, $max, $data, $rates );

            // Some fields need post-processing
            if( $gen['code'] == 'EMAIL' ) {
                $dataValue = generateEmail();
            }
            if( $gen['code'] == 'USER' ) {
                $dataValue = generateUser();
            }

            echo '<tr>';
            echo   '<th>'.$i.'</th>';
            echo   '<td>'.$dataValue.'</td>';
            echo '</tr>';
        } 

        echo         '</tbody>';
        echo       '</table>';
        echo     '</div>';
        echo   '</section>';
        echo '</div>';
    }

    echo  '</div>';

    require_once 'common-bottom.php';

?>

