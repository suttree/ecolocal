class Members::ProfileController < MembersController
  layout "main"

  def index
    @user = User.find(session[:user][:id])
  end

  def password
      @user = User.find(session[:user][:id])
  end

  def update
    @user = User.find(params[:id])
    if @user.update_attributes(params[:user])
      flash[:notice] = 'Profile successfully updated.'
      redirect_to :controller => '/members', :action => 'index'
    else
      render :action => 'index'
    end
  end

  def update_password
    @user = User.find(session[:user][:id])
    if @user.update_attributes( :password => MD5.new(params[:user][:password]).to_s )
      flash[:notice] = 'Password successfully changed.'
      redirect_to :action => 'index'
    else    
      render :action => 'password'
    end
  end
end
