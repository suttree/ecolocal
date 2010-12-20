# Methods added to this helper will be available to all templates in the application.
module ApplicationHelper

    def normalise( name )

        # Munges a string into a wiki-linkable one
        name = name.downcase
        name = name.gsub( / /, '_' )
        name = name.gsub( /\W*/, '' )
        name = h(name)
    end

    def de_normalise( name )
        name = name.gsub( /_/, ' ' )
        name = h(name)
    end

    def linkify( text )
        # Escape the text for html display and convert any url-type strings into active links
        if text != nil
            h( text )
            text = text.gsub( /\r/, '<br/>' )

            #auto_link( text, :all, :target => '_new', :class => 'article_link' ) do |link_text|
	    #    truncate(link_text, 100)
	    #end

	    # auto_link is the default helper built into rails, but there are a few problems with it recognising urls.
	    # We're using part of a patch file for auto_link here since I can't edit the Textdrive rails installation directly.
	    # See this for more info - http://dev.rubyonrails.org/attachment/ticket/1043/auto-link.patch
	    text.gsub(/(<\w+.*?>|[^=!:'"\/]|^)((?:http[s]?:\/\/)|(?:www\.))([^\s<]+\/?)([[:punct:]]|\s|<|$)/) do
		    all, a, b, c, d = $&, $1, $2, $3, $4
		    if a =~ /<a\s/i # don't replace URL's that are already linked
			    all
		    else
			    #%(#{a}<a href="#{b=="www."?"http://www.":b}#{c}">#{b}#{c}</a>#{d})
			    %(#{a}<a href="#{b=="www."?"http://www.":b}#{c}">#{b}#{c}</a>#{d})
			    %(#{a}<a href="#{b=="www."?"http://www.":b}#{c}">) + truncate( (b + c), 50 ) + %(</a>#{d})
		    end
	    end


            # This works, but we'll favour the built in auto_link helper for now
            #text = text.gsub( /http:\/\/([^<\s]*)/i, '<a href=\'http://\1\' target=\'_blank\' alt=\'External link\' title=\'Link to \1, opens in a new window\'>\1</a>&nbsp;<img src=\'/images/offsite.gif\' border=\'0\'/>' )
            # 
            # This (unused) example from http://www.nshb.net/node/252
            #text = text.gsub( /^(http|https):\/\/([a-z0-9]+)[\-\.]{1}([a-z0-9]+)*\.([a-z]{2,5})(([0-9]{1,5})?\/.*)?$/ix, '<a href=\'\1://\2.\3.\4\5\' target=\'_blank\' alt=\'External link\' title=\'Link to \1.\2.\3, opens in a new window\'>\2.\3.\4\5</a>&nbsp;<img src=\'/images/offsite.gif\' border=\'0\'/>' )
        end
    end

    def latest_comments
        Comment.find( :all, 
                      :order => 'updated_on DESC',
                      :limit => 10 )
    end

    def latest_events
      Article.find_tagged_with :any => 'events', :order => 'created_on DESC', :limit => 10
    end  
end
