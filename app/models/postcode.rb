class Postcode < ActiveRecord::Base
   has_and_belongs_to_many :places
end
