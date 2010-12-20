class NullUser
  # Is the user authenticated?
  def authenticated?; false end
  
  # Does the user have administration privileges?
  def admin?; false end
  
  # Stub for id
  def id; nil end 
  
  # Provide a name
  def username; 'Anonymous' end
  def nickname; 'Anonymouse' end

  def email; nil end
end
