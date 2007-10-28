<?PHP
include ("../mainfile.php");
include ("../header.php");
include('incCTGFFLfunctions.php');
include('incConnectDBs.php');

if (date("D") == "Sun")
{
	$thisweek = date(W)-35;
}
else
{
	$thisweek = date(W)-36;
}
$thruTue = date("Y-m-d",mktime(0,0,0,0,(($week+39+1)*7)-2,2007));
$supercommish = 0;

$chosenleague = 1;
$supercommish = 1;

if ($supercommish)
{
    extract ($_POST);
    if (!$chosenleague)
    {
        $LeagueNameQuery = "SELECT leaguename, leagueid FROM leagues";
        $LeagueNameResult = $cffldb->query($LeagueNameQuery);
        DBerror($LeagueNameQuery, $LeagueNameResult);
        ?>
        <form action="CFFLScoreStandings.php" method="POST">
        <select name="chosenleague">
		<option value="99">All leagues</option>
        <?PHP
        while ($leaguenamerow = $LeagueNameResult->fetchrow(DB_FETCHMODE_ASSOC))
        {
           	$leaguename = $leaguenamerow['leaguename'];
            $leagueid = $leaguenamerow['leagueid'];
            echo "<option value=".$leagueid.">".$leaguename."</option>";
        }
        ?>
            </select>
            <input type="submit" name="submitbutton" value="Select League">
            </form>
        <?PHP
    }
	else
	{
		if ($chosenleague != 99)
		{
			$leagueadd = " AND leagueid = $chosenleague";
		}
		$schedulequery = "SELECT * FROM schedule WHERE week = $thisweek".$leagueadd." ORDER BY leagueid";
        $scheduleresult = $cffldb->query($schedulequery);
        DBerror($schedulequery, $scheduleresult);
        while ($schedulerow = $scheduleresult->fetchrow(DB_FETCHMODE_ASSOC))
        {
			extract ($schedulerow);
            echo "home: $homeid v:$visitorid ";
			$homecollpointsquery = "SELECT sum(points) FROM playerlineups a, players b WHERE week = $thisweek AND starter = 'Y' AND teamid = $homeid AND a.playerid = b.playerid and year != 'Pro' GROUP by teamid";
            echo $homecollpointsquery;
			$homecollpoints = $cffldb->getOne($homecollpointsquery);
			$visitorcollpointsquery = "SELECT sum(points) FROM playerlineups a, players b WHERE week = $thisweek AND starter = 'Y' AND teamid = $visitorid AND a.playerid = b.playerid and year != 'Pro' GROUP by teamid";
			$visitorcollpoints = $cffldb->getOne($visitorcollpointsquery);
            $homepropointsquery = "SELECT sum(points) FROM playerlineups a, players b WHERE week = $thisweek AND starter = 'Y' AND teamid = $homeid AND a.playerid = b.playerid and year = 'Pro' GROUP by teamid";
            $homepropoints = $cffldb->getOne($homepropointsquery);
            $visitorpropointsquery = "SELECT sum(points) FROM playerlineups a, players b WHERE week = $thisweek AND starter = 'Y' AND teamid = $visitorid AND a.playerid = b.playerid and year = 'Pro' GROUP by teamid";
            $visitorpropoints = $cffldb->getOne($visitorpropointsquery);

            $conferencequery = "SELECT conferenceid FROM teams WHERE teamid = $homeid or teamid = $visitorid";
			$conferenceresult = $cffldb->getAll($conferencequery);
			if ($conferenceresult[0]['conferenceid'] == $conferenceresult[1]['conferenceid'])
			{
			    $confstandings = 1;
			}
			else
			{
				$confstandings = 0;
			}

            $divquery = "SELECT divisionid FROM teams WHERE teamid = $homeid or teamid = $visitorid";
            $divresult = $cffldb->getAll($divquery);
            if ($conferenceresult[0]['divisionid'] == $conferenceresult[1]['divisionid'])
            {
                $divstandings = 1;
            }
            else
            {
                $divstandings = 0;
            }

            $homedivwins = 0;
            $homedivlosses = 0;
            $homeconfwins = 0;
            $homeconflosses = 0;
            $visitordivlosses = 0;
            $visitorconflosses = 0;
            $visitordivwins = 0;
            $visitorconfwins = 0;
			if ($homecollpoints > $visitorcollpoints)
			{
				$homewins = 1;
				$homelosses = 0;
				$hometies = 0;
				$visitorwins = 0;
				$visitorlosses = 1;
				$visitorties = 0;
				$homedivwins = 0;
				$homedivlosses = 0;
				$homedivties = 0;
				$visitordivwins = 0;
				$visitordivlosses = 0;
				$visitordivties = 0;
				if ($divstandings)
				{
					$homedivwins = 1;
					$visitordivlosses = 1;
				}
                if ($confstandings)
                {
                    $homeconfwins = 1;
                    $visitorconflosses = 1;
                }
			}
			elseif ($homecollpoints < $visitorcollpoints)
			{
				$homewins = 0;
				$homelosses = 1;
				$hometies = 0;
				$visitorwins = 1;
				$visitorlosses = 0;
				$visitorties = 0;
				$homedivwins = 0;
				$homedivlosses = 0;
				$homedivties = 0;
				$visitordivwins = 0;
				$visitordivlosses = 0;
				$visitordivties = 0;
				if ($divstandings)
				{
					$homedivlosses = 1;
					$visitordivwins = 1;
				}
                if ($confstandings)
                {
                    $homeconflosses = 1;
                    $visitorconfwins = 1;
                }
			}
			else
			{
				$homewins = 0;
				$homelosses = 0;
				$hometies = 1;
				$visitorwins = 0;
				$visitorlosses = 0;
				$visitorties = 1;
				$homedivwins = 0;
				$homedivlosses = 0;
				$homedivties = 0;
				$visitordivwins = 0;
				$visitordivlosses = 0;
				$visitordivties = 0;
				if ($divstandings)
				{
					$homedivties = 1;
					$visitordivties = 1;
				}
                if ($confstandings)
                {
                    $homeconfties = 1;
                    $visitorconfties = 1;
                }
			}
			
            if ($homepropoints > $visitorpropoints)
            {
                $homewins += 1;
                $homelosses += 0;
                $hometies += 0;
                $visitorwins += 0;
                $visitorlosses += 1;
                $visitorties += 0;
                $homedivwins += 0;
                $homedivlosses += 0;
                $homedivties += 0;
                $visitordivwins += 0;
                $visitordivlosses += 0;
                $visitordivties += 0;
                if ($divstandings)
                {
                    $homedivwins += 1;
                    $visitordivlosses += 1;
                }
                if ($confstandings)
                {
                    $homeconfwins += 1;
                    $visitorconflosses += 1;
                }
            }
            elseif ($homepropoints < $visitorpropoints)
            {
                $homewins += 0;
                $homelosses += 1;
                $hometies += 0;
                $visitorwins += 1;
                $visitorlosses += 0;
                $visitorties += 0;
                $homedivwins += 0;
                $homedivlosses += 0;
                $homedivties += 0;
                $visitordivwins += 0;
                $visitordivlosses += 0;
                $visitordivties += 0;
                if ($divstandings)
                {
                    $homedivlosses += 1;
                    $visitordivwins += 1;
                }
                if ($confstandings)
                {
                    $homeconflosses += 1;
                    $visitorconfwins += 1;
                }
            }
            else
            {
                $homewins += 0;
                $homelosses += 0;
                $hometies += 1;
                $visitorwins += 0;
                $visitorlosses += 0;
                $visitorties += 1;
                $homedivwins += 0;
                $homedivlosses += 0;
                $homedivties += 0;
                $visitordivwins += 0;
                $visitordivlosses += 0;
                $visitordivties += 0;
                if ($divstandings)
                {
                    $homedivties += 1;
                    $visitordivties += 1;
                }
                if ($confstandings)
                {
                    $homeconfties += 1;
                    $visitorconfties += 1;
                }
            }

            if ($week == 0)
            {
                $visitorpropoints = 0;
                $homepropoints = 0;
            }

            $updatevisitorquery = "UPDATE standings SET wins=wins + $visitorwins, losses = losses + $visitorlosses, ties = ties + $visitorties, totalpoints = totalpoints + $visitorcollpoints + $visitorpropoints, divwins = divwins + $visitordivwins, divlosses = divlosses + $visitordivlosses, divties = divties + $visitordivties, confwins = confwins + $visitorconfwins, conflosses = conflosses + $visitorconflosses WHERE teamid = $visitorid";
			$updatevisitorresult = $cffldb->query($updatevisitorquery );
			DBerror($updatevisitorquery,$updatevisitorresult);
			$updatehomequery = "UPDATE standings SET wins=wins + $homewins, losses = losses + $homelosses, ties = ties + $hometies, totalpoints = totalpoints + $homecollpoints + $homepropoints, divwins = divwins + $homedivwins, divlosses = divlosses + $homedivlosses, divties = divties + $homedivties, confwins = confwins + $homeconfwins, conflosses = conflosses + $homeconflosses WHERE teamid = $homeid";
			$updatehomeresult = $cffldb->query($updatehomequery );
			DBerror($updatehomequery,$updatehomeresult);
		}
	}
}
include ("../footer.php");
?>