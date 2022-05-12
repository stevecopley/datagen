<?php
    require_once 'common-session.php';
    require_once 'common-functions.php';
    require_once 'data-functions.php';
?>
    

<!doctype html>

<html>
    
<head>
    <title>Data Gen</title>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="icon" href="favicon.svg" type="image/svg+xml">

    <link rel="stylesheet" href="css/reset.css">
    <link rel="stylesheet" href="css/styles.css">
</head>

<body>
    <header id="main-header">

        <h1>Data Gen</h1>

        <nav id="main-nav">
            
            <label for="toggle">
                <img src="images/menu.svg">
            </label>

            <input id="toggle" type="checkbox">

            <ul>
                <label for="toggle">
                    <img src="images/close.svg">
                </label>

                <li><a href="list-tables.php">Tables</a></li>
                <li><a href="list-generators.php">Generators</a></li>
                <li><a href="list-converters.php">Converters</a></li>
                <li><a href="form-new-query.php">Create Query</a></li>
                <li><a href="show-query.php">Show Query</a></li>
            </ul>
        </nav>

    </header>

    <main>

