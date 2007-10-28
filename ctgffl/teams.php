<DIV ID="d11" CLASS="d11"></div>
<?php
include ("../mainfile.php");
include ("../header.php");
include('incCTGFFLfunctions.php');
include('incConnectDBs.php');
?>
<STYLE TYPE="text/css">
.D11
  {
   POSITION:absolute;
   VISIBILITY:hidden;
   Z-INDEX:200;
  }
</STYLE>

<script TYPE="text/javascript">
Xoffset=-60;
Yoffset= 20;
var isNS4=document.layers?true:false;
var isIE=document.all?true:false;
var isNS6=!isIE&&document.getElementById?true:false;
var old=!isNS4&&!isNS6&&!isIE;

var skn;
function initThis()
{
  if(isNS4)skn=document.d11;
  if(isIE)skn=document.all.d11.style;
  if(isNS6)skn=document.getElementById("d11").style;
}

function popup(_m,_b)
{
  var content="<TABLE BORDER=1 BORDERCOLOR=black CELLPADDING=2 CELLSPACING=0 "+"BGCOLOR="+_b+"><TD><FONT COLOR=black SIZE=2>"+_m+"</FONT></TD></TABLE>";
  if(old)
  {
    alert("You have an old web browser:\n"+_m);
	return;
  }
  else
  {
	if(isNS4)
	{
	  skn.document.open();
	  skn.document.write(content);
	  skn.document.close();
	  skn.visibility="visible";
	}
	if(isNS6)
	{
	  document.getElementById("d11").style.position="absolute";
	  document.getElementById("d11").style.left=x;
	  document.getElementById("d11").style.top=y;
	  document.getElementById("d11").innerHTML=content;
	  skn.visibility="visible";
	}
	if(isIE)
	{
	  document.all("d11").innerHTML=content;
	  skn.visibility="visible";
	}
  }
}

var x;
var y;
function get_mouse(e)
{
  x=(isNS4||isNS6)?e.pageX:event.clientX+document.body.scrollLeft;
  y=(isNS4||isNS6)?e.pageY:event.clientY+document.body.scrollLeft;
  if(isIE&&navigator.appVersion.indexOf("MSIE 4")==-1)
	  y+=document.body.scrollTop;
  skn.left=x+Xoffset;
  skn.top=y+Yoffset;
}


function removeBox()
{
  if(!old)
  {
	skn.visibility="hidden";
  }
}


if(isNS4)
  document.captureEvents(Event.MOUSEMOVE);
if(isNS6)
  document.addEventListener("mousemove", get_mouse, true);
if(isNS4||isIE)
  document.onmousemove=get_mouse;

</script>
<?PHP
$teamid = $_GET['teamid'];
$teaminfoquery = "SELECT * FROM teams WHERE teamid = $teamid";
$teaminforesult = $cffldb->query($teaminfoquery);
DBerror($teaminfoquery,$teaminforesult);
$teaminforow = $teaminforesult->fetchrow();
extract ($teaminforow, EXTR_PREFIX_ALL, "team");
//echo "<title>$team_teamname</title>";
//find top college player

$topcollidquery = "SELECT players.playerid as topid, SUM(points) as totalpoints FROM players, playerlineups WHERE playerlineups.playerid=players.playerid AND playerlineups.teamid = $teamid AND playerlineups.starter = 'Y' AND players.year != 'Pro' GROUP BY topid ORDER BY totalpoints DESC";
//$topcollidquery = "SELECT players.playerid as topid, dv FROM players, ctgffldv, playerteams WHERE playerteams.teamid = $teamid AND playerteams.playerid = players.playerid AND players.playerid = ctgffldv.playerid AND year != 'Pro' ORDER BY dv";
$topcollidresult = $cffldb->query($topcollidquery);
DBerror($topcollidquery,$topcollidresult);
$topcollidrow = $topcollidresult->fetchrow();
extract ($topcollidrow, EXTR_PREFIX_ALL, "topcoll");

$collnameline = getPlayerName($topcoll_topid, $cffldb);
$topcoll_picture = $topcoll_topid.".png";

//find top pro player

$topproidquery = "SELECT players.playerid as topid, SUM(points) as totalpoints FROM players, playerlineups WHERE playerlineups.playerid=players.playerid AND playerlineups.teamid = $teamid AND playerlineups.starter = 'Y' AND players.year = 'Pro' GROUP BY topid ORDER BY totalpoints DESC";
//$topproidquery = "SELECT players.playerid as topid, dv FROM players, ctgffldv, playerteams WHERE playerteams.teamid = $teamid AND playerteams.playerid = players.playerid AND players.playerid = ctgffldv.playerid ORDER BY dv";
$topproidresult = $cffldb->query($topproidquery);
$topproidrow = $topproidresult->fetchrow();
$toppro_id = $topproidrow['topid'];
DBerror($topproidquery,$topproidresult);

$pronameline = getPlayerName($toppro_id, $cffldb);
$toppro_picture = $toppro_id.".png";

?>
<table align="center" border="0">
<tr>
<TD align="center" width="50%"><table><tr><td align="center"><img src="http://www.ctgffl.com/images/players/<?PHP echo $topcoll_picture ?>" width="100" height="150"><BR><font size="2"><?PHP echo $collnameline ?></font></td><td align="center"><img src="http://www.ctgffl.com/images/players/<?PHP echo $toppro_picture ?>" width="100" height="150"><BR><font size="2"><?PHP echo $pronameline ?></font></td></tr></table></TD>
<TD align="center"></tD>
<?PHP

$teamownerquery = "SELECT a.name, a.uname, a.email, b.user_from FROM xoops_users a, xoops_user_profile b WHERE a.uid=$team_teamownerid AND a.uid = b.profileid";
$teamownerresult = $ctgffldb->query($teamownerquery);
DBerror($teamownerquery,$teamownerresult);
$teamownerrow = $teamownerresult->fetchrow();
extract ($teamownerrow, EXTR_PREFIX_ALL, "owner");
$coach = strtoupper($owner_name?$owner_name:$owner_uname);
?>
<TD valign="top" align = "center" width="300"><font size=3 color="#<?PHP if ($team_primcolor < $team_seccolor) echo $team_primcolor; else echo $team_seccolor;?>"><?PHP echo $team_teamname?></font><BR>Coach: <?PHP echo $coach?><BR><font size="2"><?PHP echo $owner_user_from?></font><BR><font size="1"><a href="mailto:<?PHP echo $owner_email?>">email: <?PHP echo $owner_email?></a></font></TD>
<TD align="center"><img src="http://www.ctgffl.com/images/teamlogos/<?PHP echo $team_largelogo ?>.png" height=150></TD>
</TR>
<TR>
<TD colspan=2 align="center" bgcolor="#000000"><font color="#009900">2006 SCHEDULE</font></TD>
<TD align="center" bgcolor="#000000" width="5%"><font color="#FFFFFF">&nbsp;</font></TD>
<TD align="center" bgcolor="#000000"><font color="#009900">Roster</font></TD>
</TR>
<TR>
<TD align="center" valign="top" colspan=2>
<table>
<font size=1>
<?PHP
$schedulequery = "SELECT * FROM schedule WHERE visitorid = $teamid OR homeid=$teamid ORDER BY week";
$scheduleresult = $cffldb->query($schedulequery);
DBerror($schedulequery,$scheduleresult);
while ($schedulerow = $scheduleresult->fetchrow())
{
	extract ($schedulerow, EXTR_PREFIX_ALL, "sched");
	$oppteamid = $sched_visitorid==$teamid ? $sched_homeid : $sched_visitorid;
	$oppquery = "SELECT teamname FROM teams WHERE teamid = $oppteamid";
	$oppteamname = $cffldb->getOne($oppquery);
	if ($teamid == $sched_visitorid)
		$attext = "AT ";
	else
		$attext = "";
	$dayinyear = (($sched_week+39)*7);
	$wednesday = date("M d", mktime (12,0,0,0,$dayinyear,2006));
	$tuesday = date("M d", mktime (12,0,0,0,$dayinyear+6,2006));
	$teamcollscorequery = "SELECT SUM(a.points) as teampoints FROM playerlineups a, players b WHERE a.playerid = b.playerid AND starter = 'Y' AND week = $sched_week AND teamid = $teamid AND year !='Pro' GROUP BY teamid";
	$teamcollpoints = $cffldb->getOne($teamcollscorequery);
    $teamcollpoints = round($teamcollpoints,1);
	$oppteamcollscorequery = "SELECT SUM(a.points) as teampoints FROM playerlineups a, players b WHERE a.playerid=b.playerid AND starter = 'Y' AND week = $sched_week AND teamid = $oppteamid AND year !='Pro' GROUP BY teamid";
	$oppteamcollpoints = $cffldb->getOne($oppteamcollscorequery);
    $oppteamcollpoints = round($oppteamcollpoints,1);
	$collfontcolor = $teamcollpoints > $oppteamcollpoints ? "green" : "red";

    $teamproscorequery = "SELECT SUM(a.points) as teampoints FROM playerlineups a, players b WHERE a.playerid = b.playerid AND starter = 'Y' AND week = $sched_week AND teamid = $teamid AND year ='Pro' GROUP BY teamid";
    $teampropoints = $cffldb->getOne($teamproscorequery);
    $teampropoints = round($teampropoints,1);
    $oppteamproscorequery = "SELECT SUM(a.points) as teampoints FROM playerlineups a, players b WHERE a.playerid=b.playerid AND starter = 'Y' AND week = $sched_week AND teamid = $oppteamid AND year ='Pro' GROUP BY teamid";
    $oppteampropoints = $cffldb->getOne($oppteamproscorequery);
    $oppteampropoints = round($oppteampropoints,1);
    $profontcolor = $teampropoints > $oppteampropoints ? "green" : "red";
    if ($sched_week == 0)
        $sched_week = -1; //allows the link to process Week 0 games
?>
<TR>
<TD><?PHP echo $wednesday.' - '.$tuesday; ?></TD><TD><?PHP echo "$attext<a href=\"http://www.ctgffl.com/lgmgmt/teams.php?teamid=$oppteamid\">$oppteamname</a>"; ?></TD><TD align="center"><?PHP echo "<a href=\"http://www.ctgffl.com/lgmgmt/ScoreReport.php?chosenleague=$team_leagueid&chosenweek=$sched_week#$oppteamname\"><font color=\"$collfontcolor\">$teamcollpoints - $oppteamcollpoints</font><br><font color=\"$profontcolor\">$teampropoints - $oppteampropoints</font></a>"; ?></TD><TD></TD>
</TR>
<?PHP
}
?>
</font>
</table>
<td><!--Divisional Record --></td>
<td>
<table>
<tr><td colspan=2 align="center"><em>College</em></td></tr>
<?PHP
     //show college players
$rosterquery = "SELECT players.playerid as playerid, lastname, firstname, lgteamabbr, year, players.pos as playerpos, dv, irweek FROM playerteams, players, positionorder, lgteams, ctgffldv WHERE players.playerid = ctgffldv.playerid AND playerteams.teamid = $teamid AND playerteams.playerid = players.playerid AND players.pos = positionorder.pos AND players.lgteamid = lgteams.lgteamid AND year != 'Pro' ORDER BY posorder, lastname";
$rosterresult = $cffldb->query($rosterquery);
DBerror($rosterquery,$rosterresult);
while ($rosterrow = $rosterresult->fetchrow())
{
	extract($rosterrow, EXTR_PREFIX_ALL, "ros");
	switch ($ros_year)
	{
		case 'Freshman':
			$ros_year='FR';
			break;
		case 'Sophomore':
			$ros_year='SO';
			break;
		case 'Junior':
			$ros_year='JR';
			break;
		case 'Senior':
			$ros_year='SR';
			break;
	}
	$pointsquery = "SELECT week, starter, points FROM playerlineups WHERE playerid = $ros_playerid and teamid = $team_teamid ORDER BY week";
	$pointsresult = $cffldb->query($pointsquery);
	DBerror($pointsquery,$pointsresult);
	?>
<tr>
<td><font size=1><?PHP if ($ros_irweek != -1) echo "<del>"; echo $ros_playerpos; if ($ros_irweek != -1) echo "</del>";?></font></td><td><A HREF="#"  style="text-decoration:none" ONMOUSEOVER="popup('<font color=#002064 face=arial size=2><?PHP echo "$ros_firstname $ros_lastname<br>";	while ($pointsrow = $pointsresult->fetchrow()){extract ($pointsrow, EXTR_PREFIX_ALL, "pts"); echo "Week $pts_week: ";if ($pts_starter=="Y"){echo "<b>$pts_points</b><br>";}else{echo "$pts_points<br>";}}?></font>','#CCCCCC')" ONMOUSEOUT="removeBox()"><font size=1><?PHP if ($ros_irweek != -1) echo "<del>"; echo "$ros_firstname $ros_lastname ($ros_year) - $ros_lgteamabbr ($ros_dv)"; if ($ros_irweek != -1) echo "</del>"; ?></font></a></td>
</tr>
<?PHP
}
?>
<tr><td colspan=2>&nbsp;</td></tr>
<tr><td colspan=2 align="center"><em>Pro</em></td></tr>
<?PHP
     //show pro players
$rosterquery = "SELECT players.playerid as playerid, lastname, firstname, lgteamabbr, year, players.pos as playerpos, dv, irweek FROM playerteams, players, positionorder, lgteams, ctgffldv WHERE players.playerid = ctgffldv.playerid AND playerteams.teamid = $teamid AND playerteams.playerid = players.playerid AND players.pos = positionorder.pos AND players.lgteamid = lgteams.lgteamid AND year = 'Pro' ORDER BY posorder, lastname";
$rosterresult = $cffldb->query($rosterquery);
DBerror($rosterquery,$rosterresult);
while ($rosterrow = $rosterresult->fetchrow())
{
	extract($rosterrow, EXTR_PREFIX_ALL, "ros");
	$pointsquery = "SELECT week, starter, points FROM playerlineups WHERE playerid = $ros_playerid and teamid = $team_teamid ORDER BY week";
	$pointsresult = $cffldb->query($pointsquery);
	DBerror($pointsquery,$pointsresult);
	?>
<tr>
<td><font size=1><?PHP if ($ros_irweek != -1) echo "<em>"; echo $ros_playerpos; if ($ros_irweek != -1) echo "</em>";?></font></td><td><A HREF="#"  style="text-decoration:none" ONMOUSEOVER="popup('<font color=#002064 face=arial size=2><?PHP echo "$ros_firstname $ros_lastname<br>";	while ($pointsrow = $pointsresult->fetchrow()){extract ($pointsrow, EXTR_PREFIX_ALL, "pts"); echo "Week $pts_week: ";if ($pts_starter=="Y"){echo "<b>$pts_points</b><br>";}else{echo "$pts_points<br>";}}?></font>','#CCCCCC')" ONMOUSEOUT="removeBox()"><font size=1><?PHP if ($ros_irweek != -1) echo "<del>"; echo "$ros_firstname $ros_lastname - $ros_lgteamabbr ($ros_dv)"; if ($ros_irweek != -1) echo "</del>"; ?></font></a></td>
</tr>
<?PHP
}
?>
</table>
</td>
</tr>
</table>
<script>
initThis();
</script>
<?PHP
$cffldb->disconnect();
include ("../footer.php");
?>
