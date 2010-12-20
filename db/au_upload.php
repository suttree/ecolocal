<?

$states = array(
					"NSW" => "New South Wales",
					"ACT" => "Australian Capital Territory",
					"VIC" => "Victoria",
					"QLD" => "Queensland",
					"SA" => "South Australia",
					"WA" => "Western Australia",
					"TAS" => "Tasmania",
					"NT" => "Norther Territory"
				);

class CSVparse
  {
  // From http://uk2.php.net/fgetcsv
  var $mappings = array();

  function parse_file($filename)
   {
   $id = fopen($filename, "r"); //open the file
   $data = fgetcsv($id, filesize($filename)); /*This will get us the */
                                               /*main column names */

   if(!$this->mappings)
       $this->mappings = $data;

   while($data = fgetcsv($id, filesize($filename)))
       {
         if($data[0])
           {
           foreach($data as $key => $value)
               $converted_data[$this->mappings[$key]] = addslashes($value);
           $table[] = $converted_data; /* put each line into */
             }                                /* its own entry in    */
         }                                    /* the $table array    */
   fclose($id); //close file
   return $table;
   }
  }

$data =  CSVparse::parse_file( "aussie_postcodes.csv" );

// Pull out place and county
// lookup country
// insert into country
// insert into places
// insert into counties

//mysql_connect( "127.0.0.1", "ecolocal", "r0cket7" );
mysql_connect( "127.0.0.1", "suttree", "moriarty" );

//mysql_select_db( "ecolocal_production" );
mysql_select_db( "ecolocal_development" );

foreach( $data as $id => $row )
{
	$county_url_name = strtolower( str_replace( " ", "_", $states[ $row[ "State" ] ] ) );
	$county = ucwords( strtolower( $states[ $row[ "State" ] ] ) );

	$place_url_name = strtolower( str_replace( " ", "_", $row[ "Locality" ] ) );
	$place = ucwords( strtolower( $row[ "Locality" ] ) );

	$zipcode = $row[ "Pcode" ];

	if ( !$states[ $row->county ] )
	{
		//var_dump( "Missing -- " . $row->county );
	}
	
	// Counties
	$existing_result = mysql_query( "select count(*) as count from counties where name = \"{$county}\" or url_name = \"{$county_url_name}\"" );

	$existing_row = mysql_fetch_object( $existing_result );
	
	if ( $existing_row->count == 0 )
	{
		mysql_query( "insert into counties (country_id,name,url_name) values ( 6, \"{$county}\", \"{$county_url_name}\" )" );
	}
	else
	{
		//var_dump( "More than one county called " . $county );
	}

	$current_county = mysql_query( "select id from counties where country_id = 6 and name = \"{$county}\"" );

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
