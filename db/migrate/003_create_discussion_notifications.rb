class CreateDiscussionNotifications < ActiveRecord::Migration
  def self.up
    create_table :discussion_notifications do |t|
      # t.column :name, :string
    end
  end

  def self.down
    drop_table :discussion_notifications
  end
end
