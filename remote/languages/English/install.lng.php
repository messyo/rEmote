<?php

// GENERAL

$lng['next']            = 'Continue';
$lng['title']           = 'Install rEmote';
$lng['chooselng']       = 'Please choose your language';
$lng['save']            = 'Save';
$lng['coulntread']      = 'FATAL: Could not read File (\\1)';

$lng['checkrt']         = 'Check connection to rTorrent';
$lng['insecurerpc']     = 'You are using an insecure RPC connection';
$lng['insechint']       = 'Secure your connection by using an unguessable path, like "\\1" (this path is generated on random, so feel free to use it. Or secure your connection with authentication by user and password.';
$lng['insechint']      .= 'Do not Forget to change the SCGI mount command in your httpd.conf accordingly (e.g. \\2)';
$lng['insechint']      .= '<br />You also could secure your connection, by binding your RPC path to a specific IP-Address in your webserver\'s configuration, to avoid unauthorized access.';
$lng['noconnect']       = 'Could not connect to rTorrent, please ensure rTorrent is started';
$lng['rtfound']         = 'Found rtorrent with Version \\1';
$lng['rtwrongvers']     = 'This rTorrent version is not supported, please use rTorrent version \\1 or higher';
$lng['retry']           = 'Retry';


$lng['database']        = 'Database';
$lng['connected']       = 'Successfully connected to database';
$lng['couldntcon']      = 'Could not connect to database with the following parameters: \\1';
$lng['dbtype']          = 'Database type: \\1';
$lng['dbdb']            = 'Database: \\1';
$lng['dbhost']          = 'Host: \\1';
$lng['dbuser']          = 'User: \\1';
$lng['dbpass']          = 'Password: \\1';
$lng['incomplete']      = 'Connection parameters incomplete';
$lng['uptodate']        = 'All tables up to date';

$lng['firstuser']       = 'Creating first user';
$lng['userfound']       = 'Found user';
$lng['invname']         = 'Invalid username';
$lng['invpass']         = 'Invalid password';
$lng['invdir']          = 'Invalid direcotry';
$lng['nodir']           = 'No such directory';
$lng['username']        = 'Username';
$lng['password']        = 'Password';
$lng['dir']             = 'Directory';

$lng['settings']        = 'Settings';
$lng['adding']          = 'Adding option "\\1"';
$lng['deleting']        = 'Deleting option "\\1"';
$lng['settingsu2d']     = 'All settings are up to date';
$lng['newsettings']     = 'New options have been added. Go to the settings menu in your control panel after the installation to adjust them.';
$lng['errorins']        = 'An error has occured while inserting new options';



$lng['dirs']            = 'Directories';
$lng['path']            = 'Path';
$lng['privs']           = 'Permissions';
$lng['exists']          = 'Exists';
$lng['readable']        = 'Readable';
$lng['writeable']       = 'Writeable';
$lng['executable']      = 'Executable';
$lng['not']             = 'not';
$lng['pass']            = 'Passed';
$lng['function']        = 'Function';
$lng['tmpdir']          = 'Temporary files';
$lng['default_dir']     = 'Default home directory';
$lng['user_dir']        = 'First user\'s directory';
$lng['ppassed']         = 'Some functions may not work (mostly filebrowser-Functions)';
$lng['pbad']            = 'There are more permissions needed to continue installation';
$lng['pgood']           = 'The directory has the neccessary attributes';
$lng['changedir']       = 'Change directories';

$lng['binaries']        = 'Programs';
$lng['bpassed']         = 'Some functions may not work';
$lng['bbad']            = 'rEmote will not work';
$lng['bgood']           = 'All requirements satisfied';
$lng['binary']          = 'Program';
$lng['bnotfound']       = 'Not Found';
$lng['autodet']         = 'Autodetection';
$lng['changebin']       = 'Change paths';

$lng['completion']      = 'Completion';
$lng['comsuccess']      = 'The installer has been successfully locked. <strong>The installation is now complete.</strong> You can proceed by logging into rEmote and creating other users and adjusting your settings';
$lng['notlocked']       = 'The installer could not be locked. Please make sure the installer has the permission to create the file ".lock" or the file is created and writable by the rEmote installer';
$lng['notlockedi']      = 'You can also delete the install-folder to complete the installation.';
$lng['goremote']        = 'Go to rEmote';

$lng['reqs']            = 'Requirements';
$lng['req']             = 'Requirement';
$lng['yes']             = 'Yes';
$lng['no']              = 'No';
$lng['reqd']            = 'Required';
$lng['curr']            = 'Current';
$lng['apachemods']      = 'Apache modules';
$lng['phpextens']       = 'PHP extensions';
$lng['notava']          = 'Not available';

$lng['mod_scgi']        = 'Apache module SCGI';
$lng['xmlrpc']          = 'PHP extension XMLRPC';
$lng['gd']              = 'PHP extension GDLib';
$lng['xml']             = 'PHP extension XML';
$lng['PDO']             = 'PHP extension PDO';
$lng['pdo_mysql']       = 'PDO driver for MySQL';
$lng['pdo_pgsql']       = 'PDO driver for PostgreSQL';
$lng['pdo_sqlite']      = 'PDO driver for SQLite';
$lng['pdo_oci']         = 'PDO driver for Oracle';
$lng['mysql']           = 'PHP extension MySQL';
$lng['sysvsem']         = 'PHP extension for Semaphore-Support';
$lng['session']         = 'PHP extension for Sessions';
$lng['on']              = 'On';
$lng['off']             = 'Off';
$lng['safe_mode']       = 'Safe Mode';
$lng['open_basedir']    = 'Open Basedir Restriction';
$lng['allow_url_fopen'] = 'Allow URL fopen';
$lng['dbinvalid']       = 'Invalid database type';

?>
