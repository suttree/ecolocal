require File.dirname(__FILE__) + '/../test_helper'

class SessionTest < Test::Unit::TestCase
  fixtures :sessions

  # Replace this with your real tests.
  def test_truth
    assert_kind_of Session, sessions(:first)
  end
end
