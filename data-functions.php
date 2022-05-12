<?php

    require_once 'common-functions.php';

    $counter = 0;
    $counterStep = 1;


    /*------------------------------------------------------------------
     * Convert given data using one of the defined conversion functions
     *------------------------------------------------------------------*/
    function convert( $originalData, $converterCode, $n=null, $data=null ) {

        switch( $converterCode ) {
            case 'MULT':
                $convertedData = $originalData * ($n ? $n : 1);
                break;

            case 'LOWER':
                $convertedData = strtolower( $originalData );
                break;

            case 'UPPER':
                $convertedData = strtoupper( $originalData );
                break;

            case 'LEFT':
                $convertedData = $n ? substr( $originalData, 0, $n ) : $originalData;
                break;

            case 'RIGHT':
                $convertedData = $n ? substr( $originalData, -1 * $n ) : $originalData;
                break;

            case 'PRE':
                $convertedData = $data.$originalData;
                break;

            case 'APP':
                $convertedData = $originalData.$data;
                break;
    
            default:
                $convertedData = $originalData;
        }

        return $convertedData;
    }


    /*------------------------------------------------------------------
     * Reset counter for AUTO INC value
     *------------------------------------------------------------------*/
    function resetCounter( $start=1, $step=1 ) {
        global $counter;
        global $counterStep;
        $counter = $start;
        $counterStep = $step;
    } 


    function pickWithOdds( $options, $odds ) {
        // Check we have the correct number of odds
        if( count( $odds ) != count( $options ) ) {
            // If not, just create even odds for all options
            $evenOdds = floor( 100 / count( $options ) );
            $odds = array_fill( 0, count( $options ), $evenOdds );
        }

        // Roll the dice
        $diceRoll = rand( 1, 100 );
        $oddsTotal = 0;
        $option = '';

        // Check where the dice fell within the odds
        for( $r = 0; $r < count( $odds ); $r++ ) {
            $oddsTotal += $odds[$r];
            // Have we hit the correct odds?
            if( $diceRoll <= $oddsTotal ) {
                $option = $options[$r];
                break;
            }
        }

        return $option;
    }


    /*------------------------------------------------------------------
     * Generate a data values based on given criteria / data
     *------------------------------------------------------------------*/
    function generate( $generatorCode, 
                       $n=null, $min=null, $max=null, 
                       $data=null, $rates=null ) {

        global $counter;
        global $counterStep;
        global $passChars;
        global $lorem;

        switch( $generatorCode ) {
            case 'AUTO':
                $genData = $counter;
                $counter += $counterStep;
                break;

            case 'CONS':
                $genData = $data ? $data : 1;
                break;

            case 'INT':
                $min = $min === null ?   0 : $min;
                $max = $max === null ? 100 : max( $min, $max );
                $genData = rand( $min, $max );
                break;

            case 'INT-BIN':
                $min = $min === null ?   0 : max( 0, $min );
                $max = $max === null ? 255 : max( $min, $max );
                $digits = $n ? $n : 8;
                $maxDigits = strlen( decbin( $max ) );
                $digits = max( $digits, $maxDigits );
                $genData = rand( $min, $max );
                $genData = decbin( $genData );
                $genData = str_pad( $genData, $digits, "0", STR_PAD_LEFT );
                // $genData = '0b'.$genData;
                break;

            case 'INT-HEX':
                $min = $min === null ?   0 : max( 0, $min );
                $max = $max === null ? 255 : max( $min, $max );
                $digits = $n ? $n : 2;
                $maxDigits = strlen( dechex( $max ) );
                $digits = max( $digits, $maxDigits );
                $genData = rand( $min, $max );
                $genData = dechex( $genData );
                $genData = str_pad( $genData, $digits, "0", STR_PAD_LEFT );
                $genData = strtoupper( $genData );
                // $genData = '0x'.$hexValue;
                break;

            case 'FLOAT':
                $min = $min === null ?   0 : $min;
                $max = $max === null ? 100 : max( $min, $max );
                $n = $n ? $n : 1;
                $scaling = pow( 10, $n );
                $genData = rand( $min * $scaling, $max * $scaling ) / $scaling;
                $genData = number_format( $genData, $n, '.', '' );
                break;

            case 'NORM':
                // https://natedenlinger.com/php-random-number-generator-with-normal-distribution-bell-curve/
                $sd = ($max - $min) / 5;
                $mean = (float)($min + $max) / 2;
                do {
                    $rand1 = (float)rand() / (float)getrandmax();
                    $rand2 = (float)rand() / (float)getrandmax();
                    $gaussianNum = sqrt( -2 * log( $rand1 ) ) * cos( 2 * M_PI * $rand2 );
                    $rand = round( ($gaussianNum * $sd) + $mean );
                } while( $rand < $min || $rand > $max );                
                $genData = $rand;
                break;

            case 'ASCII':
                // Only displayable characters
                $min = $min === null ?  33 : max( 33, $min );
                $max = $max === null ? 126 : min( 126, $max );
                $genData = '';
                $num = $n ? $n : 1;
                for( $i = 0; $i < $num; $i++ ) {
                    $genData .= chr( rand( $min, $max ) );
                }
                break;

            case 'BOOL':
            case 'LIST':
                $options = array_map( 'trim', explode( '|', ($data ? $data : 'No|Yes') ) );
                $odds    = array_map( 'trim', explode( '|', ($rates ? $rates : '50|50') ) );
                $min = $min === null ? 1 : $min;
                $max = $max === null ? 1 : min( $max, count( $options ) );
                $num = rand( $min, $max );
                // Create the list of items
                $genData = '';
                $count = 0;
                while( $count < $num ) {
                    $option = pickWithOdds( $options, $odds );
                    // Have this option already? If so, try again
                    if( strpos( $genData, $option ) !== false ) continue;
                    // Nope, so add to the list
                    if( $count > 0 ) $genData .= ', ';
                    $genData .= $option;
                    $count++;
                }
                break;

            case 'EMAIL':
                $genData = '{EMAIL}';
                break;

            case 'TEL':
                $genData = '02';
                $len = rand( 8, 9 );
                for( $i = 0; $i < $len; $i++ ) {
                    $genData .= rand( 0, 9 );
                    // Add spaces to format
                    if( $i == 0 || $i == $len - 5 ) $genData .= ' ';
                }
                break;

            case 'USER':
                $genData = '{USER}';
                break;

            case 'PASS':
                $genData = '';
                $len = ($n ? $n : 8) + rand( 0, 3 );
                $maxIndex = strlen( $passChars ) - 1;
                for( $i = 0; $i < $len; $i++ ) {
                    $pick = rand( 0, $maxIndex );
                    $genData .= $passChars[$pick];
                }
                break;

            case 'DATE':
                $min = strtotime( ($min ? $min : 1900).'-01-01' );
                $max = strtotime( ($max ? $max : 2000).'-12-31' );
                $genData = rand( $min, $max );
                $genData = date( ($data ? $data : 'Y-m-d'), $genData );
                break;

            case 'TIME':
                $min = strtotime( ($min ? $min : 00).':00' );
                $max = strtotime( ($max ? $max : 23).':59' );
                $genData = rand( $min, $max );
                $genData = date( ($data ? $data : 'H:i'), $genData );
                break;
    
            case 'IPV4':
                $genData = rand( 1, 255 ).'.'.rand( 0, 255 ).'.'.rand( 0, 255 ).'.'.rand( 0, 255 );
                break;

            case 'CODE':
                $len = strlen( $data );
                $genData = '';
                for( $i = 0; $i < $len; $i++ ) {
                         if( $data[$i] == 'A' ) $genData .= chr( rand( 65, 90 ) ); 
                    else if( $data[$i] == 'a' ) $genData .= chr( rand( 97, 122 ) ); 
                    else if( $data[$i] == '0' ) $genData .= rand( 0, 9 ); 
                    else                        $genData .= $data[$i];
                }
                break;

            case 'TEXT':
                $min = $min === null ? 1 : max( 0, $min );
                $max = $max === null ? 1 : max( $min, $max );
                $num = rand( $min, $max );
                $genData = '';
                $count = 0;
                while( $count < $num ) {
                    $sentence = $lorem[array_rand( $lorem )];
                    
                    // Have this sentence already? If so, try again
                    if( strpos( $genData, $sentence ) !== false ) continue;

                    if( $count > 0 ) $genData .= ' ';
                    $genData .= $sentence;
                    $count++;
                }
                break;

            case 'WORD':
                $min = $min === null ? 1 : max( 0, $min );
                $max = $max === null ? 1 : max( $min, $max );
                $num = rand( $min, $max );
                $genData = '';
                $count = 0;
                while( $count < $num ) {
                    $sentence = $lorem[array_rand( $lorem )];
                    $sentence = str_replace( '.', '', $sentence );
                    $words = explode( ' ', $sentence );
                    $word = strtolower( $words[array_rand( $words )] );

                    // Don't want tiny words
                    if( strlen( $word ) < 4 ) continue; 
                    // Have this word already? If so, try again
                    if( strpos( $genData, $word ) !== false ) continue;

                    if( $count > 0 ) $genData .= ' ';
                    $genData .= $word;
                    $count++;
                }
                break;

            default:
                $genData = 'ERROR!';
        }

        return $genData;
    }


    /*------------------------------------------------------------------
     * Generate a username using given names, or random otherwise
     *------------------------------------------------------------------*/
    function generateUser( $forename=null, $surname=null ) {
        global $forenames;
        global $surnames;
        global $starters;

        $joiners  = array( '.', '-', '_' );

        if( !$forename ) $forename = strtolower( $forenames[array_rand( $forenames )] );
        if( !$surname )  $surname  = strtolower( $surnames[array_rand( $surnames )] );
        
        $starter = $starters[array_rand( $starters )];
        $joiner  = rand( 1, 10 ) > 7 ? $joiners[array_rand( $joiners )] : '';

        // Which format to use?
        if( rand( 1, 10 ) > 8 ) {
            $username = $starter.$joiner.$forename;  // Just forename with a prefix
        }
        else {
            if( rand( 1, 10 ) > 7 ) $forename = substr( $forename, 0, 1 );  // Single initial
            $username = $forename.$joiner.$surname;  // First & last names
        }

        // Add a number?
        $numRoll = rand( 1, 10 );
        if( $numRoll > 7 ) $username .= $joiner.rand( 1, 10 );
        else if( $numRoll > 3 ) $username .= $joiner.rand( 1960, 2010 );

        return $username;
    }

    /*------------------------------------------------------------------
     * Generate an email using given names, or random otherwise
     *------------------------------------------------------------------*/
    function generateEmail( $forename=null, $surname=null ) {
        global $emailProviders;

        $email = generateUser( $forename, $surname );
        $email .= '@';
        $email .= $emailProviders[array_rand( $emailProviders )];        
        
        return $email;
    }




    // Avoid any awkward charcters
    $passChars = '!@#$%^&*_-+=.?|0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

    $emailProviders = array(
        'gmail.net',
        'outlook.net',
        'mail.org',
        'yahoo.net',
        'icloud.org',
        'hotmail.org',
        'protonmail.net',
        'aol.net',
        'googlemail.org',
        'email.org'
    );

    $starters = array( 
        'tiny', 
        'little',
        'big', 

        'cool', 
        'awesome', 
        'best', 

        'happy', 
        'cheeky', 
        'magic',
        'hacker'
    );


    $forenames = array(
        'Ada',
        'Adam',
        'Aiden',
        'Alexander',
        'Amaia',
        'Amara',
        'Amber',
        'Amelia',
        'Anna',
        'Archer',
        'Archie',
        'Ari',
        'Aria',
        'Arlo',
        'Arthur',
        'Asher',
        'Austin',
        'Ava',
        'Beau',
        'Beauden',
        'Bella',
        'Benjamin',
        'Blake',
        'Bodhi',
        'Bonnie',
        'Braxton',
        'Caleb',
        'Carter',
        'Charles',
        'Charlie',
        'Charlotte',
        'Chloe',
        'Cleo',
        'Connor',
        'Cooper',
        'Daniel',
        'Eden',
        'Edward',
        'Eleanor',
        'Eli',
        'Elijah',
        'Eliza',
        'Elizabeth',
        'Ella',
        'Ellie',
        'Eloise',
        'Emily',
        'Ethan',
        'Evelyn',
        'Ezekiel',
        'Ezra',
        'Felix',
        'Finn',
        'Freddie',
        'Freya',
        'Gabriel',
        'George',
        'Georgia',
        'Grace',
        'Grayson',
        'Hannah',
        'Harley',
        'Harlow',
        'Harper',
        'Harrison',
        'Harry',
        'Harvey',
        'Hazel',
        'Henry',
        'Hudson',
        'Hugo',
        'Hunter',
        'Isaac',
        'Isabella',
        'Isaiah',
        'Isla',
        'Ivy',
        'Jack',
        'Jackson',
        'Jacob',
        'James',
        'Jasmine',
        'Jasper',
        'Jaxon',
        'Jayden',
        'John',
        'Jordan',
        'Joseph',
        'Joshua',
        'Kaia',
        'Kiara',
        'Kora',
        'Lachlan',
        'Leah',
        'Leo',
        'Leon',
        'Levi',
        'Liam',
        'Lilly',
        'Lily',
        'Lincoln',
        'Logan',
        'Louie',
        'Louis',
        'Luca',
        'Lucas',
        'Lucy',
        'Luka',
        'Luke',
        'Luna',
        'Maddison',
        'Maisie',
        'Margot',
        'Marley',
        'Mason',
        'Matilda',
        'Max',
        'Maya',
        'Mia',
        'Micah',
        'Michael',
        'Mila',
        'Miles',
        'Nathan',
        'Nevaeh',
        'Nico',
        'Nikau',
        'Nina',
        'Noah',
        'Oakley',
        'Olive',
        'Oliver',
        'Olivia',
        'Oscar',
        'Otis',
        'Paige',
        'Phoebe',
        'Piper',
        'Pippa',
        'Quinn',
        'Riley',
        'River',
        'Roman',
        'Rosie',
        'Ruby',
        'Ryan',
        'Ryder',
        'Sadie',
        'Samuel',
        'Sebastian',
        'Sophia',
        'Sophie',
        'Spencer',
        'Stella',
        'Summer',
        'Theo',
        'Theodore',
        'Thomas',
        'Tobias',
        'Toby',
        'Tyler',
        'William',
        'Willow',
        'Zachary',
        'Zara',
        'Zion',
        'Zoe',
        'Zoey'
    );

    $surnames = array(
        'Adams',
        'Allan',
        'Allard',
        'Allen',
        'Anderson',
        'Baker',
        'Bennett',
        'Boone',
        'Brown',
        'Burns',
        'Campbell',
        'Chen',
        'Clark',
        'Clarke',
        'Collins',
        'Cook',
        'Cooper',
        'Cox',
        'Davies',
        'Davis',
        'Duncan',
        'Edwards',
        'Ferguson',
        'Fraser',
        'Gray',
        'Hall',
        'Harris',
        'Hart',
        'Henderson',
        'Hill',
        'Holmes',
        'James',
        'Johnson',
        'Johnston',
        'Jones',
        'Kaur',
        'Kemp',
        'King',
        'Kumar',
        'Lee',
        'Li',
        'Marshall',
        'Martin',
        'McDonald',
        'Mitchell',
        'Moore',
        'Murphy',
        'Parker',
        'Patel',
        'Reid',
        'Richardson',
        'Roberts',
        'Robertson',
        'Robinson',
        'Rutherford',
        'Scott',
        'Simpson',
        'Singh',
        'Smith',
        'Stewart',
        'Tan',
        'Taylor',
        'Thomas',
        'Thompson',
        'Thomson',
        'Turner',
        'Walker',
        'Wang',
        'Ward',
        'Watson',
        'White',
        'Williams',
        'Wilson',
        'Wright',
        'Young',
        'Zhang'
    );


    $lorem = array(
        'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        'Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
        'Fusce id velit ut tortor pretium viverra.',
        'Nulla porttitor massa id neque aliquam.',
        'Non consectetur a erat nam at lectus.',
        'Vitae tempus quam pellentesque nec.',
        'Aliquam sem fringilla ut morbi tincidunt augue interdum.',
        'Mauris commodo quis imperdiet massa.',
        'Suspendisse faucibus interdum posuere lorem ipsum dolor sit.',
        'Enim ut sem viverra aliquet eget sit amet.',
        'Eleifend quam adipiscing vitae proin sagittis nisl rhoncus mattis.',
        'At auctor urna nunc id cursus.',
        'Quisque id diam vel quam elementum.',
        'Imperdiet proin fermentum leo vel orci porta.',
        'Feugiat sed lectus vestibulum mattis ullamcorper.',
        'Sollicitudin tempor id eu nisl nunc mi ipsum faucibus vitae.',
        'Amet consectetur adipiscing elit duis tristique sollicitudin nibh.',
        'Magna eget est lorem ipsum dolor sit amet consectetur adipiscing.',
        'Nulla porttitor massa id neque.',
        'Odio morbi quis commodo odio.',
        'Suspendisse ultrices gravida dictum fusce.',
        'Faucibus ornare suspendisse sed nisi lacus.',
        'Blandit libero volutpat sed cras ornare arcu dui.',
        'A lacus vestibulum sed arcu non odio euismod lacinia at.',
        'Luctus accumsan tortor posuere ac ut consequat semper.',
        'Et tortor at risus viverra adipiscing.',
        'Sagittis purus sit amet volutpat consequat mauris nunc.',
        'Lorem ipsum dolor sit amet consectetur adipiscing elit.',
        'Lorem sed risus ultricies tristique nulla aliquet.',
        'Diam maecenas sed enim ut sem viverra aliquet eget.',
        'Eget arcu dictum varius duis at consectetur.',
        'Placerat in egestas erat imperdiet.',
        'Posuere lorem ipsum dolor sit amet consectetur adipiscing.',
        'Vitae tempus quam pellentesque nec nam aliquam sem et tortor.',
        'Orci eu lobortis elementum nibh tellus molestie nunc.',
        'Dolor sit amet consectetur adipiscing elit.',
        'Vitae semper quis lectus nulla at.',
        'Convallis convallis tellus id interdum velit.',
        'Et leo duis ut diam quam nulla porttitor massa.',
        'Natoque penatibus et magnis dis.',
        'Nunc vel risus commodo viverra maecenas accumsan.',
        'Id nibh tortor id aliquet lectus proin nibh nisl condimentum.',
        'Augue lacus viverra vitae congue eu consequat ac felis donec.',
        'Mi sit amet mauris commodo quis imperdiet massa tincidunt nunc.',
        'Mauris sit amet massa vitae tortor condimentum lacinia quis.',
        'Nulla aliquet enim tortor at auctor urna.',
        'Ultrices neque ornare aenean euismod elementum nisi.',
        'Imperdiet sed euismod nisi porta lorem.',
        'Fringilla urna porttitor rhoncus dolor purus non enim praesent elementum.',
        'Dictum varius duis at consectetur lorem donec massa.',
        'Laoreet sit amet cursus sit amet dictum.',
        'Ut sem nulla pharetra diam sit amet nisl suscipit adipiscing.',
        'Consequat nisl vel pretium lectus quam id leo in vitae.',
        'Maecenas volutpat blandit aliquam etiam erat velit scelerisque.',
        'Sit amet mauris commodo quis imperdiet massa tincidunt nunc.',
        'Iaculis nunc sed augue lacus viverra.',
        'Adipiscing elit ut aliquam purus sit amet.',
        'Vitae congue mauris rhoncus aenean vel.',
        'Rhoncus est pellentesque elit ullamcorper dignissim cras tincidunt lobortis.',
        'Etiam sit amet nisl purus in mollis nunc sed id.',
        'Viverra aliquet eget sit amet tellus.',
        'Mollis aliquam ut porttitor leo a diam sollicitudin tempor.',
        'Bibendum neque egestas congue quisque.',
        'Volutpat consequat mauris nunc congue nisi vitae.',
        'Iaculis eu non diam phasellus vestibulum lorem sed.',
        'Semper viverra nam libero justo laoreet.',
        'Diam maecenas sed enim ut sem viverra.',
        'Suscipit tellus mauris a diam maecenas sed.',
        'Non diam phasellus vestibulum lorem sed risus ultricies tristique.',
        'Donec ac odio tempor orci dapibus ultrices.',
        'Vulputate mi sit amet mauris commodo quis imperdiet massa tincidunt.',
        'Platea dictumst quisque sagittis purus sit amet volutpat.',
        'Sagittis vitae et leo duis.',
        'Pulvinar pellentesque habitant morbi tristique senectus et.',
        'Amet consectetur adipiscing elit duis tristique sollicitudin nibh sit amet.',
        'Diam in arcu cursus euismod quis viverra.',
        'Mauris in aliquam sem fringilla ut morbi tincidunt.',
        'Interdum posuere lorem ipsum dolor sit amet consectetur adipiscing elit.',
        'Aenean sed adipiscing diam donec.',
        'Id aliquet risus feugiat in ante metus dictum at.',
        'Elit duis tristique sollicitudin nibh.',
        'Sed augue lacus viverra vitae congue eu.',
        'Feugiat nisl pretium fusce id velit ut.',
        'Dictum at tempor commodo ullamcorper.',
        'Nec ultrices dui sapien eget mi proin sed.',
        'Facilisi cras fermentum odio eu feugiat.',
        'Pulvinar neque laoreet suspendisse interdum.',
        'Lacus viverra vitae congue eu consequat ac felis.',
        'Arcu bibendum at varius vel pharetra.',
        'Eget nunc scelerisque viverra mauris in aliquam sem fringilla ut.',
        'Ornare quam viverra orci sagittis.',
        'Leo duis ut diam quam nulla porttitor massa.',
        'Tempus imperdiet nulla malesuada pellentesque.',
        'Dignissim suspendisse in est ante in nibh mauris cursus mattis.',
        'Dui id ornare arcu odio ut sem.',
        'Sed cras ornare arcu dui vivamus arcu felis.',
        'Blandit turpis cursus in hac habitasse platea.',
        'Nulla facilisi cras fermentum odio eu feugiat pretium.',
        'Nunc eget lorem dolor sed viverra.',
        'Nunc sed id semper risus.',
        'Quis commodo odio aenean sed adipiscing diam donec adipiscing tristique.',
        'Id ornare arcu odio ut sem nulla pharetra.',
        'Massa tempor nec feugiat nisl pretium.',
        'Nullam ac tortor vitae purus faucibus ornare suspendisse sed nisi.',
        'Vulputate odio ut enim blandit.',
        'Nisl rhoncus mattis rhoncus urna neque.',
        'Tempor orci eu lobortis elementum nibh tellus.',
        'Nulla pellentesque dignissim enim sit amet venenatis.',
        'Nec ultrices dui sapien eget mi proin sed libero enim.',
        'Arcu odio ut sem nulla pharetra diam sit.',
        'Velit egestas dui id ornare arcu odio.',
        'Viverra accumsan in nisl nisi scelerisque eu ultrices vitae.',
        'Convallis aenean et tortor at risus viverra adipiscing.',
        'Nam at lectus urna duis convallis convallis tellus.',
        'Purus in massa tempor nec feugiat nisl pretium fusce.',
        'Fermentum et sollicitudin ac orci phasellus egestas.',
        'Aliquam vestibulum morbi blandit cursus risus at ultrices mi.',
        'Molestie a iaculis at erat pellentesque adipiscing.',
        'At augue eget arcu dictum.',
        'Amet nulla facilisi morbi tempus iaculis.',
        'Bibendum neque egestas congue quisque.',
        'Cras adipiscing enim eu turpis egestas.',
        'Neque volutpat ac tincidunt vitae semper quis lectus nulla.',
        'Habitant morbi tristique senectus et netus et malesuada fames.',
        'Malesuada fames ac turpis egestas.',
        'Volutpat lacus laoreet non curabitur gravida arcu ac tortor dignissim.',
        'Nec feugiat nisl pretium fusce id velit ut tortor pretium.',
        'Feugiat nibh sed pulvinar proin gravida hendrerit.',
        'Pretium aenean pharetra magna ac placerat vestibulum lectus.',
        'Netus et malesuada fames ac turpis egestas integer eget aliquet.',
        'Eu ultrices vitae auctor eu augue ut lectus arcu bibendum.',
        'Ac turpis egestas integer eget aliquet nibh praesent tristique.',
        'Amet venenatis urna cursus eget nunc.',
        'Cursus metus aliquam eleifend mi in.',
        'Sit amet mauris commodo quis.',
        'Mi bibendum neque egestas congue quisque egestas diam.',
        'Elit eget gravida cum sociis natoque.',
        'Non quam lacus suspendisse faucibus interdum posuere lorem ipsum dolor.',
        'Dictumst quisque sagittis purus sit amet volutpat consequat mauris.',
        'Et malesuada fames ac turpis egestas integer eget aliquet nibh.',
        'Felis imperdiet proin fermentum leo vel orci porta non pulvinar.',
        'Rhoncus est pellentesque elit ullamcorper dignissim cras.',
        'Pretium aenean pharetra magna ac placerat.',
        'Habitant morbi tristique senectus et netus et.',
        'Amet porttitor eget dolor morbi non arcu risus quis varius.',
        'Nunc eget lorem dolor sed.',
        'Congue nisi vitae suscipit tellus.',
        'Enim neque volutpat ac tincidunt vitae semper quis.',
        'Libero justo laoreet sit amet.',
        'Et ultrices neque ornare aenean euismod elementum nisi.',
        'Enim nec dui nunc mattis.',
        'Pellentesque nec nam aliquam sem et.',
        'Leo urna molestie at elementum eu facilisis sed odio morbi.',
        'Scelerisque purus semper eget duis.',
        'Dignissim suspendisse in est ante in nibh mauris cursus.',
        'Commodo nulla facilisi nullam vehicula ipsum a arcu cursus vitae.',
        'Tempor nec feugiat nisl pretium fusce id.',
        'Volutpat commodo sed egestas egestas fringilla.',
        'Egestas tellus rutrum tellus pellentesque eu tincidunt.',
        'Consectetur purus ut faucibus pulvinar elementum integer.',
        'Eu lobortis elementum nibh tellus molestie nunc.',
        'At risus viverra adipiscing at in tellus integer feugiat scelerisque.',
        'Volutpat maecenas volutpat blandit aliquam etiam erat velit scelerisque in.',
        'Leo vel fringilla est ullamcorper eget nulla facilisi etiam.',
        'Cras ornare arcu dui vivamus.',
        'Viverra accumsan in nisl nisi scelerisque eu ultrices vitae.',
        'Consequat ac felis donec et odio.',
        'Leo urna molestie at elementum eu facilisis.',
        'Ultrices mi tempus imperdiet nulla malesuada pellentesque elit eget gravida.',
        'Vitae suscipit tellus mauris a diam maecenas sed.',
        'Integer vitae justo eget magna fermentum.',
        'Donec enim diam vulputate ut pharetra.',
        'Lacus sed turpis tincidunt id aliquet risus feugiat in ante.',
        'Imperdiet sed euismod nisi porta.',
        'Ultrices mi tempus imperdiet nulla malesuada pellentesque elit eget.',
        'Sit amet nulla facilisi morbi tempus iaculis urna.',
        'Pellentesque massa placerat duis ultricies lacus sed.',
        'Nisi scelerisque eu ultrices vitae auctor eu augue.',
        'Eu nisl nunc mi ipsum faucibus vitae aliquet nec.',
        'Pellentesque elit ullamcorper dignissim cras.',
        'Felis bibendum ut tristique et egestas quis ipsum suspendisse ultrices.',
        'Purus in mollis nunc sed id semper risus in.',
        'Tempus imperdiet nulla malesuada pellentesque elit eget gravida.',
        'Odio morbi quis commodo odio.',
        'Sem viverra aliquet eget sit.',
        'Sit amet luctus venenatis lectus magna fringilla urna porttitor.',
        'Enim diam vulputate ut pharetra sit amet aliquam id.',
        'Venenatis a condimentum vitae sapien pellentesque habitant morbi.',
        'Neque egestas congue quisque egestas diam.',
        'Sem et tortor consequat id.',
        'Lectus sit amet est placerat in egestas erat.',
        'Commodo elit at imperdiet dui accumsan sit amet nulla facilisi.',
        'Aliquam etiam erat velit scelerisque in dictum.',
        'Nunc consequat interdum varius sit amet mattis vulputate.',
        'Nunc non blandit massa enim.',
        'Volutpat consequat mauris nunc congue.',
        'Urna id volutpat lacus laoreet non.',
        'Orci nulla pellentesque dignissim enim sit amet.',
        'In dictum non consectetur a.',
        'Sem et tortor consequat id porta nibh.',
        'Feugiat vivamus at augue eget arcu dictum.',
        'Non arcu risus quis varius quam.',
        'At ultrices mi tempus imperdiet nulla.',
        'Risus commodo viverra maecenas accumsan lacus vel facilisis.',
        'Pharetra diam sit amet nisl suscipit adipiscing bibendum est ultricies.',
        'Id consectetur purus ut faucibus pulvinar elementum.',
        'Amet nisl purus in mollis nunc sed id semper.',
        'Eros donec ac odio tempor.',
        'Aliquam sem fringilla ut morbi tincidunt augue interdum velit euismod.',
        'Neque ornare aenean euismod elementum nisi quis eleifend quam.',
        'Montes nascetur ridiculus mus mauris vitae ultricies leo integer malesuada.',
        'Odio facilisis mauris sit amet.',
        'Porttitor rhoncus dolor purus non enim praesent.',
        'Convallis convallis tellus id interdum velit.',
        'Amet dictum sit amet justo donec enim diam vulputate ut.',
        'In hendrerit gravida rutrum quisque non tellus orci ac.',
        'Odio eu feugiat pretium nibh ipsum consequat nisl vel.',
        'Tortor vitae purus faucibus ornare suspendisse sed.',
        'Mollis aliquam ut porttitor leo a diam sollicitudin tempor id.',
        'Egestas tellus rutrum tellus pellentesque eu tincidunt tortor aliquam.',
        'Scelerisque fermentum dui faucibus in.',
        'Quam nulla porttitor massa id neque aliquam vestibulum morbi.',
        'Feugiat scelerisque varius morbi enim nunc faucibus a pellentesque.',
        'Lectus proin nibh nisl condimentum id.',
        'Ut pharetra sit amet aliquam id diam maecenas.',
        'Cursus euismod quis viverra nibh.',
        'Vitae et leo duis ut diam quam.',
        'Turpis massa sed elementum tempus egestas sed sed risus.',
        'Urna condimentum mattis pellentesque id nibh.',
        'Fusce ut placerat orci nulla pellentesque dignissim enim sit.',
        'Ullamcorper dignissim cras tincidunt lobortis feugiat vivamus at augue eget.',
        'Non quam lacus suspendisse faucibus.',
        'Vitae proin sagittis nisl rhoncus mattis rhoncus urna neque.',
        'Vestibulum morbi blandit cursus risus at.',
        'Lacus sed viverra tellus in hac habitasse platea dictumst.',
        'Leo urna molestie at elementum.',
        'Fringilla est ullamcorper eget nulla facilisi etiam dignissim.',
        'Quis eleifend quam adipiscing vitae proin.',
        'Cursus euismod quis viverra nibh.',
        'Sed felis eget velit aliquet sagittis id consectetur purus ut.',
        'Id volutpat lacus laoreet non curabitur gravida.',
        'Viverra justo nec ultrices dui sapien.',
        'Id velit ut tortor pretium.',
        'Interdum consectetur libero id faucibus nisl tincidunt eget nullam.',
        'Feugiat scelerisque varius morbi enim.',
        'Suspendisse interdum consectetur libero id faucibus.',
        'Viverra nibh cras pulvinar mattis nunc sed blandit libero volutpat.',
        'Id venenatis a condimentum vitae sapien pellentesque.',
        'A diam sollicitudin tempor id eu.',
        'Sagittis purus sit amet volutpat consequat mauris nunc congue.',
        'Dictum sit amet justo donec enim.',
        'Interdum varius sit amet mattis vulputate enim nulla aliquet porttitor.',
        'Vel pretium lectus quam id leo in vitae turpis massa.',
        'Nisi scelerisque eu ultrices vitae auctor eu augue ut.',
        'A erat nam at lectus urna duis convallis convallis tellus.',
        'Commodo viverra maecenas accumsan lacus vel facilisis volutpat est.',
        'Id cursus metus aliquam eleifend mi in.',
        'Nulla posuere sollicitudin aliquam ultrices sagittis orci a scelerisque purus.',
        'Senectus et netus et malesuada fames ac turpis.',
        'Venenatis urna cursus eget nunc scelerisque.',
        'Adipiscing bibendum est ultricies integer.',
        'A diam maecenas sed enim ut sem viverra aliquet eget.',
        'Eu mi bibendum neque egestas congue quisque egestas.',
        'Purus non enim praesent elementum facilisis leo vel fringilla.',
        'Vel fringilla est ullamcorper eget nulla facilisi etiam dignissim.',
        'Sem viverra aliquet eget sit amet tellus.',
        'Lorem dolor sed viverra ipsum nunc aliquet.',
        'Elit ut aliquam purus sit amet luctus venenatis lectus magna.',
        'Sem integer vitae justo eget.',
        'In vitae turpis massa sed elementum.',
        'A scelerisque purus semper eget duis.',
        'Mattis aliquam faucibus purus in.',
        'Ultrices mi tempus imperdiet nulla.',
        'Elit duis tristique sollicitudin nibh.',
        'Quam vulputate dignissim suspendisse in est ante in.',
        'Amet luctus venenatis lectus magna fringilla urna porttitor rhoncus.',
        'Nec feugiat in fermentum posuere.',
        'Donec ultrices tincidunt arcu non sodales neque sodales ut.',
        'At elementum eu facilisis sed odio morbi quis commodo odio.',
        'Integer malesuada nunc vel risus commodo viverra maecenas.',
        'Massa sed elementum tempus egestas sed sed risus pretium.',
        'Lectus mauris ultrices eros in cursus.',
        'Nulla pharetra diam sit amet nisl.',
        'Nunc id cursus metus aliquam eleifend mi in nulla posuere.',
        'Congue nisi vitae suscipit tellus mauris a diam.',
        'Volutpat diam ut venenatis tellus in metus vulputate eu scelerisque.',
        'Hendrerit gravida rutrum quisque non tellus orci.',
        'Feugiat nibh sed pulvinar proin gravida hendrerit lectus.',
        'Pellentesque sit amet porttitor eget dolor morbi non.',
        'Morbi tincidunt ornare massa eget egestas.',
        'Morbi enim nunc faucibus a pellentesque sit.',
        'Commodo quis imperdiet massa tincidunt nunc pulvinar sapien.',
        'Auctor elit sed vulputate mi sit.',
        'Ac tincidunt vitae semper quis lectus nulla.',
        'In ante metus dictum at tempor commodo ullamcorper a lacus.',
        'Interdum posuere lorem ipsum dolor sit.',
        'Ipsum nunc aliquet bibendum enim facilisis.',
        'Amet dictum sit amet justo donec enim diam vulputate.',
        'Nulla at volutpat diam ut venenatis tellus in metus vulputate.',
        'Iaculis eu non diam phasellus vestibulum lorem sed risus ultricies.',
        'Feugiat nibh sed pulvinar proin gravida hendrerit lectus.',
        'Sed lectus vestibulum mattis ullamcorper velit sed ullamcorper morbi.',
        'Integer feugiat scelerisque varius morbi enim nunc.',
        'Amet dictum sit amet justo donec enim.',
        'Auctor urna nunc id cursus.',
        'Dictum sit amet justo donec enim diam vulputate.',
        'Nisi quis eleifend quam adipiscing vitae proin.',
        'Molestie at elementum eu facilisis sed odio morbi.',
        'At urna condimentum mattis pellentesque.',
        'Amet risus nullam eget felis eget.',
        'Vitae suscipit tellus mauris a diam maecenas sed enim ut.',
        'Vel quam elementum pulvinar etiam non quam.',
        'Quam lacus suspendisse faucibus interdum.'
    );




?>