class HelpMailer < ActionMailer::Base
	def contact( message, email )
		@recipients = 'duncan@suttree.com,jane@ecolocal.com'
		@from = "ecolocal Contact <contact@ecolocal.com>"
		@subject = "ecolocal Contact" 

		@body[ "message" ] = message
    @body[ "email" ] = email
	end
end
