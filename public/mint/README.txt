================================================================================
GET YOUR MINT ON

Installation in three easy steps. Update & uninstall instructions appear below.
--------------------------------------------------------------------------------

1. Open up /config/db.php, add the appropriate database values for server, 
username, password and database (name) between the corresponding single quotes, 
then save and close.
(Please contact your host if you are unsure of the correct values.)

2. Upload the entire directory to your server. You can now change the name to 
just /mint/--Mint is distributed as a versioned folder so you can know at a 
glance if you've downloaded a new version. Now visit Mint in a web browser. eg. 
http://www.yourdomain.com/mint/

3. Proceed with installation and follow the remaining instructions. You should 
have your Activation Key from the confirmation email or your Account Center 
page handy. NOTE: The login you create during installation is completely 
separate from your Mint Account Center login.


================================================================================
Update from a previous version (Updating from Mint 1.2x? Keep scrolling.)

BEFORE UPDATING TO MINT 1.2 OR GREATER PLEASE BE SURE YOUR MINT INSTALLATION IS 
UP TO DATE. AS OF THIS RELEASE, 1.14 IS THE NEXT MOST UP-TO-DATE VERSION. YOU 
WILL NOT BE ABLE TO UPDATE FROM OLDER VERSIONS OF MINT.

AS IS THE CASE WITH MOST SOFTWARE UPDATES, DATALOSS CAN OCCUR DURING UPDATE. BE
SURE TO MAKE A BACKUP OF YOUR DATA (INCLUDING DATABASE) BEFORE PROCEEDING. PLEASE
SEE THIS THREAD IF YOU RUN INTO PROBLEMS:
http://haveamint.com/forum/viewtopic.php?pid=5539#p5539

The update to Mint 1.2 is a complete architecture change from all versions 
before it and features a new Pepper API. As a result you may want to check for 
updates to your favorite Pepper before updating as Pepper written for older 
versions will no longer work. To ensure that your Pepper data persists through 
the update be sure to add the updated Pepper to the /pepper/ directory BEFORE 
running this update.
--------------------------------------------------------------------------------

1. Remove/comment out the Mint JavaScript include from your site. (If you are 
using the .htaccess method be sure to comment out the php_value 
auto_prepend_file line)

2. Open up /config/db.php, add the appropriate database values for server, 
username, password and database name (all between the empty single quotes), 
then save and close. (These are the same values from previous versions of Mint 
that lived in /lib/configuration.php)

3. Upload /mint_v1xx/ to your server (without renaming the folder)

4. Once the upload is complete, rename the existing /mint/ directory to 
/mint_old/ then rename /mint_v1xx/ to /mint/

5. Visit http://www.yourdomain.com/mint/ in a web browser to automatically 
update Mint. Follow the onscreen instructions. DO NOT CANCEL OR RELOAD THE PAGE 
until you are presented with notification that the update is complete.

6. Restore/uncomment the Mint JavaScript include on your site. The script
src should be updated from /mint/mint.js.php to /mint/?js (If you are using the
.htaccess method, you will need to update the php_value auto_prepend_file 
replacing 'auto.mint.php' with 'config/auto.php')


================================================================================
Update from Mint 1.2x

--------------------------------------------------------------------------------
Replace the following files and folders:

1. /mint/app/

2. /mint/index.php

3. /mint/pepper/shauninman/default/

NOTE: If you are updating from a version prior to Mint 1.28 for the first time 
you will be prompted for your Activation Key. This will only be required on your
initial update to Mint 1.28 or higher. Your Activation Key can be found in your
original purchase email or in the Mint Account Center:
http://haveamint.com/account/manage-licenses

================================================================================
Uninstalling Mint

Halitosis be damned!
--------------------------------------------------------------------------------

1. Log into your Mint installation and click on Preferences.

2. Click on Uninstall and follow the instructions.

3. If you chose to attach the Mint JavaScript include using the advanced method,
don't forget to remove the related code from your .htaccess files.


================================================================================
Copyright 2004-2005 Shaun Inman. This package cannot be redistributed without
permission from http://www.shauninman.com/

More info at: http://www.haveamint.com/
