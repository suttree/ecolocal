require File.dirname(__FILE__) + '/../test_helper'

class ArticleRatingsTest < Test::Unit::TestCase
  fixtures :article_ratings

  # Replace this with your real tests.
  def test_truth
    assert_kind_of ArticleRatings, article_ratings(:first)
  end
end
