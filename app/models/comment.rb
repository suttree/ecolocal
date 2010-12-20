class Comment < ActiveRecord::Base
    belongs_to  :user
    belongs_to  :article
    belongs_to  :discussion
    has_many    :comment_ratings
    has_many    :comment_notifications
    acts_as_tree :order => "id"

    validates_presence_of   :user_id, :title, :body
end