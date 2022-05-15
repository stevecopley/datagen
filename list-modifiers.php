<?php

    require_once 'common-top.php';

    echo '<h2>Data Modifiers</h2>';

    echo '<div class="card-list">';

    // Let's get all of the tables...
    $sql = 'SELECT code, name, description, type
            FROM modifiers
            ORDER BY name ASC';

    $convs = getRecords( $sql );

    foreach( $convs as $conv ) {
        echo '<div class="card fixed narrow">';

        echo   '<header>';
        echo     '<h2>'.$conv['name'].'</h2>';
        echo     '<p>Applies to '.$conv['type'].' data</p>';
        echo   '</header>';

        echo   '<section>';
        $conv['description'] = str_replace( '{', '<em>',  $conv['description'] );
        $conv['description'] = str_replace( '}', '</em>', $conv['description'] );
        echo     '<p>'.$conv['description'].'</p>';
        echo   '</section>';

        echo '</div>';
    }

    echo  '</div>';

    require_once 'common-bottom.php';

?>

