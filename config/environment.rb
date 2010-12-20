# Be sure to restart your web server when you modify this file.

# As per http://forums.site5.com/showthread.php?t=11954
ENV['GEM_PATH'] = '/home/suttreec/gems:/usr/lib/ruby/gems/1.8'

# Uncomment below to force Rails into production mode
# (Use only when you can't set environment variables through your web/app server)

# Uncommented in accordance with http://wiki.rubyonrails.org/rails/pages/HowToInstallOnSite5
ENV['RAILS_ENV'] ||= 'production'

# Bootstrap the Rails environment, frameworks, and default configuration
require File.join(File.dirname(__FILE__), 'boot')

Rails::Initializer.run do |config|
  # Skip frameworks you're not going to use
  # config.frameworks -= [ :action_web_service, :action_mailer ]

  # Add additional load paths for your own custom dirs
  # config.load_paths += %W( #{RAILS_ROOT}/extras )

  # Force all environments to use the same logger level 
  # (by default production uses :info, the others :debug)
  # config.log_level = :debug

  # Use the database for sessions instead of the file system
  # (create the session table with 'rake create_sessions_table')
  # config.action_controller.session_store = :active_record_store

  # Enable page/fragment caching by setting a file-based store
  # (remember to create the caching directory and make it readable to the application)
  config.action_controller.fragment_cache_store = :file_store, "#{RAILS_ROOT}/tmp/cache"

  # Activate observers that should always be running
  # config.active_record.observers = :cacher, :garbage_collector

  # Make Active Record use UTC-base instead of local time
  # config.active_record.default_timezone = :utc
  
  # Use Active Record's schema dumper instead of SQL when creating the test database
  # (enables use of different database adapters for development and test environments)
  # config.active_record.schema_format = :ruby

  # See Rails::Configuration for more options

  # Rotate log files on size rather than time (creates 50 1MB files)
  # See http://blog.caboo.se/articles/2005/12/15/who-said-size-is-not-important
  config.logger = Logger.new(config.log_path, 50, 1048576)
end

# Add new inflection rules using the following format 
# (all these examples are active by default):
# Inflector.inflections do |inflect|
#   inflect.plural /^(ox)$/i, '\1en'
#   inflect.singular /^(ox)en/i, '\1'
#   inflect.irregular 'person', 'people'
#   inflect.uncountable %w( fish sheep )
# end

# Include your application configuration below
#require_gem 'acts_as_taggable'
gem 'acts_as_taggable'

# runt, date, Runt and DPrecision all needed for runt to work
require 'runt'
require 'date'

include Runt
include DPrecision

# To whitelist allowed html in comments and articles
require 'sanitize'

# From http://wiki.rubyonrails.com/rails/pages/LocalizingDates
# This gives us a nice way to format and output dates
# using date.to_s(:presentable_date) in our templates
ActiveSupport::CoreExtensions::Time::Conversions::DATE_FORMATS.merge!(
    :date => "%Y-%m-%d",
    :presentable_datetime => "%a %b %d, %Y %H:%M %p",
    :presentable_date => "%a %b %d, %Y" 
)

NEGATIVE_CAPTCHA_SECRET = 'er1c:c4nt0n4'
