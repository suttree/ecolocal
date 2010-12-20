require File.dirname(__FILE__) + '/../test_helper'

class CommentRatingsTest < Test::Unit::TestCase
  fixtures :comment_ratings

  # Replace this with your real tests.
  def test_truth
    assert_kind_of CommentRatings, comment_ratings(:first)
  end
end
