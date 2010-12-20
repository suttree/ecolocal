class CreateDiscusses < ActiveRecord::Migration
  def self.up
    create_table :discusses do |t|
      # t.column :name, :string
    end
  end

  def self.down
    drop_table :discusses
  end
end
