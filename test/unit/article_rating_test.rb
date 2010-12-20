require File.dirname(__FILE__) + '/../test_helper'

class ArticleRatingTest < Test::Unit::TestCase
  fixtures :article_ratings

  # Replace this with your real tests.
  def test_truth
    assert_kind_of ArticleRating, article_ratings(:first)
  end
end
