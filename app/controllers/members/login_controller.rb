class Members::LoginController < ApplicationController
    layout "main"

    def login
        render :action => "login"
    end

    def validate
        if @session[:user] = User.find( :first,
                                        :conditions => [ "nickname = ? and password = MD5( ? )", @params[:login][:nickname], @params[:login][:password] ] )
	    # Remember me
	    rm = Session.new
	    rm.user_id = @session[:user].id

	    while !rm.valid?
	        # The following from http://www.bigbold.com/snippets/posts/show/491
	        chars = ("a".."z").to_a + ("A".."Z").to_a + ("1".."9").to_a
	        code = ''
	        1.upto(32) { |i| code << chars[rand(chars.size-1)] }
	        rm.remember_me = code
	    end

	    rm.save
	    cookies[:remember_me] = { :domain => '.ecolocal.com', :value => code, :expires => Time.now + ( 90 * (60*60*24) ) }
	    cookies[:remember_me] = { :domain => '.ecolocal.co.uk', :value => code, :expires => Time.now + ( 90 * (60*60*24) ) }
                
	    redirect_to :controller => "/members"
        else
            flash[:note] = 'There was a problem logging in. Please check your details and try again'
            redirect_to :action => "index"
        end
    end

    def logout
        session[:user] = nil
        cookies[:remember_me] = { :domain => '.ecolocal.com', :value => nil, :expires => Time.now }
        cookies[:remember_me] = { :domain => '.ecolocal.co.uk', :value => nil, :expires => Time.now }
        redirect_to :controller => "/"
    end

    def forgotten_password
      # Just a view
    end

    def forgotten_password_sent
      # Just a view
    end

    def send_forgotten_password
      user = User.find_by_email(params[:email]) 

      if user
        chars = ("a".."z").to_a + ("A".."Z").to_a + ("1".."9").to_a
        code = ''
        1.upto(8) { |i| code << chars[rand(chars.size-1)] }
        user.password = MD5.new(code).to_s
        user.save(false)

        RegisterMailer.deliver_forgotten_password(user, code)

        flash[:note] = "A new password has been sent to your email address"
        redirect_to :action => "forgotten_password_sent"
      else
        flash[:note] = "There was a problem resetting your password, please try again or <a href='http://www.ecolocal.com/help/contact'>contact us for more help</a>"
        redirect_to :action => "forgotten_password_sent"
      end
    end
end
