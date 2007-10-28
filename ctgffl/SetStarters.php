<?PHP

// Connect to PostgreSQL.
//
include ("../mainfile.php");
include ("../header.php");
include('incCTGFFLfunctions.php');
include('incConnectDBs.php');

$week = date(W)-35;
$Wednesday = mktime(0,0,0,0,(($week+39)*7)-1,2007);
$Saturday = mktime(0,0,0,0,(($week+39)*7)+2,2007);
$Tuesday = mktime(0,0,0,0,(($week+39+1)*7)-2,2007);
$Tuesday = date("Y-m-d",$Tuesday);
$Saturday = date("Y-m-d",$Saturday);
$Wednesday = date("Y-m-d",$Wednesday);
$today = date("Y-m-d");
$todaytime = date("G");

if (((date(w)==0) || (date(w)==6)) && (date(G) >=12))
    $collegedue = 1;

if (!$xoopsUser)
{
	echo "You must be signed in to set your starters.";
}
else
{
	if ($_POST['teamname'])
	{
		$TeamName = $_POST['teamname'];
		$teamnamequery = "SELECT primcolor, seccolor FROM teams where teamname = '$TeamName';";
		$teamnameresult = $cffldb->query($teamnamequery);
		DBerror($teamnamequery, $teamnamerow);
		$teamnamerow = $teamnameresult->fetchrow();
	}
	else
	{
		$userid = $xoopsUser->getVar('uid');
		$teamnamequery = "SELECT teamid, teamname, primcolor, seccolor FROM teams where teamownerid = $userid;";
		$teamnameresult = $cffldb->query($teamnamequery);
		DBerror($teamnamequery, $teamnamerow);
		$teamnamerow = $teamnameresult->fetchrow();
		$TeamName = $teamnamerow['teamname'];
        $teamid = $teamnamerow['teamid'];
	}
	$bgcolor = $teamnamerow['primcolor'];
	$fgcolor = $teamnamerow['seccolor'];
	//Load the players
	$PlayersQuery = "SELECT players.playerid, lastname, firstname, lgteams.lgteamid as lgteamid, lgteamabbr, pos, year, irweek FROM players, playerteams, lgteams WHERE players.playerid = playerteams.playerid AND playerteams.teamid = $teamid AND players.lgteamid = lgteams.lgteamid ORDER BY lastname";
	$PlayersResult = $cffldb->query($PlayersQuery );
	DBerror($PlayersQuery, $PlayersResult);
	
	$PlayersNumrows = $PlayersResult->numrows();
	for ($i=0; $i<$PlayersNumrows; $i++)
	{
		$ProcessPlayer = $PlayersResult->fetchrow(DB_FETCHMODE_ASSOC, $i);
// 	print_r ($ProcessPlayer);  					//this is a debug line
		if ($ProcessPlayer['irweek'] == -1)
		{
		switch ($ProcessPlayer['year'])
		{
			case 'Pro':
				switch ($ProcessPlayer['pos'])
				{
					case 'QB':
						$ProQBID[] = $ProcessPlayer['playerid'];
						$ProQBname[] = $ProcessPlayer['firstname'] . ' ' . $ProcessPlayer['lastname'] . ' - ' . $ProcessPlayer['lgteamabbr'];
						break;
					case 'RB':
                        $ProRBID[] = $ProcessPlayer['playerid'];
                        $ProRBname[] = $ProcessPlayer['firstname'] . ' ' . $ProcessPlayer['lastname'] . ' - ' . $ProcessPlayer['lgteamabbr'];
                        $ProFlexID[] = $ProcessPlayer['playerid'];
                        $ProFlexname[] = $ProcessPlayer['pos'] . ' ' . $ProcessPlayer['firstname'] . ' ' . $ProcessPlayer['lastname'] . ' - ' . $ProcessPlayer['lgteamabbr'];
                        break;
                    case 'WR':
                        $ProWRID[] = $ProcessPlayer['playerid'];
                        $ProWRname[] = $ProcessPlayer['firstname'] . ' ' . $ProcessPlayer['lastname'] . ' - ' . $ProcessPlayer['lgteamabbr'];
                        $ProFlexID[] = $ProcessPlayer['playerid'];
                        $ProFlexname[] = $ProcessPlayer['pos'] . ' ' . $ProcessPlayer['firstname'] . ' ' . $ProcessPlayer['lastname'] . ' - ' . $ProcessPlayer['lgteamabbr'];
                        break;
					case 'TE':
						$ProTEID[] = $ProcessPlayer['playerid'];
						$ProTEname[] = $ProcessPlayer['firstname'] . ' ' . $ProcessPlayer['lastname'] . ' - ' . $ProcessPlayer['lgteamabbr'];
                        $ProFlexID[] = $ProcessPlayer['playerid'];
                        $ProFlexname[] = $ProcessPlayer['pos'] . ' ' . $ProcessPlayer['firstname'] . ' ' . $ProcessPlayer['lastname'] . ' - ' . $ProcessPlayer['lgteamabbr'];
						break;
					case 'K':
						$ProKID[] = $ProcessPlayer['playerid'];
						$ProKname[] = $ProcessPlayer['firstname'] . ' ' . $ProcessPlayer['lastname'] . ' - ' . $ProcessPlayer['lgteamabbr'];
						break;
					case 'D':
						$ProDID[] = $ProcessPlayer['playerid'];
						$ProDname[] = $ProcessPlayer['lastname'];
						break;
				}
				break;
			default:
				$HomeScheduleQuery = "SELECT lgteamabbr, visitor FROM lgteams, ncaasched WHERE home='{$ProcessPlayer['lgteamid']}' AND gamedate >= '$Wednesday' AND gamedate <= '$Tuesday' AND visitor = lgteamid";
				$HomeScheduleResult = $cffldb->query($HomeScheduleQuery );
				DBerror($HomeScheduleQuery, $HomeScheduleResult);
				$HomeScheduleNumrows = $HomeScheduleResult->numrows();
				if ($HomeScheduleNumrows)
				{
					$HomeScheduleRow = $HomeScheduleResult->fetchrow(DB_FETCHMODE_ASSOC);
					$gametext = " (vs. ".$HomeScheduleRow['lgteamabbr'].")";
				}
				else
				{
					$VisScheduleQuery = "SELECT lgteamabbr, home FROM lgteams, ncaasched WHERE visitor='{$ProcessPlayer['lgteamid']}' AND gamedate >= '$Wednesday' AND gamedate <= '$Tuesday' AND home=lgteamid";
					$VisScheduleResult = $cffldb->query($VisScheduleQuery );
					DBerror($VisScheduleQuery, $VisScheduleResult);
					$VisScheduleNumrows = $VisScheduleResult->numrows();
					if ($VisScheduleNumrows)
					{
						$VisScheduleRow = $VisScheduleResult->fetchrow(DB_FETCHMODE_ASSOC);
						$gametext = " (at ".$VisScheduleRow['lgteamabbr'].")";
					}
					else
					{
						$gametext = " (BYE)";
					}
				}

				switch ($ProcessPlayer['pos'])
				{
					case 'QB':
						$CollQBID[] = $ProcessPlayer['playerid'];
						$CollQBname[] = $ProcessPlayer['firstname'] . ' ' . $ProcessPlayer['lastname'] . ' - ' . $ProcessPlayer['lgteamabbr'].$gametext;
						break;
					case 'RB':
                        $CollRBID[] = $ProcessPlayer['playerid'];
                        $CollRBname[] = $ProcessPlayer['firstname'] . ' ' . $ProcessPlayer['lastname'] . ' - ' . $ProcessPlayer['lgteamabbr'].$gametext;
                        $CollFlexID[] = $ProcessPlayer['playerid'];
                        $CollFlexname[] = $ProcessPlayer['pos'] . ' ' . $ProcessPlayer['firstname'] . ' ' . $ProcessPlayer['lastname'] . ' - ' . $ProcessPlayer['lgteamabbr'].$gametext;
                        break;
					case 'WR':
                    case 'TE':
						$CollWRID[] = $ProcessPlayer['playerid'];
						$CollWRname[] = $ProcessPlayer['firstname'] . ' ' . $ProcessPlayer['lastname'] . ' - ' . $ProcessPlayer['lgteamabbr'].$gametext;
                        $CollFlexID[] = $ProcessPlayer['playerid'];
                        $CollFlexname[] = $ProcessPlayer['pos'] . ' ' . $ProcessPlayer['firstname'] . ' ' . $ProcessPlayer['lastname'] . ' - ' . $ProcessPlayer['lgteamabbr'].$gametext;
						break;
					case 'K':
						$CollKID[] = $ProcessPlayer['playerid'];
						$CollKname[] = $ProcessPlayer['firstname'] . ' ' . $ProcessPlayer['lastname'] . ' - ' . $ProcessPlayer['lgteamabbr'].$gametext;
						break;
					case 'D':
						$CollDID[] = $ProcessPlayer['playerid'];
						$CollDname[] = $ProcessPlayer['firstname'].$gametext;
						break;
				}
				break;
			}	
		}
	}

// Fill drop down
    

//    $starterquery = "SELECT playerid FROM playerlineups WHERE teamid = $teamid AND starter = 'Y' AND week = $week";
    $starterquery = "SELECT a.playerid, pos, year FROM playerlineups a, players b WHERE a.playerid = b.playerid AND teamid = $teamid AND starter = 'Y' AND week = $week";
    $starterresult = $cffldb->query($starterquery);
    DBerror($starterquery,$starterresult);

    while ($starterrow = $starterresult->fetchrow())
    {
        extract($starterrow, EXTR_PREFIX_ALL, 'strtr');
        if ($strtr_year == 'Pro')
        {
            $starters['P'][$strtr_pos][]['playerid'] = $strtr_playerid;
        }
        else
        {
            $gametimequery = "SELECT gamedate FROM ncaasched a, players b WHERE playerid = $strtr_playerid AND (a.visitor = b.lgteamid OR a.home = b.lgteamid) AND gamedate >= '$Wednesday' AND gamedate <= '$Tuesday'";
            $starters['C'][$strtr_pos][] = array ('playerid' => $strtr_playerid, 'gametime' => $cffldb->getOne($gametimequery));
        }
    }
//    print_r($starters);
	?>
	<form action="ShowStarters.php" method="POST">
	<TABLE>
	<TR><TD colspan="3" align="center" bgcolor="#<?PHP echo $bgcolor;?>"><font size=+2 color="#<?PHP echo $fgcolor;?>"><b><?PHP echo $TeamName;?></b></font></TD></TR>
	<TR><TH width="35%" align="right">
	College Starters
	</TH><TH width="10%" align="center">
	Position
	</TH><TH width="35%">
	Pro Starters
	</TH></TR>
	<TR>
    <?PHP
    if ($collegedue)
    {
        echo "<TD></TD><TD></TD><TD></TD></TR>";
        echo "<input type=\"hidden\" name=\"prostartonly\" value=1>";
    }
    else
    { ?>
        <TD align="center">College Starters Only?<INPUT type="checkbox" name="colstartonly"></TD><TD></TD><TD align="center">Pro Starters Only?<INPUT type=	"checkbox" name="prostartonly"></TD></TR>
    <?PHP
    }
    ?>
	<TR><TD align="right">
		<select name="StartingCollQB">
	<?PHP
	for ($i=0; $i < count($CollQBID); $i++)
	{
	?>
			<option value="<?PHP echo "$CollQBID[$i]".'"'; if (in_array($CollQBID[$i], $starters)) echo " SELECTED";?>><?PHP echo "$CollQBname[$i]"?>
	<?PHP
	}
	?>
	</select>
	</TD><TD align="Center">
	<b>QB</B>
	</TD><TD>
		<select name="StartingProQB">
	<?PHP
	for ($i=0; $i < count($ProQBID); $i++)
	{
	?>
			<option value="<?PHP echo "$ProQBID[$i]".'"'; if (in_array($ProQBID[$i], $starters)) echo " SELECTED";?>><?PHP echo "$ProQBname[$i]"?>
	<?PHP
	}
	?>
	</select>
	</TR>
	<br>
	<TR><TD align="right">
		<select name="StartingCollRB">
	<?PHP
	for ($i=0; $i < count($CollRBID); $i++)
	{
	?>
			<option value="<?PHP echo "$CollRBID[$i]".'"'; if (in_array($CollRBID[$i], $starters)) echo " SELECTED";?>><?PHP echo "$CollRBname[$i]"?>
	<?PHP
	}
	?>
	</select>
	</TD><TD align="center">
	<b>RB</b>
	</TD><TD>
		<select name="StartingProRB">
	<?PHP
	for ($i=0; $i < count($ProRBID); $i++)
	{
        ?>
    	<option value="<?PHP echo "$ProRBID[$i]".'"'; if (in_array($ProRBID[$i], $starters)) echo " SELECTED";?>><?PHP echo "$ProRBname[$i]"?>
	    <?PHP
	}
	?>
	</select>
	</TD></TR>
	<TR><TD align="right">
	<select name="StartingCollWR1">
	<?PHP
	for ($i=0; $i < count($CollWRID); $i++)
	{
	?>
			<option value="<?PHP echo "$CollWRID[$i]".'"'; if (in_array($CollWRID[$i], $starters)) echo " SELECTED";?>><?PHP echo "$CollWRname[$i]"?>
	<?PHP
	}
	?>
	</select>
	</TD><TD align="center">
	<b>WR1</b>
	</TD><TD>
	<select name="StartingProWR1">
	<?PHP
	for ($i=0; $i < count($ProWRID); $i++)
	{
        ?>
    	<option value="<?PHP echo "$ProWRID[$i]".'"'; if (in_array($ProWRID[$i], $starters)) echo " SELECTED";?>><?PHP echo "$ProWRname[$i]"?>
	    <?PHP
	}
	?>
	</select>
	</TD></TR>
	<TR><TD align="right">
	<select name="StartingCollWR2">
	<?PHP
	for ($i=0; $i < count($CollWRID); $i++)
	{
	?>
			<option value="<?PHP echo "$CollWRID[$i]".'"'; if (in_array($CollWRID[$i], $starters)) echo " SELECTED";?>><?PHP echo "$CollWRname[$i]"?>
	<?PHP
	}
	?>
	</select>
	</TD><TD align="center">
	<b>WR2</b>
	</TD><TD>
	<select name="StartingProWR2">
	<?PHP
	for ($i=0; $i < count($ProWRID); $i++)
	{
        ?>
    	<option value="<?PHP echo "$ProWRID[$i]".'"'; if (in_array($ProWRID[$i], $starters)) echo " SELECTED";?>><?PHP echo "$ProWRname[$i]"?>
	    <?PHP
	}
	?>
	</select>
	</TD></TR>
	<TR><TD align="right">
	<select name="StartingCollFlex">
	<?PHP
	for ($i=0; $i < count($CollFlexID); $i++)
	{
	?>
			<option value="<?PHP echo "$CollFlexID[$i]".'"'; if (in_array($CollFlexID[$i], $starters)) echo " SELECTED";?>><?PHP echo "$CollFlexname[$i]"?>
	<?PHP
	}
	?>
	</select>
	</TD><TD align="center">
	<b>Flex</b>
	</TD><TD>
	<select name="StartingProFlex">
	<?PHP
	for ($i=0; $i < count($ProFlexID); $i++)
	{
        ?>
    	<option value="<?PHP echo "$ProFlexID[$i]".'"'; if (in_array($ProFlexID[$i], $starters)) echo " SELECTED";?>><?PHP echo "$ProFlexname[$i]"?>
	    <?PHP
	}
	?>
	</select>
	</TD></TR>
	<TR><TD>
	</TD><TD align = "center">
	<b>TE</b>
	</TD><TD>
		<select name="StartingProTE">
	<?PHP
	for ($i=0; $i < count($ProTEID); $i++)
	{
	?>
			<option value="<?PHP echo "$ProTEID[$i]".'"'; if (in_array($ProTEID[$i], $starters)) echo " SELECTED";?>><?PHP echo "$ProTEname[$i]"?>
	<?PHP
	}
	?>
	</select>
	</TD></TR>
	<TR><TD align="right">
		<select name="StartingCollK">
	<?PHP
	for ($i=0; $i < count($CollKID); $i++)
	{
	?>
			<option value="<?PHP echo "$CollKID[$i]".'"'; if (in_array($CollKID[$i], $starters)) echo " SELECTED";?>><?PHP echo "$CollKname[$i]"?>
	<?PHP
	}
	?>
	</select>
	</TD><TD align="Center">
	<b>K</b>
	</TD><TD>
		<select name="StartingProK">
	<?PHP
	for ($i=0; $i < count($ProKID); $i++)
	{
	?>
			<option value="<?PHP echo "$ProKID[$i]".'"'; if (in_array($ProKID[$i], $starters)) echo " SELECTED";?>><?PHP echo "$ProKname[$i]"?>
	<?PHP
	}
	?>
	</select>
	</TD></TR>
	<br>
	<TR><TD align="right">
		<select name="StartingCollD">
	<?PHP
	for ($i=0; $i < count($CollDID); $i++)
	{
	?>
			<option value="<?PHP echo "$CollDID[$i]".'"'; if (in_array($CollDID[$i], $starters)) echo " SELECTED";?>><?PHP echo "$CollDname[$i]"?>
	<?PHP
	}
	?>
	</select>
	</TD><TD align="center">
	<b>D</b>
	</TD><TD>
		<select name="StartingProD">
	<?PHP
	for ($i=0; $i < count($ProDID); $i++)
	{
	?>
			<option value="<?PHP echo "$ProDID[$i]".'"'; if (in_array($ProDID[$i], $starters)) echo " SELECTED";?>><?PHP echo "$ProDname[$i]"?>
	<?PHP
	}
	?>
	</select>
	</TD></TR>
	<br>
	<TR><TD colspan="3" align="center">
	<input type="submit" name="submitbutton" value="Submit Starters">
	<input type="hidden" name="teamname" value="<?PHP echo "$TeamName" ?>">
	<input type="hidden" name="teamid" value="<?PHP echo "$teamid" ?>">
	<?PHP
	if ($_POST['week'])
	{
		$week = $_POST['week'];
	?>
	<input type="hidden" name="week" value="<?PHP echo "$week" ?>">
	</TD></TR>
	<?PHP
	}
	?>
	<TR><TD colspan="3" align="center">Every attempt has been made to make sure the schedule is accurate, but it can not be guaranteed.<BR>Please use the opponent for your players at your own risk.</td></tr></TABLE></form>
	<?PHP
}
include ("../footer.php");
?>