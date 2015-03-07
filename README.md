# roundcube_upgrade_helper
PHP Helper script for updating Roundcube Mail

# Usage
* Place script somewhere at your webspace
* Change content of variable $folder to the correct path of you roundcube installation
  * in my case, the upgrade script is located at '$webspacepath/temp' and roundcube at '$webspacepath/roundcube', so the $folder needs to be '../roundcube'
* Call the following URLs to
 * backup: http://yourdomain.tld/path/to/roundcube_upgrade_helper.php?action=backup&oldversion=1.0.3&newversion=1.0.4
 * download the specified version: http://yourdomain.tld/path/to/roundcube_upgrade_helper.php?action=download&oldversion=1.0.3&newversion=1.0.4
 * upgrade to the new version: http://yourdomain.tld/path/to/roundcube_upgrade_helper.php?action=update&oldversion=1.0.3&newversion=1.0.4

