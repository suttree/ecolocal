class DiscussionController < ApplicationController
  layout 'main'

  def index
    self.list
  end
  
  def list  
  	unless read_fragment 'discuss', :page => params[:page], :logged_in => session[:logged_in]
  		@discussion_pages = Paginator.new self, Discussion.count, 10, params[:page]

      @discussions = Discussion.find( :all, 
                                      :include => [ :comments, :user, :discussion_notifications, :tags ],
                        							:limit  =>  @discussion_pages.items_per_page,
                        							:offset =>  @discussion_pages.current.offset,
                                      :order => 'discussions.created_on DESC'
                                    )
  	end

    render :action => 'list'
  end

  def tag_list
    total = Discussion.find_total_by_tag( params[:tag] )

    @discussion_pages = Paginator.new self, total, 10, @params['page']

    @discussions = Discussion.find_tagged_with( 
                                          :any => [ params[:tag] ],
                                          :include => [ :comments, :user, :tags ],
                                          :limit  =>  @discussion_pages.items_per_page,
                                          :offset =>  @discussion_pages.current.offset
                                        )

    render :action => 'tag_list'
  end

  def edit
    @discussion = Discussion.find(  :first, 
                                    :conditions => [ 'normalised_title = ?', params[:title] ],
                                    :include => [ :comments, :user, :discussion_notifications, :tags ],
                                    :order => 'discussions.created_on DESC'
                                  )
  end

  def new
    @discussion = Discussion.new
  end
  
  def save
    @discussion = Discussion.new

    # Whitelist the incoming data
    params[:discussion].each do | key, value |
      params[:discussion][key] = sanitize(value, 'a href, b, br, i, p')
    end

    params[:tags] = sanitize(params[:tags], 'a href, b, br, i, p')

    @discussion.title = params[:discussion][:title]
    @discussion.body = params[:discussion][:body]
    @discussion.url = params[:discussion][:url]

    @discussion.user_id = session[:user].id
    @discussion.normalised_title = self.normalise( @discussion.title )

    #@discussion.tag params[:tags], :separator => ',', :attributes => { :user_id => session[:user].id } if params[:tags] != nil
    @discussion.tag params[:tags], :separator => ',' if params[:tags] != nil

    # Make sure that the discussion link is a real link
    if @discussion.url !~ /^http:\/\/*/ix && @discussion.url != nil && @discussion.url != ''
        @discussion.url = 'http://' + @discussion.url
    end

    # Keep the titles unique
		previous_discussions = Discussion.find_all_by_normalised_title( @discussion.normalised_title )

    while previous_discussions.size > 0
        @discussion.normalised_title += '_'
        previous_discussions = Discussion.find_all_by_normalised_title( @discussion.normalised_title )
    end

    if @discussion.valid?
        @discussion.save

        flash[:notice] = 'Discussion successfully created'

  	    self.clear_cache()
  	    self.ping_technorati

        # Does the user want to be notified?
        if params[:notification]
          notification = DiscussionNotification.new
          notification.discussion_id = @discussion.id
          notification.user_id = @discussion.user_id

          @discussion.discussion_notifications << notification
          @discussion.save
        end

        redirect_to :controller => 'discussion', :action => 'show', :section => 'discuss', :title => @discussion.normalised_title

    else
        render :action => 'new'
    end
  end

  def show
  	unless read_fragment 'show_discussion', :titile => params[:title], :notice => flash[:notice], :logged_in => session[:logged_in], :user_id => session[:user].id, :admin => session[:user].admin?
      @discussion = Discussion.find(  :first, 
                                      :conditions => [ 'normalised_title = ?', params[:title] ],
                                      :include => [ :comments, :user, :discussion_notifications, :tags ],
                                      :order => 'discussions.created_on DESC'
                                    )
    end
  end



  def new_comment
      if @session[:user].id
          # Not very DRY, yet..
          params[:comment].each do | key, value |
            params[:comment][key] = sanitize(value, 'a href, b, br, i, p, img')
          end

          comment = Comment.new(params[:comment])
          comment.user_id = session[:user].id

          @discussion = Discussion.find_by_normalised_title( params[:title] )
          @discussion.comments << comment
          @discussion.save

          # Send notification emails to anyone who has requested them
          self.send_discussion_notifications @discussion, comment.id
          self.send_comment_notifications comment
          
          flash[:notice] = 'Comment succesfully added'
      else
          # Testing anonymous comments
          params[:comment].each do | key, value |
            params[:comment][key] = sanitize(value, 'a href, b, br, i, p, img')
          end

          user = User.find_by_nickname('Anonymous')
          comment = Comment.new(params[:comment])
          comment.user_id = user.id

          @discussion = Discussion.find_by_normalised_title( params[:title] )

          if is_spam?('Anonymous', comment.body)
            flash[:notice] = "Sorry, but your comment looks like spam and has been rejected"
            redirect_to :controller => 'discussion', :action => 'show', :section => 'discuss', :title => @discussion.normalised_title, :anchor => 'comment_' + comment.id.to_s
            return
          end

          @discussion.comments << comment
          @discussion.save

          # Anonymous comments don't get notifications sent out
          # in case they're spam, spam, spam
          self.clear_cache
          flash[:notice] = 'Comment succesfully added'
      end

      redirect_to :controller => 'discussion', :action => 'show', :section => 'discuss', :title => @discussion.normalised_title, :anchor => 'comment_' + comment.id.to_s
  end

  def reply_to_comment
      # Wee hack to support dodgy routing rules I've created for all this (title vs. id)
      comment = Comment.find(params[:title])
      @discussion = Discussion.find( comment.discussion_id )
      @parent_comment = comment
  end

  def edit_comment
    # Wee hack to support dodgy routing rules I've created for all this (title vs. id)
    @comment = Comment.find(params[:title])
    @discussion = Discussion.find(@comment.discussion_id)
    
    # Only the comment owner can edit the comment
    if @comment.user_id != session[:user].id
      redirect_to :controller => 'discussion', :action => 'show', :title => @discussion.normalised_title, :anchor => 'comment_' + comment.id.to_s
      return
    end
  end

  def update_comment
    # Wee hack to support dodgy routing rules I've created for all this (title vs. id)
    comment = Comment.find( params[:title] )
    @discussion = Discussion.find( comment.discussion_id )

    if @session[:user].id == session[:user].id

        params[:comment].each do | key, value |
          params[:comment][key] = sanitize(value, 'a href, b, br, i, p')
        end

        comment.title = params[:comment][:title]
        comment.body = params[:comment][:body]
        comment.save

	self.clear_cache()

      flash[:notice] = 'Comment succesfully edited'
    end

    redirect_to :controller => 'discussion', :action => 'show', :title => @discussion.normalised_title, :anchor => 'comment_' + comment.id.to_s
  end

  def update
    @discussion = Discussion.find_by_normalised_title( params[:title] )

  	if @discussion.user_id != session[:user].id
			redirect_to :controller => 'discussion', :action => 'show', :title => @discussion.normalised_title
  	end

    if @discussion.update_attributes( params[:discussion] )
  		#@discussion.tag params[:tags], :clear => true, :separator => ',', :attributes => { :user_id => session[:user].id } if params[:tags] != nil
  		@discussion.tag params[:tags], :clear => true, :separator => ',' if params[:tags] != nil
    
    	@discussion.save

	self.clear_cache()

    	flash[:notice] = 'Discussion successfully updated'

      redirect_to :controller => 'discussion', :action => 'show', :title => @discussion.normalised_title
    else
    	render :action => 'edit'
    end
  end

  def discussion_notification
    # Ajax call for toggling article notifications
    # If the notification exists, delete it. Otherwise, create it.
    @discussion = Discussion.find(params[:discussion_id])
    notification = DiscussionNotification.find_by_discussion_id_and_user_id( params[:discussion_id], session[:user].id )

    if notification == nil
      notification = DiscussionNotification.new
      notification.discussion_id = params[:discussion_id]
      notification.user_id = session[:user].id

      @discussion.discussion_notifications << notification
      @discussion.save
    else
      notification.destroy
      notification.save
    end

    render :partial => 'partial/discussion_notification'
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

    render :partial => 'partial/comment_notification_discussion'
  end

  protected
  def normalise( name )
      name = name.downcase
      name = name.gsub( / /, '_' )
      name = name.gsub( /\W*/, '' )
      name = name[0..50]
  end

  def clear_cache()
    # Just expire all fragments, rather than selecting which ones to get rid of.
    # Not very scalable, sadly, but it'll do for now..
    expire_fragment( Regexp.new('/*/') )
  end

  def ping_technorati
  	begin
  		server = XMLRPC::Client.new( "rpc.technorati.com", "/rpc/ping", 80 )

  		begin
  			result = server.call( "weblogUpdates.ping", "ecolocal", "http://ecolocal.co.uk/index.xml" )
  		rescue XMLRPC::FaultException => e
  			puts "Error: [#{ e.faultCode }] #{ e.faultString }" 
  		end
  	rescue Exception => e
  		puts "Error: #{ e.message }" 
  	end
  end
  
  def send_discussion_notifications (discussion, comment_id)	
    for notification in discussion.discussion_notifications
      NotificationMailer.deliver_discussion notification.user, discussion, comment_id
    end
  end
  
  def send_comment_notifications (comment)
    if comment.parent != nil
      for notification in comment.parent.comment_notifications
        NotificationMailer.deliver_comment notification.user, comment, comment.id
      end
    end
  end
end
