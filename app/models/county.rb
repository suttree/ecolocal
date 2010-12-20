class County < ActiveRecord::Base
  has_and_belongs_to_many :articles
  has_many    :places
  belongs_to  :country
  
   # Ajax method for finding a county
  def self.search(q, locale)
    country = Country.find_by_url_name locale

    @counties = County.find( :all, :conditions => [ "name like ? and country_id = ?", "%" + q + "%", country.id ] )

    # We only use geonames for UK data as the rest of it is poorly laid out, for our use at least.
    if @counties.size == 0 && locale == 'uk'
      require 'rexml/document'

      @ws_url = 'http://ws.geonames.org/postalCodeSearch?placename=' + URI.escape( q ) + '&maxRows=50&style=LONG&country=' + locale
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

        @county.country = @country
        @county.save
        @counties << @county
      end
    end
    @counties
  end
end
