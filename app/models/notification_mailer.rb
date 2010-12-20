class NotificationMailer < ActionMailer::Base

  def article (user, article, comment_id)
    @subject    = 'A reply has been posted to your article ' + article.title
    @recipients = user.email
    @from       = 'noreply@ecolocal.com'
    @body['user'] = user 
    @body['article'] = article
    @body['comment_id'] = comment_id.to_s
  end

  def discussion (user, discussion, comment_id)
    @subject    = 'A reply has been posted to your discussion ' + discussion.title
    @recipients = user.email
    @from       = 'noreply@ecolocal.com'
    @body['user'] = user 
    @body['discussion'] = discussion 
    @body['comment_id'] = comment_id.to_s
  end

  def comment (user, comment, comment_id)
    @subject    = 'A reply has been posted to your comment ' + comment.title
    @recipients = user.email
    @from       = 'noreply@ecolocal.com'
    @body['user'] = user 
    @body['comment' ] = comment
    @body['comment_id'] = comment_id.to_s
  end
end
