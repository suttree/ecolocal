class SectionController < ApplicationController
    layout 'main'
    #after_filter :compress_output

    def country
      	unless read_fragment :country => params[:country], :section => params[:section], :page => params[:page], :order => params[:order], :logged_in => session[:logged_in]

      		total = Article.find_total_by_section params[:section]

      		@article_pages = Paginator.new self, total, 10, @params[ 'page' ]

          @article = Article.find(  :all, 
                                    :conditions => [ 'countries.url_name = ? AND tags.name = ?', params[:country], params[:section] ],
                                    :include => [ :countries, :counties, :places, :comments, :user, :local_events, :article_notifications, :tags ], 
                      							:limit  =>  @article_pages.items_per_page,
                      							:offset =>  @article_pages.current.offset,
                                    :order => 'articles.created_on DESC'
                                  )
      	end

        render :action => 'list'
    end

    def county
      	unless read_fragment :country => params[:country], :county => params[:county], :section => params[:section], :page => params[:page], :order => params[:order], :logged_in => session[:logged_in]

      		@county = County.find_by_url_name( params[:county] )
	
      		total = Article.find_total_by_section_and_county params[:section], @county.id

      		@article_pages = Paginator.new self, total, 10, @params[ 'page' ]

          @article = Article.find(  :all, 
                                    :conditions => [ 'countries.url_name = ? AND counties.url_name = ? AND tags.name = ?', params[:country], params[:county], params[:section] ],
                                    :include => [ :countries, :counties, :places, :comments, :user, :local_events, :article_notifications, :tags ], 
                      							:limit  =>  @article_pages.items_per_page,
                      							:offset =>  @article_pages.current.offset,
                                    :order => 'articles.created_on DESC'
                                  )
	      	end

        render :action => 'list'
    end

    def place 
    	unless read_fragment :country => params[:country], :county => params[:county], :place=> params[:place], :section => params[:section], :page => params[:page], :order => params[:order], :logged_in => session[:logged_in]

    		@place = Place.find_by_url_name( params[:place] )
	
    		total = Article.find_total_by_section_and_place params[:section], @place.id

    		@article_pages = Paginator.new self, total, 10, @params[ 'page' ]

        @article = Article.find(  :all, 
                                  :conditions => [ 'countries.url_name = ? AND counties.url_name = ? AND places.url_name = ? AND tags.name = ?', params[:country], params[:county], params[:place], params[:section] ],
                                  :include => [ :countries, :counties, :places, :comments, :user, :local_events, :article_notifications, :tags ], 
                    							:limit  =>  @article_pages.items_per_page,
                    							:offset =>  @article_pages.current.offset,
                                  :order => 'articles.created_on DESC'
                                )
    	end

        render :action => 'list'
    end

    def events_tag
        	unless read_fragment :country => params[:country], :county => params[:county], :place=> params[:place], :tag => params[:tag], :section => params[:section], :page => params[:page], :order => params[:order], :logged_in => session[:logged_in]

        		if params[:place]

        			@place = Place.find_by_url_name( params[:place] )

        			total = Article.find_total_by_tags_and_place( [ 'events', params[:tag] ], @place.id )

        			@article_pages = Paginator.new self, total, 10, @params['page']

              @article = Article.find(  :all, 
                                        :conditions => [ 'countries.url_name = ? AND counties.url_name = ? AND placess.url_name = ? AND tags.name = ?', params[:country], params[:county], params[:place], params[:tag] ],
                                        :include => [ :countries, :counties, :places, :comments, :user, :local_events, :article_notifications, :tags ], 
                          							:limit  =>  @article_pages.items_per_page,
                          							:offset =>  @article_pages.current.offset,
                                        :order => 'articles.created_on DESC'
                                      )
                                      
        		elsif params[:county]

        			@county = County.find_by_url_name( params[:county] )

        			total = Article.find_total_by_tags_and_county( [ 'events', params[:tag] ], @county.id )

        			@article_pages = Paginator.new self, total, 10, @params['page']

              @article = Article.find(  :all, 
                                        :conditions => [ 'countries.url_name = ? AND counties.url_name = ? AND tags.name = ?', params[:country], params[:county], params[:tag] ],
                                        :include => [ :countries, :counties, :places, :comments, :user, :local_events, :article_notifications, :tags ], 
                          							:limit  =>  @article_pages.items_per_page,
                          							:offset =>  @article_pages.current.offset,
                                        :order => 'articles.created_on DESC'
                                      )
        		else

        			total = Article.find_total_by_tags( [ 'events', params[:tag] ] )

        			@article_pages = Paginator.new self, total, 10, @params['page']

              @article = Article.find(  :all, 
                                        :conditions => [ 'countries.url_name = ? AND tags.name = ?', params[:country], params[:tag] ],
                                        :include => [ :countries, :counties, :places, :comments, :user, :local_events, :article_notifications, :tags ], 
                          							:limit  =>  @article_pages.items_per_page,
                          							:offset =>  @article_pages.current.offset,
                                        :order => 'articles.created_on DESC'
                                      )
        		end
        	end

          render :action => 'list'
    end

    def events_date
        	unless read_fragment :country => params[:country], :county => params[:county], :place=> params[:place], :tag => params[:tag], :section => params[:section], :page => params[:page], :order => params[:order], :logged_in => session[:logged_in]
        		# This is a wee bit complicated and def. needs caching.
        		# We calcualted the dates required and then pull out all of the events matching this country/county/place.
        		# Then we cycle through each event and create a runt object from it.
        		# We then test to see if this Runt object matches with the supplied time frame.
        		# The matching events are then extracted from the database and rendered.

        		if params[:tag] == 'today'
        			date = Time.now
        		elsif params[:tag] == 'tomorrow'
        			date = Time.now + 1
        		elsif params[:tag] == 'this_week'
        			start_date = Time.now.at_beginning_of_week - 1.day
        			end_date = start_date + 6.days
        		elsif params[:tag] == 'next_week'
        			start_date = Time.now.next_week(:sunday)
        			end_date = start_date + 6.days
        		elsif params[:tag] == 'this_weekend'
        			end_date = Time.now.at_beginning_of_week + 6.days
        			start_date = end_date - 2.days
        		elsif params[:tag] == 'next_weekend'
        			end_date = Time.now.at_beginning_of_week + 1.week + 6.days
        			start_date = end_date - 2.days
        		elsif params[:tag] == 'this_month'
        			 start_date = Time.now.at_beginning_of_month
        			 end_date = Time.now.at_end_of_month
        		elsif params[:tag] == 'next_month'
        			start_date = Time.now.next_month.at_beginning_of_month
        			end_date = Time.now.next_month.at_end_of_month
        		elsif params[:tag] == 'all'
        			redirect_to :controller => 'section', :action => 'country', :country => params[:country], :section => params[:section]
        			return
        		end

        		if params[:place]
        			@place = Place.find_by_url_name( params[:place] )

        			total = Article.find_total_by_section_and_place params[:section], @place.id
        			@article_pages = Paginator.new self, total, 10, @params[ 'page' ]

              all_articles = Article.find(  :all, 
                                        :conditions => [ 'countries.url_name = ? AND counties.url_name = ? AND places.url_name = ? AND tags.name = ?', params[:country], params[:county], params[:place], params[:section] ],
                                        :include => [ :countries, :counties, :places, :comments, :user, :local_events, :article_notifications, :tags ], 
                          							#:limit  =>  @article_pages.items_per_page,
                          							#:offset =>  @article_pages.current.offset,
                                        :order => 'articles.created_on DESC'
                                      )
        		elsif params[:county]
        			@county = County.find_by_url_name( params[:county] )

        			total = Article.find_total_by_section_and_county params[:section], @county.id
        			@article_pages = Paginator.new self, total, 10, @params[ 'page' ]

              all_articles = Article.find(  :all, 
                                        :conditions => [ 'countries.url_name = ? AND counties.url_name = ? AND tags.name = ?', params[:country], params[:county], params[:section] ],
                                        :include => [ :countries, :counties, :places, :comments, :user, :local_events, :article_notifications, :tags ], 
                          							#:limit  =>  @article_pages.items_per_page,
                          							#:offset =>  @article_pages.current.offset,
                                        :order => 'articles.created_on DESC'
                                      )
        		else
        			total = Article.find_total_by_section params[:section]

        			@article_pages = Paginator.new self, total, 10, @params[ 'page' ]

              all_articles = Article.find(  :all, 
                                        :conditions => [ 'countries.url_name = ? AND tags.name = ?', params[:country], params[:section] ],
                                        :include => [ :countries, :counties, :places, :comments, :user, :local_events, :article_notifications, :tags ], 
                          							#:limit  =>  @article_pages.items_per_page,
                          							#:offset =>  @article_pages.current.offset,
                                        :order => 'articles.created_on DESC'
                                      )
        		end

        		# Here's where we search all the matching articles, create runt objects and extract the correct ones.
        		# Note the two different paths depending on whether the event has a set time frame (start date and end date)
        		# or a recurring window.
        		article_ids = []
        		for article in all_articles
        			if article.local_events.size == 0
        				next
        			end

        			if article.local_events[0].start_date && article.local_events[0].end_date
        				if start_date && end_date
        					if start_date >= article.local_events[0].start_date && end_date <= article.local_events[0].end_date
        						article_ids << article.id
        					end
        				else
        					if date >= article.local_events[0].start_date && date <= article.local_events[0].end_date
        						article_ids << article.id
        					end
        				end
        			elsif article.local_events[0].event_type == 'recurring_week_month'
        				for event_str in article.local_events
        					# The dates are now strings which aren't accepted to Runt.
        					# The Runt object either uses ints to specify the week number or day index,
        					# or it uses literal constants defined in the base Runt class that are 
        					# aliased to the int equivalents. The easiest way to get our data back
        					# into Runt-friendly shapes is by using eval, which turns the string
        					# into an int index, and seems to work.
        					# See the Runt RDocs for an explanation of the difference between DIMonth,
        					# REMonth and DIWeek - http://runt.rubyforge.org/doc/index.html
        					recurrence = event_str.recurrence.split(',') 
        					event = DIMonth.new( eval(recurrence[0]), eval(recurrence[1]) )

        					if start_date && end_date
        						date = start_date
        						while date <= end_date
        							# Format the date from the Time.now calculations above into a 
        							# formal Date object as Runt seems to much prefer those
        							if event.include?( Date.new( date.year, date.month, date.day ) ) && !article_ids.include?( article.id )
        								article_ids << article.id
        							end
        							date = date + 1.day
        						end
        					else
        						if event.include?( date ) && !article_ids.include?( article.id )
        							article_ids << article.id
        						end
        					end
        				end
        			elsif article.local_events[0].event_type == 'recurring_month_day'
        				for event_str in article.local_events
        					event = DIWeek.new( eval(event_str.recurrence) )
					
        					if start_date && end_date
        						date = start_date
        						while date <= end_date
        							if event.include?( Date.new( date.year, date.month, date.day ) ) && !article_ids.include?( article.id )
        								article_ids << article.id
        							end
        							date = date + 1.day
        						end
        					else
        						if event.include?( date ) && !article_ids.include?( article.id )
        							article_ids << article.id
        						end
        					end
        				end
        			elsif article.local_events[0].event_type == 'recurring_month_index'
        				for event_str in article.local_events
        					event = REMonth.new( eval(event_str.recurrence) )

        					if start_date && end_date
        						date = start_date
        						while date <= end_date
        							if event.include?( Date.new( date.year, date.month, date.day ) ) && !article_ids.include?( article.id )
        								article_ids << article.id
        							end
        							date = date + 1.day
        						end
        					else
        						if event.include?( date ) && !article_ids.include?( article.id )
        							article_ids << article.id
        						end
        					end
        				end
        			end
        		end
		
        		if article_ids.size > 0
        			@article_pages = Paginator.new self, article_ids.size, 10, @params[ 'page' ]

              @article = Article.find(  :all, 
                                        :conditions => [ 'articles.id IN ( ? )', article_ids ],
                                        :include => [ :countries, :counties, :places, :comments, :user, :local_events, :article_notifications, :tags ], 
                          							:limit  =>  @article_pages.items_per_page,
                          							:offset =>  @article_pages.current.offset,
                                        :order => 'articles.created_on DESC'
                                      )
        		else

        			@article = {}

        			@article_pages = Paginator.new self, 0, 10, @params[ 'page' ]
        		end

            article_ids = []
            for article in @article
                article_ids << article.id
            end

        	end

          render :action => 'list'
    end
end
