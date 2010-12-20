class XmlController < ApplicationController
	after_filter :compress_output

	def rss
    @articles = Article.find( :all, 
                              :include => [ :countries, :counties, :places, :comments, :user, :local_events, :article_notifications, :tags ],
                							:limit  =>  10,
                							:offset =>  0,
                              :order => 'articles.created_on DESC'
                            )

    @discussions = Discussion.find( :all,
                                    :limit => 10,
                                    :order => 'created_on DESC'
                                  )

		render :layout => false
	end
end
