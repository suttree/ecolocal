require File.dirname(__FILE__) + '/../test_helper'
require 'create_controller'

# Re-raise errors caught by the controller.
class CreateController; def rescue_action(e) raise e end; end

class CreateControllerTest < Test::Unit::TestCase
  def setup
    @controller = CreateController.new
    @request    = ActionController::TestRequest.new
    @response   = ActionController::TestResponse.new
  end

  # Replace this with your real tests.
  def test_truth
    assert true
  end
end
