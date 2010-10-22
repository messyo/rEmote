<?

// GENERAL

$lng['next']            = 'Continue';
$lng['title']           = 'Install rEmote';
$lng['chooselng']       = 'Please choose your language';
$lng['save']            = 'Save';
$lng['coulntread']      = 'FATAL: Could not read File (\\1)';

$lng['checkrt']         = 'Check for rTorrent-Connection';
$lng['insecurerpc']     = 'You are Using an Insecure RPC-Connection';
$lng['insechint']       = 'Secure your connection by using an unguessable Path, like "\\1" (this Path is generated on random, so feel free to use it. Or secure your connection with authentication by user and password.';
$lng['insechint']      .= 'Do not Forget to change the SCGI-Mount-Command in your httpd.conf accordingly (e.g. \\2)';
$lng['insechint']      .= '<br />You also could secure your connection, by binding your RPC-Path to a specific IP-Address in your webservers configuration, to avoid unauthorized access.';
$lng['noconnect']       = 'Could not connect to rTorrent, please ensure rTorrent is started';
$lng['rtfound']         = 'Found rtorrent with Version \\1';
$lng['rtwrongvers']     = 'This rTorrent-Version is not supported, please use rtorrent in version \\1 or higher';
$lng['retry']           = 'Retry';


$lng['database']        = 'Database';
$lng['connected']       = 'Successfully connected to Database';
$lng['couldntcon']      = 'Could not connect to Databse with following parameters: \\1';
$lng['dbtype']          = 'Database-Type: \\1';
$lng['dbdb']            = 'Database: \\1';
$lng['dbhost']          = 'Host: \\1';
$lng['dbuser']          = 'User: \\1';
$lng['dbpass']          = 'Password: \\1';
$lng['incomplete']      = 'Connection-Parameters incomplete';
$lng['uptodate']        = 'All Tables up to date';

$lng['firstuser']       = 'Creating First User';
$lng['userfound']       = 'Found User';
$lng['invname']         = 'Invalid Username';
$lng['invpass']         = 'Invalid Password';
$lng['invdir']          = 'Invalid Direcotry';
$lng['nodir']           = 'No such directory';
$lng['username']        = 'Username';
$lng['password']        = 'Password';
$lng['dir']             = 'Directory';

$lng['settings']        = 'Settings';
$lng['adding']          = 'Adding option "\\1"';
$lng['deleting']        = 'Deleting option "\\1"';
$lng['settingsu2d']     = 'All settings are up to date';
$lng['newsettings']     = 'New options have been added. Go to the settings menu in your Control-Panel after the installation to adjust them.';
$lng['errorins']        = 'An error has occured while inserting new Options';



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
$lng['tmpdir']          = 'Temporary Files';
$lng['default_dir']     = 'Default Home-Directory';
$lng['user_dir']        = 'First Users Directory';
$lng['ppassed']         = 'Some functions may not work (mostly Filebrowser-Functions)';
$lng['pbad']            = 'There are more permissions needed to continue installation';
$lng['pgood']           = 'The directory has the neccessary attributes';
$lng['changedir']       = 'Change Directories';

$lng['binaries']        = 'Programs';
$lng['bpassed']         = 'Some functions may not work';
$lng['bbad']            = 'rEmote will not work';
$lng['bgood']           = 'All requirements satisfied';
$lng['binary']          = 'Program';
$lng['bnotfound']       = 'Not Found';
$lng['autodet']         = 'Autodetection';
$lng['changebin']       = 'Change Pathes';

$lng['completion']      = 'Completion';
$lng['comsuccess']      = 'The installer has been successfully locked. <strong>The Installation is now complete.</strong> You can proceed logging into rEmote and creating other Users and adjust your settings';
$lng['notlocked']       = 'The installer could not be locked. Please make sure the installer has the permission to create the file ".lock" or the file is created and writable by the rEmote-Installer';
$lng['notlockedi']      = 'You can also delete the install-folder to complete the installation.';
$lng['goremote']        = 'Go to rEmote';

$lng['reqs']            = 'Requirements';
$lng['req']             = 'Requirement';
$lng['yes']             = 'Yes';
$lng['no']              = 'No';
$lng['reqd']            = 'Required';
$lng['curr']            = 'Current';
$lng['apachemods']      = 'Apache Modules';
$lng['phpextens']       = 'PHP-Extensions';
$lng['notava']          = 'Not available';

$lng['mod_scgi']        = 'Apache-Module SCGI';
$lng['xmlrpc']          = 'PHP-Extension XMLRPC';
$lng['gd']              = 'PHP-Extension GDLib';
$lng['xml']             = 'PHP-Extension XML';
$lng['PDO']             = 'PHP-Extension PDO';
$lng['pdo_mysql']       = 'PDO-Driver for MySQL';
$lng['pdo_pgsql']       = 'PDO-Driver for PostgreSQL';
$lng['pdo_sqlite']      = 'PDO-Driver for SQLite';
$lng['pdo_oci']         = 'PDO-Driver for Oracle';
$lng['mysql']           = 'PHP-Extension MySQL';
$lng['on']              = 'On';
$lng['off']             = 'Off';
$lng['safe_mode']       = 'Safe Mode';
$lng['open_basedir']    = 'Open Basedir Restriction';
$lng['allow_url_fopen'] = 'Allow URL fopen';
$lng['dbinvalid']       = 'Invalid database-type';

?>
