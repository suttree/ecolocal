# Filters added to this controller will be run for all controllers in the application.
# Likewise, all the methods added will be available for all controllers.
class ApplicationController < ActionController::Base
  before_filter :remember_me
  before_filter :set_subdomain
  before_filter :set_locale

  # The following from http://wiki.rubyonrails.org/rails/pages/HowtoSendEmailWhenRailsThrowsAnException
  protected  

  def log_error(exception) 
    super(exception)

    #begin   
    #  ErrorMailer.deliver_snapshot(
    #    exception, 
    #    clean_backtrace(exception), 
    #    @session.instance_variable_get("@data"), 
    #    @params, 
    #    @request.env)
    #rescue => e
    #  logger.error(e)
    #end
  end

  # From http://wiki.rubyonrails.com/rails/pages/HowtoConfigureTheErrorPageForYourRailsApp
  def rescue_action_in_public(exception)
    render :template => '500'
  end

  # For testing in development
  def local_request?
    false   
  end

  def remember_me
    #logger.info ">>>>"
    #logger.info request.user_agent.downcase
    #logger.info "<<<<"

    if session[:user] != nil
      if session[:user].id == nil
        session[:logged_in] = false
      else
        session[:logged_in] = true
      end
      return
    elsif cookies[:remember_me]
        if rm = Session.find(   :first, :conditions => [ "remember_me = ?", cookies[:remember_me] ] )
            if session[:user] = User.find_by_id( rm.user_id )
              session[:logged_in] = true
              return
            else
              session[:user] = NullUser.new
              session[:logged_in] = false
              old_sessions = Session.find( :all, :conditions => [ "remember_me = ?", cookies[:remember_me] ] )
              for old_session in old_sessions
                old_sessions.destroy
              end

              cookies[:remember_me] = { :domain => '.ecolocal.com', :value => nil, :expires => Time.now }
              cookies[:remember_me] = { :domain => '.ecolocal.co.uk', :value => nil, :expires => Time.now }
            end
        else
            session[:user] = NullUser.new 
            session[:logged_in] = false
            old_sessions = Session.find( :all, :conditions => [ "remember_me = ?", cookies[:remember_me] ] )

            for old_session in old_sessions
              old_sessions.destroy
            end

            cookies[:remember_me] = { :domain => '.ecolocal.com', :value => nil, :expires => Time.now }
            cookies[:remember_me] = { :domain => '.ecolocal.co.uk', :value => nil, :expires => Time.now }
        end
    else
        session[:user] = NullUser.new
        session[:logged_in] = false
        cookies[:remember_me] = { :domain => '.ecolocal.com', :value => nil, :expires => Time.now }
        cookies[:remember_me] = { :domain => '.ecolocal.co.uk', :value => nil, :expires => Time.now }
    end
  end

  def set_subdomain
    # From http://mikeburnscoder.wordpress.com/2006/06/14/subdomains-as-account-keys/
    # and http://mentalized.net/journal/2005/05/18/notetagger_adding_users_and_subdomains/
    sd = request.subdomains.first
    unless sd.blank? || sd == 'www' || sd == 'ecolocal'
        @subdomain = sd
    else
      	@subdomain = 'uk'
    end
  end

  def set_locale
    if session[:locale] == nil
      session[:locale] = 'uk'
    end
  end

  def normalise( name )
    # Munges a string into a wiki-linkable one
    name = name.downcase
    name = name.gsub( / /, '_' )
    name = name.gsub( /\W*/, '' )
  end

  def de_normalise( name )
    # Reverse the nomalisation process
    name = name.gsub( /_/, ' ' )
  end

  def is_spam?(author, email, text)
    @akismet = Akismet.new('8443a36f14be', 'http://www.ecolocal.com')

    # return true when API key isn't valid, YOUR FAULT!!
    return true unless @akismet.verifyAPIKey 

    # will return false, when everthing is ok and true when Akismet thinks the comment is spam. 
    return @akismet.commentCheck(
              request.remote_ip,            # remote IP
              request.user_agent,           # user agent
              request.env['HTTP_REFERER'],  # http referer
              '',                           # permalink
              'comment',                    # comment type
              author,                       # author name
              email,                        # author email
              '',                           # author url
              text,                         # comment text
              {})                           # other 
  end

  def submit_spam(author, text)
    @akismet = Akismet.new('8443a36f14be', 'http://www.ecolocal.com')

    # return true when API key isn't valid, YOUR FAULT!!
    return true unless @akismet.verifyAPIKey 

    return @akismet.submitSpam(
              request.remote_ip,            # remote IP
              request.user_agent,           # user agent
              request.env['HTTP_REFERER'],  # http referer
              '',                           # permalink
              'comment',                    # comment type
              author,                       # author name
              '',                           # author email
              '',                           # author url
              text,                         # comment text
              {})                           # other 
  end
end
