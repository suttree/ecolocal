class RegisterMailer < ActionMailer::Base

  def activate(register)
    @subject    = 'Activate your ecolocal account'
    @recipients = register.email
    @from       = 'noreply@ecolocal.com'
    @body['register'] = register 
  end

  def welcome(register)
    @subject    = 'Welcome to ecolocal'
    @recipients = register.email
    @from       = 'noreply@ecolocal.com'
    @body['register'] = register 
  end

  def forgotten_password(user, password)
    @subject    = 'Your new ecolocal password'
    @recipients = user.email
    @from       = 'noreply@ecolocal.com'
    @body['user'] = user 
    @body['password'] = password 
  end
end
