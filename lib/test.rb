require 'runt'
require 'date'

last_thursday = DIMonth.new(Last_of,Thursday)

august = REYear.new(8)

expr = last_thursday & august

expr.include?(Date.new(2002,8,29)) #Thurs 8/29/02 => true
expr.include?(Date.new(2003,8,28)) #Thurs 8/28/03 => true
expr.include?(Date.new(2004,8,26)) #Thurs 8/26/04 => true
expr.include?(Date.new(2004,3,18)) #Thurs 3/18/04 => true

expr.include?(Date.new(2004,8,27)) #Fri 8/27/04 => false
