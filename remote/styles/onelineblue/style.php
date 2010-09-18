<?php

$stylesheets = array('styles/onelineblue/style.css');
$imagedir    = 'images/newblue/';
$fileimgs    = 'images/filetypes/';
$dynimgs     = 'styles/newblue/dynimgs/';
$smileyimgs  = 'images/newblue/smileys/';
$stylejs     = array('styles/onelineblue/onelineblue.js');

/*
 * trows sets the number of Table-Rows one Torrent uses
 */

$trows = 1;

/*
 * Table-Rows of torrentlist for head, body and footer
 * For the Torrentlist-Lines there are the following variables:
 * {l_statusimg}     - Status-image (for Seeding, Leeching, etc...)
 * {l_statuskey}     - Status eg: \"Seeding\"
 * {l_name}          - Torrent name
 * {l_done}          - Torrent-Progress
 * {l_eta}           - ETA
 * {l_sup}           - Upspeed
 * {l_sdwn}          - Downspeed
 * {l_seeded}        - Amount of seeded Data
 * {l_completed}     - Completed Bytes
 * {l_size}          - Torrent Size
 * {l_peers}         - Peer Informations
 * {l_ratio}         - Torrent-Ratio
 * {l_check}         - Checkbox to multi-mark torrents
 *
 * {l_even}          - 0 or 1 for highlighting different lines
 *
 *
 * NOTICE: In EVERY Element of the Table has to be the css-id of the first row the torrent-hash
 *
 */

$listhead   = '<tr><td>&nbsp;</td><td>{l_name}</td><td>{l_done}</td><td>{l_eta}</td><td colspan="2">{l_sup} {lngspeed} {l_sdwn}</td><td>{l_seeded}</td><td>{l_completed}</td><td>{l_size}</td><td>{l_peers}</td><td>{l_ratio}</td><td>{l_check}</td></tr>';
// $listbody   = '<tr class=\"row$l_even\" id=\"torrent$l_hash\"><td rowspan=\"2\" class=\"statusimg\">$l_statusimg</td><td colspan=\"11\" class=\"name\">$l_name<span class=\"torrentmessage\">$l_message</span></td></tr>';
// $listbody  .= '<tr class=\"row$l_even\"><td class=\"links\">$l_links</td><td class=\"done\">$l_done</td><td class=\"eta\">$l_eta</td><td class=\"upspeed\">$l_sup</td><td class=\"downspeed\">$l_sdwn</td>';
// $listbody  .= '<td class=\"seeded\">$l_seeded</td><td class=\"completed\">$l_completed</td><td class=\"size\">$l_size</td><td class=\"peers\">$l_peers</td><td class=\"ratio\">$l_ratio</td><td class=\"check\">$l_check</td></tr>';
$listbody   = "\n".'<tr class="row{l_even}" id="torrent{l_hash}"><td class="statusimg">{l_statusimg}<span>{l_links}</span></td><td class="name">{l_name}</td>';
$listbody  .= '<td class="done">{l_done}</td><td class="eta">{l_eta}</td><td class="upspeed">{l_sup}</td><td class="downspeed">{l_sdwn}</td>';
$listbody  .= '<td class="seeded">{l_seeded}</td><td class="completed">{l_completed}</td><td class="size">{l_size}</td><td class="peers">{l_peers}</td><td class="ratio">{l_ratio}</td><td class="check">{l_check}</td></tr>';

/* Some other variables for footer
 *
 * {l_count}     - Number of Torrents
 * {l_done}      - Average Progress
 * {l_sup}       - Total Upspeed
 * {l_sdwn}      - Total Downspeed
 * {l_speeds}    - Both Speeds (Comfortable Displaying)
 * {l_seeded}    - Total amount of seeded data
 * {l_completed} - Total amount of completed data
 * {l_size}      - Sum of all torrent-sizes
 * {l_ratio}     - Average Ratio
 */

$listfoot   = '<tr id="group{l_groupid}"><td colspan="2"><strong>:lng[total]</strong>&nbsp;:lng[count]: {l_count}</td><td class="done">{l_done}</td><td>&nbsp;</td><td class="upspeed">{l_sup}</td><td class="downspeed">{l_sdwn}</td><td class="seeded">{l_seeded}</td><td class="completed">{l_completed}</td><td class="size">{l_size}</td><td>&nbsp;</td><td class="ratio">{l_ratio}</td><td>&nbsp;</td></tr>';

$numcolumns = 12;  // Number of columns in Table-Line

/*
 *
 * Enter pictures for Hover-Effects to be precached
 */

$precache   = array('menu_torrents.png',
							'menu_upload.png',
							'menu_files.png',
							'menu_feeds.png',
							'menu_cp.png',
							'menu_logout.png',
							'empty1.png',
							'empty2.png',
							'empty3.png',
							'empty4.png',
							'empty5.png',
							'empty6.png',
							'empty7.png',
							'empty8.png',
							'empty9.png',
							'empty10.png',
							);

/*
 * Sorticons, displayed when sorting torrent-list
 */

$sorticons  = array('ASC' => '&nbsp;&dArr;', 'DESC' => '&nbsp;&uArr;');

?>
