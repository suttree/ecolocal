class Article < ActiveRecord::Base
   acts_as_taggable
   belongs_to  :user
   has_many    :comments
   has_many	  :local_events
   has_many    :article_notifications
   has_and_belongs_to_many :countries
   has_and_belongs_to_many :counties
   has_and_belongs_to_many :places

   validates_presence_of   :title, :body, :user_id

   def self.find_total_by_country( country_id )
      total = find_by_sql(
      [
         'SELECT count(*) as count
         FROM articles, countries, articles_countries
         WHERE articles.id = articles_countries.article_id
         AND articles_countries.country_id = countries.id
         AND countries.id = ?',
         country_id
      ]
      )

      return total[0].count.to_i
   end

   def self.find_total_by_county( county_id )
      total = find_by_sql(
      [
         'SELECT count(*) as count
         FROM articles, counties, articles_counties
         WHERE articles.id = articles_counties.article_id
         AND articles_counties.county_id = counties.id
         AND counties.id = ?',
         county_id
      ]
      )

      return total[0].count.to_i
   end

   def self.find_total_by_place( place_id )
      total = find_by_sql(
      [
         'SELECT count(*) as count
         FROM articles, places, articles_places
         WHERE articles.id = articles_places.article_id
         AND articles_places.place_id = places.id
         AND places.id = ?',
         place_id
      ]
      )

      return total[0].count.to_i
   end

   def self.find_total_by_tag( tag )
      total = find_by_sql(
      [
         'SELECT count(*) as count
         FROM articles, tags, tags_articles
         WHERE articles.id = tags_articles.article_id
         AND tags_articles.tag_id = tags.id
         AND tags.name = ?',
         tag
      ]
      )

      return total[0].count.to_i
   end

   def self.find_total_by_tag_and_county( tag, county_id )
      total = find_by_sql(
      [
         'SELECT count(*) as count
         FROM articles, articles_counties, tags, tags_articles
         WHERE articles.id = tags_articles.article_id
         AND articles.id = articles_counties.article_id
         AND tags_articles.tag_id = tags.id
         AND tags.name = ?
         AND articles_counties.county_id = ?',
         tag, county_id
      ]
      )

      return total[0].count.to_i
   end

   def self.find_total_by_tag_and_place( tag, place_id )
      total = find_by_sql(
      [
         'SELECT count(*) as count
         FROM articles, articles_places, tags, tags_articles
         WHERE articles.id = tags_articles.article_id
         AND articles.id = articles_places.article_id
         AND tags_articles.tag_id = tags.id
         AND tags.name = ?
         AND articles_places.place_id = ?',
         tag, place_id
      ]
      )

      return total[0].count.to_i
   end

   def self.find_total_by_section( section )
      total = find_by_sql(
      [
         'SELECT count(*) as count
         FROM articles, tags_articles, tags
         WHERE articles.id = tags_articles.article_id
         AND tags_articles.tag_id = tags.id
         AND tags.name = ?',
         section
      ]
      )

      return total[0].count.to_i
   end

   def self.find_total_by_section_and_county( section, county_id )
      total = find_by_sql(
      [
         'SELECT count(*) as count
         FROM articles, articles_counties, tags_articles, tags
         WHERE articles.id = tags_articles.article_id
         AND tags_articles.tag_id = tags.id
         AND tags.name = ?
         AND articles.id = articles_counties.article_id
         AND articles_counties.county_id = ?',
         section, county_id
      ]
      )

      return total[0].count.to_i
   end

   def self.find_total_by_section_and_place( section, place_id )
      total = find_by_sql(
      [
         'SELECT count(*) as count
         FROM articles, articles_places, tags_articles, tags
         WHERE articles.id = tags_articles.article_id
         AND tags_articles.tag_id = tags.id
         AND tags.name = ?
         AND articles.id = articles_places.article_id
         AND articles_places.place_id = ?',
         section, place_id
      ]
      )

      return total[0].count.to_i
   end

   def self.find_total_by_tags( tags )
      total = find_by_sql(
      [
         "SELECT count(*) as count,
         count( distinct(tags.name) ) as num_tags
         FROM articles, tags, tags_articles
         WHERE articles.id = tags_articles.article_id
         AND tags_articles.tag_id = tags.id
         AND tags.name in ( ? )
         HAVING num_tags = ?",
         tags, tags.size
      ]
      )

      return total[0].count.to_i
   end

   def self.find_total_by_tags_and_county( tags, county )
      total = find_by_sql(
      [
         'SELECT count(*) as count,
         count( distinct(tags.name) ) as num_tags
         FROM articles, tags, tags_articles, articles_counties
         WHERE articles.id = tags_articles.article_id
         AND tags_articles.tag_id = tags.id
         AND articles.id = articles_counties.article_id
         AND tags.name in ( ? )
         AND articles_counties.county_id = ?
         HAVING num_tags = ?',
         tags, county, tags.size
      ]
      )

      return total[0].count.to_i
   end

   def self.find_total_by_tags_and_place( tags, place )
      total = find_by_sql(
      [
         'SELECT count(*) as count,
         count( distinct(tags.name) ) as num_tags
         FROM articles, tags, tags_articles, articles_places
         WHERE articles.id = tags_articles.article_id
         AND tags_articles.tag_id = tags.id
         AND articles.id = articles_places.article_id
         AND tags.name in ( ? )
         AND articles_places.place_id = ?
         HAVING num_tags = ?',
         tags, place, tags.size
      ]
      )

      return total[0].count.to_i
   end
end
