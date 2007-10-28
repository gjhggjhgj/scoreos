<?PHP
include ("../mainfile.php");
include ("../header.php");
include('incCTGFFLfunctions.php');
include('incConnectDBs.php');

$FirstTeam = $_POST['SelectedGame'];
$week = $_POST['week'];

if ($_GET['SelectedGame'])
{
    $FirstTeam = $_GET['SelectedGame'];
    $fullstats = 1;
}

if (!$_GET['week'] && !$week)
{
    $transweek = date(W) - 35;
}
elseif ($week)
{
    $transweek = $week;
}
else
{
    $transweek = $_GET['week'];
    $week = $_GET['week'];   
}

if (date(w) == 0)
    $dayinyear = (($transweek+39)*7);
else
    $dayinyear = (($transweek+39)*7);

$wednesday = date("Y-m-d", mktime (12,0,0,0,$dayinyear-1,2007));
$tuesday = date("Y-m-d", mktime (12,0,0,0,$dayinyear+5,2007));

if (!$FirstTeam)
{
    echo "Games for Week $transweek";

    //Load the schedule
    $SchedQuery = "SELECT * FROM nflsched WHERE gamedate >= '$wednesday' AND gamedate <= '$tuesday'";
    echo $SchedQuery."<br>";
    $SchedResult = $cffldb->query($SchedQuery);
    ?>
    <form action="EnterStats.php" method="POST">
    <select name="SelectedGame">
    <?PHP
    while ($ProcessSched = $SchedResult->fetchrow(DB_FETCHMODE_ASSOC))
    {
        extract ($ProcessSched);
        $FirstTeam = $visitor;
        $visabbrquery = "SELECT lgteamabbr FROM lgteams WHERE lgteamid = $visitor";
        $visabbr = $cffldb->getOne($visabbrquery);
        DBerror($visabbrquery, $visabbr);
        
        $homeabbrquery = "SELECT lgteamabbr FROM lgteams WHERE lgteamid = $home";
        $homeabbr = $cffldb->getOne($homeabbrquery);
        DBerror($homeabbrquery, $homeabbr);

    	$Game = "$visabbr at $homeabbr";
    // Fill drop down
    ?>
    <option value="<?PHP echo "$FirstTeam"?>"><?PHP echo "$Game"?>
    <?PHP
    }
    ?>
    </select>
    <input type="hidden" value=<?PHP echo "$transweek"?> name="week">
    <input type="submit" name="submitbutton" value="Score this game">
    </form>
    <?PHP
}
else
{
    ?>
    <FORM method="post" action="ProcessStats.php">
    <INPUT type="hidden" Name="curwk" value="<?PHP echo "$transweek"?>">
    <TABLE cellpadding="1">
    <?PHP
    //Get opponent
    if ($FirstTeam < 900)
    {
        $SecondTeamQuery = "SELECT home from ncaasched where gamedate >= '$wednesday' and gamedate <= '$tuesday' and visitor='$FirstTeam'";
        echo $SecondTeamQuery."<br>";
        $SecondTeam = $cffldb->getOne($SecondTeamQuery);
    }
    else
    {
        $SecondTeamQuery = "SELECT home from nflsched where gamedate >= '$wednesday' and gamedate <= '$tuesday' and visitor='$FirstTeam'";
        echo $SecondTeamQuery."<br>";
        $SecondTeam = $cffldb->getOne($SecondTeamQuery);
    }

    $CurrentTeam = '';

    if ($fullstats)
    {
        $playerquery = "SELECT DISTINCT a.* FROM players a, playerstats b WHERE lgteamid=$FirstTeam AND a.playerid = b.playerid AND (b.carries > 1 OR b.attempts > 1 OR b.recepts > 1 OR b.puntreturns > 1 OR b.kickreturns > 1 OR b.patatt > 1) AND (pos != 'DB' AND pos != 'DL' AND pos != 'LB' AND pos !='P') AND firstname != 'TEAM' ORDER BY lastname, firstname";
    }
    else
    {
        $playerquery = "SELECT a.* FROM players a, playerlineups b WHERE b.week=$week AND a.playerid = b.playerid AND a.lgteamid=$FirstTeam AND (pos='QB' OR pos='RB' OR pos='WR' OR pos='TE' OR pos='K' OR pos='D') ORDER BY lastname, firstname";
    }
    $playerresult = $cffldb->query($playerquery);
    DBerror($playerquery,$playerresult);

    ?>
    <TR><TH><?PHP echo getTeamAbbr($cffldb, $FirstTeam); ?></TH></TR>
    <?PHP
    while ($playerrow = $playerresult->fetchRow())
    {
        extract($playerrow);
        echo "<TR><TH colspan=6>$firstname $lastname - $pos</th></tr>";
        if ($pos != 'D')
        {
        ?>
            <TR><TD>COMP<INPUT type="text" name="prostats[<?PHP echo $playerid ?>][completions]" size="2"></TD><TD>ATT<INPUT type="text" name="prostats[<?PHP echo $playerid ?>][attempts]" size="2"></TD><TD>YDS<INPUT type="text" name="prostats[<?PHP echo $playerid ?>][passyds]" size="2"></TD><TD>TDS<INPUT type="text" name="prostats[<?PHP echo $playerid ?>][passtds]" size="2"></TD><TD>INTS<INPUT type="text" name="prostats[<?PHP echo $playerid ?>][passints]" size="2"></TD></TR>";
            <TR><TD>RUSHES<INPUT type="text" name="prostats[<?PHP echo $playerid ?>][carries]" size="2"></TD><TD>YDS<INPUT type="text" name="prostats[<?PHP echo $playerid ?>][rushyds]" size="2"></TD><TD>TDS<INPUT type="text" name="prostats[<?PHP echo $playerid ?>][rushtds]" size="2"></TD><TD>XP2<INPUT type="text" name="prostats[<?PHP echo $playerid ?>][xp2]" size="2"></TD></TR>
            <TR><TD>RECS<INPUT type="text" name="prostats[<?PHP echo $playerid; ?>][recepts]" size="2"></TD><TD>YDS<INPUT type="text" name="prostats[<?PHP echo $playerid ?>][recyds]" size="2"></TD><TD>TDS<INPUT type="text" name="prostats[<?PHP echo $playerid ?>][rectds]" size="2"></TD></TR>
            <TR><TD>PUNTRETS<INPUT type="text" name="prostats[<?PHP echo $playerid ?>][puntreturns]" size="2"></TD><TD>PUNTRETYDS<INPUT type="text" name="prostats[<?PHP echo $playerid ?>][puntretyds]" size="2"></TD><TD>PUNTRETTDS<INPUT type="text" name="prostats[<?PHP echo $playerid ?>][puntrettds]" size="2"></TD><TD>KICKRETS<INPUT type="text" name="prostats[<?PHP echo $playerid ?>][kickreturns]" size="2"></TD><TD>KICKRETYDS<INPUT type="text" name="prostats[<?PHP echo $playerid ?>][kickretyds]" size="2"></TD><TD>KICKRETTDS<INPUT type="text" name="prostats[<?PHP echo $playerid ?>][kickrettds]" size="2"></TD></TR>
        <?PHP
        }
        else
        {
        ?>
            <TR><TD>PTSALLOW<INPUT type="text" name="prostats[<?PHP echo $playerid ?>][pointsallow]" size="2"></TD><TD>RUSHYDSALLOW<INPUT type="text" name="prostats[<?PHP echo $playerid ?>][rushydsallow]" size="2"></TD><TD>PASSYDSALLOW<INPUT type="text" name="prostats[<?PHP echo $playerid ?>][passydsallow]" size="2"></TD><TD>DEFTD<INPUT type="text" name="prostats[<?PHP echo $playerid ?>][deftd]" size="2"></TD><TD>STTD<INPUT type="text" name="prostats[<?PHP echo $playerid ?>][specialteamtd]" size="2"></TD></TR>
            <TR><TD>SACKS<INPUT type="text" name="prostats[<?PHP echo $playerid ?>][sacks]" size="2"></TD><TD>INTS<INPUT type="text" name="prostats[<?PHP echo $playerid ?>][intercepts]" size="2"></TD><TD>FUMBLERECS<INPUT type="text" name="prostats[<?PHP echo $playerid ?>][fumblerec]" size="2"></TD></TR>
        <?PHP
        }
        if ($pos == 'K')
        {
        ?>
            <TR><TD>FGMADE<INPUT type="text" name="prostats[<?PHP echo $playerid ?>][fgmade]" size="2"></TD><TD>FGATT<INPUT type="text" name="prostats[<?PHP echo $playerid ?>][fgatt]" size="2"></TD><TD>PATMADE<INPUT type="text" name="prostats[<?PHP echo $playerid ?>][patmade]" size="2"></TD><TD>PATATT<INPUT type="text" name="prostats[<?PHP echo $playerid ?>][patatt]" size="2"></TD></TR>
        <?PHP
        }
    }

    if ($fullstats)
    {
        $playerquery = "SELECT DISTINCT a.* FROM players a, playerstats b WHERE lgteamid=$SecondTeam AND a.playerid = b.playerid AND (b.carries > 1 OR b.attempts > 1 OR b.recepts > 1 OR b.puntreturns > 1 OR b.kickreturns > 1 OR b.patatt > 1) AND (pos != 'DB' AND pos != 'DL' AND pos != 'LB' AND pos != 'P') AND firstname != 'TEAM' ORDER BY lastname, firstname";
    }
    else
    {
        $playerquery = "SELECT a.* FROM players a, playerlineups b WHERE b.week=$week AND a.playerid = b.playerid AND a.lgteamid=$SecondTeam AND (pos='QB' OR pos='RB' OR pos='WR' OR pos='TE' OR pos='K' OR pos='D') ORDER BY lastname, firstname";
    }
    $playerresult = $cffldb->query($playerquery);
    DBerror($playerquery,$playerresult);

    ?>
    <TR><TH><?PHP echo getTeamAbbr($cffldb, $SecondTeam); ?></TH></TR>
    <?PHP
    while ($playerrow = $playerresult->fetchRow())
    {
        extract($playerrow);
        echo "<TR><TH colspan=6>$firstname $lastname - $pos</th></tr>";
        if ($pos != 'D')
        {
        ?>
            <TR><TD>COMP<INPUT type="text" name="prostats[<?PHP echo $playerid ?>][completions]" size="2"></TD><TD>ATT<INPUT type="text" name="prostats[<?PHP echo $playerid ?>][attempts]" size="2"></TD><TD>YDS<INPUT type="text" name="prostats[<?PHP echo $playerid ?>][passyds]" size="2"></TD><TD>TDS<INPUT type="text" name="prostats[<?PHP echo $playerid ?>][passtds]" size="2"></TD><TD>INTS<INPUT type="text" name="prostats[<?PHP echo $playerid ?>][passints]" size="2"></TD></TR>";
            <TR><TD>RUSHES<INPUT type="text" name="prostats[<?PHP echo $playerid ?>][carries]" size="2"></TD><TD>YDS<INPUT type="text" name="prostats[<?PHP echo $playerid ?>][rushyds]" size="2"></TD><TD>TDS<INPUT type="text" name="prostats[<?PHP echo $playerid ?>][rushtds]" size="2"></TD><TD>XP2<INPUT type="text" name="prostats[<?PHP echo $playerid ?>][xp2]" size="2"></TD></TR>
            <TR><TD>RECS<INPUT type="text" name="prostats[<?PHP echo $playerid; ?>][recepts]" size="2"></TD><TD>YDS<INPUT type="text" name="prostats[<?PHP echo $playerid ?>][recyds]" size="2"></TD><TD>TDS<INPUT type="text" name="prostats[<?PHP echo $playerid ?>][rectds]" size="2"></TD></TR>
            <TR><TD>PUNTRETS<INPUT type="text" name="prostats[<?PHP echo $playerid ?>][puntreturns]" size="2"></TD><TD>PUNTRETYDS<INPUT type="text" name="prostats[<?PHP echo $playerid ?>][puntretyds]" size="2"></TD><TD>PUNTRETTDS<INPUT type="text" name="prostats[<?PHP echo $playerid ?>][puntrettds]" size="2"></TD><TD>KICKRETS<INPUT type="text" name="prostats[<?PHP echo $playerid ?>][kickreturns]" size="2"></TD><TD>KICKRETYDS<INPUT type="text" name="prostats[<?PHP echo $playerid ?>][kickretyds]" size="2"></TD><TD>KICKRETTDS<INPUT type="text" name="prostats[<?PHP echo $playerid ?>][kickrettds]" size="2"></TD></TR>
        <?PHP
        }
        else
        {
        ?>
            <TR><TD>PTSALLOW<INPUT type="text" name="prostats[<?PHP echo $playerid ?>][pointsallow]" size="2"></TD><TD>RUSHYDSALLOW<INPUT type="text" name="prostats[<?PHP echo $playerid ?>][rushydsallow]" size="2"></TD><TD>PASSYDSALLOW<INPUT type="text" name="prostats[<?PHP echo $playerid ?>][passydsallow]" size="2"></TD><TD>DEFTD<INPUT type="text" name="prostats[<?PHP echo $playerid ?>][deftd]" size="2"></TD><TD>STTD<INPUT type="text" name="prostats[<?PHP echo $playerid ?>][specialteamtd]" size="2"></TD></TR>
            <TR><TD>SACKS<INPUT type="text" name="prostats[<?PHP echo $playerid ?>][sacks]" size="2"></TD><TD>INTS<INPUT type="text" name="prostats[<?PHP echo $playerid ?>][intercepts]" size="2"></TD><TD>FUMBLERECS<INPUT type="text" name="prostats[<?PHP echo $playerid ?>][fumblerec]" size="2"></TD></TR>
        <?PHP
        }
        if ($pos == 'K')
        {
        ?>
            <TR><TD>FGMADE<INPUT type="text" name="prostats[<?PHP echo $playerid ?>][fgmade]" size="2"></TD><TD>FGATT<INPUT type="text" name="prostats[<?PHP echo $playerid ?>][fgatt]" size="2"></TD><TD>PATMADE<INPUT type="text" name="prostats[<?PHP echo $playerid ?>][patmade]" size="2"></TD><TD>PATATT<INPUT type="text" name="prostats[<?PHP echo $playerid ?>][patatt]" size="2"></TD></TR>
        <?PHP
        }
    }
    ?>
    </TABLE>
    <INPUT Type="submit" Value="Score this game">
    </FORM>
    <?PHP
}
$cffldb->disconnect();
include ("../footer.php");
?>
