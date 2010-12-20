class User < ActiveRecord::Base
    validates_presence_of :nickname, :password, :email, :forename, :surname
    validates_uniqueness_of :nickname, :email
    validates_length_of :nickname, :within => 2..20
    validates_length_of :password, :within => 4..32
    validates_format_of :email, :with => /^([^@\s]+)@((?:[-a-z0-9]+.)+[a-z]{2,})$/

    def admin?
      return self.admin
    end
end
