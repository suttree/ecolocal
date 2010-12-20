class Register < ActiveRecord::Base
    set_table_name "users" 
    require 'md5'

    validates_presence_of :nickname, :forename, :surname, :email

    # Step 1
    validates_uniqueness_of :nickname, :email
    validates_format_of :nickname, :with => /^\w+$/, :message => "cannot contain whitespace"
    validates_length_of :nickname, :within => 3..20
    validates_length_of :password, :within => 4..32
    validates_length_of :forename, :within => 3..50
    validates_length_of :surname, :within => 3..50
    validates_format_of :email, :with => /^([^@\s]+)@((?:[-a-z0-9]+.)+[a-z]{2,})$/  
    validates_confirmation_of :password, :if => Proc.new { |user| user.signup_step == 1 }

    attr_accessor :signup_step

    def before_create
        self.password = MD5.new(self.password).to_s
    end

    def after_create
        @password = nil
    end
end
