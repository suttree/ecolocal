class ArticleController < ApplicationController
  session :off, :only => [ :rss ]
  layout 'main'

    def index
      @article = Article.find_by_normalised_title( params[:title])
    end

    def new
        @article = Article.new
        session[:create_action] = 'new'
    end

    def whats_on
        @article = Article.new
        session[:create_action] = 'whats_on'
    end

    def event
        @article = Article.new
        session[:create_action] = 'picture'
    end

    def save
        @article = Article.new

        # Whitelist the incoming data
        params[:article].each do | key, value |
          params[:article][key] = sanitize(value, 'a href, b, br, i, p')
        end

        params[:tags] = sanitize(params[:tags], 'a href, b, br, i, p')

        @article.title = params[:article][:title]
        @article.body = params[:article][:body]
        @article.url = params[:article][:url]
        @article.address = params[:article][:address] 
        @article.postcode = params[:article][:postcode] 

        @article.user_id = session[:user].id
        @article.normalised_title = self.normalise( @article.title )
        
      	if params[:event_type] == 'one_off'

      		event = LocalEvent.new
      		event.event_type = 'one_off'
      		event.update_attributes(params[:local_events])
      		@article.local_events << event
      		#@article.tag 'events', :separator => ',', :attributes => { :user_id => session[:user].id }
      		@article.tag 'events', :separator => ','

      	elsif params[:event_type] == 'recurring'

      		# Store the recurrence dates in the database as just comma separated text
      		# Searching for matching events or displaying events will just require the creation of 
      		# Runt objects, rather than Marhsalling them in and out of the database
		
      		if params[:recurring_period] == 'recurring_week_month'
      			i = 0
      			while i < params[:local_event][:week].size
      				event = LocalEvent.new
      				event.event_type = params[:recurring_period]
      				event.recurrence = params[:local_event][:week][i].to_s + ',' + params[:local_event][:day][i]
              #@article.tag 'events', :separator => ',', :attributes => { :user_id => session[:user].id }
              @article.tag 'events', :separator => ','
      				@article.local_events << event
      				i += 1
      			end
      		elsif params[:recurring_period] == 'recurring_month_day'
      			i = 0
      			while i < params[:local_event][:month_day].size
      				event = LocalEvent.new
      				event.event_type = params[:recurring_period]
      				event.recurrence = params[:local_event][:month_day][i].to_s
              #@article.tag 'events', :separator => ',', :attributes => { :user_id => session[:user].id }
              @article.tag 'events', :separator => ','
      				@article.local_events << event
      				i += 1
      			end
      		elsif params[:recurring_period] == 'recurring_month_index'
      			event = LocalEvent.new
      			event.event_type = params[:recurring_period]
      			event.recurrence = params[:local_event][:month_index].to_s
            #@article.tag 'events', :separator => ',', :attributes => { :user_id => session[:user].id }
            @article.tag 'events', :separator => ','
      			@article.local_events << event
      		end
      	end

        # Note that we can't allow the tag 'events' to be used willy-nilly, it must only come via the section.
        # So, it's this article has been tagged as with 'events', remove it here
        params[:tag] = params[:tags].sub( '\W+events,', '' )

        # Same goes for home life, health and family tags, so that we don't get duplicate tags
        params[:tag] = params[:tags].sub( '\W+home_life,', '' )
        params[:tag] = params[:tags].sub( '\W+health,', '' )
        params[:tag] = params[:tags].sub( '\W+family,', '' )
        
        #@article.tag params[:tags], :separator => ',', :attributes => { :user_id => session[:user].id } if params[:tags] != nil
        #@article.tag params[:article][:section], :separator => ',', :attributes => { :user_id => session[:user].id } if params[:article][:section] != nil
        @article.tag params[:tags], :separator => ',' if params[:tags] != nil
        @article.tag params[:article][:section], :separator => ',' if params[:article][:section] != nil
          
        # Make sure that the article link is a real link
        if @article.url !~ /^http:\/\/*/ix && @article.url != nil && @article.url != ''
            @article.url = 'http://' + @article.url
        end

        # Keep the titles unique within the place/county/country
	      if params[:place]
      		previous_articles = Article.find(  :all, 
    					  :conditions => [ 'normalised_title = ? AND countries.url_name = ? AND counties.url_name = ? AND places.url_name = ?', @article.normalised_title, params[:country], params[:county], params[:place] ],
    					  :include => [ :countries, :counties, :places, :comments, :user, :local_events, :article_notifications, :tags ]
					)
	      elsif params[:county]
      		previous_articles = Article.find(  :all, 
    					  :conditions => [ 'normalised_title = ? AND countries.url_name = ? AND counties.url_name = ?', @article.normalised_title, params[:country], params[:county] ],
    					  :include => [ :countries, :counties, :places, :comments, :user, :local_events, :article_notifications, :tags ]
					)
	      else
      		previous_articles = Article.find(  :all, 
    					  :conditions => [ 'normalised_title = ? AND countries.url_name = ?', @article.normalised_title, params[:country] ],
    					  :include => [ :countries, :counties, :places, :comments, :user, :local_events, :article_notifications, :tags ]
					)
	      end

        while previous_articles.size > 0
            @article.normalised_title += '_'
            previous_articles = Article.find_all_by_normalised_title( @article.normalised_title )
        end

        if @article.valid?
            @article.save

            # There's some weird bug that means the counties are only being added
            # if we do it here, so, we're doing it here
      	    if ( !params[:country] )
      		    params[:country] = 'uk'
      	    end

            @country = Country.find_by_url_name( params[:country] ) if params[:country]
            @article.countries << @country if params[:country]

            @county = County.find_by_url_name( params[:county] ) if params[:county]
            @article.counties << @county if params[:county]

            @place = Place.find_by_url_name( params[:place] ) if params[:place]
            @article.places << @place if params[:place]

            @article.save
		
            flash[:notice] = 'Article successfully created'

      	    self.clear_cache()
      	    #self.ping_technorati

            # Does the user want to be notified?
            if params[:notification]
              notification = ArticleNotification.new
              notification.article_id = @article.id
              notification.user_id = @article.user_id

              @article.article_notifications << notification
              @article.save
            end

            if @article.counties.empty? or @article.places.empty?
              redirect_to :controller => 'article', :action => 'show', :country => params[ :country ], :county => params[ :county ], :place => params[ :place ], :section => params[ :section ], :title => @article.normalised_title, :type => 'check_location'
            else
              redirect_to :controller => 'article', :action => 'show', :country => params[ :country ], :county => params[ :county ], :place => params[ :place ], :section => params[ :section ], :title => @article.normalised_title
            end
        else
            render :action => @session[ :create_action ]
        end
    end

    def edit_location
      @article = Article.find(params[:id])
      if params[:county] && params[:county][:search]
        county = County.find_by_name(params[:county][:search])
        unless county.nil?
          @article.counties = []
          @article.counties << county
        end
      end

      if params[:place] && params[:place][:search]
        place = Place.find_by_name(params[:place][:search])
        unless place.nil?
          @article.places = []
          @article.places << place
        end
      end

      @article.save

      flash[:notice] = "Article updated!"
      redirect_to params[:article_url]
    end

    def edit
      @country = Country.find_by_url_name( params[:country] ) if params[:country]
      @county = County.find_by_url_name( params[:county] ) if params[:county]
      @place = Place.find_by_url_name( params[:place] ) if params[:place]

      # The index templates uses a threaded comments patch taken from
      # http://scottstuff.net/blog/articles/2005/07/05/threaded-comments-in-typo

      if @place
	@article = Article.find(  :all, 
				  :conditions => [ 'normalised_title = ? AND countries.url_name = ? AND counties.url_name = ? AND places.url_name = ?', params[:title], @country.url_name, @county.url_name, @place.url_name ],
				  :include => [ :countries, :counties, :places, :comments, :user, :local_events, :article_notifications, :tags ]
				)
      elsif @county
	@article = Article.find(  :all, 
				  :conditions => [ 'normalised_title = ? AND countries.url_name = ? AND counties.url_name = ?', params[:title], @country.url_name, @county.url_name ],
				  :include => [ :countries, :counties, :places, :comments, :user, :local_events, :article_notifications, :tags ]
				)
      else
	@article = Article.find(  :all, 
				  :conditions => [ 'normalised_title = ? AND countries.url_name = ?', params[:title], @country.url_name ],
				  :include => [ :countries, :counties, :places, :comments, :user, :local_events, :article_notifications, :tags ]
				)
      end

      @article = @article[0]

    	if session[:user].nickname != 'suttree' && session[:user].nickname != 'jane' && @article.user_id != session[:user].id
    		if params[:county] && params[:section]
    			redirect_to :controller => 'article', :action => 'show', :country => params[ :country ], :county => params[ :county ], :section => params[ :section ], :title => @article.normalised_title
    		elsif params[:county]
    			redirect_to :controller => 'article', :action => 'show', :country => params[ :country ], :county => params[ :county ], :title => @article.normalised_title
    		elsif params[:section]
    			redirect_to :controller => 'article', :action => 'show', :country => params[ :country ], :section => params[ :section ], :title => @article.normalised_title
    		else
    			redirect_to :controller => 'article', :action => 'show', :country => params[ :country ], :title => @article.normalised_title
    		end
    	end

    	if @article.tagged_with?('events')
    		@is_event = true
    	end

      if params[:type] == 'check_location'
        render :action => 'check_location' and return
      end
    end

    def check_location
    	redirect_to '/' if session[:user].nickname != 'suttree' && session[:user].nickname != 'jane' && @article.user_id != session[:user].id
    end

    def edit_comment
      # Wee hack to support dodgy routing rules I've created for all this (title vs. id)
      @comment = Comment.find(params[:title])
      @article = Article.find(@comment.article_id)
      
      # Only the comment owner can edit the comment
      if @comment.user_id != session[:user].id
        redirect_to :controller => 'article', :action => 'show', :country => params[ :country ], :county => params[ :county ], :place => params[ :place ], :section => params[ :section ], :title => @article.normalised_title, :anchor => 'comment_' + comment.id.to_s
        return
      end
    end

    def update_comment
      # Wee hack to support dodgy routing rules I've created for all this (title vs. id)
      comment = Comment.find(params[:title])
      @article = Article.find(comment.article_id)

      if session[:user].id == session[:user].id

          params[:comment].each do | key, value |
            params[:comment][key] = sanitize(value, 'a href, b, br, i, p')
          end

          comment.title = params[:comment][:title]
          comment.body = params[:comment][:body]
          comment.save

	self.clear_cache()

        flash[:notice] = 'Comment succesfully edited'
      end

      redirect_to :controller => 'article', :action => 'show', :country => params[ :country ], :county => params[ :county ], :place => params[ :place ], :section => params[ :section ], :title => @article.normalised_title, :anchor => 'comment_' + comment.id.to_s
    end
      
    def update
      @article = Article.find_by_normalised_title( params[:title] )

    	if @article.user_id != session[:user].id
    		if params[:county] && params[:section]
    			redirect_to :controller => 'article', :action => 'show', :country => params[ :country ], :county => params[ :county ], :section => params[ :section ], :title => @article.normalised_title
    		elsif params[:county]
    			redirect_to :controller => 'article', :action => 'show', :country => params[ :country ], :county => params[ :county ], :title => @article.normalised_title
    		elsif params[:section]
    			redirect_to :controller => 'article', :action => 'show', :country => params[ :country ], :section => params[ :section ], :title => @article.normalised_title
    		else
    			redirect_to :controller => 'article', :action => 'show', :country => params[ :country ], :title => @article.normalised_title
    		end
    	end

      if @article.update_attributes(params[:article])
    		#@article.tag params[:tags], :clear => true, :separator => ',', :attributes => { :user_id => session[:user].id } if params[:tags] != nil
    		#@article.tag params[:article][:section], :separator => ',', :attributes => { :user_id => session[:user].id } if params[:article][:section] != nil
    		@article.tag params[:tags], :clear => true, :separator => ',' if params[:tags] != nil
    		@article.tag params[:article][:section], :separator => ',' if params[:article][:section] != nil

    		# Update any event data by resetting everything
    		# and adding in all the relevant times and dates
    		for event in @article.local_events
    			event.destroy
    		end

    		@article.local_events = {}

    		if params[:event_type] == 'one_off'

    			event = LocalEvent.new
    			event.event_type = 'one_off'
    			event.update_attributes(params[:local_events])
    			@article.local_events << event
    			@article.tag 'events'

    		elsif params[:event_type] == 'recurring'

    			# Store the recurrence dates in the database as just comma separated text
    			# Searching for matching events or displaying events will just require the creation of 
    			# Runt objects, rather than Marhsalling them in and out of the database
			
    			if params[:recurring_period] == 'recurring_week_month'
    				i = 0
    				while i < params[:local_event][:week].size
    					event = LocalEvent.new
    					event.event_type = params[:recurring_period]
    					event.recurrence = params[:local_event][:week][i].to_s + ',' + params[:local_event][:day][i]
    					@article.tag 'events'
    					@article.local_events << event
    					i += 1
    				end
    			elsif params[:recurring_period] == 'recurring_month_day'
    				i = 0
    				while i < params[:local_event][:month_day].size
    					event = LocalEvent.new
    					event.event_type = params[:recurring_period]
    					event.recurrence = params[:local_event][:month_day][i].to_s
    					@article.tag 'events'
    					@article.local_events << event
    					i += 1
    				end
    			elsif params[:recurring_period] == 'recurring_month_index'
    				event = LocalEvent.new
    				event.event_type = params[:recurring_period]
    				event.recurrence = params[:local_event][:month_index].to_s
    				@article.tag 'events'
    				@article.local_events << event
    			end
    		end
      
      	@article.save
	
	self.clear_cache()

      	flash[:notice] = 'Article successfully updated'

        redirect_to :controller => 'article', :action => 'show', :country => params[ :country ], :county => params[ :county ], :place => params[ :place ], :section => params[ :section ], :title => @article.normalised_title
      else
      	render :action => 'edit'
      end
    end

    def destroy
        Article.find(params[:id]).destroy
        redirect_to :action => 'list'
    end
    
  def show
      unless read_fragment 'show_article', :country => params[:country], :county => params[:county], :place => params[:place], :section => params[:section], :title => params[:title], :notice => flash[:notice], :logged_in => session[:logged_in], :user_id => session[:user].id, :admin => session[:user].admin?
	      @country = Country.find_by_url_name( params[:country] ) if params[:country]
	      @county = County.find_by_url_name( params[:county] ) if params[:county]
	      @place = Place.find_by_url_name( params[:place] ) if params[:place]

        # We won't be able to find any stories with titles that have anchors in them, like blah#comment_id
        # so strip that out now..
        params[:title] = params[:title].split('#')[0]

	      # The index templates uses a threaded comments patch taken from
	      # http://scottstuff.net/blog/articles/2005/07/05/threaded-comments-in-typo

	      if @place
		@article = Article.find(  :first, 
					  :conditions => [ 'normalised_title = ? AND countries.url_name = ? AND counties.url_name = ? AND places.url_name = ?', params[:title], @country.url_name, @county.url_name, @place.url_name ],
					  :include => [ :countries, :counties, :places, :comments, :user, :local_events, :article_notifications, :tags ]
					)
	      elsif @county
		@article = Article.find(  :first, 
					  :conditions => [ 'normalised_title = ? AND countries.url_name = ? AND counties.url_name = ?', params[:title], @country.url_name, @county.url_name ],
					  :include => [ :countries, :counties, :places, :comments, :user, :local_events, :article_notifications, :tags ]
					)
	      else
		@article = Article.find(  :first, 
					  :conditions => [ 'normalised_title = ? AND countries.url_name = ?', params[:title], @country.url_name ],
					  :include => [ :countries, :counties, :places, :comments, :user, :local_events, :article_notifications, :tags ]
					)
	      end

	      #@article = @article[0]

        @events = []
        for event in @article.local_events
          @events << event.recurrence.split(',')
        end

		@postcode = Postcode.find_by_postcode( @article.postcode )

		if @postcode != nil && @article.address != nil && @article.address != ''

			@map = GMap.new("map_div")
			@map.control_init(:large_map => true,:map_type => true)

			info_text = "<div style='width: 260px;'><b>" + @article.title + "</b><br/>" + @article.address + "<br/>" + @article.postcode + "</div>"

			@map.center_zoom_init([@postcode.longitude,@postcode.latitude],13)
			@map.overlay_init(GMarker.new(@article.address, :title => @article.title, :info_window => info_text))
			
		else
			@map = nil
		end
	end
  end

  def new_comment
      @country = Country.find_by_url_name( params[:country] ) if params[:country]
      @county = County.find_by_url_name( params[:county] ) if params[:county]
      @place = Place.find_by_url_name( params[:place] ) if params[:place]

      if @place
        @article = Article.find(  :all, 
                :conditions => [ 'normalised_title = ? AND countries.url_name = ? AND counties.url_name = ? AND places.url_name = ?', params[:title], @country.url_name, @county.url_name, @place.url_name ],
                :include => [ :countries, :counties, :places, :comments, :user, :local_events, :article_notifications, :tags ]
              )
      elsif @county
        @article = Article.find(  :all, 
                :conditions => [ 'normalised_title = ? AND countries.url_name = ? AND counties.url_name = ?', params[:title], @country.url_name, @county.url_name ],
                :include => [ :countries, :counties, :places, :comments, :user, :local_events, :article_notifications, :tags ]
              )
      else
        @article = Article.find(  :all, 
                :conditions => [ 'normalised_title = ? AND countries.url_name = ?', params[:title], @country.url_name ],
                :include => [ :countries, :counties, :places, :comments, :user, :local_events, :article_notifications, :tags ]
              )
      end

      @article = @article[0]

      if session[:user].id != nil
          # Not very DRY, yet..
          params[:comment].each do | key, value |
            params[:comment][key] = sanitize(value, 'a href, b, br, i, p, img, img src')
          end

          comment = Comment.new(params[:comment])
          comment.user_id = session[:user].id

          @article.comments << comment
          @article.save

          # Send notification emails to anyone who has requested them
          self.send_article_notifications @article, comment.id
          self.send_comment_notifications comment

          self.clear_cache
          
          flash[:notice] = 'Comment succesfully added'
      else
          # Anonymous comments....
          # Not very DRY, yet..
          params[:comment].each do | key, value |
            params[:comment][key] = sanitize(value, 'a href, b, br, i, p, img, img src')
          end

          user = User.find_by_nickname('Anonymous')
          comment = Comment.new(params[:comment])
          comment.user_id = user.id

          if is_spam?('Anonymous', params[:email], comment.body)
            flash[:notice] = "Sorry, but your comment looks like spam and has been rejected"
            redirect_to :controller => 'article', :action => 'show', :country => params[ :country ], :county => params[ :county ], :place => params[ :place ], :section => params[ :section ], :title => @article.normalised_title, :anchor => 'comment_' + comment.id.to_s
            return
          end

          @country = Country.find_by_url_name( params[:country] ) if params[:country]
          @county = County.find_by_url_name( params[:county] ) if params[:county]
          @place = Place.find_by_url_name( params[:place] ) if params[:place]

          if @place
            @article = Article.find(  :all, 
                    :conditions => [ 'normalised_title = ? AND countries.url_name = ? AND counties.url_name = ? AND places.url_name = ?', params[:title], @country.url_name, @county.url_name, @place.url_name ],
                    :include => [ :countries, :counties, :places, :comments, :user, :local_events, :article_notifications, :tags ]
                  )
          elsif @county
            @article = Article.find(  :all, 
                    :conditions => [ 'normalised_title = ? AND countries.url_name = ? AND counties.url_name = ?', params[:title], @country.url_name, @county.url_name ],
                    :include => [ :countries, :counties, :places, :comments, :user, :local_events, :article_notifications, :tags ]
                  )
          else
            @article = Article.find(  :all, 
                    :conditions => [ 'normalised_title = ? AND countries.url_name = ?', params[:title], @country.url_name ],
                    :include => [ :countries, :counties, :places, :comments, :user, :local_events, :article_notifications, :tags ]
                  )
          end

          @article = @article[0]
          @article.comments << comment
          @article.save

          # Anonymous comments don't get notifications sent out
          # in case they're spam, spam, spam
          self.clear_cache
          flash[:notice] = 'Comment succesfully added'
      end

      redirect_to :controller => 'article', :action => 'show', :country => params[ :country ], :county => params[ :county ], :place => params[ :place ], :section => params[ :section ], :title => @article.normalised_title, :anchor => 'comment_' + comment.id.to_s
  end

  def reply_to_comment
      # Wee hack to support dodgy routing rules I've created for all this (title vs. id)
      comment = Comment.find(params[:title])
      @article = Article.find(comment.article_id)
      @parent_comment = comment
  end

  def article_notification
    if !session[:logged_in] || session[:user].id == nil
      redirect_to '/'
    end

    # Ajax call for toggling article notifications
    # If the notification exists, delete it. Otherwise, create it.
    @article = Article.find(params[:article_id])
    notification = ArticleNotification.find_by_article_id_and_user_id( params[:article_id], session[:user].id )

    if notification == nil
      notification = ArticleNotification.new
      notification.article_id = params[:article_id]
      notification.user_id = session[:user].id

      @article.article_notifications << notification
      @article.save
    else
      notification.destroy
      notification.save
    end

    render :partial => 'partial/article_notification'
  end

  def comment_notification
    # Ajax call for toggling comment notifications
    # If the notification exists, delete it. Otherwise, create it.
    @comment = Comment.find(params[:comment_id])
    notification = CommentNotification.find_by_comment_id_and_user_id( params[:comment_id], session[:user].id )

    if notification == nil
      notification = CommentNotification.new
      notification.comment_id = params[:comment_id]
      notification.user_id = session[:user].id

      @comment.comment_notifications << notification
      @comment.save
    else
      notification.destroy
      notification.save
    end

    render :partial => 'partial/comment_notification'
  end

  def add_recurring_week_month
  	render :partial => 'partial/recurring_week_month'
  end

  # The following methods are for Yahoo Slurp which is crawling an old copy of the site
  # from who knows where, and causing thousands of alerts. So, redirect them all to the home page.
  def good_article
    redirect_to :controller => 'home', :action => 'index'
  end

  def bad_article
    redirect_to :controller => 'home', :action => 'index'
  end

  def good_comment
    redirect_to :controller => 'home', :action => 'index'
  end

  def bad_comment
    redirect_to :controller => 'home', :action => 'index'
  end

  def rss
    require 'parserss'

    rss_user = User.find_by_nickname('econews')

    uk = Country.find_by_url_name('uk')

    if params[:feed] == 'dartford'

    	kent = County.find_by_name('Kent')
    	dartford = Place.find_by_name('Dartford')
    	gravesend = Place.find_by_name('Gravesend')

	@rss_data = ParseRss.new('http://www.newsshopper.co.uk/news/dartgravenews/rss.xml').parse()
	self.parse_news_shopper_feed( @rss_data, rss_user, uk, kent, [ dartford, gravesend ] )

    elsif params[:feed] == 'bromley'

	kent = County.find_by_name('Kent')
	bromley = Place.find_by_name('Bromley')

	@rss_data = ParseRss.new('http://www.newsshopper.co.uk/news/bromnews/rss.xml').parse()
	self.parse_news_shopper_feed( @rss_data, rss_user, uk, kent, [ bromley ] )

    elsif params[:feed] == 'bexley'

	kent = County.find_by_name('Kent')
    	bexley = Place.find_by_name('Bexley')

    	@rss_data = ParseRss.new('http://www.newsshopper.co.uk/news/newsbexley/rss.xml').parse()
    	self.parse_news_shopper_feed( @rss_data, rss_user, uk, kent, [ bexley ] )

    elsif params[:feed] == 'greenpr'

    	greater_london = County.find_by_name('Greater London')
    	@rss_data = ParseRss.new('http://www.yourlocalguardian.co.uk/campaigns/gguardian/greenpressreleases/rss.xml').parse()
    	self.parse_news_shopper_feed( @rss_data, rss_user, uk, greater_london, [] )

    elsif params[:feed] == 'greentips'

    	greater_london = County.find_by_name('Greater London')
    	@rss_data = ParseRss.new('http://www.yourlocalguardian.co.uk/campaigns/gguardian/greentoptips/rss.xml').parse()
	self.parse_news_shopper_feed( @rss_data, rss_user, uk, greater_london, [] )

    end

    self.clear_cache
    self.ping_technorati
  end

  def report_spam
    if session[:user].admin?
      comment = Comment.find(params[:comment_id])
      submit_spam('Anonymous',comment.body)
      comment.destroy
      self.clear_cache
    end
    redirect_to :controller => 'home'
  end

  def ping_technorati
  	begin
  		server = XMLRPC::Client.new( "rpc.technorati.com", "/rpc/ping", 80 )

  		begin
  			result = server.call( "weblogUpdates.ping", "ecolocal", "http://ecolocal.com/index.xml" )
  		rescue XMLRPC::FaultException => e
  			puts "Error: [#{ e.faultCode }] #{ e.faultString }" 
  		end
  	rescue Exception => e
  		puts "Error: #{ e.message }" 
  	end
  end

  def report_article
    redirect_to '/'
  end
  
  # Ajax helper method
  def auto_complete_for_county_search
    @page_title = 'County search results'
    @counties = County.search(params[:county][:search], session[:locale])
    render_partial 'county_search_results'
  end

  # Ajax helper method
  def auto_complete_for_place_search
    @page_title = 'Place search results'
    @places = Place.search(params[:place][:search], session[:locale])
    render_partial 'place_search_results'
  end

  protected
  def normalise( name )
      name = name.downcase
      name = name.gsub( / /, '_' )
      name = name.gsub( /\W*/, '' )
      name = name[0..50]
  end

  def clear_cache( order = '' )
    # Just expire all fragments, rather than selecting which ones to get rid of.
    # Not very scalable, sadly, but it'll do for now..
    expire_fragment( Regexp.new('/*/') )
  end

  def send_article_notifications (article, comment_id)	
    for notification in article.article_notifications
      NotificationMailer.deliver_article notification.user, article, comment_id
    end
  end
  
  def send_comment_notifications (comment)
    if comment.parent != nil
      for notification in comment.parent.comment_notifications
        NotificationMailer.deliver_comment notification.user, comment, comment.id
      end
    end
  end

  def parse_news_shopper_feed( rss_data, rss_user, country, county, places )
    for item in rss_data['items']
	article = Article.find_by_title(item['title'])

	if ( !article )
		article = Article.new
		article.user_id = rss_user.id
		article.title = item['title']
		article.normalised_title = self.normalise( article.title )
		article.body = item['description']
		article.url = item['link']

		article.tag 'local news', :separator => ',', :attributes => { :user_id => 1 }

		article.countries << country
		article.counties << county

		for place in places
			article.places << place
		end

		article.save
	end
    end
  end
end
