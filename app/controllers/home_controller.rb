class HomeController < ApplicationController
	layout "main"

	def index
		@latest_events = Article.find_tagged_with :any => 'events', :order => 'created_on DESC, updated_on DESC', :limit => 5
    @latest_articles = Article.find :all, :include => :user, :conditions => "users.nickname != 'econews'", :order => 'articles.updated_on DESC', :limit => 5
		@latest_discussions = Discussion.find :all, :order => 'updated_on DESC', :limit => 5
	end

  def euro
		@latest_events = Article.find_tagged_with :any => 'events', :order => 'created_on DESC', :limit => 5
    @latest_articles = Article.find :all, :include => :user, :conditions => "users.nickname != 'econews'", :order => 'articles.updated_on DESC', :limit => 5
		@latest_discussions = Discussion.find :all, :order => 'updated_on DESC', :limit => 5
  end

	  def locale
	    if params[:locale] == 'gb'
	      session[:locale] = 'uk'
	    else
	      session[:locale] = params[:locale]
	    end

	    redirect_to :controller => 'home', :action => 'index', :locale => 'updated'
	  end
end
