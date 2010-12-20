<?

$states = array(
					"AR" => "Arkansas",
					"CA" => "California",
					"CO" => "Colorado",
					"CT" => "Connecticut",
					"DE" => "Delaware",
					"FL" => "Florida",
					"GA" => "Georgia",
					"HI" => "Hawaii",
					"ID" => "Idaho",
					"IL" => "Illinois",
					"IN" => "Indiana",
					"IA" => "Iowa",
					"KS" => "Kansas",
					"KY" => "Kentucky",
					"LA" => "Louisiana",
					"ME" => "Maine",
					"MD" => "Maryland",
					"MA" => "Massachusetts",
					"MI" => "Michigan",
					"MN" => "Minnesota",
					"MS" => "Mississippi",
					"MO" => "Missouri",
					"MT" => "Montana",
					"NE" => "Nebraska",
					"NV" => "Nevada",
					"NH" => "New Hampshire",
					"NJ" => "New Jersey",
					"NM" => "New Mexico",
					"NY" => "New York",
					"NC" => "North Carolina",
					"ND" => "North Dakota",
					"OH" => "Ohio",
					"OK" => "Oklahoma",
					"OR" => "Oregon",
					"PA" => "Pennsylvania",
					"RI" => "Rhode Island",
					"SC" => "South Carolina",
					"SD" => "South Dakota",
					"TN" => "Tennessee",
					"TX" => "Texas",
					"UT" => "Utah",
					"VT" => "Vermont",
					"VA" => "Virginia",
					"WA" => "Washington",
					"WV" => "West Virginia",
					"WI" => "Wisconsin",
					"WY" => "Wyoming",
					
					"PR" => "Puerto Rico",
					"VI" => "Virgin Islands",
					"DC" => "District of Columbia",
					"AL" => "Alabama",
					"AZ" => "Arizona",
					"AK" => "Alaska",
					"AS" => "American Samoa",
				);

// pull out place and county
// lookup country
// insert into country
// insert into places
// insert into counties
//mysql_connect( "127.0.0.1", "ecolocal", "r0cket7" );
mysql_connect( "127.0.0.1", "suttree", "moriarty" );

//mysql_select_db( "ecolocal_production" );
mysql_select_db( "ecolocal_development" );

$result = mysql_query( "select * from zipcodes" );

while ( $row = mysql_fetch_object( $result ) )
{
	$county_url_name = strtolower( str_replace( " ", "_", $states[ $row->county ] ) );
	$county = $states[ $row->county ];

	$place_url_name = strtolower( str_replace( " ", "_", $row->place ) );
	$place = $row->place;

	$zipcode = $row->zipcode;

	if ( !$states[ $row->county ] )
	{
		//var_dump( "Missing -- " . $row->county );
	}
	
	// Counties
	$existing_result = mysql_query( "select count(*) as count from counties where name = \"{$county}\" or url_name = \"{$county_url_name}\"" );

	$existing_row = mysql_fetch_object( $existing_result );
	
	if ( $existing_row->count == 0 )
	{
		mysql_query( "insert into counties (country_id,name,url_name) values ( 2, \"{$county}\", \"{$county_url_name}\" )" );
	}
	else
	{
		//var_dump( "More than one county called " . $county );
	}

	$current_county = mysql_query( "select id from counties where country_id = 2 and name = \"{$county}\"" );

	$current_county_row = mysql_fetch_object( $current_county );

	$county_id = $current_county_row->id;
	
	// Places
	$existing_result = mysql_query( "select count(*) as count from places where name = \"{$place}\" and county_id = {$county_id}" );

	$existing_row = mysql_fetch_object( $existing_result );
	
	if ( $existing_row->count == 0 )
	{
		mysql_query( "insert into places (county_id,name,url_name) values ( \"{$county_id}\", \"{$place}\", \"{$place_url_name}\" )" );
	}
	else
	{
		//var_dump( "More than one place called " . $place );
	}

	$current_place = mysql_query( "select id from places where name = \"{$place}\" and county_id = \"{$county_id}\"" );

	$current_place_row = mysql_fetch_object( $current_place );

	$place_id = $current_place_row->id;
	
	// Postcodes
	$existing_result = mysql_query( "select count(*) as count from postcodes where postcode = \"{$zipcode}\"" );

	$existing_row = mysql_fetch_object( $existing_result );
	
	if ( $existing_row->count == 0 )
	{
		mysql_query( "insert into postcodes (postcode) values ( \"{$zipcode}\" )" );
	}
	else
	{
		//var_dump( "More than one postcode called " . $zipcode );
	}

	$current_postcode = mysql_query( "select id from postcodes where postcode = \"{$zipcode}\"" );

	$current_postcode_row = mysql_fetch_object( $current_postcode );

	$postcode_id = $current_postcode_row->id;
	
	// Postcodes + places
	$existing_result = mysql_query( "select count(*) as count from places_postcodes where place_id = \"{$place_id}\" and postcode_id = \"{$postcode_id}\"" );

	$existing_row = mysql_fetch_object( $existing_result );
	
	if ( $existing_row->count == 0 )
	{
		mysql_query( "insert into places_postcodes (place_id,postcode_id) values ( \"{$place_id}\", \"{$postcode_id}\" )" );
	}
	else
	{
		//var_dump( "More than one postcode called " . $zipcode );
	}
}

mysql_free_result( $result );

?>
