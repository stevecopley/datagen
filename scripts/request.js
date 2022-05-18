async function getFilters( source ) {
    const tableCode = source.value;
    console.log( 'Requesting filers for table:' + tableCode );

    const response = await fetch( 'get-filters.php?table=' + tableCode );
    const filters = await response.json();

    return filters;
}



async function updateFilters( source ) {
    const filters = await getFilters( source );

    const filterList = document.getElementById( 'filter-list' );
    filterList.innerHTML = '';

    filters.forEach( filter => {
        const option = document.createElement( 'option' );
        option.innerHTML = filter.name;
        option.value = filter.code;
        filterList.appendChild( option );
    } );

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

