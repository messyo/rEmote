<?php

// GENERAL

$lng['next']        = 'Fortsetzen';
$lng['title']       = 'Installiere rEmote';
$lng['chooselng']   = 'Bitte wählen Sie Ihre Sprache';
$lng['save']        = 'Speichern';
$lng['coulntread']  = 'FEHLER: Konnte Datei nicht lesen (\\1)';

$lng['checkrt']     = 'Überprüfe rTorrent-Verbindung';
$lng['insecurerpc'] = 'Sie nutzen eine unsichere RPC-Verbindung';
$lng['insechint']   = 'Sichern Sie ihre Verbindung, in dem Sie einen schwer erratbaren Pfad, wie z.B. "\\1" (dieser Pfad wurde zufällig erstellt, sodass Sie ihn auchübernehmen önnen). Oder sichern Sie Ihre Verbindung per Authentifizierung mit User und Passwort.';
$lng['insechint']  .= 'Vergessen Sie nicht das SCGI-Mount-Kommando in Ihrer httpd.conf entsprechend zu ändern (z.B. \\2)';
$lng['insechint']  .= '<br />Natürlich können Sie auch ihre Verbindung am Webserver auf eine oder mehrere IP-Addressen beschränken, um unberechtigte Zugriffe zu verhindern.';
$lng['noconnect']   = 'Konnte nicht zu rTorrent verbinden. Bitte vergewissern Sie sich, dass rTorrent gestartet ist.';
$lng['rtfound']     = 'rTorrent Version \\1 gefunden';
$lng['rtwrongvers'] = 'Diese rTorrent-Version wird nicht unterstützt.Bitte nutzen Sie rTorrent ab Version  \\1';
$lng['retry']       = 'wiederholen';


$lng['database']    = 'Datenbank';
$lng['connected']   = 'Erfolgreich zur Datenbank verbunden';
$lng['couldntcon']  = 'Konnte mit folgenden Einstellungen nicht zur Datenbank verbinden: \\1';
$lng['dbtype']      = 'Datenbank-Typ: \\1';
$lng['dbdb']        = 'Datenbank: \\1';
$lng['dbhost']      = 'Host: \\1';
$lng['dbuser']      = 'User: \\1';
$lng['dbpass']      = 'Passwort: \\1';
$lng['incomplete']  = 'Verbindungs Einstellungen unvollständig';
$lng['uptodate']    = 'Alle Tabellen aktualisiert';

$lng['firstuser']   = 'Erstelle ersten User';
$lng['userfound']   = 'User gefunden';
$lng['invname']     = 'Ungültiger Username';
$lng['invpass']     = 'Ungültiges Passwort';
$lng['invdir']      = 'Ungültiges Verzeichnis';
$lng['nodir']       = 'Verzeichnis nicht gefunden';
$lng['username']    = 'Username';
$lng['password']    = 'Passwort';
$lng['dir']         = 'Verzeichnis';

$lng['settings']    = 'Einstellungen';
$lng['adding']      = 'Füge Option "\\1" ein';
$lng['settingsu2d'] = 'Alle Einstellungen aktualisiert';
$lng['newsettings'] = 'Neue Optionen wurden hinzugefügt. Wechseln Sie nach der Installation in das Einstellungs-Menü unter Control-Panel, um sie anzupassen.';
$lng['errorins']    = 'Fehler beim Hinzufügen neuer Optionen';



$lng['dirs']        = 'Verzeichnisse';
$lng['path']        = 'Pfad';
$lng['privs']       = 'Rechte';
$lng['exists']      = 'vorhanden';
$lng['readable']    = 'lesbar';
$lng['writeable']   = 'schreibbar';
$lng['executable']  = 'ausführbar';
$lng['not']         = 'nicht';
$lng['pass']        = 'bestanden';
$lng['function']    = 'Funktion';
$lng['tmpdir']      = 'Temporäre Dateien';
$lng['default_dir'] = 'Standard-Verzeichnis für neue User';
$lng['user_dir']    = 'Verzeichnis des ersten Users';
$lng['ppassed']     = 'Einige Funktionen werden nicht optimal funktionieren (vor allem Dateimanager-Funktionen)';
$lng['pbad']        = 'Es werden keine Rechte benöigt, um die Installation fortzusetzen';
$lng['pgood']       = 'Das Verzeichnis hat die nötigen Eigenschaften';
$lng['changedir']   = 'ändere Verzeichnisse';

$lng['binaries']    = 'Programme';
$lng['bpassed']     = 'Einige Funktionen werden nicht optimal funktionieren';
$lng['bbad']        = 'rEmote wird nicht funktionieren';
$lng['bgood']       = 'Alle Voraussetzungen erfüllt';
$lng['binary']      = 'Programm';
$lng['bnotfound']   = 'nicht gefunden';
$lng['autodet']     = 'Automatische Erkennung';
$lng['changebin']   = 'ändere Pfade';

$lng['completion']  = 'Fertigstellung';
$lng['comsuccess']  = 'Der Installer wurde erfolgreich gesperrt. <strong>Die Installation ist nun abgeschlossen.</strong> Sie können mit dem Einloggen in rEmote und Erstellen anderer User sowie dem Anpassen Ihrer Einstellungen fortfahren.';
$lng['notlocked']   = 'Der Installer konnte nicht gesperrt werden. Bitte stellen Sie sicher, dass der Installer die nötigen Rechte besitzt um die Datei ".lock" zu erstellen oder die erstellte ".lock" Datei vom Installer veränderbar ist';
$lng['notlockedi']  = 'Um die Installation abzuschließen, können Sie auch das Verzeichnis "install" entfernen und dieses Fenster schließen.';
$lng['goremote']    = 'Wechsle zu rEmote';

$lng['reqs']            = 'Vorraussetzungen';
$lng['req']             = 'Vorraussetzung';
$lng['yes']             = 'Ja';
$lng['no']              = 'Nein';
$lng['reqd']            = 'Benötigt';
$lng['curr']            = 'Aktuell';
$lng['apachemods']      = 'Apache Module';
$lng['phpextens']       = 'PHP-Erweiterungen';
$lng['notava']          = 'Nicht vorhanden';

$lng['mod_scgi']        = 'Apache-Modul SCGI';
$lng['xmlrpc']          = 'PHP-Erweiterung XMLRPC';
$lng['gd']              = 'PHP-Erweiterung GDLib';
$lng['xml']             = 'PHP-Erweiterung XML';
$lng['PDO']             = 'PHP-Erweiterung PDO';
$lng['pdo_mysql']       = 'PDO-Treiber für MySQL';
$lng['pdo_pgsql']       = 'PDO-Treiber für PostgreSQL';
$lng['pdo_sqlite']      = 'PDO-Treiber für SQLite';
$lng['pdo_oci']         = 'PDO-Treiber für Oracle';
$lng['mysql']           = 'PHP-Erweiterung MySQL';
$lng['on']              = 'An';
$lng['off']             = 'Aus';
$lng['safe_mode']       = 'Safe Mode';
$lng['open_basedir']    = 'Open Basedir Restriction';
$lng['allow_url_fopen'] = 'Allow URL fopen';
$lng['dbinvalid']       = 'Ungültiger Datenbanktyp';

?>
