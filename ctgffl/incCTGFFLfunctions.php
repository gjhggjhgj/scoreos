<?PHP

function DBerror($DBquery, $DBresult)
// this function takes the query and result from a query and checks it for an error
{
	if (DB::isError($DBresult))
	{
		echo DB::errorMessage($DBresult).' on query: '.$DBquery;
	}
}

function array_csort() {  //coded by Ichier2003
    $args = func_get_args();
    $marray = array_shift($args);

    $msortline = "return(array_multisort(";
    foreach ($args as $arg) {
        $i++;
        if (is_string($arg)) {
            foreach ($marray as $row) {
                $sortarr[$i][] = $row[$arg];
            }
        } else {
            $sortarr[$i] = $arg;
        }
        $msortline .= "\$sortarr[".$i."],";
    }
    $msortline .= "\$marray));";

    eval($msortline);
    return $marray;
}

function stddev($std)
    {
    $total;
    while(list($key,$val) = each($std))
        {
        $total += $val;
        }
    reset($std);
    $mean = $total/count($std);

    while(list($key,$val) = each($std))
        {
        $sum += pow(($val-$mean),2);
        }
    $var = sqrt($sum/(count($std)-1));
    return $var;
    }

function ordinal($number) {

    // when fed a number, adds the English ordinal suffix. Works for any
    // number, even negatives

    if ($number % 100 > 10 && $number %100 < 14):
        $suffix = "th";
    else:
        switch($number % 10) {

            case 0:
                $suffix = "th";
                break;

            case 1:
                $suffix = "st";
                break;

            case 2:
                $suffix = "nd";
                break;

            case 3:
                $suffix = "rd";
                break;

            default:
                $suffix = "th";
                break;
        }

    endif;

    return "${number}<SUP>$suffix</SUP>";

}

function playerPastDeadline($playerid)
//this function determines if a player's team has already played for the week
{
	$week = date(W);
	$now = time();
	$deadline = mktime (19,0,0);
	if ($now > $deadline)
	{
		$todaylocked = 1;
	}
	$today = date("Y-m-d",$now);
	$Wednesday = date("Y-m-d",mktime(19,0,0,0,($week*7)+24,2004));
	$Thursday = date("Y-m-d",mktime(19,0,0,0,($week*7)+25,2004));
	$Friday = date("Y-m-d",mktime(19,0,0,0,($week*7)+26,2004));
	$Tuesday = date("Y-m-d",mktime(0,0,0,0,($week+1)*7+23,2004));
	$gamedatequery = "SELECT gamedate FROM ncaasched a, players b WHERE (a.home = b.teamname || a.visitor = b.teamname) AND b.playerid = $playerid AND gamedate >= '$Wednesday' AND gamedate <= '$Tuesday'";
	$gamedate = $db->getOne($gamedatequery);
    if ($gamedate < $today || ($gamedate == $today && $todaylocked))
		return 1;
	else
		return 0;
}

function getPlayerName($playerid, $cffldb)
{
	$playernamequery = "SELECT firstname, lastname, lgteamname, pos, year FROM players, lgteams WHERE players.lgteamid = lgteams.lgteamid AND players.playerid = $playerid";
	$playernamerow =  $cffldb->getRow($playernamequery);
	DBerror($playernamequery, $playernamerow);

	$playername = $playernamerow['firstname']." ".$playernamerow['lastname']." - ".$playernamerow['pos']." - ".$playernamerow['lgteamname'];
	return $playername;
}

function getPlayerNameLastFirst($playerid, $cffldb)
{
	$playernamequery = "SELECT firstname, lastname, lgteamname, pos, year FROM players, lgteams WHERE players.lgteamid = lgteams.lgteamid AND players.playerid = $playerid";
	$playernamerow =  $cffldb->getRow($playernamequery);
	DBerror($playernamequery, $playernamerow);

	$playername = $playernamerow['lastname'].",".$playernamerow['firstname']." - ".$playernamerow['pos']." - ".$playernamerow['lgteamname'];
	return $playername;
}

function emailLeague($subject,$message)
{
	$fromname = "CTGFFL System";
	$fromemail = "ctgffl@ctgffl.com";

	$toname = "CTGFFL Owners";
//	$toemail = "caelon@gmail.com, caelon@cfl.rr.com";
	$toemail = "sabreman75@hotmail.com, eldeacon@yahoo.com, caelon@gmail.com, cjb32@msn.com, bpdouglass@gmail.com, hcffl@yahoo.com, damien.arabie@gmail.com, acevalenta@neo.rr.com, lane34@sbcglobal.net, petedymeck@yahoo.com, jhorvath1@austin.rr.com, leoilstop@yahoo.com, ghstryder@cfl.rr.com, tblaz@cox.net, fantasy@hyphencreative.com, will.pridemore@gmail.com";

	$subject = "CTGFFL: ".$subject;

	$headers .= "MIME-Version: 1.0\r\n";
	$headers .= "Content-type: text/html; charset=iso-8859-1\r\n";
	$headers .= "From: ".$fromname." <".$fromemail.">\r\n";
	$headers .= "To: ".$toname." <".$toemail.">\r\n";
//	$headers .= "CC: <caelon@cfl.rr.com>\r\n";
	$headers .= "Reply-To: ".$fromname." <caelon@gmail.com>\r\n";
	$headers .= "X-Priority: 1\r\n";
	$headers .= "X-MSMail-Priority: High\r\n";
	$headers .= "X-Mailer: Just My Server";

	$mailmessage = "This is a system generated message from CTGFFL...\r\n".$message;

	mail($toemail, $subject, $mailmessage, $headers);

}

function emailPick($db, $picknumber, $chosenplayer)
{
	$playername = getPlayerName($chosenplayer, $db);
	$currtime = getdate();
	$nextpickduemonth = $currtime['mon'];
	$nextpickdueday = $currtime['mday'];
	$nextpickduehour = $currtime['hours'];
	$nextpickduemin = $currtime['minutes'];
	if ($nextpickduehour >= 12)
	{
		$nextpickduehour -= 12;
		if ($nextpickdueday == 31)
		{
			$nextpickduemonth = 8;
			$nextpickdueday = 1;
		}
		else
		{
			$nextpickdueday += 1;
		}
	}
	else
	{
		$nextpickduehour += 12;
	}

	$nextpickdue = $nextpickduemonth."-".$nextpickdueday." at ".$nextpickduehour.":".$nextpickduemin;

	$numteams = 16;
	$roundofnextpick = ceil($picknumber/$numteams);
	$numberofnextpick = $picknumber%$numteams;
	if ($numberofnextpick == 0) $numberofnextpick = $numteams;
	$numberofnextpick = sprintf("%02s", $numberofnextpick);
	$roundpicknext = $roundofnextpick.".".$numberofnextpick;

	$teamnamequery = "SELECT teamname FROM teams a, draftpicks b WHERE pick=$picknumber AND a.teamid = b.teamid";
	$teamname =  $db->getOne($teamnamequery);
	DBerror($teamnamequery, $teamname);

	$picknumber++;

	$nextteamnamequery = "SELECT teamname FROM teams a, draftpicks b WHERE pick=$picknumber AND a.teamid = b.teamid";
	$nextteamname =  $db->getOne($nextteamnamequery);
	DBerror($nextteamnamequery, $nextteamname);

	$picknumber++;

	$deckteamnamequery = "SELECT teamname FROM teams a, draftpicks b WHERE pick=$picknumber AND a.teamid = b.teamid";
	$deckteamname =  $db->getOne($deckteamnamequery);
	DBerror($deckteamnamequery, $deckteamname);

	$picknumber++;

	$holeteamnamequery = "SELECT teamname FROM teams a, draftpicks b WHERE pick=$picknumber AND a.teamid = b.teamid";
	$holeteamname =  $db->getOne($holeteamnamequery);
	DBerror($holeteamnamequery, $holeteamname);

	$message = "With pick ".$roundpicknext.", the ".$teamname." select ".$playername.".\r\n\r\n";
	$message .= $nextteamname." are up and their pick is due ".$nextpickdue."\r\n";
	$message .= $deckteamname." are on deck.\r\n\r\n";
	$message .= $holeteamname." are in the hole.\r\n\r\n";
	$message .= "For the full draft report, go to www.ctgffl.com/lgmgmt/DraftReport.php";

	$subject = "Draft Pick ".$roundpicknext." by ".$teamname;
	emailLeague($subject, $message);

}

function getncaaopponent($db, $team, $week)
{
    $Thursday = mktime(0,0,0,0,(($week+39)*7)+1,2006);
    $Wednesday = mktime(0,0,0,0,(($week+39+1)*7),2006);
    $Thursday = date("Y-m-d",$Thursday);
    $Wednesday = date("Y-m-d",$Wednesday);

    $HomeScheduleQuery = "SELECT lgteamabbr, visitor FROM lgteams, ncaasched WHERE home=$team AND gamedate >= '$Thursday' AND gamedate <= '$Wednesday' AND visitor = lgteamid";
    $HomeScheduleResult = $db->query($HomeScheduleQuery );
    DBerror($HomeScheduleQuery, $HomeScheduleResult);
    $HomeScheduleNumrows = $HomeScheduleResult->numrows();
    if ($HomeScheduleNumrows)
    {
        $HomeScheduleRow = $HomeScheduleResult->fetchrow(DB_FETCHMODE_ASSOC);
        $gametext = " (vs. ".$HomeScheduleRow['lgteamabbr'].")";
    }
    else
    {
        $VisScheduleQuery = "SELECT lgteamabbr, home FROM lgteams, ncaasched WHERE visitor=$team AND gamedate >= '$Thursday' AND gamedate <= '$Wednesday' AND home=lgteamid";
        $VisScheduleResult = $db->query($VisScheduleQuery );
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
}

function getTeamAbbr($db, $teamid)
{
    $abbrquery = "SELECT lgteamabbr FROM lgteams WHERE lgteamid = $teamid";
    $abbr = $db->getOne($abbrquery);
    DBerror($abbrquery, $abbr);

    return $abbr;
}
?>
