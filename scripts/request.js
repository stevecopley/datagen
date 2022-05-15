function getFilters( source ) {
    const tableCode = source.value;

    console.log( tableCode );

    console.log( 'Requesting filers for table:' + tableCode );

    // Setup an HTTP request
    const request = new XMLHttpRequest();

    // Add the id to the request URL
    const url = 'get-filters.php?code=' + tableCode;

    // Send the request to the server
    request.open( 'GET', url );
    request.send();

    // Function to run when we get back a response
    request.onreadystatechange = () => {
        // Is it a positive response?
        if( request.readyState == 4 && request.status == 200 ) {
            // Yes, so process the JSON data received
            const filterJSON = request.responseText;
            console.log( 'Response received: ' + filterJSON );
            const filterInfo = JSON.parse( filterJSON );

            // TODO: update other select based on response

            // // Clear out the details div
            // const detailsDiv = document.getElementById( 'petdetails' );
            // detailsDiv.innerHTML = '';

            // // Create new elements to display the pet info
            // const name        = document.createElement( 'h3' );
            // const species     = document.createElement( 'p' );
            // const description = document.createElement( 'p' );

            // // Populate the info
            // name.innerHTML        = petInfo.name;
            // species.innerHTML     = 'Species: ' + petInfo.species;
            // description.innerHTML = 'Description: ' + petInfo.description;
            
            // // And display it
            // detailsDiv.appendChild( name );
            // detailsDiv.appendChild( species );
            // detailsDiv.appendChild( description );
        }
    };
}

