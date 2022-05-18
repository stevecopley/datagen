<?php

    require_once 'common-top.php';

    $tables     = getRecords( 'SELECT * FROM tables     ORDER BY category, name' );
    // $filters    = getRecords( 'SELECT * FROM filters    ORDER BY code' );
    $generators = getRecords( 'SELECT * FROM generators ORDER BY category, name' );
    $modifiers  = getRecords( 'SELECT * FROM modifiers  ORDER BY name' );

?>

    <h2>Create a New Query</h2>

    <div class="card-list">

        <div class="card wide">

            <header>
            <h2>Select Tables / Generators</h2>
            </header>

            <section>
                <h3>Data Sources</h2>

                <form id="query">

                    <div id="source" class="group">

                        <fieldset id="tables">

                            <legend>Tables</legend>

                            <input type="radio" name="sourcepick" value="table" checked>

                            <label>
                                Table
                                <select id="table-list" onchange="updateFilters( this );">
                                    <option value="null">Pick one...</option>
<?php
    foreach( $tables as $table ) {
        echo '<option value="'.$table['code'].'">'.$table['name'].'</option>';
    }
?>
                                </select>
                            </label>

                            <label>
                                Filter
                                <select id="filter-list" name="filter">
                                    <option value="null"></option>
                                </select>
                            </label>

                        </fieldset>
                    
                        <p>OR</p>

                        <fieldset id="generators">

                            <legend>Generators</legend>
    
                            <input type="radio" name="sourcepick" value="generator">

                            <label>
                                Generator
                                <select id="gen_list" name="gen">
                                    <option value="null">Pick one...</option>
<?php
    foreach( $generators as $generator ) {
        echo '<option value="'.$generator['code'].'">'.$generator['name'].'</option>';
    }
?>
                                </select>
                            </label>

                            <label>N      <input id="gen-n"      name="genn"    type="number"></label>
                            <label>MIN    <input id="gen-min"    name="genmin"  type="number"></label>
                            <label>MAX    <input id="gen-max"    name="genmax"  type="number"></label>
                            <label>FORMAT <input id="gen-format" name="genform" type="text"></label>
                            <label>INFO   <input id="gen-info"   name="geninfo" type="text"></label>
                            
                        </fieldset>

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

