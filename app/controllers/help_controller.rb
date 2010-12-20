class HelpController < ApplicationController
	layout "main"
	after_filter :compress_output

	def index
	end

	def about
		@page_title = 'About'
	end

	def contact
		@page_title = 'Contact'
	end

	def contact_send
		HelpMailer::deliver_contact(params[:message], params[:email])
		redirect_to :action => 'thankyou'
	end

	def thankyou
		@page_title = 'Thank you'
	end

	def terms
		@page_title = 'Terms & Conditions'
	end
end
