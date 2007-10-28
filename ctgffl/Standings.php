<?php

// Connect to mySQL.
//
include ("../mainfile.php");
include ("../header.php");
include('incCTGFFLfunctions.php');
include('incConnectDBs.php');
?>
<TABLE>
<?PHP
$chosenleague = 1;
$currconf = '';
$currdiv = '';

$teamsquery = "SELECT teamname, conferenceid, divisionid, wins, losses, divwins, divlosses, confwins, conflosses, totalpoints FROM teams a, standings b WHERE a.teamid = b.teamid ORDER BY conferenceid, divisionid, wins DESC, divwins DESC, confwins DESC, totalpoints DESC";
$teamsresult = $cffldb->query($teamsquery );
DBerror($teamsquery, $teamsresult);

while ($teamsrow = $teamsresult->fetchrow(DB_FETCHMODE_ASSOC))
{
    extract($teamsrow);
    if ($currconf != $conferenceid)
    {
        $currconf = $conferenceid;
        $confnamequery = "SELECT conference_name FROM conferences WHERE conference_id = $conferenceid";
        $confname = $cffldb->getOne($confnamequery);
        echo "<TR><TD colspan=3 align=\"center\" WIDTH=30><strong>$confname</strong></td></tr>";
        echo "<TR><TD width=\"10\"></td><td width=\"10\">Record</td><td width=\"10\">Total<br>Points</td></tr>";
    }
    if ($currdiv != $divisionid)
    {
        $currdiv = $divisionid;
        $divnamequery = "SELECT division_name FROM divisions WHERE division_id = $divisionid";
        $divname = $cffldb->getOne($divnamequery);
        echo "<tr><br></tr><TR><TD width=\"10%\"><em>$divname</em></td></tr>";
    }
    echo "<TR><TD width=\"10\">$teamname</td><td width=\"10\">$wins-$losses</td><td width=\"10\">$totalpoints</td></tr>";
}

echo "</TABLE>";
$cffldb->disconnect();
include ("../footer.php");
?>
