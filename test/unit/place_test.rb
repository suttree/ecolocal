require File.dirname(__FILE__) + '/../test_helper'

class PlaceTest < Test::Unit::TestCase
  fixtures :places

  # Replace this with your real tests.
  def test_truth
    assert_kind_of Place, places(:first)
  end
end
