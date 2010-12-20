class RegisterController < ApplicationController
    layout "main"
    before_filter :setup_negative_captcha, :only => [:index, :validate_step1]

    def index
        session[:register] = nil
        @register = Register.new
        render :action => "index"
    end

    def validate_step1
        #@register = Register.new(params[:register])
logger.info @captcha.inspect
logger.info "---"
        @register = Register.new(@captcha.values)

        if @register.valid? && @captcha.valid?
            session[:register] = @register
            redirect_to :action => 'done'
        else
            flash[:notice] = @captcha.error if @captcha.error
            render :action => 'index'
        end
    end

    def done
        @register = Register.new(params[:register])
        @register.nickname = session[:register].nickname
        @register.forename = session[:register].forename
        @register.surname = session[:register].surname
        @register.email = session[:register].email
        @register.password = session[:register].password

        # The following from http://www.bigbold.com/snippets/posts/show/491
        chars = ("a".."z").to_a + ("A".."Z").to_a + ("1".."9").to_a
        activation_code = ""
        1.upto(10) { |i| activation_code << chars[rand(chars.size-1)] }
        @register.activation_code = activation_code

        @register.save

        RegisterMailer.deliver_activate(@register)

        redirect_to :action => 'thankyou'
    end

    def thankyou
	@page_title = 'Thank You'
        render :action => 'thankyou'
    end

    def activate
        @register = Register.find_by_activation_code(params[:id])
        @register.activation_date = Time.now
        
	@register.save

        if @register.valid?
            RegisterMailer.deliver_welcome(@register)
            redirect_to :action => 'welcome'
        else
            render :action => 'activate'
        end

    end

    def welcome
      @page_title = 'Welcome to ecolocal'
    end

    private
      def setup_negative_captcha
        @captcha = NegativeCaptcha.new(
          :secret => NEGATIVE_CAPTCHA_SECRET, #A secret key entered in environment.rb.  'rake secret' will give you a good one.
          :spinner => request.remote_ip, 
          :fields => [:nickname, :forename, :surname, :email, :password], #Whatever fields are in your form 
          :params => params)
      end
end
