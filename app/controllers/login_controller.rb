class LoginController < ApplicationController
	after_filter :compress_output
end
