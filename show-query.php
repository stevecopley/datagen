<?php

    require_once 'common-top.php';

    $qid = 1;


    echo '<h2>Query</h2>';

    echo '<div class="card-list">';

    echo '<div class="card wide">';

    echo   '<header>';

    $sql = 'SELECT name, quantity
            FROM queries
            WHERE queries.id = ?';

    $queries = getRecords( $sql, 'i', [$qid] );
    $query = $queries[0];

    echo     '<h2>'.$query['name'].'</h2>';
    echo     '<p>'.$query['quantity'].' records</p>';

    echo   '</header>';
    
    // A mighty query that grabs everything we need regarding the query criteria
    $sql = 'SELECT criteria.headings,
                   
                   criteria.filter_code,
                   criteria.fields,

                   filters.name           AS fname,
                   filters.where_clause,

                   tables.name            AS tname,
                   tables.table_name,

                   criteria.gen_code,
                   criteria.gen_min,
                   criteria.gen_max,
                   criteria.gen_n,
                   criteria.gen_data,
                   criteria.gen_rates,

                   generators.name        AS gname,
                   generators.type,
                   generators.description AS gdesc,
                   generators.post_process,
                   
                   criteria.conv_code,
                   criteria.conv_n,
                   criteria.conv_data,

                   converters.name        AS cname,
                   converters.description AS cdesc
                   

            FROM queries

            JOIN criteria        ON queries.id           = criteria.query_id
            
            LEFT JOIN filters    ON criteria.filter_code = filters.code
            LEFT JOIN tables     ON filters.table_code   = tables.code
            
            LEFT JOIN generators ON criteria.gen_code    = generators.code
            
            LEFT JOIN converters ON criteria.conv_code   = converters.code
            
            WHERE queries.id = ?
            
            ORDER BY criteria.sort_order ASC';

    // Let's get those criteria
    $criteria = getRecords( $sql, 'i', [$qid] );

    // Get actual columns for '*' selector or no fields given
    for( $i = 0; $i < count( $criteria ); $i++ ) {
        if( $criteria[$i]['filter_code'] && (!$criteria[$i]['fields'] || $criteria[$i]['fields'] == '*') ) {
            $criteria[$i]['fields'] = '';
            // Get the actual column names
            $sql = 'SHOW COLUMNS FROM '.$criteria[$i]['table_name'];
            $columns = getRecords( $sql );
            // And replace the '*' with them in the query
            for( $c = 0; $c < count( $columns ); $c++ ) {
                if( $c > 0 ) $criteria[$i]['fields'] .= ', ';
                $criteria[$i]['fields'] .= $columns[$c]['Field'];
            }
        }
    }

    echo   '<section>';
    echo     '<h3>Data Sources</h3>';

    echo     '<ul>';

    // Show the query criteria details

    $forenameColumn = null;
    $surnameColumn = null;
    
    foreach( $criteria as $criterion ) {
        
        // Is this a table filter?
        if( $criterion['filter_code'] ) {
            // Show the table involved
            echo '<li>From table: <strong>'.$criterion['tname'].'</strong> ';
            // If a filetr is in use, show that too
            if( $criterion['fname'] != 'All' ) echo '(<strong>'.$criterion['fname'].'</strong>)';

            // Are we using a converter
            if( $criterion['conv_code'] ) {
                $desc = $criterion['cdesc'];
                if( $criterion['conv_n'] ) $desc = str_replace( '{N}', '<strong>'.$criterion['conv_n'].'</strong>', $desc );
                echo ' &xrarr; <strong>'.$desc.'</strong> ';
            }

            // What fields are we using?
            $fields   = array_map( 'trim', explode( ',', $criterion['fields'] ) );
            $headings = array_map( 'trim', explode( ',', $criterion['headings'] ) );

            echo ' &xrarr; fields: ';

            // Show the field list, along with aliases if provided
            for( $i = 0; $i < count( $fields ); $i++ ) {
                if( $i > 0 ) echo ', ';
                echo '<em>'.$fields[$i].'</em>';
                if( $criterion['headings'] && count( $headings ) == count( $fields ) ) echo ' as <em>\''.$headings[$i].'\'</em>';
            }
        }

        // Or a generator?
        else if( $criterion['gen_code'] ) {
            echo '<li>Generator: <strong>'.$criterion['gname'].'</strong> ';

            if( $criterion['gdesc'] ) {
                $desc = $criterion['gdesc'];
                if( $criterion['gen_min']   !== null ) $desc = str_replace( '{MIN}',    '<strong>'.$criterion['gen_min']   .'</strong>', $desc );
                if( $criterion['gen_max']   !== null ) $desc = str_replace( '{MAX}',    '<strong>'.$criterion['gen_max']   .'</strong>', $desc );
                if( $criterion['gen_n']     !== null ) $desc = str_replace( '{N}',      '<strong>'.$criterion['gen_n']     .'</strong>', $desc );
                if( $criterion['gen_data']  !== null ) $desc = str_replace( '{DATA}',   '<strong>'.$criterion['gen_data']  .'</strong>', $desc );
                if( $criterion['gen_rates'] !== null ) $desc = str_replace( '{RATES}',  '<strong>'.$criterion['gen_rates'] .'</strong>', $desc );

                echo '('.$desc.')';
            }

            // Are we using a converter
            if( $criterion['conv_code'] ) {
                $desc = $criterion['cdesc'];
                if( $criterion['conv_n']    !== null ) $desc = str_replace( '{N}',    '<strong>'.$criterion['conv_n']   .'</strong>', $desc );
                if( $criterion['conv_data'] !== null ) $desc = str_replace( '{DATA}', '<strong>'.$criterion['conv_data'].'</strong>', $desc );
                echo ' &xrarr; <strong>'.$desc.'</strong> ';
            }

            echo ' &xrarr; value as <em>\''.$criterion['headings'].'\'</em>';
        }
    }

    echo     '</ul>';
    echo   '</section>';
    

    // Generate the data!

    $data = [];
    $keys = [];

    foreach( $criteria as $criterion ) {

        // Is this a table filter?
        if( $criterion['filter_code'] ) {
            // Build the query
            $sql  = 'SELECT '.$criterion['fields'];
            $sql .= ' FROM '.$criterion['table_name'];
            if( $criterion['where_clause'] ) $sql .= ' WHERE '.$criterion['where_clause'];
            $sql .= ' ORDER BY RAND() LIMIT '.$query['quantity'];

            // Possible that table doesn't have enough rows to fulfill quantity...
            $results = [];
            while( count( $results ) < $query['quantity'] ) {
                // ... so run query until we have enough ...
                $newRecords = getRecords( $sql );
                $results = [...$results, ...$newRecords];
            }
            // ... then trim back to size
            $results = array_slice( $results, 0, $query['quantity'] );

            // get the column headings from the results
            $newKeys = array_keys( $results[0] );
            // And replace with headings if needed
            $headings = array_map( 'trim', explode( ',', $criterion['headings'] ) );
            if( $criterion['headings'] && count( $headings ) == count( $newKeys ) ) $newKeys = $headings;
            
            // Add to the key collection
            $keys = [...$keys, ...$newKeys];

            // Collate the data
            for( $i = 0; $i < $query['quantity']; $i++ ) { 
                // Starting a new row? If so, create a new array
                if( !isset( $data[$i] ) ) $data[$i] = [];

                // Append data to the row
                foreach( $results[$i] as $dataValue ) {
                    // Are we using a converter?
                    if( $criterion['conv_code'] ) {
                        $dataValue = convert( $dataValue, 
                                              $criterion['conv_code'], 
                                              $criterion['conv_n'],
                                              $criterion['conv_data'] );
                    }
                    $data[$i][] = $dataValue;
                }
            }
        }

        // Or a generator?
        else if( $criterion['gen_code'] ) {
            // Reset counter if needed
            if( $criterion['gen_code'] == 'AUTO' ) resetCounter( $criterion['gen_min'], $criterion['gen_n'] );

            // Add to the key collection
            $keys = [...$keys, $criterion['headings']];

            // Collate the data
            for( $i = 0; $i < $query['quantity']; $i++ ) { 
                // Starting a new row? If so, create a new array
                if( !isset( $data[$i] ) ) $data[$i] = [];

                // Generate appropriate data
                $dataValue = generate( $criterion['gen_code'],
                                       $criterion['gen_n'], 
                                       $criterion['gen_min'], $criterion['gen_max'],
                                       $criterion['gen_data'], $criterion['gen_rates'] );

                // Are we using a converter?
                if( $criterion['conv_code'] ) {
                    $dataValue = convert( $dataValue, 
                                          $criterion['conv_code'], 
                                          $criterion['conv_n'],
                                          $criterion['conv_data'] );
                }

                // Append data to the row
                $data[$i][] = $dataValue;
            }
        }
    }

    for( $i = 0; $i < count( $data ); $i++ ) {
        $found;
    }

    // TODO: Implement post-gen email and username processing


    echo   '<section>';
    echo     '<h3>Results (Table)</h3>';

    echo     '<div class="results">';
    echo       '<table>';
    echo         '<thead>';
    echo           '<tr>';
    echo             '<th>#</th>';
    foreach( $keys as $key ) {
        echo '<th>'.$key.'</th>';
    }
    echo           '</tr>';
    echo         '</thead>';

    echo         '<tbody>';
    $count = 0;
    foreach( $data as $row ) {
        echo       '<tr>';
        $count++;
        echo         '<th>'.$count.'</th>';
        foreach( $row as $value ) {
            echo   '<td>'.$value.'</td>';
        }
        echo       '</tr>';
    }
    echo         '</tbody>';
    echo       '</table>';
    echo     '</div>';
    echo   '</section>';


    echo   '<section>';
    echo     '<h3>Results (CSV)</h3>';

    echo     '<div class="results">';
    echo       '<pre><code class="language-csv">';

    for( $i = 0; $i < count( $keys ); $i++ ) {
        if( $i > 0 ) echo '<span class="comma">,</span>';
        echo '<span class="heading col'.($i+1).'">'.$keys[$i].'</span>';
    }
    echo PHP_EOL;

    foreach( $data as $row ) {
        for( $i = 0; $i < count( $row ); $i++ ) {
            if( $i > 0 ) echo '<span class="comma">,</span>';
            echo '<span class="value col'.($i+1).'">';
            // Check if data contains a comman, and wrap it with speech marks if so
            if( strpos( $row[$i], ',' ) ) echo '"';
            echo $row[$i];
            if( strpos( $row[$i], ',' ) ) echo '"';
            echo '</span>';
        }
        echo PHP_EOL;
    }

    echo       '</code></pre>';
    echo     '</div>';
    echo   '</section>';

    echo   '<section>';
    echo     '<p><a href="download-csv.php">Download CSV File</a>';

    $tempFilePath = tempnam( sys_get_temp_dir(), 'datagen' );
    $_SESSION['tempDataFilePath'] = $tempFilePath;

    $tempFile = fopen( $tempFilePath, 'w' );
    fputcsv( $tempFile, $keys, ',' );
    foreach( $data as $row ) {
        fputcsv( $tempFile, $row, ',' );
    }

    echo   '</section>';

    echo '</div>';

    require_once 'common-bottom.php';

?>

