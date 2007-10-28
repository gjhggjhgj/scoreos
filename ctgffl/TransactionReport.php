<?php

// Connect to mySQL.
//
include ("../mainfile.php");
include ("../header.php");
include('incCTGFFLfunctions.php');
include('incConnectDBs.php');
?>
<TABLE>
<TR><TH>Team Name</TH><TH>Dropped Players</TH><TH>Added Player</TH></TR>
<?PHP

$currentteam = "";
$currentyear = "";
$currentweek = "99";

$week = date(W)-35;
$Wednesday = mktime(0,0,0,0,(($week+39)*7)-1,2007);
$Saturday = mktime(0,0,0,0,(($week+39)*7)+2,2007);
$Tuesday = mktime(0,0,0,0,(($week+39+1)*7)-2,2007);

$DropPlayerQuery = "SELECT transtime, lastname, firstname, lgteamabbr, pos, toir FROM players, transactions, lgteams WHERE players.playerid = transactions.dropplayerid AND players.lgteamid = lgteams.lgteamid ORDER BY transtime, transactions.teamid, transactions.transid";
//$DropPlayerQuery = "SELECT week, lastname, firstname, lgteamabbr, pos FROM players, transactions, lgteams WHERE players.playerid = transactions.dropplayerid AND players.lgteamid = lgteams.lgteamid ORDER BY week DESC, transactions.teamid, transactions.id";
$DropPlayerResult = $cffldb->query($DropPlayerQuery );
$DropPlayerNumrows = $DropPlayerResult->numRows();

$AddPlayerQuery = "SELECT transtime, lastname, firstname, lgteamabbr, pos, fromir, teams.teamname as transteam FROM players, transactions, lgteams, teams WHERE players.playerid = transactions.addplayerid AND players.lgteamid = lgteams.lgteamid AND transactions.teamid = teams.teamid ORDER BY transtime, transactions.teamid, transactions.transid";
//$AddPlayerQuery = "SELECT lastname, firstname, lgteamabbr, pos, transactions.teamid as transteam FROM players, transactions, lgteams WHERE players.playerid = transactions.addplayerid AND players.lgteamid = lgteams.lgteamid ORDER BY week DESC, transactions.teamid, transactions.transid";
$AddPlayerResult = $cffldb->query($AddPlayerQuery );
//$AddPlayerNumrows = $cffldb->numRows($AddPlayerResult);
for ($i=0; $i < $DropPlayerNumrows; $i++)
{
	$DropPlayerrow = $DropPlayerResult->fetchrow(DB_FETCHMODE_ASSOC, $i );
	$AddPlayerrow = $AddPlayerResult->fetchrow(DB_FETCHMODE_ASSOC, $i );
    $playerweek = date(W,strtotime($DropPlayerrow['transtime']))-35;
	if ($currentweek != $playerweek)
	{
		$currentweek = $playerweek;
		echo "<TR><TD><b>Week $currentweek</b></TD></TR>";
	}

	$DropPlayerFullName = $DropPlayerrow['firstname'].' '.$DropPlayerrow['lastname'].' - '.$DropPlayerrow['pos'].' - '.$DropPlayerrow['lgteamabbr'];
    if ($DropPlayerrow['toir'])
    {
        $DropPlayerFullName .= " (to IR)";
    }
	$AddPlayerFullName = $AddPlayerrow['firstname'].' '.$AddPlayerrow['lastname'].' - '.$AddPlayerrow['pos'].' - '.$AddPlayerrow['lgteamabbr'];
    if ($AddPlayerrow['fromir'])
    {
        $AddPlayerFullName .= " (from IR)";
    }
	echo "<TR><TD>{$AddPlayerrow['transteam']}</TD><TD>$DropPlayerFullName</TD><TD>$AddPlayerFullName</TD></TR>";
}
$cffldb->disconnect();
include ("../footer.php");
?>
