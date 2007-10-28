<DIV ID="d11" CLASS="d11"></div>
<?PHP
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
Xoffset=-260;
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
if ($_GET)
{
	extract ($_GET);
}
elseif ($_POST)
{
	extract ($_POST);
}

$chosenleague = 1;
//Get week
if (!$chosenweek)
{
    if (date("w") >=3 || date("w") == 0)
        $chosenweek = date("W") - 35;
    else
        $chosenweek = date("W") - 36;
}
elseif ($chosenweek == -1)
{
    $chosenweek = 0;
}

//Check for HFA
$HFAQuery = "SELECT homefieldadv FROM leagues WHERE leagueid = $chosenleague";
$HFA = $cffldb->getOne($HFAQuery );
if ($chosenleague == 1 && $chosenweek > 45)
{
    $HFA = 0;
}
//Load the schedule
$SchedQuery = "SELECT * FROM schedule WHERE week = $chosenweek AND leagueid = $chosenleague";
$SchedResult = $cffldb->query($SchedQuery );
DBerror($SchedQuery,$SchedResult);
echo "<TABLE border=\"1\" width=\"700\">";

while ($ProcessSched = $SchedResult->fetchrow(DB_FETCHMODE_ASSOC))
{
	$FirstTeam = $ProcessSched['visitorid'];
	$FirstTeamQuery = "SELECT teamname, primcolor, seccolor FROM teams WHERE teamid = $FirstTeam";
	$FirstTeamResult = $cffldb->query($FirstTeamQuery);
	DBerror($FirstTeamQuery,$FirstTeamResult);
	$FirstTeamRow = $FirstTeamResult->fetchrow(DB_FETCHMODE_ASSOC, 0);
	$FirstTeamName = $FirstTeamRow['teamname'];
	$FirstTeamColor = "#".$FirstTeamRow['primcolor'];
	$FirstTeamFontColor = "#".$FirstTeamRow['seccolor'];
	$SecondTeam = $ProcessSched['homeid'];
	$SecondTeamQuery = "SELECT teamname, primcolor, seccolor FROM teams WHERE teamid = $SecondTeam";
	$SecondTeamResult = $cffldb->query($SecondTeamQuery);
	DBerror($SecondTeamQuery,$SecondTeamResult);
    $SecondTeamRow = $SecondTeamResult->fetchrow(DB_FETCHMODE_ASSOC, 0);
	$SecondTeamName = $SecondTeamRow['teamname'];
	$SecondTeamColor = "#".$SecondTeamRow['primcolor'];
	$SecondTeamFontColor = "#".$SecondTeamRow['seccolor'];

	echo "<TR><a name=\"$FirstTeamName\"></a><a name=\"$SecondTeamName\"></a><TD colspan=\"2\" align=\"center\" bgcolor=\"$FirstTeamColor\" width=\"40%\"><a href=\"http://www.ctgffl.com/lgmgmt/teams.php?teamid=$FirstTeam\"><font color=\"$FirstTeamFontColor\">$FirstTeamName</font></a></TD><TD></TD><TD colspan=\"2\" align=\"center\" bgcolor=\"$SecondTeamColor\" width=\"40%\"><a href=\"http://www.ctgffl.com/lgmgmt/teams.php?teamid=$SecondTeam\"><font color=\"$SecondTeamFontColor\">$SecondTeamName</a></font></TD></TR>";
    echo "<TR><TD></TD><TD></TD><TD align=\"center\"><em>College</em></TD><TD></TD><TD></TD></TR>";
    $FirstTeamCollStatsQuery = "SELECT players.playerid as playerid, lastname, firstname, lgteamabbr, players.pos, posorder, points, lgteams.lgteamid as lgteamid FROM players, positionorder, playerlineups, lgteams WHERE week=$chosenweek AND playerlineups.playerid = players.playerid AND playerlineups.starter='Y' AND playerlineups.teamid=$FirstTeam AND positionorder.pos = players.pos AND players.lgteamid = lgteams.lgteamid AND year !='Pro' order by posorder, lastname";
	$FirstTeamCollStatsResult = $cffldb->query($FirstTeamCollStatsQuery );
	DBerror($FirstTeamCollStatsQuery,$FirstTeamCollStatsResult);
	$FirstTeamCollStatsNumrows = $FirstTeamCollStatsResult->numrows();
/*here's one*/	$SecondTeamCollStatsQuery = "SELECT players.playerid as playerid, lastname, firstname, lgteamabbr, players.pos, points, lgteams.lgteamid as lgteamid FROM players, positionorder, playerlineups, lgteams WHERE week=$chosenweek AND playerlineups.playerid = players.playerid AND playerlineups.starter='Y' AND playerlineups.teamid=$SecondTeam AND positionorder.pos = players.pos AND players.lgteamid = lgteams.lgteamid AND year !='Pro' order by posorder, lastname";
	$SecondTeamCollStatsResult = $cffldb->query($SecondTeamCollStatsQuery );
	DBerror($SecondTeamCollStatsQuery,$SecondTeamCollStatsResult);
	$SecondTeamCollStatsNumrows = $SecondTeamCollStatsResult->numrows();
	if ($FirstTeamCollStatsNumrows == 0 && $SecondTeamCollStatsNumrows == 0)
	{
		echo "<TR><TD colspan=\"5\" align=\"center\">No score reported yet</TD></TR>";
	}
	else
	{
		$FirstTeamScore = 0;
		$SecondTeamScore = 0;
		$maxrows = max($FirstTeamCollStatsNumrows,$SecondTeamCollStatsNumrows);
		for ($i=0; $i<$maxrows; $i++)
		{
			$first_completions = 0;
			$first_attempts = 0;
			$first_passyds =0;
			$first_passtds=0;
			$first_passints=0;
			$first_carries=0;
			$first_rushyds=0;
			$first_rushtds=0;
			$first_recepts=0;
			$first_recyds=0;
			$first_rectds=0;
			$first_xp2=0;
			$first_kickreturntds=0;
			$first_puntreturntds=0;
			$first_pointsallow=0;
			$first_defwin = 0;
			$first_ydsallow = 0;
			$first_deftd=0;
			$first_specialteamtd=0;
			$first_safety=0;
			$first_sacks=0;
			$first_intercepts=0;
			$first_fumblerec=0;
			$first_fgmade=0;
			$first_patmade=0;
			$second_completions = 0;
			$second_attempts = 0;
			$second_passyds =0;
			$second_passtds=0;
			$second_passints=0;
			$second_carries=0;
			$second_rushyds=0;
			$second_rushtds=0;
			$second_recepts=0;
			$second_recyds=0;
			$second_rectds=0;
			$second_xp2=0;
			$second_kickreturntds=0;
			$second_puntreturntds=0;
			$second_pointsallow=0;
			$second_defwin = 0;
			$second_ydsallow = 0;
			$second_deftd=0;
			$second_specialteamtd=0;
			$second_safety=0;
			$second_sacks=0;
			$second_intercepts=0;
			$second_fumblerec=0;
			$second_fgmade=0;
			$second_patmade=0;
			$FirstTeamCollRow = $FirstTeamCollStatsResult->fetchrow(DB_FETCHMODE_ASSOC, $i);
			$FirstPlayerID = $FirstTeamCollRow['playerid'];
			$FirstTeamColl = $FirstTeamCollRow['lgteamid'];
//			print_r($FirstTeamCollRow);
			$SecondTeamCollRow = $SecondTeamCollStatsResult->fetchrow(DB_FETCHMODE_ASSOC, $i);
			$SecondPlayerID = $SecondTeamCollRow['playerid'];
			if ($FirstTeamCollRow['pos'] != 'D')
			{
				$FirstPlayer = $FirstTeamCollRow['firstname'].' '.$FirstTeamCollRow['lastname'].' - '.$FirstTeamCollRow['lgteamabbr'];
                //$FirstPlayer = "<a href=\"http://www.ctgffl.com/lgmgmt/PlayerReport.php?player=$FirstPlayerID\">".$FirstPlayer."</a>";
			}
			else
			{
				$FirstPlayer = $FirstTeamCollRow['firstname'].' '.$FirstTeamCollRow['lastname'];
			}
			if ($SecondTeamCollRow['pos'] != 'D')
			{
				$SecondPlayer = $SecondTeamCollRow['firstname'].' '.$SecondTeamCollRow['lastname'].' - 	'.$SecondTeamCollRow['lgteamabbr'];
			}
			else
			{
				$SecondPlayer = $SecondTeamCollRow['firstname'].' '.$SecondTeamCollRow['lastname'];
			}
			$FirstPlayerPoints = $FirstTeamCollRow['points'];
			$firststatdetailquery = "SELECT * FROM playerstats WHERE playerid = $FirstPlayerID AND week = $chosenweek";
			$firststatdetail = $cffldb->getRow($firststatdetailquery);
			extract ($firststatdetail, EXTR_PREFIX_ALL, "first");
			$SecondPlayerPoints = $SecondTeamCollRow['points'];
            $secondstatdetailquery = "SELECT * FROM playerstats WHERE playerid = $SecondPlayerID AND week = $chosenweek";
			$secondstatdetail = $cffldb->getRow($secondstatdetailquery);
			extract ($secondstatdetail, EXTR_PREFIX_ALL, "second");
			$position = $FirstTeamCollRow['pos'];
//			echo "<TR><TD width=\"38%\">$FirstPlayer</TD><TD width=\"7%\" align=\"center\">$FirstPlayerPoints</TD><TD width=\"10%\" align=\"center\">$position</TD><TD width=\"38%\">$SecondPlayer</TD><TD width=\"7%\" align=\"center\">$SecondPlayerPoints</TD></tr>";
/* from here */
if ($FirstTeamColl)
    $FirstOpp = getncaaopponent($cffldb, $FirstTeamColl,$chosenweek);
if ($SecondTeamCollRow['lgteamid'])
    $SecondOpp = getncaaopponent($cffldb, $SecondTeamCollRow['lgteamid'],$chosenweek);
?>
			<TR><TD width="38%"><?PHP echo $FirstPlayer; ?></TD><TD width="7%" align="center"><A HREF="#"  style="text-decoration:none" ONMOUSEOVER="popup('<font color=#002064 face=arial size=2><?PHP if ($position != 'D' && $position != 'K') echo "<b>$FirstPlayer $FirstOpp: $FirstPlayerPoints</b><br>Passing: $first_completions -  $first_attempts - $first_passyds - $first_passtds TDs - $first_passints Ints<br>"."Rushing: $first_carries - $first_rushyds - $first_rushtds<br>Recving: $first_recepts - $first_recyds - $first_rectds<br>"."2XP: $first_xp2  Kick Return TDs: $first_kickrettds  Punt Return TDs: $first_puntrettds"; elseif ($position == 'K') echo "<b>$FirstPlayer $FirstOpp: $FirstPlayerPoints</b><br>Field Goals: $first_fgmade - Extra Points: $first_patmade<br>Passing: $first_completions -  $first_attempts - $first_passyds - $first_passtds TDs - $first_passints Ints<br>"."Rushing: $first_carries - $first_rushyds - $first_rushtds<br>Recving: $first_recepts - $first_recyds - $first_rectds<br>"."2XP: $first_xp2  Kick Return TDs: $first_kickreturntds  Punt Return TDs: $first_puntreturntds"; else { echo "<b>$FirstPlayer $FirstOpp</b><br>Points allowed: $first_pointsallow<br>Defensive TDs: $first_deftd<br>Safeties: $first_safety<br>Sacks: $first_sacks<br>Interceptions: $first_intercepts - Fumble Recoveries: $first_fumblerec<br>";}?></font>','#CCCCCC')" ONMOUSEOUT="removeBox()"><?PHP echo $FirstPlayerPoints; ?></a></TD>
            <TD width="10%" align="center"><?PHP echo $position; ?></TD>
            <TD width=\"38%\"><?PHP echo $SecondPlayer; ?></TD><TD width="7%" align="center"><A HREF="#"  style="text-decoration:none" ONMOUSEOVER="popup('<font color=#002064 face=arial size=2><?PHP if ($position != 'D' && $position != 'K') echo "<b>$SecondPlayer $SecondOpp: $SecondPlayerPoints<br></b><br>Passing: $second_completions - $second_attempts - $second_passyds - $second_passtds TDs - $second_passints Ints<br>"."Rushing: $second_carries - $second_rushyds - $second_rushtds<br>Recving: $second_recepts - $second_recyds - $second_rectds<br>"."2XP: $second_xp2  Kick Return TDs: $second_kickrettds  Punt Return TDs: $second_puntrettds"; elseif ($position == 'K') echo "<b>$SecondPlayer $SecondOpp: $SecondPlayerPoints</b><br>Field Goals: $second_fgmade - Extra Points: $second_patmade<br>Passing: $second_completions -  $second_attempts - $second_passyds - $second_passtds TDs - $second_passints Ints<br>"."Rushing: $second_carries - $second_rushyds - $second_rushtds<br>Recving: $second_recepts - $second_recyds - $second_rectds<br>"."2XP: $second_xp2  Kick Return TDs: $second_kickreturntds  Punt Return TDs: $second_puntreturntds"; else {echo "<b>$SecondPlayer $SecondOpp</b><br>Points allowed: $second_pointsallow<br>Defensive TDs: $second_deftd<br>Safeties: $second_safety<br>Sacks: $second_sacks<br>Interceptions: $second_intercepts - Fumble Recoveries: $second_fumblerec<br>";}?></font>','#CCCCCC')" ONMOUSEOUT="removeBox()"><?PHP echo $SecondPlayerPoints;?></a></TD></tr>
            <?PHP
/* to here */			$FirstTeamScore = $FirstTeamScore + $FirstPlayerPoints;
			$SecondTeamScore = $SecondTeamScore + $SecondPlayerPoints;
		}
		if ($HFA)
		{
			echo "<TR><TD width=\"38%\"></TD><TD width=\"7%\" align=\"center\"></TD><TD width=\"10%\" align=\"center\">HFA</TD><TD width=\"38%\"></TD><TD width=\"7%\" align=\"center\">$HFA</TD></tr>";
			$SecondTeamScore+= $HFA;
		}
		if ($FirstTeamScore > $SecondTeamScore)
		{
		echo "<TR bgcolor=\"#999999\"><TD width=\"38%\"></TD><TD width=\"7%\" align=\"center\"><b>$FirstTeamScore</b></TD><TD width=\"10%\" align=\"center\"><B>Total</B></TD><TD width=\"38%\"></TD><TD width=\"7%\" align=\"center\">$SecondTeamScore</TD></TR>";
		}
		else
		{
		echo "<TR bgcolor=\"#999999\"><TD width=\"38%\"></TD><TD width=\"7%\" align=\"center\">$FirstTeamScore</TD><TD width=\"10%\" align=\"center\"><B>Total</B></TD><TD width=\"38%\"></TD><TD width=\"7%\" align=\"center\"><b>$SecondTeamScore</b></TD></TR>";
		}
	}
    echo "<TR><TD></TD><TD></TD><TD align=\"center\"><em>Pro</em></TD><TD></TD><TD></TD></TR>";
    $FirstTeamProStatsQuery = "SELECT players.playerid as playerid, lastname, firstname, lgteamabbr, players.pos, posorder, points, lgteams.lgteamid as lgteamid FROM players, positionorder, playerlineups, lgteams WHERE week=$chosenweek AND playerlineups.playerid = players.playerid AND playerlineups.starter='Y' AND playerlineups.teamid=$FirstTeam AND positionorder.pos = players.pos AND players.lgteamid = lgteams.lgteamid AND year ='Pro' order by posorder, lastname";
    $FirstTeamProStatsResult = $cffldb->query($FirstTeamProStatsQuery );
    DBerror($FirstTeamProStatsQuery,$FirstTeamProStatsResult);
    $FirstTeamProStatsNumrows = $FirstTeamProStatsResult->numrows();
	$SecondTeamProStatsQuery = "SELECT players.playerid as playerid, lastname, firstname, lgteamabbr, players.pos, points, lgteams.lgteamid as lgteamid FROM players, positionorder, playerlineups, lgteams WHERE week=$chosenweek AND playerlineups.playerid = players.playerid AND playerlineups.starter='Y' AND playerlineups.teamid=$SecondTeam AND positionorder.pos = players.pos AND players.lgteamid = lgteams.lgteamid AND year ='Pro' order by posorder, lastname";
    $SecondTeamProStatsResult = $cffldb->query($SecondTeamProStatsQuery );
    DBerror($SecondTeamProStatsQuery,$SecondTeamProStatsResult);
    $SecondTeamProStatsNumrows = $SecondTeamProStatsResult->numrows();
    if ($FirstTeamProStatsNumrows == 0 && $SecondTeamProStatsNumrows == 0)
    {
        echo "<TR><TD colspan=\"5\" align=\"center\">No score reported yet</TD></TR>";
    }
    else
    {
        $FirstTeamScore = 0;
        $SecondTeamScore = 0;
        $maxrows = max($FirstTeamProStatsNumrows,$SecondTeamProStatsNumrows);
        for ($i=0; $i<$maxrows; $i++)
        {
            $first_completions = 0;
            $first_attempts = 0;
            $first_passyds =0;
            $first_passtds=0;
            $first_passints=0;
            $first_carries=0;
            $first_rushyds=0;
            $first_rushtds=0;
            $first_recepts=0;
            $first_recyds=0;
            $first_rectds=0;
            $first_xp2=0;
            $first_kickreturntds=0;
            $first_puntreturntds=0;
            $first_pointsallow=0;
            $first_defwin = 0;
            $first_ydsallow = 0;
            $first_deftd=0;
            $first_specialteamtd=0;
            $first_safety=0;
            $first_sacks=0;
            $first_intercepts=0;
            $first_fumblerec=0;
            $first_fgmade=0;
            $first_patmade=0;
            $second_completions = 0;
            $second_attempts = 0;
            $second_passyds =0;
            $second_passtds=0;
            $second_passints=0;
            $second_carries=0;
            $second_rushyds=0;
            $second_rushtds=0;
            $second_recepts=0;
            $second_recyds=0;
            $second_rectds=0;
            $second_xp2=0;
            $second_kickreturntds=0;
            $second_puntreturntds=0;
            $second_pointsallow=0;
            $second_defwin = 0;
            $second_ydsallow = 0;
            $second_deftd=0;
            $second_specialteamtd=0;
            $second_safety=0;
            $second_sacks=0;
            $second_intercepts=0;
            $second_fumblerec=0;
            $second_fgmade=0;
            $second_patmade=0;
            $FirstTeamProRow = $FirstTeamProStatsResult->fetchrow(DB_FETCHMODE_ASSOC, $i);
            $FirstPlayerID = $FirstTeamProRow['playerid'];
            $FirstTeamPro = $FirstTeamProRow['lgteamid'];
//			print_r($FirstTeamProRow);
            $SecondTeamProRow = $SecondTeamProStatsResult->fetchrow(DB_FETCHMODE_ASSOC, $i);
            $SecondPlayerID = $SecondTeamProRow['playerid'];
            if ($FirstTeamProRow['pos'] != 'D')
            {
                $FirstPlayer = $FirstTeamProRow['firstname'].' '.$FirstTeamProRow['lastname'].' - '.$FirstTeamProRow['lgteamabbr'];
            }
            else
            {
                $FirstPlayer = $FirstTeamProRow['firstname'].' '.$FirstTeamProRow['lastname'];
            }
            if ($SecondTeamProRow['pos'] != 'D')
            {
                $SecondPlayer = $SecondTeamProRow['firstname'].' '.$SecondTeamProRow['lastname'].' - 	'.$SecondTeamProRow['lgteamabbr'];
            }
            else
            {
                $SecondPlayer = $SecondTeamProRow['firstname'].' '.$SecondTeamProRow['lastname'];
            }
            $FirstPlayerPoints = $FirstTeamProRow['points'];
            $firststatdetailquery = "SELECT * FROM playerstats WHERE playerid = $FirstPlayerID AND week = $chosenweek";
            $firststatdetail = $cffldb->getRow($firststatdetailquery);
            extract ($firststatdetail, EXTR_PREFIX_ALL, "first");
            $SecondPlayerPoints = $SecondTeamProRow['points'];
            $secondstatdetailquery = "SELECT * FROM playerstats WHERE playerid = $SecondPlayerID AND week = $chosenweek";
            $secondstatdetail = $cffldb->getRow($secondstatdetailquery);
            extract ($secondstatdetail, EXTR_PREFIX_ALL, "second");
            $position = $FirstTeamProRow['pos'];
//			echo "<TR><TD width=\"38%\">$FirstPlayer</TD><TD width=\"7%\" align=\"center\">$FirstPlayerPoints</TD><TD width=\"10%\" align=\"center\">$position</TD><TD width=\"38%\">$SecondPlayer</TD><TD width=\"7%\" align=\"center\">$SecondPlayerPoints</TD></tr>";
/* from here */
if ($FirstTeamPro)
    $FirstOpp = getncaaopponent($cffldb, $FirstTeam,$chosenweek);
if ($SecondTeamProRow['lgteamid'])
    $SecondOpp = getncaaopponent($cffldb, $SecondTeamProRow['lgteamid'],$chosenweek);
?>
            <TR><TD width="38%"><?PHP echo $FirstPlayer; ?></TD><TD width="7%" align="center"><A HREF="#"  style="text-decoration:none" ONMOUSEOVER="popup('<font color=#002064 face=arial size=2><?PHP if ($position != 'D' && $position != 'K') echo "<b>$FirstPlayer $FirstOpp: $FirstPlayerPoints</b><br>Passing: $first_completions -  $first_attempts - $first_passyds - $first_passtds TDs - $first_passints Ints<br>"."Rushing: $first_carries - $first_rushyds - $first_rushtds<br>Recving: $first_recepts - $first_recyds - $first_rectds<br>"."2XP: $first_xp2  Kick Return TDs: $first_kickrettds  Punt Return TDs: $first_puntrettds"; elseif ($position == 'K') echo "<b>$FirstPlayer $FirstOpp: $FirstPlayerPoints</b><br>Field Goals: $first_fgmade - Extra Points: $first_patmade<br>Passing: $first_completions -  $first_attempts - $first_passyds - $first_passtds TDs - $first_passints Ints<br>"."Rushing: $first_carries - $first_rushyds - $first_rushtds<br>Recving: $first_recepts - $first_recyds - $first_rectds<br>"."2XP: $first_xp2  Kick Return TDs: $first_kickreturntds  Punt Return TDs: $first_puntreturntds"; else { echo "<b>$FirstPlayer $FirstOpp</b><br>Points allowed: $first_pointsallow<br>Defensive TDs: $first_deftd<br>Safeties: $first_safety<br>Sacks: $first_sacks<br>Interceptions: $first_intercepts - Fumble Recoveries: $first_fumblerec<br>";}?></font>','#CCCCCC')" ONMOUSEOUT="removeBox()"><?PHP echo $FirstPlayerPoints; ?></a></TD>
            <TD width="10%" align="center"><?PHP echo $position; ?></TD>
            <TD width=\"38%\"><?PHP echo $SecondPlayer; ?></TD><TD width="7%" align="center"><A HREF="#"  style="text-decoration:none" ONMOUSEOVER="popup('<font color=#002064 face=arial size=2><?PHP if ($position != 'D' && $position != 'K') echo "<b>$SecondPlayer $SecondOpp: $SecondPlayerPoints<br></b><br>Passing: $second_completions - $second_attempts - $second_passyds - $second_passtds TDs - $second_passints Ints<br>"."Rushing: $second_carries - $second_rushyds - $second_rushtds<br>Recving: $second_recepts - $second_recyds - $second_rectds<br>"."2XP: $second_xp2  Kick Return TDs: $second_kickrettds  Punt Return TDs: $second_puntrettds"; elseif ($position == 'K') echo "<b>$SecondPlayer $SecondOpp: $SecondPlayerPoints</b><br>Field Goals: $second_fgmade - Extra Points: $second_patmade<br>Passing: $second_completions -  $second_attempts - $second_passyds - $second_passtds TDs - $second_passints Ints<br>"."Rushing: $second_carries - $second_rushyds - $second_rushtds<br>Recving: $second_recepts - $second_recyds - $second_rectds<br>"."2XP: $second_xp2  Kick Return TDs: $second_kickreturntds  Punt Return TDs: $second_puntreturntds"; else {echo "<b>$SecondPlayer $SecondOpp</b><br>Points allowed: $second_pointsallow<br>Defensive TDs: $second_deftd<br>Safeties: $second_safety<br>Sacks: $second_sacks<br>Interceptions: $second_intercepts - Fumble Recoveries: $second_fumblerec<br>";}?></font>','#CCCCCC')" ONMOUSEOUT="removeBox()"><?PHP echo $SecondPlayerPoints;?></a></TD></tr>
            <?PHP
/* to here */			$FirstTeamScore = $FirstTeamScore + $FirstPlayerPoints;
            $SecondTeamScore = $SecondTeamScore + $SecondPlayerPoints;
        }
        if ($HFA)
        {
            echo "<TR><TD width=\"38%\"></TD><TD width=\"7%\" align=\"center\"></TD><TD width=\"10%\" align=\"center\">HFA</TD><TD width=\"38%\"></TD><TD width=\"7%\" align=\"center\">$HFA</TD></tr>";
            $SecondTeamScore+= $HFA;
        }
        if ($FirstTeamScore > $SecondTeamScore)
        {
        echo "<TR bgcolor=\"#999999\"><TD width=\"38%\"></TD><TD width=\"7%\" align=\"center\"><b>$FirstTeamScore</b></TD><TD width=\"10%\" align=\"center\"><B>Total</B></TD><TD width=\"38%\"></TD><TD width=\"7%\" align=\"center\">$SecondTeamScore</TD></TR>";
        }
        else
        {
        echo "<TR bgcolor=\"#999999\"><TD width=\"38%\"></TD><TD width=\"7%\" align=\"center\">$FirstTeamScore</TD><TD width=\"10%\" align=\"center\"><B>Total</B></TD><TD width=\"38%\"></TD><TD width=\"7%\" align=\"center\"><b>$SecondTeamScore</b></TD></TR>";
        }
    }
}
echo "</TABLE>";
?>
<script>
initThis();
</script>
<?PHP
include ("../footer.php");
?>
