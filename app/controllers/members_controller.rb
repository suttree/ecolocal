class MembersController < ApplicationController
    layout "main"
    after_filter :compress_output
    before_filter :authenticate, :except => [ :index, :login, :validate_login ]

    def index
      if params[:nickname] == nil && session[:user]
        @user = User.find( session[:user][:id] )
      elsif params[:nickname]
        @user = User.find_by_nickname( params[:nickname] )
      else
        redirect_to :controller => "/members/login"
        return false
      end
      
      @page_title = @user.nickname

      @articles = Article.find( :all, 
                                :conditions => [ 'articles.user_id = ?', @user.id ],
                                :include => [ :countries, :counties, :places, :comments, :user, :local_events, :article_notifications, :tags ],
                                :order => 'articles.created_on DESC'
                              )

      @discussions = Discussion.find( :all, 
                                :conditions => [ 'discussions.user_id = ?', @user.id ],
                                :include => [ :comments, :user, :discussion_notifications, :tags ],
                                :order => 'discussions.created_on DESC'
                              )

      @comments = Comment.find( :all,
                                :conditions => [ 'comments.user_id = ?', @user.id ],
                                :include => [ :user, :article, :discussion ],
                                :order => 'comments.created_on DESC'
                              )

      @comment_articles = []
      for comment in @comments
        @comment_articles << comment.article
      end

    end

    # From http://www.37signals.com/rails/wiki/Authentication.html
    protected
    def authenticate
        unless @session[:user]
            redirect_to :controller => "/members/login"
            return false
        end
    end
end
