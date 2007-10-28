<?PHP
// Connect to PostgreSQL.
//
include ("../mainfile.php");
include ("../header.php");
include('incCTGFFLfunctions.php');
include('incConnectDBs.php');

$CollegeStarter[] = $_POST['StartingCollQB'];
$CollegeStarter[] = $_POST['StartingCollRB'];
$CollegeStarter[] = $_POST['StartingCollWR1'];
$CollegeStarter[] = $_POST['StartingCollWR2'];
$CollegeStarter[] = $_POST['StartingCollFlex'];
$CollegeStarter[] = $_POST['StartingCollK'];
$CollegeStarter[] = $_POST['StartingCollD'];
$ProStarter[] = $_POST['StartingProQB'];
$ProStarter[] = $_POST['StartingProRB'];
$ProStarter[] = $_POST['StartingProWR1'];
$ProStarter[] = $_POST['StartingProWR2'];
$ProStarter[] = $_POST['StartingProFlex'];
$ProStarter[] = $_POST['StartingProTE'];
$ProStarter[] = $_POST['StartingProK'];
$ProStarter[] = $_POST['StartingProD'];
$TeamName = $_POST['teamname'];
$teamid = $_POST['teamid'];

if ($_POST['colstartonly'] || $_POST['prostartonly'])
{
	if ($_POST['colstartonly'])
	{
		$StartCol = 1;
	}
	if ($_POST['prostartonly'])
	{
		$StartPro = 1;
	}
}
else
{
	$StartCol = 1;
	$StartPro = 1;
}

if ($_POST['week'])
{
	if ($_POST['week'] == -1)
	{
		$week =0;
	}
	else
	{
		$week = $_POST['week'];
	}
}
else
{
	$week = date(W) - 35;
}

echo "<br><b>$TeamName</b><BR>";

//Do college first
if ($StartCol)
{
	$CheckExistingCollStartnumrows = 0;
	$CheckExistingCollStartquery = "SELECT players.playerid FROM playerlineups, playerteams, players WHERE week = $week AND playerlineups.teamid=$teamid AND playerlineups.playerid=playerteams.playerid AND players.playerid=playerteams.playerid AND year!='Pro'";
	$CheckExistingCollStartresult = $cffldb->query($CheckExistingCollStartquery);
	DBerror($CheckExistingCollStartquery,$CheckExistingCollStartresult);
	$CheckExistingCollStartnumrows = $CheckExistingCollStartresult->numrows();

	if ($CheckExistingCollStartnumrows != 0) //clear starter flags if players exist in DB
	{
		for ($i=0; $i<$CheckExistingCollStartnumrows; $i++)
		{
			$ClearCollStarter = $CheckExistingCollStartresult->fetchrow(DB_FETCHMODE_ASSOC, $i);
			$ClearCollStarterQuery = "UPDATE playerlineups SET starter = 'N' WHERE week=$week AND playerid='{$ClearCollStarter['playerid']}'";
			$ClearCollStarterResult = $cffldb->query($ClearCollStarterQuery );
			DBerror($ClearCollStarterQuery,$ClearCollStarterResult);
		}
	}
	else //fill the playerstats DB with this team's current players
	{
		$GetCollegePlayersQuery = "SELECT players.playerid FROM players, playerteams WHERE players.playerid = playerteams.playerid AND teamid=$teamid AND year != 'Pro'";
		$GetCollegePlayersResult = $cffldb->query($GetCollegePlayersQuery);
		DBerror($GetCollegePlayersQuery,$GetCollegePlayersResult);
		$GetCollegePlayersnumrows = $GetCollegePlayersResult->numrows();
		for ($i=0; $i<$GetCollegePlayersnumrows; $i++)
		{
			$ProcessCollPlayer = $GetCollegePlayersResult->fetchrow(DB_FETCHMODE_ASSOC, $i);
			$FillCollPlayerQuery = "INSERT INTO playerlineups VALUES ($week,'{$ProcessCollPlayer['playerid']}','N',0,$teamid)";
			$FillCollPlayerResult = $cffldb->query($FillCollPlayerQuery);
			DBerror($FillCollPlayerQuery,$FillCollPlayerResult);
		}
	}

    foreach ($CollegeStarter as $collstarter)
    {
        $StartCollquery = "UPDATE playerlineups SET starter = 'Y' WHERE playerid=$collstarter AND week=$week";
    	$StartCollresult = $cffldb->query($StartCollquery);
    	DBerror($StartCQBquery,$StartCQBresult);
    }
}

if ($StartPro)
{
//Now do pro
	$CheckExistingProStartnumrows = 0;
	$CheckExistingProStartquery = "SELECT players.playerid FROM playerlineups, players WHERE week = $week AND playerlineups.teamid=$teamid AND playerlineups.playerid=players.playerid AND year='Pro'";
	$CheckExistingProStartresult = $cffldb->query($CheckExistingProStartquery);
	DBerror(CheckExistingProStartquery,CheckExistingProStartresult);
	$CheckExistingProStartnumrows = $CheckExistingProStartresult->numrows();

	if ($CheckExistingProStartnumrows != 0) //clear starter flags if players exist in DB
	{
		for ($i=0; $i<$CheckExistingProStartnumrows; $i++)
		{
			$ClearProStarter = $CheckExistingProStartresult->fetchrow(DB_FETCHMODE_ASSOC, $i);
			$playeridx = $ClearProStarter['playerid'];
			$ClearProStarterQuery = "UPDATE playerlineups SET starter = 'N' WHERE week=$week AND playerid=$playeridx";
			$ClearProStarterResult = $cffldb->query($ClearProStarterQuery );
			DBError($ClearProStarterQuery,$ClearProStarterResult);
		}
	}
	else //fill the playerstats DB with this team's current players
	{
		$GetProPlayersQuery = "SELECT players.playerid FROM playerteams, players WHERE teamid=$teamid AND year = 'Pro' AND playerteams.playerid=players.playerid";
		$GetProPlayersResult = $cffldb->query($GetProPlayersQuery);
		DBError($GetProPlayersQuery,$GetProPlayersResult);
		$GetProPlayersnumrows = $GetProPlayersResult->numrows();
		for ($i=0; $i<$GetProPlayersnumrows; $i++)
		{
			$ProcessProPlayer = $GetProPlayersResult->fetchrow(DB_FETCHMODE_ASSOC, $i);
			$processplayer = $ProcessProPlayer['playerid'];
			$FillProPlayerQuery = "INSERT INTO playerlineups VALUES ($week,$processplayer,'N',0,$teamid)";
			$FillProPlayerResult = $cffldb->query($FillProPlayerQuery);
			DBError($FillProPlayerQuery,$FillProPlayerResult);
		}
	}

    foreach ($ProStarter as $prostarter)
    {
        $StartPQBquery = "UPDATE playerlineups SET starter='Y' WHERE playerid=$prostarter AND week=$week";
        $StartPQBresult = $cffldb->query($StartPQBquery );
        DBerror($StartPQBquery,$StartPQBresult);
    }
}

$ListStartersQuery = "SELECT lastname, firstname, lgteamabbr, pos, year FROM players, playerlineups, lgteams WHERE playerlineups.teamid = $teamid AND week=$week AND players.playerid=playerlineups.playerid AND starter='Y' AND players.lgteamid = lgteams.lgteamid";
$ListStartersResult = $cffldb->query($ListStartersQuery );
DBerror($ListStartersQuery,$ListStarterResult);

unset($ListStarting);

while ($ProcessStarter = $ListStartersResult->fetchrow())
{
    if ($ProcessStarter['year'] == 'Pro')
        $side = 'P';
    else
        $side = 'C';

    if ($ProcessStarter['pos'] != 'RB' && $ProcessStarter['pos'] != 'WR' && $ProcessStarter['pos'] != 'TE')
        $ListStarting[$side][$ProcessStarter['pos']] = $ProcessStarter['firstname'].' '.$ProcessStarter['lastname'].' - '.$ProcessStarter['lgteamabbr'];
    elseif ($ProcessStarter['pos'] == 'RB')
    {
        if (isset($ListStarting[$side]['RB']))
            $ListStarting[$side]['FLEX'] = $ProcessStarter['pos'].' '.$ProcessStarter['firstname'].' '.$ProcessStarter['lastname'].' - '.$ProcessStarter['lgteamabbr'];
        else
            $ListStarting[$side]['RB'] = $ProcessStarter['firstname'].' '.$ProcessStarter['lastname'].' - '.$ProcessStarter['lgteamabbr'];
    }
    elseif ($ProcessStarter['pos'] == 'WR' || ($side == 'C' && $ProcessStarter['pos'] == 'TE'))
    {
        if (isset($ListStarting[$side]['WR'][0]) && isset($ListStarting[$side]['WR'][1]))
            $ListStarting[$side]['FLEX'] = $ProcessStarter['pos'].' '.$ProcessStarter['firstname'].' '.$ProcessStarter['lastname'].' - '.$ProcessStarter['lgteamabbr'];
        else
            $ListStarting[$side]['WR'][] = $ProcessStarter['firstname'].' '.$ProcessStarter['lastname'].' - '.$ProcessStarter['lgteamabbr'];
    }
    elseif ($ProcessStarter['pos'] == 'TE')
    {
        if (isset($ListStarting[$side]['TE']))
            $ListStarting[$side]['FLEX'] = $ProcessStarter['pos'].' '.$ProcessStarter['firstname'].' '.$ProcessStarter['lastname'].' - '.$ProcessStarter['lgteamabbr'];
        else
            $ListStarting[$side]['TE'] = $ProcessStarter['firstname'].' '.$ProcessStarter['lastname'].' - '.$ProcessStarter['lgteamabbr'];
    }
}

?>
<TABLE cellpadding="10" cellspacing="5" align="center">
<TR><TH>
Your college starters are
</TH><TH>
Position
</TH><TH>
Your pro starters are
</TH></TR>
<TR><TD>
<?PHP 
echo $ListStarting['C']['QB'];
$mailmessage = "<TABLE><TR><TD>";
$mailmessage .= $ListStarting['C']['QB']."</TD><TD>";
?>
</TD><TD align="center">
<B>QB</B>
</TD><TD>
<?PHP
$mailmessage .= $ListStarting['P']['QB']."</TD></TR><TR><TD>";
echo $ListStarting['P']['QB'] 
?>
</TD></TR>
<TR><TD>
<?PHP 
$mailmessage .= $ListStarting['C']['RB']."</TD><TD>";
echo $ListStarting['C']['RB'];
?>
</TD><TD align="center">
<B>RB</B>
</TD><TD>
<?PHP 
$mailmessage .= $ListStarting['P']['RB']."</TD></TR><TR><TD>";
echo $ListStarting['P']['RB']; ?>
</TD></TR>
<TR><TD>
<?PHP 
$mailmessage .= $ListStarting['C']['WR'][0]."</TD><TD>";
echo $ListStarting['C']['WR'][0]; ?>
</TD><TD align="center">
<B>WR</B>
</TD><TD>
<?PHP 
$mailmessage .= $ListStarting['P']['WR'][0]."</TD></TR><TR><TD>";
echo $ListStarting['P']['WR'][0]; ?>
</TD></TR>
<TR><TD>
<?PHP 
$mailmessage .= $ListStarting['C']['WR'][1]."</TD><TD>";
echo $ListStarting['C']['WR'][1]; ?>
</TD><TD align="center">
<B>WR</B>
</TD><TD>
<?PHP 
$mailmessage .= $ListStarting['P']['WR'][1]."</TD></TR><TR><TD>";
echo $ListStarting['P']['WR'][1] ?>
</TD></TR>
<TR><TD>
<?PHP 
$mailmessage .= $ListStarting['C']['FLEX']."</TD><TD>";
echo $ListStarting['C']['FLEX']; ?>
</TD><TD align="center">
<B>Flex</B>
</TD><TD>
<?PHP 
$mailmessage .= $ListStarting['P']['FLEX']."</TD></TR><TR><TD>";
echo $ListStarting['P']['FLEX']; ?>
</TD></TR>
<TR><TD>
 
</TD><TD align="center">
<B>TE</B>
</TD><TD>
<?PHP 
$mailmessage .= "</TD><TD>".$ListStarting['P']['TE']."</TD></TR><TR><TD>";
echo $ListStarting['P']['TE']; ?>
</TD></TR>
<TR><TD>
<?PHP 
$mailmessage .= $ListStarting['C']['K']."</TD><TD>";
echo $ListStarting['C']['K']; ?>
</TD><TD align="center">
<B>K</B>
</TD><TD>
<?PHP 
$mailmessage .= $ListStarting['P']['K']."</TD></TR><TR><TD>";
echo $ListStarting['P']['K']; ?>
</TD></TR>
<TR><TD>
<?PHP 
$mailmessage .= $ListStarting['C']['D']."</TD><TD>";
echo $ListStarting['C']['D']; ?>
</TD><TD align="center">
<B>D</B>
</TD><TD>
<?PHP 
$mailmessage .= $ListStarting['P']['D']."</TD></TR><TR><TD>";
echo $ListStarting['P']['D']; ?>
</TD></TR>
</TABLE>
<?PHP

$fromname = "CTGFFL Starting Lineups"; 
$fromemail = "lineups@ctgffl.com"; 

$toname = $TeamName;
// Get opponent
$GetOpponentQuery = "SELECT homeid, visitorid FROM schedule WHERE leagueid = 1 AND week=$week AND (homeid=$teamid OR visitorid=$teamid)";
$GetOpponentRow = $cffldb->getRow($GetOpponentQuery );
DBError($GetOpponentQuery,$GetOpponentRow);
if ($GetOpponentRow['homeid'] == $teamid)
{
	$opponent = $GetOpponentRow['visitorid'];
}
else
{
	$opponent = $GetOpponentRow['homeid'];
}
$teamownerid = $cffldb->getOne("SELECT teamownerid FROM teams WHERE teamid = $teamid");
$oppownerid = $cffldb->getOne("SELECT teamownerid FROM teams WHERE teamid = $opponent");

$teamemail = $ctgffldb->getOne("SELECT email FROM xoops_users WHERE uid = $teamownerid");
$oppemail = $ctgffldb->getOne("SELECT email FROM xoops_users WHERE uid = $oppownerid");

$subject = "Starting lineup for $toname"; 

$headers .= "MIME-Version: 1.0\r\n";
$headers .= "Content-type: text/html; charset=iso-8859-1\r\n";
$headers .= "From: ".$fromname." <".$fromemail.">\r\n";
$headers .= "To: <".$oppemail.">\r\n";
$headers .= "CC: <".$teamemail.">\r\n";
//$headers .= "To: Don <caelon@gmail.com>\r\n";
$headers .= "Reply-To: ".$fromname." <caelon@gmail.com>\r\n";
$headers .= "X-Priority: 1\r\n";
$headers .= "X-MSMail-Priority: High\r\n";
$headers .= "X-Mailer: Just My Server";

//$mailmessage .= "Team email $teamemail opp email $oppemail";

mail("", $subject, $mailmessage, $headers);

include ("../footer.php");
?>
<!--<A HREF="CTGFFLTeamselect.php">Go back to Team selection</A>-->