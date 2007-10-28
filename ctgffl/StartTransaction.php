<?PHP
// Connect to mySQL.
//
include ("../mainfile.php");
include ("../header.php");
include('incCTGFFLfunctions.php');
include('incConnectDBs.php');

$chosenleague = 1;

if (!$_POST)
{
	$TeamNameQuery = "SELECT teamid, teamname FROM teams WHERE leagueid = $chosenleague ORDER BY teamname;";
	$TeamNameResult = $cffldb->query($TeamNameQuery);
	DBerror($TeamNameQuery, $TeamNameResult);
?>
<form action="StartTransaction.php" method="POST">
<select name="chosenteam">
<?PHP
	while ($teamnamerow = $TeamNameResult->fetchrow(DB_FETCHMODE_ASSOC))
	{
//	print_r ($teamnamerow);
		$teamname = $teamnamerow['teamname'];
		$teamid = $teamnamerow['teamid'];
?>
		<option value="<?PHP echo $teamid?>"><?PHP echo $teamname;
	}
?>
</select>
<input type="submit" name="submitbutton" value="Create Transaction">
</form>
<?PHP
}
else
{
//What team was selected?

	$chosenteam = $_POST['chosenteam'];
	$TeamQuery = "SELECT teamname FROM teams WHERE teamid = $chosenteam";
	$chosenteamname = $cffldb->getOne($TeamQuery );
	echo "<b><big><big>$chosenteamname</big></big></b>";

	//Load the players

	$PlayersQuery = "SELECT * FROM players, playerteams WHERE players.playerid = playerteams.playerid AND teamid = '$chosenteam' order by lastname, firstname";
	$PlayersResult = $cffldb->query($PlayersQuery );
	DBerror($PlayersQuery, $PlayersResult);

	$FreeAgentsQuery = "SELECT DISTINCT lastname, firstname, playerid, lgteamid, pos, year FROM players WHERE (NOT EXISTS (SELECT playerid FROM playerteams WHERE leagueid =1 AND players.playerid = playerteams.playerid) AND (pos = 'QB' OR pos = 'RB' OR pos = 'WR' OR pos = 'TE' OR pos = 'K' OR pos='D')) ORDER BY lastname";
	$FreeAgentsResult = $cffldb->query($FreeAgentsQuery );
	DBerror($FreeAgentsQuery, $FreeAgentsResult);

	// Fill drop down
?>
<form action="CommitTransaction.php" method="POST">
	<select name="dropplayer">
<?PHP
	while ($Playerrow = $PlayersResult->fetchrow(DB_FETCHMODE_ASSOC))
	{
		extract ($Playerrow, EXTR_PREFIX_ALL, "player");
		$TeamQuery = "SELECT lgteamname FROM lgteams WHERE lgteamid = $player_lgteamid";
		$lgteamname = $cffldb->getOne($TeamQuery );
		$PlayerName = $player_firstname.' '.$player_lastname.' - '.$player_pos.' - '.$lgteamname;
?>
<option value="<?PHP echo "$player_playerid"?>"><?PHP echo "$PlayerName" ?>
<?PHP
	}
?>
	</select>
	Put player on IR?<input type="checkbox" name="putplayeronir"><br>
	<select name="addplayerlist">
		<option value="UseForm">Fill out form if player is not on this list
<?PHP
	while ($FreeAgentrow = $FreeAgentsResult->fetchrow(DB_FETCHMODE_ASSOC))
	{
		extract ($FreeAgentrow, EXTR_PREFIX_ALL, "fa");
		$TeamQuery = "SELECT lgteamabbr FROM lgteams WHERE lgteamid = $fa_lgteamid";
		$lgteamname = $cffldb->getOne($TeamQuery );
		$FreeAgentName = $fa_lastname.', '.$fa_firstname.' - '.$fa_pos.' - '.$lgteamname;
?>
		<option value="<?PHP echo "$fa_playerid"?>"><?PHP echo"$FreeAgentName"?>
<?PHP
	}

	$TeamsQuery = "SELECT lgteamid, lgteamname, lgteammascot FROM lgteams ORDER BY lgteamname";
	$TeamsResult = $cffldb->query($TeamsQuery );
	DBerror($TeamsQuery, $TeamsResult);

?>
	</select><br>
	First Name:<input type="text" name="addfirstname" size=12>
	Last Name:<input type="text" name="addlastname" size=12>
	Player's Team:
	<select name="addteamid">
<?PHP
	while ($Teamrow = $TeamsResult->fetchrow(DB_FETCHMODE_ASSOC))
	{
?>
		<option value="<?PHP echo "{$Teamrow['lgteamid']}"?>"><?PHP echo"{$Teamrow['lgteamname']} {$Teamrow['lgteammascot']}"?>
<?PHP
	}
?>
	</select><br>
	Position:
	<select name="addposition">
		<option>QB
		<option>RB
		<option>WR
		<option>TE
		<option>K
		<option>D
	</select>
	Year:
	<select name="addyear">
		<option>Freshman
		<option>Sophomore
		<option>Junior
		<option>Senior
		<option>Pro
	</select><BR>
	<input type="submit" name="submittrans" value="Submit Transaction">
	<input type="hidden" name="chosenleague" value="<?PHP echo $chosenleague; ?>">
	<input type="hidden" name="chosenteam" value="<?PHP echo $chosenteam; ?>">
</form>
<?PHP
}
$cffldb->disconnect();
include ("../footer.php");
?>