class CreateLocalEvents < ActiveRecord::Migration
  def self.up
    create_table :local_events do |t|
      # t.column :name, :string
    end
  end

  def self.down
    drop_table :local_events
  end
end
