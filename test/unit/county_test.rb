require File.dirname(__FILE__) + '/../test_helper'

class CountyTest < Test::Unit::TestCase
  fixtures :counties

  # Replace this with your real tests.
  def test_truth
    assert_kind_of County, counties(:first)
  end
end
