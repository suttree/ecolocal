class GroupsController < ApplicationController
  layout "main"

  def index
  end
  
  def create
    @group = Group.new
  end

  def list
    @groups = Group.find_all
    @group_pages = Paginator.new self, Group.count, 10, @params['page']
  end
end
