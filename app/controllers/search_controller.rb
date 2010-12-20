class SearchController < ApplicationController
   layout "main"
   after_filter :compress_output

   def index
      # Tell us where you live...
      @page_title = 'Search results'

      unless session[:locale] != nil
         session[:locale] = 'uk'
      end

      country = Country.find_by_url_name session[:locale]

      # If we're dealing with a UK postcode, just grab the first three characters as that is all geonames handles
      params[:q] = params[:q].strip

      if params[:q] =~ /\b[A-PR-UWYZ][A-HK-Y0-9][A-HJKSTUW0-9]?[ABEHMNPRVWXY0-9]? {1,2}[0-9][ABD-HJLN-UW-Z]{2}\b/i && session[:locale] == 'uk'
         params[:q] = params[:q][0..2]
      end

      @postcodes = []
      @places = []
      @counties = []

      @postcodes = Postcode.find_all_by_postcode( params[:q] )
      @places = Place.find( :all, :include => [ :county ], :conditions => [ "places.name like ? and counties.country_id = ?", "%" + params[:q] + "%", country.id ], :group => 'places.county_id' )
      @counties = County.find( :all, :conditions => [ "name like ? and country_id = ?", "%" + params[:q] + "%", country.id ] )

	# There's a bug in the database, where postcodes aren't linked to places and therefore counties,
	# meaning that the places_postcodes is empty for a chunk of postcodes :(
	# So, check it now and if the data is missing, call GeoNames...
	if @postcodes.size > 0
		if @postcodes[0].places.size == 0
			@postcodes = []
		end
	end

      # We only use geonames for UK data as the rest of it is poorly laid out, for our use at least.
      if @counties.size == 0 && @places.size == 0 && @postcodes.size == 0 && session[:locale] == 'uk'
         require 'rexml/document'

         @ws_url = 'http://ws.geonames.org/postalCodeSearch?placename=' + URI.escape( params[:q] ) + '&maxRows=50&style=LONG&country=' + session[:locale]
         logger.info 'GeoNames URL :: ' + @ws_url
         @content = Net::HTTP.get( URI.parse( @ws_url ) )

         xml = REXML::Document.new(@content)
         xml.elements.each('//code') do |item|
            # Create the relevant locations and then
            # create the relations between them all

            # Hack to make GB work like UK
            if item.elements['countryCode'].text == 'gb' || item.elements['countryCode'].text == 'GB'
               item.elements['countryCode'].text = 'uk'
            end

            @country = Country.find_or_create_by_url_name item.elements['countryCode'].text

            @county = County.find_or_create_by_name item.elements['adminName2'].text
            @county.url_name = normalise( @county.name )

            @place = Place.find_or_create_by_name_and_county_id item.elements['name'].text, @county.id

            @place.name = item.elements['name'].text
            @place.url_name = normalise( @place.name )

            @postcode = Postcode.find_or_create_by_postcode item.elements['postalcode'].text

            @county.country = @country
            @place.county = @county
            @place.postcodes << @postcode

            @county.save
            @place.save
            @postcode.save

            @counties << @county
            @places << @place
         end

         if @places.size == 1
            redirect_to :controller => 'location', :country => session[:locale], :county => @places[0].county.url_name, :place => @places[0].url_name, :action => 'place'
            return
         elsif @counties.size == 1
            redirect_to :controller => 'location', :country => session[:locale], :county => @counties[0].url_name, :action => 'county'
            return
         end

         render :action => 'index'
         return

         #@counties = []
         #@places = []
         #@postcode = Postcode.find_all_by_postcode( params[:q] )
         #@place = Place.find_all_by_name( params[:q] )
         #@county = County.find_all_by_name( params[:q] )

      elsif @counties.size == 0 && @places.size == 0 && @postcodes.size > 0

         # We've got a postcode match. Pull out all the relevant places and display an interstitial page
         
         if @postcodes.size == 1
            redirect_to :controller => 'location', :country => session[:locale], :county => @postcodes[0].places[ 0 ].county.url_name, :place => @postcodes[0].places[ 0 ].url_name, :action => 'place'
            return
         else
            render :action => 'index'
            return
         end
      elsif @counties.size > 0 && @places.size > 0
         render :action => 'index'
         return
      end

      # Now redirect to the right page, or dislay an interstitial so that the user can choose
      if @places.size == 1
         redirect_to :controller => 'location', :country => session[:locale], :county => @places[0].county.url_name, :place => @places[0].url_name, :action => 'place'
         return
      elsif @counties.size == 1
         redirect_to :controller => 'location', :country => session[:locale], :county => @counties[0].url_name, :action => 'county'
         return
      end

      render :action => 'index'

   end

   def index_old
      # Tell us where you live...
      @page_title = 'Search results'

      # User types in where they live, could be a postcode, placename or county.
      # We'll search in reverse order, widening our search as we go.
      #
      # Search in postcodes - Postcode.find
      # Search in places - Place.find
      # Search in counties - County.find
      #
      # If one result found, redirect to it. Otherwise if more than one result is found,
      # display an interstitial page # and let the user choose. Remember, postcodes are
      # only used to help people find a place or county.
      #
      # If no results are found, call ws.geonames.org and store the results in the db afterwards.
      #
      # Currently, this doesn't do wildcard searching, e.g. entering 'ken' brings up 'kent'. At
      # the moment it'll return 'no results'.

      unless session[:locale] != nil
         session[:locale] = 'uk'
      end

      # If we're dealing with a UK postcode, just grab the first three characters as that is all geonames handles
      params[:q] = params[:q].strip

      if params[:q] =~ /\b[A-PR-UWYZ][A-HK-Y0-9][A-HJKSTUW0-9]?[ABEHMNPRVWXY0-9]? {1,2}[0-9][ABD-HJLN-UW-Z]{2}\b/i && session[:locale] == 'uk'
         params[:q] = params[:q][0..2]
      end

      @postcode = Postcode.find_all_by_postcode( params[:q] )
      @place = Place.find( :all, :conditions => [ "name like ?", "%" + params[:q] + "%" ] )
      @county = County.find( :all, :conditions => [ "name like ?", "%" + params[:q] + "%" ] )

      @places = []
      @counties = []

      # We only use geonames for UK data as the rest of it is poorly laid out, for our use at least.
      if @county.size == 0 && @place.size == 0 && @postcode.size == 0 && session[:locale] == 'uk'
         require 'rexml/document'

         @ws_url = 'http://ws.geonames.org/postalCodeSearch?placename=' + URI.escape( params[:q] ) + '&maxRows=50&style=LONG&country=' + session[:locale]
         logger.info 'GeoNames URL :: ' + @ws_url
         @content = Net::HTTP.get( URI.parse( @ws_url ) )

         xml = REXML::Document.new(@content)
         xml.elements.each('//code') do |item|
            # Create the relevant locations and then
            # create the relations between them all

            # Hack to make GB work like UK
            if item.elements['countryCode'].text == 'gb' || item.elements['countryCode'].text == 'GB'
               item.elements['countryCode'].text = 'uk'
            end

            @country = Country.find_or_create_by_url_name item.elements['countryCode'].text

            @county = County.find_or_create_by_name item.elements['adminName2'].text
            @county.url_name = normalise( @county.name )

            @place = Place.find_or_create_by_name_and_county_id item.elements['name'].text, @county.id

            @place.name = item.elements['name'].text
            @place.url_name = normalise( @place.name )

            @postcode = Postcode.find_or_create_by_postcode item.elements['postalcode'].text

            @county.country = @country
            @place.county = @county
            @place.postcodes << @postcode

            @county.save
            @place.save
            @postcode.save

            @counties << @county
            @places << @place
         end

         if @places.size == 1
            redirect_to :controller => 'location', :country => session[:locale], :county => @places[0].county.url_name, :place => @places[0].url_name, :action => 'place'
            return
         elsif @counties.size == 1
            redirect_to :controller => 'location', :country => session[:locale], :county => @counties[0].url_name, :action => 'county'
            return
         end

         render :action => 'index'
         return

         #@counties = []
         #@places = []
         #@postcode = Postcode.find_all_by_postcode( params[:q] )
         #@place = Place.find_all_by_name( params[:q] )
         #@county = County.find_all_by_name( params[:q] )

      elsif @county.size == 0 && @place.size == 0 && @postcode.size > 0

         # We've got a postcode match. Pull out all the relevant places
         # and display an interstitial page
         @places = Place.find_all_by_postcode_id( @postcode[0].id )

         if @places.size == 1
            redirect_to :controller => 'location', :country => session[:locale], :county => @places[ 0 ].county.url_name, :place => @places[ 0 ].url_name, :action => 'place'
            return
         else
            render :action => 'index'
            return
         end
      elsif @county.size > 0 && @place.size > 0
         render :action => 'index'
         return
      end

      # Now redirect to the right page, or dislay an interstitial so that the user can choose
      if @places.size == 0 && @place.size == 1
         redirect_to :controller => 'location', :country => session[:locale], :county => @place[0].county.url_name, :place => @place[0].url_name, :action => 'place'
         return
      elsif @counties.size == 0 &&  @county.size == 1
         redirect_to :controller => 'location', :country => session[:locale], :county => @county[0].url_name, :action => 'county'
         return
      end

      render :action => 'index'
   end

   def results
      # Search for matching articles
      @page_title = 'Search results'

      @article = Article.find_by_sql(
      ['SELECT articles.*
         FROM articles
         WHERE title LIKE ?
         OR body LIKE ?
         OR url LIKE ?
      LIMIT 10', '%' + params[:q] + '%', '%' + params[:q] + '%', '%' + params[:q] + '%']
      )

      render :action => 'results'
   end

   def events_results
      @article = Article.find_by_sql(
      ['SELECT articles.*
         FROM articles, local_events, tags_articles, tags
         WHERE articles.id = local_events.article_id
         AND articles.id = tags_articles.article_id
         AND tags_articles.tag_id = tags.id
         AND tags.name = ?
         AND articles.section = ?
         OR articles.title LIKE ?
         OR articles.body LIKE ?
         GROUP BY articles.id',
         params[:q],
         'events',
         params[:q],
         params[:q]
      ]
      )

      render :action => 'results'
   end

   def counties
      @county = County.find(params['county']['id'])
      redirect_to :controller => 'uk/' + @county.url_name
   end

end
