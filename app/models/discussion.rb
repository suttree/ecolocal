class Discussion < ActiveRecord::Base
  acts_as_taggable
  belongs_to  :user
  has_many    :comments
  has_many    :discussion_notifications

  validates_presence_of   :title, :body, :user_id

  def self.find_total_by_tag( tag )
      total = find_by_sql(
      [
         'SELECT count(*) as count
         FROM discussions, tags, tags_discussions
         WHERE discussions.id = tags_discussions.discussion_id
         AND tags_discussions.tag_id = tags.id
         AND tags.name = ?',
         tag
      ]
      )
      return total[0].count.to_i
  end
end
