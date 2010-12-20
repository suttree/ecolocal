class LocationController < ApplicationController
    layout 'main'
    #after_filter :compress_output

    def country
      	unless read_fragment :country => params[:country], :page => params[:page], :order => params[:order], :logged_in => session[:logged_in]
      		@article_pages = Paginator.new self, Article.count, 10, params[:page]

          @article = Article.find(  :all, 
                                    :conditions => [ 'countries.url_name = ?', params[:country] ],
                                    :include => [ :countries, :counties, :places, :comments, :user, :local_events, :article_notifications, :tags ],
                      							:limit  =>  @article_pages.items_per_page,
                      							:offset =>  @article_pages.current.offset,
                                    :order => 'articles.created_on DESC'
                                  )
      	end

        render :action => 'list'
    end

    def county
      	unless read_fragment :country => params[:country], :county => params[:county], :page => params[:page], :order => params[:order], :logged_in => session[:logged_in]

      		@county = County.find_by_url_name( params[:county] )

      		total = Article.find_total_by_county( @county.id )

      		@article_pages = Paginator.new self, total, 10, @params[ 'page' ]
      		
          @article = Article.find(  :all, 
                                    :conditions => [ 'countries.url_name = ? AND counties.url_name = ?', params[:country], params[:county] ],
                                    :include => [ :countries, :counties, :places, :comments, :user, :local_events, :article_notifications, :tags ],
                      							:limit  =>  @article_pages.items_per_page,
                      							:offset =>  @article_pages.current.offset,
                                    :order => 'articles.created_on DESC'
                                  )
          end

        render :action => 'list'
    end

    def place
      	unless read_fragment :country => params[:country], :county => params[:county], :place => params[:place], :page => params[:page], :order => params[:order], :logged_in => session[:logged_in]

      		@place = Place.find_by_url_name( params[:place] )

			    total = Article.find_total_by_place( @place.id )

			    @article_pages = Paginator.new self, total, 10, @params[ 'page' ]

          @article = Article.find(  :all, 
                                    :conditions => [ 'countries.url_name = ? AND counties.url_name = ? AND places.url_name = ?', params[:country], params[:county], params[:place] ],
                                    :include => [ :countries, :counties, :places, :comments, :user, :local_events, :article_notifications, :tags ],
                      							:limit  =>  @article_pages.items_per_page,
                      							:offset =>  @article_pages.current.offset,
                                    :order => 'articles.created_on DESC'
                                  )
          end

        render :action => 'list'
    end

    def country_tag
      	unless read_fragment :country => params[:country], :tag => params[:tag], :page => params[:page], :logged_in => session[:logged_in]

      		total = Article.find_total_by_tag( params[:tag] )

      		@article_pages = Paginator.new self, total, 10, @params['page']

      		@article = Article.find_tagged_with( 
                                  		          :any => [ params[:tag] ],
                                                :include => [ :countries, :counties, :places, :comments, :user, :local_events, :article_notifications, :tags ],
                                  							:limit  =>  @article_pages.items_per_page,
                                  							:offset =>  @article_pages.current.offset
                                  						)
      	end

        render :action => 'list'
    end

    def county_tag
      	unless read_fragment :country => params[:country], :county => params[:county], :tag => params[:tag], :page => params[:page], :logged_in => session[:logged_in]

      		@county = County.find_by_url_name( params[:county] )

      		total = Article.find_total_by_tag_and_county( params[:tag], @county.id )

      		@article_pages = Paginator.new self, total, 10, @params['page']

      		@article = Article.find_tagged_with(
                                  			        :any => [ params[:tag] ],
                                                :include => [ :countries, :counties, :places, :comments, :user, :local_events, :article_notifications, :tags ],
                                  							:condition => [ 'county_id = ?', @county.id ],
                                  							:limit  =>  @article_pages.items_per_page,
                                  							:offset =>  @article_pages.current.offset
                                  						)
      	end

        render :action => 'list'
    end

    def place_tag
      	unless read_fragment :country => params[:country], :county => params[:county], :place => params[:place], :tag => params[:tag], :page => params[:page], :logged_in => session[:logged_in]

      		@county = County.find_by_url_name( params[:county] )

      		total = Article.find_total_by_tag_and_county( params[:tag], @county.id )

      		@article_pages = Paginator.new self, total, 10, @params['page']

      		@article = Article.find_tagged_with(
                                  			        :any => [ params[:tag] ],
                                                :include => [ :countries, :counties, :places, :comments, :user, :local_events, :article_notifications, :tags ],
                                  							:condition => [ 'place_id = ?', @county.id ],
                                  							:limit  =>  @article_pages.items_per_page,
                                  							:offset =>  @article_pages.current.offset
                                  						)
      	end

        render :action => 'list'
    end
end
