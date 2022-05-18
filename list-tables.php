<?php

    require_once 'common-top.php';

    echo '<h2>Data Tables</h2>';

    echo '<div class="card-list">';

    // Let's get all of the tables...
    $sql = 'SELECT code, name, category, table_name, description
            FROM tables
            ORDER BY category ASC, name ASC';

    $tables = getRecords( $sql );

    foreach( $tables as $table ) {
        echo '<div class="card">';

        echo   '<header>';
        echo     '<h2>'.$table['name'].' <em>('.$table['category'].')</em></h2>';
        echo     '<p>'.$table['description'].'</p>';
        echo   '</header>';

        echo   '<section>';
        echo     '<h3>Filters</h3>';
        echo     '<ul class="filters">';
        
        $sql = 'SELECT name
                FROM filters
                WHERE table_code = ?
                ORDER BY name ASC';

        $filters = getRecords( $sql, 's', [$table['code']] );

        if( count( $filters ) <= 1 ) {
            echo '<li>None</li>';
        }
        else {
            foreach( $filters as $filter ) {
                if( $filter['name'] != 'All' ) echo '<li><em>'.$filter['name'].'</em></li>';
            }
        }

        echo     '</ul>';
        echo   '</section>';

        echo   '<section>';
        echo     '<h3>Sample</h3>';
        echo     '<div class="results">';
        echo       '<table class="sample">';
        echo         '<thead>';
        echo           '<tr>';
        echo             '<th>#</th>';

        $sql = 'SHOW COLUMNS FROM '.$table['table_name'];
        $columns = getRecords( $sql );

        foreach( $columns as $column ) {
            echo '<th>'.$column['Field'].'</th>';
        } 

        echo           '</tr>';
        echo         '</thead>';
        echo         '<tbody>';

        $sql  = 'SELECT * FROM ';
        $sql .= $table['table_name'];
        $sql .= ' ORDER BY RAND() LIMIT 5';

        $samples = getRecords( $sql );

        $count = 0;
        foreach( $samples as $sample ) {
            echo '<tr>';
            $count++;
            echo   '<th>'.$count.'</th>';
            foreach( $sample as $key => $value ) {
                echo'<td>'.$value.'</td>';
            }
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

