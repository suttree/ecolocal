require File.dirname(__FILE__) + '/../test_helper'

class RegisterTest < Test::Unit::TestCase
  fixtures :registers

  # Replace this with your real tests.
  def test_truth
    assert_kind_of Register, registers(:first)
  end
end
