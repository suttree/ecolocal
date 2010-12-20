<?

// pull out all postcodes in zipcodes
// get matching row from postcodes
// update postcodes with lat and long

//mysql_connect( "127.0.0.1", "ecolocal", "r0cket7" );
mysql_connect( "127.0.0.1", "suttree", "moriarty" );

//mysql_select_db( "ecolocal_production" );
mysql_select_db( "ecolocal_development" );

$zipcodes_result = mysql_query( "select * from zipcodes" );

while ( $zipcode = mysql_fetch_object( $zipcodes_result ) )
{
	mysql_query( "update postcodes set longitude = '" . $zipcode->longt . "', latitude = '" . $zipcode->latt . "' where postcode = '" . $zipcode->zipcode . "'" );	
}

mysql_free_result( $zipcodes_result );

?>