<?php
include ("../mainfile.php");
include ("../header.php");
include('incCTGFFLfunctions.php');
include('incConnectDBs.php');

//Get week
if (!$_GET['week'])
{
$week = date(W) - 35; #different from the others because this is backwards-looking
}
else
{
$week = $_GET['week'];
if ($week==-1) {$week=0;}
}

echo "Week: $week<br>";

$playerquery = "SELECT * FROM playerstats WHERE week=$week";
echo $playerquery."<br>";
$playerresult = $cffldb->query($playerquery);
DBerror($playerquery, $playerresult);

while ($playerrow = $playerresult->fetchrow())
{
	echo "<BR>".$playerrow['playerid'];
	$scoreexistquery = "SELECT * FROM playerlineups WHERE week=$week AND playerid={$playerrow['playerid']}";
	$scoreexistresult = $cffldb->query($scoreexistquery);
	DBerror($scoreexistquery, $scoreexistresult);
	if ($scoreexistrow = $scoreexistresult->fetchrow())
	{
        extract ($playerrow);

        $playernamequery = "SELECT lastname, firstname, lgteamid, pos FROM players WHERE playerid=$playerid";
        $playernameresult = $cffldb->query($playernamequery);
        DBerror($playernamequery, $playernameresult);
        $playernamerow = $playernameresult->fetchRow();

        if ($playernamerow['pos'] == 'D')
        {
            if (!isset($pointsallow))
            {
                $ptsallow = 0;
            }
            elseif ($pointsallow == 0)
    		{
    			$ptsallow = 15;
    		}
    		elseif ($pointsallow < 7)
    		{
    			$ptsallow = 10;
    		}
    		elseif ($pointsallow < 11)
    		{
    			$ptsallow = 6;
    		}
    		elseif ($pointsallow < 18)
    		{
    			$ptsallow = 3;
    		}
    		else
    		{
    			$ptsallow = 0;
    		}
        }
        else
            $ptsallow = 0;

 	$points = (($xp2*2)+($puntrettds*6)+($kickrettds*6)+($rushyds/10)+($rushtds*6)+(round(($passyds/25)-0.001, 1))+($passtds*4)+(round(($recyds/8)-0.001, 1))+($rectds*6)+($recepts/2)+(round(($puntretyds/25)-0.001, 1))+(round(($kickretyds/25)-0.001, 1))+$ptsallow+($deftd*6)+($safety*2)+($sacks*2)+($intercepts*2)+($fumblerec*2)+($fgmade*3)+$patmade);
	$scorequery = "UPDATE playerlineups SET points = $points WHERE week=$week AND playerid=$playerid";
	$scoreresult = $cffldb->query($scorequery);
	DBerror($scorequery, $scoreresult);
	echo $playernamerow['firstname']." ".$playernamerow['lastname']." - ".$playernamerow['lgteamid']." scored ".$points." points.<BR>";
	}
}
		
echo "Done.";
include ("../footer.php"); 
?>