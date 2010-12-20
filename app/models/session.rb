class Session < ActiveRecord::Base
    validates_presence_of   :remember_me, :user_id
    validates_uniqueness_of :remember_me
end
