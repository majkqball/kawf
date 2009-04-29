<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>{DOMAIN} Forums: {FORUM_NAME}</title>
<link rel=StyleSheet href="{CSS_HREF}" type="text/css">
</head>

<body bgcolor="#ffffff">

{HEADER}

<center>
{AD}
</center>

<hr width="100%" size="1">

<table width="100%">
<tr>
{FORUM_HEADER}
</tr>
</table>

<table width="100%" border="0" cellspacing="0" cellpadding="0">
<tr class="tools">
  <td align="left">
  <a href="/tips/?page={PAGE}" target="_blank">Forum Tips</a>
| <a href="/search/?forum={FORUM_SHORTNAME}&amp;page={PAGE}" target="_blank">Search Forums</a>
| <a href="#post"><b>Post New Thread</b></a>
  </td>
<!-- BEGIN update_all -->
  <td align="right">
    <a href="/{FORUM_SHORTNAME}/markuptodate.phtml?tid=all&amp;page={PAGE}&amp;token={USER_TOKEN}&amp;time={TIME}">Update all</a>
  </td>
<!-- END update_all -->
</tr>
<tr class="tools">
  <td align="left" valign="bottom"><b>Page:</b> {PAGES}</td>
  <td align="right" valign="bottom">{NUMTHREADS} threads in {NUMPAGES} pages</td>
</tr>
<tr>
<td>
<form method="get" action="/redirect.phtml" style="display: inline; margin: 0;">
<select name="url" onChange="if (this.selectedIndex) { location = this.options[this.selectedIndex].value; }">
<option value="">Choose Discussion Forum</option>
<option value="/other/">Miscellaneous Forum</option>
<option value="/pr0n/">Pr0n Forum</option>
<option value="/swapmeet/">Ye Olde Ghetto Swapmeet</option>
<option value="/photos/">Photo Forum</option>
<option value="/s4/">B5 S4 Forum</option>
<option value="/food/">Piehole Stuffing Forum</option>
<option value="/flames/">FYYFF Forum</option>
<option value="/wit/">Investment Forum</option>
<option value="">---</option>
<option value="/feedback/">Feedback Forum</option>
<option value="/test/">Test Forum</option>
<option value="">---</option>
<option value="/tracking.phtml">Your tracked threads</option>
</select>
<noscript><input type="submit" value="GO"></noscript>
</form>
</td>
</tr>
</table>


<!-- BEGIN normal -->
<table width="100%" border="0" cellpadding="2" cellspacing="2">
<!-- BEGIN row -->
<tr class="{CLASS}">
  <td>
{MESSAGES}
  </td>
  <td class="messagelinks" valign="top">
{MESSAGELINKS}
  </td>
</tr>
<!-- END row -->
</table>
<!-- END normal -->

<!-- BEGIN simple -->
<!-- BEGIN row -->
{MESSAGES}
<!-- END row -->
<!-- END simple -->

<table width="100%">
<tr class="tools">
  <td align="left" valign="bottom"><b>Page:</b> {PAGES}</td>
  <td align="right" valign="bottom">{NUMTHREADS} threads in {NUMPAGES} pages</td>
</tr>
</table>

<div class="forumpost">
<a name="post"><img src="/pics/post.gif" alt="post message"></a>
{FORM}
</div>

<!-- b>{ACTIVE_USERS}</b> users and <b>{ACTIVE_GUESTS}</b> guests have been browsing the forums in the last 15 minutes<p -->

{FOOTER}

</body>
</html>

