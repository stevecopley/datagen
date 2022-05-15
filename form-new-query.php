<?php

    require_once 'common-top.php';

    $tables     = getRecords( 'SELECT * FROM tables     ORDER BY name' );
    $filters    = getRecords( 'SELECT * FROM filters    ORDER BY code' );
    $generators = getRecords( 'SELECT * FROM generators ORDER BY name' );
    $modifiers  = getRecords( 'SELECT * FROM modifiers  ORDER BY name' );

?>

    <h2>Create a New Query</h2>

    <div class="card-list">

        <div class="card wide">

            <header>
            <h2>Select Tables / Generators</h2>
            </header>

            <section>
                <h3>Query Details</h2>

                <form id="query">

                    <div id="source" class="group">

                        <div id="tables" class="group">

                            <input type="radio" name="sourcepick" checked>

                            <label>Table</label>
                            <select onchange="getFilters( this );">
<?php
    foreach( $tables as $table ) {
        echo '<option value="'.$table['code'].'">'.$table['name'].'</option>';
    }
?>
                            </select>

                            <label>Filter</label>
                            <select>
<?php
    foreach( $filters as $filter ) {
        echo '<option value="'.$filter['code'].'">'.$filter['name'].'</option>';
    }
?>
                            </select>

                        </div>
                    
                        <p>OR</p>

                        <div id="generators" class="group">

                            <input type="radio" name="sourcepick">

                            <label>Generator</label>
                            <select>
<?php
    foreach( $generators as $generator ) {
        echo '<option value="'.$generator['code'].'">'.$generator['name'].'</option>';
    }
?>
                            </select>

                        </div>

                        <input type="submit" value="Add">

                    </div>

                    </form>
                
                </section>

            <section>
                <h3>Sample Data Generated</h2>
            </section>

        </div>
    
    </div>
    
<?php

    require_once 'common-bottom.php';

?>

