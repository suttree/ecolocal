ActionController::Routing::Routes.draw do |map|
  # Add your own custom routes here.
  # The priority is based upon order of creation: first created -> highest priority.
  
  # Here's a sample route:
  # map.connect 'products/:id', :controller => 'catalog', :action => 'view'
  # Keep in mind you can assign values other than :controller and :action

  # You can have the root of your site routed by hooking up '' 
  # -- just remember to delete public/index.html.
  # map.connect '', :controller => "welcome"

  # Allow downloading Web Service WSDL as a file with an extension
  # instead of a file named 'wsdl'
  map.connect ':controller/service.wsdl', :action => 'wsdl'

  # Rules for eco-local in here
  map.connect   '',
                :controller => "home"

  map.connect   'test',
                :controller => "home",
                :action => "test"

  map.connect   'rss/:feed',
                :controller => "article",
                :action => "rss"

  map.connect   'ping_technorati',
                :controller => "article",
                :action => "ping_technorati"

  map.connect   'euro',
                :controller => "home",
                :action => "euro"

  map.connect   'locale/:locale',
                :controller => 'home',
                :action => 'locale'

  map.connect   'sitemap',
                :controller => "home",
                :action => "sitemap"

  map.connect	"/index.xml",
                :controller => "xml",
                :action => "rss"

  map.connect   'discuss/:page',
                :section => 'discuss',
                :controller => 'discussion',
                :requirements => { :page => /\d+/ }

  map.connect   'discuss/tag/:tag',
                :section => 'discuss',
                :action => 'tag_list',
                :controller => 'discussion'
                
  map.connect   'discuss/:action',
                :section => 'discuss',
                :controller => 'discussion',
                :requirements => { :action => /new|edit|save|index|show|list|comment_notification|discussion_notification|update$|update_comment|reply_to_comment|tag/ }

  map.connect   'discuss/:title',
                :section => 'discuss',
                :controller => 'discussion',
                :action => 'show'

  map.connect   'discuss/:action/:title',
                :section => 'discuss',
                :controller => 'discussion',
                :requirements => { :action => /new|edit|save|index|list|comment_notification|discussion_notification|update|update_comment|reply_to_comment|tag/ }

  map.connect   "members/",
                :controller => "members",
                :action => "index" 

  map.connect   "members/logout",
                :controller => "members/login",
                :action => "logout"

  map.connect   "members/login/:action/:id",
                :controller => "members/login"

  map.connect   "members/profile/:action/:id",
                :controller => "members/profile"

  map.connect   "members/:nickname",
                :controller => "members",
                :action => "index"

  # Search
  map.connect "/search/",
              :controller => 'search',
              :action => 'index'

  map.connect ":country/search",
              :controller => 'search',
              :action => 'index' ,
              :requirements => { :country => /de|fr|gb|nl|se|us|uk|au/ }

  map.connect ":country/search/events",
              :controller => 'search',
              :action => 'events' ,
              :requirements => { :country => /de|fr|gb|nl|se|us|uk|au/ }

  map.connect ":country/search/results",
              :controller => 'search',
              :action => 'results',
              :requirements => { :country => /de|fr|gb|nl|se|us|uk|au/ }

  map.connect ":country/search/events/results",
              :controller => 'search',
              :action => 'events_results',
              :requirements => { :country => /de|fr|gb|nl|se|us|uk|au/ }

  map.connect ":country/",
              :controller => 'location',
              :action => 'country',
              :requirements => { :country => /de|fr|gb|nl|se|us|uk|au/ }
                          
  map.connect ":country/:page",
              :controller => 'location',
              :action => 'country' ,
              :requirements => { :country => /de|fr|gb|nl|se|us|uk|au/, :page => /\d+/ }

  #Latest pages, with pagination
  map.connect ":country/:county/:place/:section/latest/:page",
              :controller => 'section',
              :action => 'place',
              :order => 'date',
              :requirements => { :section => /home_life|health|family|events/, :country => /de|fr|gb|nl|se|us|uk|au/, :page => /\d+/ }

  map.connect ":country/:county/:place/latest/:page",
              :controller => 'location',
              :action => 'place',
              :order => 'date',
              :requirements => { :country => /de|fr|gb|nl|se|us|uk|au/, :page => /\d+/ }

  map.connect ":country/:county/:section/latest/:page",
              :controller => 'section',
              :action => 'county',
              :order => 'date',
              :requirements => { :section => /home_life|health|family|events/, :country => /de|fr|gb|nl|se|us|uk|au/, :page => /\d+/ }

  map.connect ":country/:section/latest/:page",
              :controller => 'section',
              :action => 'country',
              :order => 'date',
              :requirements => { :section => /home_life|health|family|events/, :country => /de|fr|gb|nl|se|us|uk|au/, :page => /\d+/ }

  map.connect ":country/:county/latest/:page",
              :controller => 'location',
              :action => 'county',
              :order => 'date',
              :requirements => { :country => /de|fr|gb|nl|se|us|uk|au/, :page => /\d+/ }
              
  map.connect ":country/latest/:page",
              :controller => 'location',
              :action => 'country',
              :order => 'date',
              :requirements => { :country => /de|fr|gb|nl|se|us|uk|au/, :page => /\d+/ }

  # Latest pages
  map.connect ":country/:county/:place/:section/latest",
              :controller => 'section',
              :action => 'place',
              :order => 'date',
              :requirements => { :section => /home_life|health|family|events/, :country => /de|fr|gb|nl|se|us|uk|au/ }

  map.connect ":country/:county/:section/latest",
              :controller => 'section',
              :action => 'county',
              :order => 'date',
              :requirements => { :section => /home_life|health|family|events/, :country => /de|fr|gb|nl|se|us|uk|au/ }

  map.connect ":country/:section/latest",
              :controller => 'section',
              :action => 'country',
              :order => 'date',
              :requirements => { :section => /home_life|health|family|events/, :country => /de|fr|gb|nl|se|us|uk|au/ }

  map.connect ":country/:county/:place/latest",
              :controller => 'location',
              :action => 'place',
              :order => 'date',
              :requirements => { :country => /de|fr|gb|nl|se|us|uk|au/ }

  map.connect ":country/:county/latest",
              :controller => 'location',
              :action => 'county',
              :order => 'date',
              :requirements => { :country => /de|fr|gb|nl|se|us|uk|au/ }

  map.connect ":country/latest",
              :controller => 'location',
              :action => 'country',
              :order => 'date',
              :requirements => { :country => /de|fr|gb|nl|se|us|uk|au/ }

  # Events browser (must come before other tags)
  map.connect ":country/:section/tag/:tag",
	      :controller => 'section',
	      :action => 'events_tag',
	      :requirements => { :country => /de|fr|gb|nl|se|us|uk|au/, :section => /events/ }

  map.connect ":country/:county/:section/tag/:tag",
	      :controller => 'section',
	      :action => 'events_tag',
	      :requirements => { :country => /de|fr|gb|nl|se|us|uk|au/, :section => /events/ }

  map.connect ":country/:county/:place/:section/tag/:tag",
	      :controller => 'section',
	      :action => 'events_tag',
	      :requirements => { :country => /de|fr|gb|nl|se|us|uk|au/, :section => /events/ }

  map.connect ":country/:section/date/:tag",
	      :controller => 'section',
	      :action => 'events_date',
	      :requirements => { :country => /de|fr|gb|nl|se|us|uk|au/, :section => /events/ }

  map.connect ":country/:county/:section/date/:tag",
	      :controller => 'section',
	      :action => 'events_date',
	      :requirements => { :country => /de|fr|gb|nl|se|us|uk|au/, :section => /events/ }

  map.connect ":country/:county/:place/:section/date/:tag",
	      :controller => 'section',
	      :action => 'events_date',
	      :requirements => { :country => /de|fr|gb|nl|se|us|uk|au/, :section => /events/ }

  # Global tags
  map.connect ":country/tag/:tag",
              :controller => 'location',
              :action => 'country_tag',
              :requirements => { :country => /de|fr|gb|nl|se|us|uk|au/ }

  # County tags
  map.connect ":country/:county/tag/:tag",
              :controller => 'location',
              :action => 'county_tag',
              :requirements => { :country => /de|fr|gb|nl|se|us|uk|au/ }

  # Place tags
  map.connect ":country/:county/:place/tag/:tag",
              :controller => 'location',
              :action => 'place_tag',
              :requirements => { :country => /de|fr|gb|nl|se|us|uk|au/ }

  # Create pages (must come before view pages)
  map.connect ":country/submit/:action/",
              :controller => 'article',
              :requirements => { :country => /de|fr|gb|nl|se|us|uk|au/ }

  map.connect ":country/:section/submit/:action",
              :controller => 'article',
              :requirements => { :country => /de|fr|gb|nl|se|us|uk|au/, :section => /home_life|health|family|events/ }

  map.connect ":country/:county/submit/:action",
              :controller => 'article',
              :requirements => { :country => /de|fr|gb|nl|se|us|uk|au/ }

  map.connect ":country/:county/:section/submit/:action",
              :controller => 'article',
              :requirements => { :country => /de|fr|gb|nl|se|us|uk|au/, :section => /home_life|health|family|events/ }

  map.connect ":country/:county/:place/submit/:action",
              :controller => 'article',
              :requirements => { :country => /de|fr|gb|nl|se|us|uk|au/ }

  map.connect ":country/:county/:place/:section/submit/:action",
              :controller => 'article',
              :requirements => { :country => /de|fr|gb|nl|se|us|uk|au/, :section => /home_life|health|family|events/ }

  # View pages
  map.connect ":country/:section/:action/:title",
              :controller => 'article',
              :requirements => { :country => /de|fr|gb|nl|se|us|uk|au/, :section => /home_life|health|family|events/, :action => /^show$|^edit$|^new_comment$|^reply_to_comment$|^edit_comment$|^update_comment$|^comment_notification$|^article_notification$|^report_article$|^good_article$|^bad_article$|^good_comment$|^bad_comment$/ }

  map.connect ":country/:county/:section/:action/:title",
              :controller => 'article',
              :requirements => { :country => /de|fr|gb|nl|se|us|uk|au/, :section => /home_life|health|family|events/, :action => /^show$|^edit$|^new_comment$|^reply_to_comment$|^edit_comment$|^update_comment$|^comment_notification$|^article_notification$|^report_article$|^good_article$|^bad_article$|^good_comment$|^bad_comment$/ }

  map.connect ":country/:county/:place/:section/:action/:title",
              :controller => 'article',
              :requirements => { :country => /de|fr|gb|nl|se|us|uk|au/, :section => /home_life|health|family|events/, :action => /^show$|^edit$|^new_comment$|^reply_to_comment$|^edit_comment$|^update_comment$|^comment_notification$|^article_notification$|^report_article$|^good_article$|^bad_article$|^good_comment$|^bad_comment$/ }

  map.connect ":country/:county/:place/:action/:title",
              :controller => 'article',
              :requirements => { :country => /de|fr|gb|nl|se|us|uk|au/, :action => /^show$|^edit$|^new_comment$|^reply_to_comment$|^edit_comment$|^update_comment$|^comment_notification$|^article_notification$|^report_article$|^good_article$|^bad_article$|^good_comment$|^bad_comment$/ }

  map.connect ":country/:county/:action/:title",
              :controller => 'article',
              :requirements => { :country => /de|fr|gb|nl|se|us|uk|au/, :action => /^show$|^edit$|^new_comment$|^reply_to_comment$|^edit_comment$|^update_comment$|^comment_notification$|^article_notification$|^report_article$|^good_article$|^bad_article$|^good_comment$|^bad_comment$/ }

  map.connect ":country/:action/:title",
              :controller => 'article',
              :requirements => { :country => /de|fr|gb|nl|se|us|uk|au/, :action => /^show$|^edit$|^new_comment$|^reply_to_comment$|^edit_comment$|^update_comment$|^comment_notification$|^article_notification$|^report_article$|^good_article$|^bad_article$|^good_comment$|^bad_comment$/ }

  # Global home/health/family page
  map.connect ":country/:section",
              :controller => 'section',
              :action => 'country',
              :requirements => { :section => /home_life|health|family|events/, :country => /de|fr|gb|nl|se|us|uk|au/ }

  map.connect ":country/:section/:page",
              :controller => 'section',
              :action => 'country',
              :requirements => { :section => /home_life|health|family|events/, :country => /de|fr|gb|nl|se|us|uk|au/, :page => /\d+/ }

  # County home/health/family page
  map.connect ":country/:county/:section/:page",
              :controller => 'section',
              :action => 'county',
              :requirements => { :section => /home_life|health|family|events/, :country => /de|fr|gb|nl|se|us|uk|au/, :page => /\d+/ }

  map.connect ":country/:county/:section",
              :controller => 'section',
              :action => 'county',
              :requirements => { :section => /home_life|health|family|events/, :country => /de|fr|gb|nl|se|us|uk|au/ }

  # Place home/health/family page
  map.connect ":country/:county/:place/:section/:page",
              :controller => 'section',
	            :action => 'place',
              :requirements => { :section => /home_life|health|family|events/, :country => /de|fr|gb|nl|se|us|uk|au/, :page => /\d+/ }

  map.connect ":country/:county/:place/:section",
              :controller => 'section',
	            :action => 'place',
              :requirements => { :section => /home_life|health|family|events/, :country => /de|fr|gb|nl|se|us|uk|au/ }

  # County home page
  map.connect ":country/:county",
              :controller => 'location',
              :action => 'county' ,
              :requirements => { :country => /de|fr|gb|nl|se|us|uk|au/ }

  map.connect ":country/:county/:page",
              :controller => 'location',
              :action => 'county' ,
              :requirements => { :country => /de|fr|gb|nl|se|us|uk|au/, :page => /\d+/ }

  # Place home page
  map.connect ":country/:county/:place/",
              :controller => 'location',
              :action => 'place',
              :requirements => { :country => /de|fr|gb|nl|se|us|uk|au/ }

  map.connect ":country/:county/:place/:page",
              :controller => 'location',
              :action => 'place' ,
              :requirements => { :country => /de|fr|gb|nl|se|us|uk|au/, :page => /\d+/ }

  map.connect "report_spam/:comment_id",
              :controller => "article",
              :action => "report_spam"

  # Install the default route as the lowest priority.
  map.connect ':controller/:action/:id'
end
