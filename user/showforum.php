<?php

require('listthread.inc');
require('filter.inc');

$tpl->define(array(
  header => 'header.tpl',
  footer => 'footer.tpl',
  showforum => 'showforum.tpl',
  showforum_row => 'showforum_row.tpl',
  postform => 'postform.tpl',
  forum_header => 'forum/' . $forum['shortname'] . '.tpl'
));

$tpl->define_dynamic('simple_row', 'showforum_row');
$tpl->define_dynamic('normal_row', 'showforum_row');

$tpl->define_dynamic('simple', 'showforum');
$tpl->define_dynamic('normal', 'showforum');

if (isset($user['prefs.SimpleHTML'])) {
  $tpl->clear_dynamic('normal');
  $tpl->clear_dynamic('normal_row');
} else {
  $tpl->clear_dynamic('simple');
  $tpl->clear_dynamic('simple_row');
}

/* Default it to the first page if none is specified */
if (!isset($curpage))
  $curpage = 1;

/* Number of threads per page we're gonna list */
if (isset($user))
  $threadsperpage = $user['threadsperpage'];
else
  $threadsperpage = 50;

/* Open up the SQL database first */
sql_open_readonly();

function threads($key)
{
  global $user, $indexes;

  $numthreads = $indexes[$key]['active'];

  /* People with moderate privs automatically see all moderated and deleted */
  /*  messages */
  if (isset($user['cap.Moderate']))
    $numthreads += $indexes[$key]['moderated'] + $indexes[$key]['deleted'];
  else if (isset($user['prefs.ShowModerated']))
    $numthreads += $indexes[$key]['moderated'];

  return $numthreads;
}

$tpl->assign(TITLE, $forum['name']);

$tpl->assign(THISPAGE, $SCRIPT_NAME . $PATH_INFO);

$tpl->parse(FORUM_HEADER, 'forum_header');

/* We get our money from ads, make sure it's there */
include('ads.inc');

$ad = ads_view("a4.org," . $forum['shortname'], "_top");
$tpl->assign(AD, $ad);

/* FIXME: More ads (forum specific ads) */
/*
if ($forum['shortname'] == "a4" || $forum['shortname'] == "performance")
  ads_view("carreview", "_top");
*/

/* Figure out how many total threads the user can see */
$numthreads = 0;

reset($indexes);
while (list($key) = each($indexes))
  $numthreads += threads($key);

$numpages = ceil($numthreads / $threadsperpage);

$startpage = $curpage - 4;
if ($startpage < 1)
  $startpage = 1;

$endpage = $startpage + 9;
if ($endpage > $numpages)
  $endpage = $numpages;

if ($endpage == $startpage)
  $endpage++;

$pagestr = "";

if ($curpage > 1) {
  $prevpage = $curpage - 1;
  $pagestr .= "[<a href=\"$urlroot/" . $forum['shortname'] . "/pages/$prevpage.phtml\">&lt;&lt;&lt;</a>] ";
}

$pagestr .= "[<a href=\"$urlroot/" . $forum['shortname'] . "/pages/1.phtml\">";
if ($curpage == 1)
  $pagestr .= "<font size=\"+1\"><b>1</b></font>";
else
  $pagestr .= "1";
$pagestr .= "</a>]\n";

if ($startpage == 1)
  $startpage++;
elseif ($startpage < $endpage)
  $pagestr .= " ... ";

for ($i = $startpage; $i <= $endpage; $i++) {
  $pagestr .= "[<a href=\"" . $urlroot . "/" . $forum['shortname'] . "/pages/" . $i . ".phtml\">";
  if ($i == $curpage)
    $pagestr .= "<font size=\"+1\"><b>$i</b></font>";
  else
    $pagestr .= $i;
  $pagestr .= "</a>] ";
}

if ($curpage < $numpages) {
  $nextpage = $curpage + 1;
  $pagestr .= "[<a href=\"$urlroot/" . $forum['shortname'] . "/pages/$nextpage.phtml\">&gt;&gt;&gt;</a>] ";
}

$tpl->assign(PAGES, $pagestr);

$tpl->assign(NUMTHREADS, $numthreads);
$tpl->assign(NUMPAGES, $numpages);

function print_collapsed($thread, $msg, $count)
{
  global $user, $forum, $furlroot, $urlroot;

  if (!empty($msg['flags'])) {
    $flagexp = explode(",", $msg['flags']);
    while (list(,$flag) = each($flagexp))
      $flags[$flag] = "true";
  }

  $string = "<li>";

  if (isset($user['prefs.FlatThread']))
    $string .= "<a href=\"$urlroot/" . $forum['shortname'] . "/threads/" . $msg['tid'] . ".phtml#" . $msg['mid'] . "\">" . $msg['subject'] . "</a>";
  else
    $string .= "<a href=\"$urlroot/" . $forum['shortname'] . "/msgs/" . $msg['mid'] . ".phtml\">" . $msg['subject'] . "</a>";

  if (isset($flags['NoText'])) {
    if (!isset($user['prefs.SimpleHTML']))
      $string .= " <img src=\"$furlroot/pix/nt.gif\">";
    else
      $string .= " (nt)";
  }

  if (isset($flags['Picture'])) {
    if (!isset($user['prefs.SimpleHTML']))
      $string .= " <img src=\"$furlroot/pix/pic.gif\">";
    else
      $string .= " (pic)";
  }

  if (isset($flags['Link'])) {
    if (!isset($user['prefs.SimpleHTML']))
      $string .= " <img src=\"$furlroot/pix/url.gif\">";
    else
      $string .= " (link)";
  }

  if (isset($flags['Locked']))
    $string .= " (locked)";

  $string .= "&nbsp;&nbsp;-&nbsp;&nbsp;<b>" . $msg['name'] . "</b>&nbsp;&nbsp;<font size=\"-2\"><i>" . $msg['date'] . "</i>";

  $string .= " ($count " . ($count == 1 ? "reply" : "replies") . ")";

  $string .= "</font>";

  if ($msg['state'] != "Active")
    $string .= " (" . $msg['state'] . ")";

  if (isset($user['cap.Moderate'])) {
    switch ($msg['state']) {
    case "Moderated":
      $string .= " <a href=\"$urlroot/changestate.phtml?state=Active&forumname=" . $forum['shortname'] . "&mid=" . $msg['mid'] . "\">um</a>";
      if (isset($user['cap.Delete']))
        $string .= " <a href=\"$urlroot/changestate.phtml?state=Deleted&forumname=" . $forum['shortname'] . "&mid=" . $msg['mid'] . "\">dm</a>";
      break;
    case "Deleted":
      if (isset($user['cap.Delete']))
        $string .= " <a href=\"$urlroot/changestate.phtml?state=Active&forumname=" . $forum['shortname'] . "&mid=" . $msg['mid'] . "\">ud</a>";
      break;
    case "Active":
      $string .= " <a href=\"$urlroot/changestate.phtml?state=Moderated&forumname=" . $forum['shortname'] . "&mid=" . $msg['mid'] . "\">mm</a>";
      if (isset($user['cap.Delete']))
        $string .= " <a href=\"$urlroot/changestate.phtml?state=Deleted&forumname=" . $forum['shortname'] . "&mid=" . $msg['mid'] . "\">dm</a>";
      break;
    }

    if ($forum['version'] >= 2) {
      if (isset($flags['Locked']))
        $string .= " <a href=\"$urlroot/unlock.phtml?forumname=" . $forum['shortname'] . "&mid=" . $msg['mid'] . "\">ul</a>";
      else
        $string .= " <a href=\"$urlroot/lock.phtml?forumname=" . $forum['shortname'] . "&mid=" . $msg['mid'] . "\">lm</a>";
    }
  }

  if (isset($user) && isset($flags['NewStyle']) && $msg['aid'] == $user['aid'])
    $string .= " <a href=\"$urlroot/edit.phtml?forumname=" . $forum['shortname'] . "&mid=" . $msg['mid'] . "\">edit</a>";

  $string .= "</li>\n";

  return $string;
}

function print_subject($msg)
{
  global $user, $tthreads_by_tid, $forum, $furlroot, $urlroot;

  if (!empty($msg['flags'])) {
    $flagexp = explode(",", $msg['flags']);
    while (list(,$flag) = each($flagexp))
      $flags[$flag] = "true";
  }

  $string = "<li>";

  $new = (isset($tthreads_by_tid[$msg['tid']]) &&
      $tthreads_by_tid[$msg['tid']]['tstamp'] < $msg['tstamp']);

  if ($new)
    $string .= "<i><b>";
  if (isset($user['prefs.FlatThread']))
    $string .= "<a href=\"$urlroot/" . $forum['shortname'] . "/threads/" . $msg['tid'] . ".phtml#" . $msg['mid'] . "\">" . $msg['subject'] . "</a>";
  else
    $string .= "<a href=\"$urlroot/" . $forum['shortname'] . "/msgs/" . $msg['mid'] . ".phtml\">" . $msg['subject'] . "</a>";

  if ($new)
    $string .= "</b></i>";

  if (isset($flags['NoText'])) {
    if (!isset($user['prefs.SimpleHTML']))
      $string .= " <img src=\"$furlroot/pix/nt.gif\">";
    else
      $string .= " (nt)";
  }

  if (isset($flags['Picture'])) {
    if (!isset($user['prefs.SimpleHTML']))
      $string .= " <img src=\"$furlroot/pix/pic.gif\">";
    else
      $string .= " (pic)";
  }

  if (isset($flags['Link'])) {
    if (!isset($user['prefs.SimpleHTML']))
      $string .= " <img src=\"$furlroot/pix/url.gif\">";
    else
      $string .= " (link)";
  }

  if (isset($flags['Locked']))
    $string .= " (locked)";

  $string .= "&nbsp;&nbsp;-&nbsp;&nbsp;<b>" . $msg['name'] . "</b>&nbsp;&nbsp;<font size=\"-2\"><i>" . $msg['date'] . "</i>";

  if ($msg['unixtime'] > 968889231)
    $string .= " (" . $msg['views'] . " view" . ($msg['views'] == 1 ? "" : "s") . ")";

  $string .= "</font>";

  if ($msg['state'] != "Active")
    $string .= " (" . $msg['state'] . ")";

  if (isset($user['cap.Moderate'])) {
    switch ($msg['state']) {
    case "Moderated":
      $string .= " <a href=\"$urlroot/changestate.phtml?state=Active&forumname=" . $forum['shortname'] . "&mid=" . $msg['mid'] . "\">um</a>";
      if (isset($user['cap.Delete']))
        $string .= " <a href=\"$urlroot/changestate.phtml?state=Deleted&forumname=" . $forum['shortname'] . "&mid=" . $msg['mid'] . "\">dm</a>";
      break;
    case "Deleted":
      if (isset($user['cap.Delete']))
        $string .= " <a href=\"$urlroot/changestate.phtml?state=Active&forumname=" . $forum['shortname'] . "&mid=" . $msg['mid'] . "\">ud</a>";
      break;
    case "Active":
      $string .= " <a href=\"$urlroot/changestate.phtml?state=Moderated&forumname=" . $forum['shortname'] . "&mid=" . $msg['mid'] . "\">mm</a>";
      if (isset($user['cap.Delete']))
        $string .= " <a href=\"$urlroot/changestate.phtml?state=Deleted&forumname=" . $forum['shortname'] . "&mid=" . $msg['mid'] . "\">dm</a>";
      break;
    }

    if ($forum['version'] >= 2) {
      if (isset($flags['Locked']))
        $string .= " <a href=\"$urlroot/unlock.phtml?forumname=" . $forum['shortname'] . "&mid=" . $msg['mid'] . "\">ul</a>";
      else
        $string .= " <a href=\"$urlroot/lock.phtml?forumname=" . $forum['shortname'] . "&mid=" . $msg['mid'] . "\">lm</a>";
    }
  }

  if (isset($user) && isset($flags['NewStyle']) && $msg['aid'] == $user['aid'])
    $string .= " <a href=\"$urlroot/edit.phtml?forumname=" . $forum['shortname'] . "&mid=" . $msg['mid'] . "\">edit</a>";

  $string .= "</li>\n";

  return $string;
}

function display_thread($thread)
{
  global $user, $forum, $ulkludge;

  $index = find_msg_index($thread['mid']);
  $sql = "select mid, tid, pid, aid, state, date, subject, flags, name, email, views, DATE_FORMAT(date, \"%Y%m%d%H%i%s\") as tstamp, UNIX_TIMESTAMP(date) as unixtime from messages$index where tid = '" . $thread['tid'] . "' order by mid";
  $result = mysql_db_query("forum_" . $forum['shortname'], $sql) or sql_error($sql);
  while ($message = mysql_fetch_array($result))
    $messages[] = $message;

  /* We assume a thread won't span more than 1 index */
  $index++;
  if (isset($indexes[$index])) {
    $sql = "select mid, tid, pid, aid, state, date, subject, flags, name, email, DATE_FORMAT(date, \"%Y%m%d%H%i%s\") as tstamp from messages$index where tid = '" . $thread['tid'] . "' order by mid";
    $result = mysql_db_query("forum_" . $forum['shortname'], $sql) or sql_error($sql);
    while ($message = mysql_fetch_array($result))
      $messages[] = $message;
  }

  if (!isset($messages) || !count($messages))
    return "";

  /* Filter out moderated or deleted messages, if necessary */
  reset($messages);
  while (list($key, $msg) = each($messages)) {
    $tree[$msg['mid']][] = $key;
    $tree[$msg['pid']][] = $key;
  }

  $messages = filter_messages($messages, $tree, reset($tree));

  $count = count($messages);

  $messagestr = "<ul class=\"thread\">\n";
  if (isset($user['prefs.Collapsed']))
    $messagestr .= print_collapsed($thread, reset($messages), $count - 1);
  else
    $messagestr .= list_thread(print_subject, $messages, $tree, reset($tree));

  if (!$ulkludge || isset($user['prefs.SimpleHTML']))
    $messagestr .= "</ul>";

  return array($count, $messagestr);
}

# Mozilla/4.0 (compatible; MSIE 5.0; Windows NT; DigExt)
# Mozilla/4.7 (Macintosh; U; PPC)
$ulkludge =
  ereg("^Mozilla/[0-9]\.[0-9]+ \(compatible; MSIE .*", $HTTP_USER_AGENT) ||
  ereg("^Mozilla/[0-9]\.[0-9]+ \(Macintosh; .*", $HTTP_USER_AGENT);

$numshown = 0;

if (isset($tthreads)) {
  reset($tthreads);
  while (list(, $tthread) = each($tthreads)) {
echo "<!-- checking " . $tthread['tid'] . " -->\n";
    $index = find_thread_index($tthread['tid']);
    if ($index < 0) {
      echo "<!-- Warning: Invalid tthread! $index, " . $tthread['tid'] . " -->\n";
      continue;
    }

    /* Some people have duplicate threads tracked, they'll eventually fall */
    /*  off, but for now this is a simple workaround */
    if (isset($threadshown[$tthread['tid']]))
      continue;

    $sql = "select * from threads$index where tid = '" . addslashes($tthread['tid']) . "'";
    $result = mysql_db_query("forum_" . $forum['shortname'], $sql) or sql_error($sql);

    if (!mysql_num_rows($result))
      continue;

    $thread = mysql_fetch_array($result);
    if ($thread['tstamp'] > $tthread['tstamp']) {
      $threadshown[$thread['tid']] = 'true';

      if ($curpage != 1)
        continue;

      $numshown++;

      $color = ($numshown % 2) ? "#ccccee" : "#ddddff";
      $trtags = " bgcolor=\"$color\"";

      $tpl->assign(TRTAGS, $trtags);

      list($count, $messagestr) = display_thread($thread);

      /* If the thread is tracked, we know they are a user already */
      $messagelinks = "<a href=\"$urlroot/untrack.phtml?forumname=" . $forum['shortname'] . "&tid=" . $thread['tid'] . "&page=" . $SCRIPT_NAME . $PATH_INFO . "\"><font color=\"#d00000\">ut</font></a>";
      if ($count > 1) {
        if (!isset($user['prefs.Collapsed']))
          $messagelinks .= "<br>";
        else
          $messagelinks .= " ";

        $messagelinks .= "<a href=\"$urlroot/markuptodate.phtml?forumname=" . $forum['shortname'] . "&tid=" . $thread['tid'] . "&page=" . $SCRIPT_NAME . $PATH_INFO . "\"><font color=\"#0000f0\">up</font></a>";
      }

      $tpl->assign(MESSAGES, $messagestr);
      $tpl->assign(MESSAGELINKS, $messagelinks);

      $tpl->parse(MESSAGE_ROWS, ".showforum_row");
    }
  }
}

$skipthreads = ($curpage - 1) * $threadsperpage;

$threadtable = count($indexes) - 1;

while (isset($indexes[$threadtable])) {
  if (threads($threadtable) > $skipthreads)
    break;

  $skipthreads -= threads($threadtable);
  $threadtable--;
}

while ($numshown < $threadsperpage) {
  unset($result);

  while (isset($indexes[$threadtable])) {
    $index = $indexes[$threadtable];

    $ttable = "threads" . $index['iid'];
    $mtable = "messages" . $index['iid'];

    /* Get some more results */
    $sql = "select $ttable.tid, $ttable.mid from $ttable, $mtable where" .
	" $ttable.tid >= " . $index['mintid'] . " and" .
	" $ttable.tid <= " . $index['maxtid'] . " and" .
	" $ttable.mid >= " . $index['minmid'] . " and" .
	" $ttable.mid <= " . $index['maxmid'] . " and" .
	" $ttable.mid = $mtable.mid and ( $mtable.state = 'Active' ";
    if (isset($user['cap.Moderate']))
      $sql .= "or $mtable.state = 'Moderated' or $mtable.state = 'Deleted' "; 
    else if (isset($user['prefs.ShowModerated']))
      $sql .= "or $mtable.state = 'Moderated' ";

    /* Sort all of the messages by date and descending order */
    $sql .= ") order by $ttable.tid desc";

    /* Limit to the maximum number of threads per page */
    $sql .= " limit $skipthreads," . ($threadsperpage - $numshown);

    $result = mysql_db_query("forum_" . $forum['shortname'], $sql) or sql_error($sql);

    if (mysql_num_rows($result))
      break;

    $threadtable--;
  }

  if (!isset($indexes[$threadtable]))
    break;

  $skipthreads += mysql_num_rows($result);

  while ($thread = mysql_fetch_array($result)) {
    if (isset($threadshown[$thread['tid']]))
      continue;

    $numshown++;

    $color = ($numshown % 2) ? "#eeeeee" : "#ffffff";
    $trtags = " bgcolor=\"$color\"";

    $tpl->assign(TRTAGS, $trtags);

    list($count, $messagestr) = display_thread($thread);

    if (isset($user)) {
      if (isset($tthreads_by_tid[$thread['tid']]))
        $messagelinks = " <a href=\"$urlroot/untrack.phtml?forumname=" . $forum['shortname'] . "&tid=" . $thread['tid'] . "&page=" . $SCRIPT_NAME . $PATH_INFO . "\"><font color=\"#d00000\">ut</font></a>";
      else
        $messagelinks = " <a href=\"$urlroot/track.phtml?forumname=" . $forum['shortname'] . "&tid=" . $thread['tid'] . "&page=" . $SCRIPT_NAME . $PATH_INFO . "\"><font color=\"#00d000\">tt</font></a>";
    } else
      $messagelinks = "";

    $tpl->assign(MESSAGES, $messagestr);
    $tpl->assign(MESSAGELINKS, $messagelinks);

    $tpl->parse(MESSAGE_ROWS, ".showforum_row");
  }
}

if (!$numshown)
  $tpl->assign(MESSAGE_ROWS, "<font size=\"+1\">No messages in this forum</font><br>");

$action = "post";

include('post.inc');

$tpl->parse(HEADER, 'header');
$tpl->parse(FOOTER, 'footer');
$tpl->parse(CONTENT, 'showforum');
$tpl->FastPrint(CONTENT);
?>