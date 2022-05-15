<?php

    require_once 'common-functions.php';
    require_once 'data-values.php';

    $counter = 0;
    $counterStep = 1;
    $fieldmappings = [];


    /*------------------------------------------------------------------
     * Convert given data using one of the defined conversion functions
     *------------------------------------------------------------------*/
    function modifyData( &$dataVal, $modifierCode, $n=null, $data=null ) {

        switch( $modifierCode ) {
            case 'MULTIPLY':
                $dataVal *= ($n ? $n : 1);
                break;

            case 'LOWER':
                $dataVal = strtolower( $dataVal );
                break;

            case 'UPPER':
                $dataVal = strtoupper( $dataVal );
                break;

            case 'LEFT':
                $dataVal = $n ? substr( $dataVal, 0, $n ) : $dataVal;
                break;

            case 'RIGHT':
                $dataVal = $n ? substr( $dataVal, -1 * $n ) : $dataVal;
                break;

            case 'PREPEND':
                $dataVal = $data.$dataVal;
                break;

            case 'APPEND':
                $dataVal = $dataVal.$data;
                break;
        }
    }


    /*------------------------------------------------------------------
     * Generate a data values based on given criteria / data
     *------------------------------------------------------------------*/
    function generate( $generatorCode, 
                       $n=null, $min=null, $max=null, 
                       $format=null, $info=null ) {

        global $counter;
        global $counterStep;

        switch( $generatorCode ) {
            case 'AUTO':
                $genData = $counter;
                $counter += $counterStep;
                break;

            case 'CONS':
                $genData = $format ? $format : 1;
                break;

            case 'INT':
                $genData = generateInt( $min, $max );
                break;

            case 'INT-BIN':
                $genData = generateBin( $min, $max, $n );
                break;

            case 'INT-HEX':
                $genData = generateHex( $min, $max, $n );
                break;

            case 'FLOAT':
                $genData = generateFloat( $min, $max, $n );
                break;

            case 'NORM':
                $genData = generateNormal( $min, $max );
                break;

            case 'ASCII':
                $genData = generateASCII( $min, $max, $n );
                break;

            case 'BOOL':
            case 'LIST':
                $genData = generateListItem( $format, $info, $min, $max );
                break;

            case 'EMAIL':
                $genData = generateEmail( $format, $info );
                break;

            case 'TEL':
                $genData = generateTel();
                break;

            case 'USER':
                $genData = generateUser( $format, $info );
                break;

            case 'PASS':
                $genData = generatePass( $n );
                break;

            case 'DATE':
                $genData = generateDate( $min, $max, $format );
                break;

            case 'TIME':
                $genData = generateTime( $min, $max, $format );
                break;
    
            case 'IPV4':
                $genData = generateIPv4();
                break;

            case 'CODE':
                $genData = generateFromCode( $format );
                break;

            case 'TEXT':
                $genData = generateText( $min, $max );
                break;

            case 'WORD':
                $genData = generateWords( $min, $max );
                break;

            default:
                $genData = 'ERROR!';
        }

        return $genData;
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


    /*------------------------------------------------------------------
     * Save field mappings for USER and EMAIL fields
     *------------------------------------------------------------------*/
    function mapFields( $format, $info ) {
        global $fieldmappings;

        // Get all of the fieldnames / values
        $values = array_map( 'trim', explode( ',', $info ) );

        // If no format supplied
        if( !$format ) {
            // Use standard mappings, checking to see if any are fieldnames and removing the {}
            $fieldmappings['{FORENAME}'] = isset( $values[0] ) ? ($values[0][0] == '{' ? substr( $values[0], 1, -1 ) : $values[0]) : null;
            $fieldmappings['{INITIAL}']  = isset( $values[0] ) ? ($values[0][0] == '{' ? substr( $values[0], 1, -1 ) : $values[0]) : null;
            $fieldmappings['{SURNAME}']  = isset( $values[1] ) ? ($values[1][0] == '{' ? substr( $values[1], 1, -1 ) : $values[1]) : null;
            $fieldmappings['{DOMAIN}']   = isset( $values[2] ) ? ($values[2][0] == '{' ? substr( $values[2], 1, -1 ) : $values[2]) : null;
        }
        else {
            // We have a format, so get fields
            $fields = extractFields( $format );

            for( $i = 0; $i < count( $fields ); $i++ ) {
                // If there is a field / value for this field, keep it
                if( isset( $values[$i] ) ) {
                    // Ignore an empty value
                    if( $values[$i] ) {
                        // Is it a field name? If so, strip {}, otherwise keep whole thing
                        $fieldmappings[$fields[$i]] = $values[$i][0] == '{' ? substr( $values[$i], 1, -1 ) : $values[$i];
                    }
                    else 
                        $fieldmappings[$fields[$i]] = null;
                }
            }    
        }

        $_SESSION['mappings'] = $fieldmappings;
        $_SESSION['mappings']['values'] = $values;
    } 



    function generateInt( $min=0, $max=100 ) {
        $min = $min === null ?   0 : $min;
        $max = $max === null ? 100 : max( $min, $max );
        return rand( $min, $max );
    }


    function generateBin( $min=0, $max=255, $n = 8 ) {
        $min = $min === null ?   0 : max( 0, $min );
        $max = $max === null ? 255 : max( $min, $max );
        $digits = $n ? $n : 8;
        $maxDigits = strlen( decbin( $max ) );
        $digits = max( $digits, $maxDigits );
        $value = rand( $min, $max );
        $binary = decbin( $value );
        $binary = str_pad( $binary, $digits, "0", STR_PAD_LEFT );
        return $binary;
    }


    function generateHex( $min=0, $max=255, $n=2 ) {
        $min = $min === null ?   0 : max( 0, $min );
        $max = $max === null ? 255 : max( $min, $max );
        $digits = $n ? $n : 2;
        $maxDigits = strlen( dechex( $max ) );
        $digits = max( $digits, $maxDigits );
        $value = rand( $min, $max );
        $hex = dechex( $value );
        $hex = str_pad( $hex, $digits, "0", STR_PAD_LEFT );
        $hex = strtoupper( $hex );
        // $hex = '0x'.$hex;
        return $hex;
    }


    function generateFloat( $min=0, $max=100, $n=1 ) {
        $min = $min === null ?   0 : $min;
        $max = $max === null ? 100 : max( $min, $max );
        $n = $n ? $n : 1;
        $scaling = pow( 10, $n );
        $value = rand( $min * $scaling, $max * $scaling ) / $scaling;
        $float = number_format( $value, $n, '.', '' );
        return $float;
    }


    function generateNormal( $min=0, $max=100 ) {
        // https://natedenlinger.com/php-random-number-generator-with-normal-distribution-bell-curve/
        $sd = ($max - $min) / 5;
        $mean = (float)($min + $max) / 2;
        do {
            $rand1 = (float)rand() / (float)getrandmax();
            $rand2 = (float)rand() / (float)getrandmax();
            $gaussianNum = sqrt( -2 * log( $rand1 ) ) * cos( 2 * M_PI * $rand2 );
            $rand = round( ($gaussianNum * $sd) + $mean );
        } while( $rand < $min || $rand > $max );                
        return $rand;
    }


    function generateASCII( $min=33, $max=126, $n=1 ) {
        // Only displayable characters
        $min = $min === null ?  33 : max( 33, $min );
        $max = $max === null ? 126 : min( 126, $max );
        $ascii = '';
        $num = $n ? $n : 1;
        for( $i = 0; $i < $num; $i++ ) {
            $ascii .= chr( rand( $min, $max ) );
        }
        return $ascii;
    }


    function generateListItem( $format='No|Yes', $info='50|50', $min=1, $max=11 ) {
        $options = array_map( 'trim', explode( '|', ($format ? $format : 'No|Yes') ) );
        $odds    = array_map( 'trim', explode( '|', ($info ? $info : '50|50') ) );
        $min = $min === null ? 1 : $min;
        $max = $max === null ? 1 : min( $max, count( $options ) );
        $num = rand( $min, $max );
        // Create the list of items
        $picks = '';
        $count = 0;
        while( $count < $num ) {
            $option = pickWithOdds( $options, $odds );
            // Have this option already? If so, try again
            if( strpos( $picks, $option ) !== false ) continue;
            // Nope, so add to the list
            if( $count > 0 ) $picks .= ', ';
            $picks .= $option;
            $count++;
        }
        return $picks;
    }


    function generateTel() {
        $tel = '02';
        $len = rand( 8, 9 );
        for( $i = 0; $i < $len; $i++ ) {
            $tel .= rand( 0, 9 );
            // Add spaces to format
            if( $i == 0 || $i == $len - 5 ) $tel .= ' ';
        }
        return $tel;
    }


    function randCharFromString( $string ) {
        return $string[rand( 0, strlen( $string ) - 1 )];
    }

    function generatePass( $min=8, $max=12 ) {
        $passSymbols = '!@#$%^&*(){}[]_-+=.:;?~|';  // Avoid any awkward charcters like commas or speech marks
        $passDigits  = '0123456789';
        $passLowers  = 'abcdefghijklmnopqrstuvwxyz';
        $passUppers  = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        
        $min = $min === null ?  8 : max( 0, $min );
        $max = $max === null ? 12 : max( $min, $max );
        $len = rand( $min, $max );
        
        $pass = '';
        for( $i = 0; $i < $len; $i++ ) {
            if( rand( 1, 5 ) == 1 ) $pass .= randCharFromString( $passUppers );
            else                    $pass .= randCharFromString( $passLowers );
        }

        // One digit for every 8 chars
        for( $i = 0; $i < floor( $len / 5 ); $i++ ) {
            $pass[rand( 0, strlen( $pass ) - 1 )] = randCharFromString( $passDigits );
        }
        // One symbol for every 8 chars
        for( $i = 0; $i < floor( $len / 8 ); $i++ ) {
            $pass[rand( 0, strlen( $pass ) - 1 )] = randCharFromString( $passSymbols );
        }

        return $pass;
    }


    function generateDate( $min=1900, $max=2000, $format=null ) {
        $min = strtotime( ($min ? $min : 1900).'-01-01' );
        $max = strtotime( ($max ? $max : 2000).'-12-31' );
        $dateVal = rand( $min, $max );
        $date = date( ($format ? $format : 'Y-m-d'), $dateVal );
        return $date;
    }


    function generateTime( $min=0, $max=23, $format=null ) {
        $min = strtotime( ($min ? $min : 00).':00' );
        $max = strtotime( ($max ? $max : 23).':59' );
        $timeVal = rand( $min, $max );
        $time = date( ($format ? $format : 'H:i'), $timeVal );
        return $time;
    }


    function generateIPv4() {
        return rand( 1, 255 ).'.'.rand( 0, 255 ).'.'.rand( 0, 255 ).'.'.rand( 0, 255 );
    }


    function generateFromCode( $format='A' ) {
        $len = strlen( $format );
        $text = '';
        for( $i = 0; $i < $len; $i++ ) {
                 if( $format[$i] == 'A' ) $text .= chr( rand( 65, 90 ) ); 
            else if( $format[$i] == 'a' ) $text .= chr( rand( 97, 122 ) ); 
            else if( $format[$i] == '0' ) $text .= rand( 0, 9 ); 
            else                          $text .= $format[$i];
        }
        return $text;
    }


    function generateText( $min=1, $max=1 ) {
        global $lorem;
        $min = $min === null ? 1 : max( 0, $min );
        $max = $max === null ? 1 : max( $min, $max );
        $num = rand( $min, $max );
        $text = '';
        $count = 0;
        while( $count < $num ) {
            $sentence = $lorem[array_rand( $lorem )];
            
            // Have this sentence already? If so, try again
            if( strpos( $text, $sentence ) !== false ) continue;

            if( $count > 0 ) $text .= ' ';
            $text .= $sentence;
            $count++;
        }
        return $text;
    }


    function generateWords( $min=1, $max=1 ) {
        global $lorem;
        $min = $min === null ? 1 : max( 0, $min );
        $max = $max === null ? 1 : max( $min, $max );
        $num = rand( $min, $max );
        $text = '';
        $count = 0;
        while( $count < $num ) {
            $sentence = $lorem[array_rand( $lorem )];
            $sentence = str_replace( '.', '', $sentence );
            $words = explode( ' ', $sentence );
            $word = strtolower( $words[array_rand( $words )] );

            // Don't want tiny words
            if( strlen( $word ) < 4 ) continue; 
            // Have this word already? If so, try again
            if( strpos( $text, $word ) !== false ) continue;

            if( $count > 0 ) $text .= ' ';
            $text .= $word;
            $count++;
        }
        return $text;
    }


    function generateUser( $format=null, $info=null ) {
        global $starters;
        global $endings;

        $formats = array( 
            '{FORENAME}{JOIN}{SURNAME}',
            '{STARTER}{JOIN}{FORENAME}',
            '{FORENAME}{JOIN}is{JOIN}{ENDING}',
            '{INITIAL}{JOIN}{SURNAME}'
        );
        $joiners  = array( '.', '-', '_' );

        // Use given format, or pick a random one
        $format = $format ? $format : $formats[array_rand( $formats )];

        // Add in a joiner char and pre/sufix
        $starter = $starters[array_rand( $starters )];
        $ending  = $endings[array_rand( $endings )];
        $joiner = rand( 1, 10 ) > 7 ? $joiners[array_rand( $joiners )] : '';
        $format = str_replace( '{JOIN}',    $joiner,  $format );
        $format = str_replace( '{STARTER}', $starter, $format );
        $format = str_replace( '{ENDING}',  $ending,  $format );

        return $format;
    }


    function generateEmail( $format=null, $info=null ) {
        global $emailProviders;

        // Use given format, or pick a random one
        if( !$format ) {
            // Generate a random user format
            $format = generateUser( $format, $info );

            // Add a number?
            $numRoll = rand( 1, 10 );
            if( $numRoll > 7 )      $format .= rand( 1, 99 );
            else if( $numRoll > 3 ) $format .= rand( 1960, 2010 );

            $format .= '@{DOMAIN}';        
        }

        return $format;
    }


    // Get field names delimited by {...} recursively
    function extractFields( $format ) {
        // Field remaining?
        $fieldPos = strpos( $format, '{' );
        if( $fieldPos === false ) return [];
        // Valid end point?
        $fieldEnd = strpos( $format, '}' );
        if( $fieldEnd === false || $fieldEnd < $fieldPos ) return [];
        // Get the field
        $field = substr( $format, $fieldPos, $fieldEnd - $fieldPos + 1 );
        // And then work thru remainder
        $format = substr( $format, $fieldEnd + 1 );
        $remainingFields = extractFields( $format );
        // Glue the extracted field to the remainder
        return [$field, ...$remainingFields];
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


    function processField( &$value, $record=null ) {
        global $fieldmappings;
        global $forenames;
        global $surnames;
        global $emailProviders;
        
        // Go thru all the mappings
        foreach( $fieldmappings as $field => $mapping ) {
            // Found a placeholder? If so, replace the value with the data from the appropriate column
            if( strpos( $value, $field ) !== false ) { 
                // Is there a mapping value?
                if( $mapping ) {
                    // Is this a valid column? 
                    if( isset( $record[$mapping] ) ) $dataVal = $record[$mapping];
                    // If not, just keep the value given in the mapping (i.e. it's a const)
                    else                             $dataVal = $mapping;
                }
                else {
                    // If not, do we need to pick a random value?
                        if( $field == '{FORENAME}' ) $dataVal = $forenames[array_rand( $forenames )];
                    elseif( $field == '{INITIAL}' )  $dataVal = $forenames[array_rand( $forenames )];
                    elseif( $field == '{SURNAME}' )  $dataVal = $surnames[array_rand( $surnames )];
                    elseif( $field == '{DOMAIN}' )   $dataVal = $emailProviders[array_rand( $emailProviders )];
                    else                             $dataVal = '?????';
                }
                // Keep it all lowercase
                $dataVal = strtolower( $dataVal );
                // Do we just need the initial?
                if( $field == '{INITIAL}' ) $dataVal = substr( $dataVal, 0, 1 );

                $value = str_replace( $field, $dataVal, $value );
            }    
        }
    }


    function postProcessData( $keys, &$data ) {
        global $fieldmappings;
        
        // Convert field name mappings into column indices
        foreach( $fieldmappings as &$mapping ) {
            for( $i = 0; $i < count( $keys ); $i++ ) {
                if( $keys[$i] == $mapping ) {
                    $mapping = $i;
                }
            }
        }
        unset( $mapping );  
        
        $_SESSION['mappings']['after'] = $fieldmappings;

        // Run thru all the records
        foreach( $data as &$record ) {
            foreach( $record as &$value ) {
                processField( $value, $record );
            }    
        }
    }
?>

