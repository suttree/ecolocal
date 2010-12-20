require File.dirname(__FILE__) + '/../test_helper'

class AudioTest < Test::Unit::TestCase
  fixtures :audios

  # Replace this with your real tests.
  def test_truth
    assert_kind_of Audio, audios(:first)
  end
end
