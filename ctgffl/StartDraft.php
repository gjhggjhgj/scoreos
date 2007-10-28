<script type="text/javascript">

function change_pick()
{
	var baseurl = "http://www.ctgffl.com/lgmgmt/StartDraft.php?pick=";
	selectedPick = document.getElementById("predraftpick").value;
	if (selectedPick.length != 0)
	{
	  location.href = baseurl + selectedPick ;
	}

}

function confirmPick(dropdown)
{
	var confirmbtn = confirm ("Press OK to select "+dropdown+" or cancel to reselect.")
	if (confirmbtn)
	{
		return true
	}
	else
	{
		return false
	}
}

function MoveItem(ctrlSource, ctrlTarget)
{
    var Source = document.getElementById(ctrlSource);
    var Target = document.getElementById(ctrlTarget);

    if ((Source != null) && (Target != null))
    {
            var newOption = new Option(); // Create a new instance of ListItem
            newOption.text = Source.options[Source.options.selectedIndex].text;
            newOption.value = Source.options[Source.options.selectedIndex].value;

            Target.options[Target.length] = newOption; //Append the item in Target
            Source.remove(Source.options.selectedIndex);  //Remove the item from Source
    }
}

// -------------------------------------------------------------------
// hasOptions(obj)
//  Utility function to determine if a select object has an options array
// -------------------------------------------------------------------
function hasOptions(obj)
{
	if (obj!=null && obj.options!=null) { return true; }
	return false;
}

// -------------------------------------------------------------------
// swapOptions(select_object,option1,option2)
//  Swap positions of two options in a select list
// -------------------------------------------------------------------
function swapOptions(obj,i,j)
{
	var o = obj.options;
	var i_selected = o[i].selected;
	var j_selected = o[j].selected;
	var temp = new Option(o[i].text, o[i].value, o[i].defaultSelected, o[i].selected);
	var temp2= new Option(o[j].text, o[j].value, o[j].defaultSelected, o[j].selected);
	o[i] = temp2;
	o[j] = temp;
	o[i].selected = j_selected;
	o[j].selected = i_selected;
}

// -------------------------------------------------------------------
// moveOptionUp(select_object)
//  Move selected option in a select list up one
// -------------------------------------------------------------------
function moveOptionUp(obj)
{
	if (!hasOptions(obj)) { return; }
	for (i=0; i<obj.options.length; i++)
	{
		if (obj.options[i].selected)
		{
			if (i != 0 && !obj.options[i-1].selected)
			{
				swapOptions(obj,i,i-1);
				obj.options[i-1].selected = true;
			}
		}
	}
}

// -------------------------------------------------------------------
// moveOptionDown(select_object)
//  Move selected option in a select list down one
// -------------------------------------------------------------------
function moveOptionDown(obj)
{
	if (!hasOptions(obj)) { return; }
	for (i=obj.options.length-1; i>=0; i--)
	{
		if (obj.options[i].selected)
		{
			if (i != (obj.options.length-1) && ! obj.options[i+1].selected)
			{
				swapOptions(obj,i,i+1);
				obj.options[i+1].selected = true;
			}
		}
	}
}


function showSelected()
{
    var optionList = document.getElementById("PreDrafts").options;
    var data = '';
    var len = optionList.length;
    for(i=0; i<len; i++)
    {
        data += optionList.item(i).value;
        data += ',';
    }
    alert(data);
}

function submitlist(alist)
{
	// selects all values in list box
	for (i = 0; i < alist.length; ++i)
	{
		alist[i].selected = "true";
	}

	// changes the name of the <select> tag to match an array for PHP processing
	document.forms[0].PreDrafts.name = "PreDrafts[]";
}
</script>

<?PHP

include ("../mainfile.php");
include ("../header.php");
include('incCTGFFLfunctions.php');
include('incConnectDBs.php');

$chosenleague = 0;

if (!$xoopsUser)
{
?>
	<table><tr><td align="center">You must <a href="http://www.ctgffl.com">log in</a> first</td></tr></table>
<?PHP
}
else
{
	$userid = $xoopsUser->getVar('uid');
	$teamquery="SELECT teamid, teamname FROM teams WHERE teamownerid=$userid and leagueid = $chosenleague";
	$teamresult = $cffldb->getRow($teamquery);
	DBerror($teamquery, $teamresult);


	$chosenteam = $teamresult['teamid'];

	$nextteampickquery = "SELECT pick FROM draftpicks WHERE playerid IS NULL AND leagueid = $chosenleague AND teamid = $chosenteam ORDER BY pick";
	$nextteampick = $cffldb->getOne($nextteampickquery );
	DBerror($nextteampickquery, $nextteampick);

	if ($_GET['pick'])
		$nextteampick = $_GET['pick'];

	$nextpickquery = "SELECT currentpick, pickdue, numteams FROM draft WHERE leagueid = $chosenleague";
	$nextpickresult = $cffldb->getRow($nextpickquery );
	DBerror($nextpickquery, $nextpickresult);

	$nextpick = $nextpickresult['currentpick'];
	$pickduetime = $nextpickresult['pickdue'];
	$numteams = $nextpickresult['numteams'];

	$roundofnextpick = ceil($nextpick/$numteams);
	$numberofnextpick = $nextpick%$numteams;
	if ($numberofnextpick == 0) $numberofnextpick = $numteams;
	$numberofnextpick = sprintf("%02s", $numberofnextpick);
	$roundpicknext = $roundofnextpick.".".$numberofnextpick;

	$picksleftquery = "SELECT COUNT(teamid) FROM draftpicks WHERE leagueid = $chosenleague AND playerid IS NULL AND pick >= $nextpick AND pick < $nextteampick";
	$picksleft = $cffldb->getOne($picksleftquery );
	DBerror($picksleftquery, $picksleft);

	echo "<table><tr><td colspan=2 align=\"center\">";
	echo "<b><big><big>".$teamresult['teamname']."</big></big></b><br>";
	//$picksleft = $nextteampick - $nextpick;
	if ($picksleft == 1)
	{
		echo "<i><big>You are up next!</big><br>";
	}
	elseif ($picksleft == 0)
	{
		echo "<i><big>You are up!!</big><br>";
	}
	elseif ($nextteampick < $nextpick)
	{
		echo "<i><big>You have been skipped! Pick now!</big><br>";
	}
	else
	{
		echo "<i><big>You have ".$picksleft." picks before you</big><br>";
	}
	echo "The current pick ($roundpicknext) will expire at $pickduetime</i><br><br><br>";
	echo "</td></tr><tr><td><table><tr><th colspan=2>Your Roster</th></tr>";

	$rosterquery = "SELECT players.playerid as playerid, lastname, firstname, lgteamabbr, year, players.pos as playerpos FROM playerteams, players, positionorder, lgteams WHERE playerteams.teamid = $chosenteam AND playerteams.playerid = players.playerid AND players.pos = positionorder.pos AND players.lgteamid = lgteams.lgteamid ORDER BY posorder, lastname";
	$rosterresult = $cffldb->query($rosterquery);
	DBerror($rosterquery,$rosterresult);

	while ($rosterrow = $rosterresult->fetchrow(DB_FETCHMODE_ASSOC))
	{
		extract($rosterrow, EXTR_PREFIX_ALL, "ros");
		switch ($ros_year)
		{
			case 'Freshman':
				$ros_year='FR';
				break;
			case 'Sophomore':
				$ros_year='SO';
				break;
			case 'Junior':
				$ros_year='JR';
				break;
			case 'Senior':
				$ros_year='SR';
				break;
		}
	?>
	<tr>
	<td><font size=1><?PHP echo $ros_playerpos; ?></font></td><td><font size=1><?PHP echo "$ros_firstname $ros_lastname ($ros_year) - $ros_lgteamabbr";?></font></td>
	</tr>
	<?PHP
	}
	echo "</td></table><td><table>";
	if ($picksleft == 0 || $nextteampick < $nextpick)
	{
	?>
	<form name="draftform" action="ConfirmDraft.php" onSubmit="return confirmPick(document.draftform.draftplayerlist0[document.draftform.draftplayerlist0.selectedIndex].text)" method="POST">
	<input type="hidden" name="confirm" value="1">
	<?PHP
	}
	else
	{
	?>
	<form name="draftform" action="ConfirmDraft.php" method="POST">
	<tr><td align="center">Picks for: <select name="predraftpick" id="predraftpick" OnChange="change_pick()">
	<?PHP
	$teampicks = $cffldb->getCol($nextteampickquery);
	DBerror($nextteampickquery,$teampicks);
	foreach ($teampicks as $predraftpick)
	{
		$roundofpick = ceil($predraftpick/$numteams);
		$numberofpick = $predraftpick%$numteams;
		if ($numberofpick == 0) $numberofpick = $numteams;
		$numberofpick = sprintf("%02s", $numberofpick);
		$roundpick = $roundofpick.".".$numberofpick;
		echo "<option ";
		if ($nextteampick == $predraftpick)
			echo "SELECTED ";
		echo "value=\"".$predraftpick."\"";
		echo ">";
		echo $roundpick;
	}
	?>
	</select></td></tr>
	<?PHP
	}
	//Load the predrafts
	$predraftpicksquery = "SELECT playerid FROM predraft WHERE leagueid = $chosenleague AND teamid = $chosenteam AND pick = $nextteampick ORDER BY pickorder";
	$predraftpicks = $cffldb->getCol($predraftpicksquery );
	DBerror($predraftpicksquery, $predraftpicks);

	//Load the free agents

	$FreeAgentsQuery = "SELECT DISTINCT playerid, firstname, lastname, lgteamabbr, pos, year FROM players, lgteams WHERE players.lgteamid = lgteams.lgteamid AND (NOT EXISTS (SELECT playerid FROM playerteams WHERE leagueid =$chosenleague AND players.playerid = playerteams.playerid) AND (pos = 'QB' OR pos = 'RB' OR pos = 'WR' OR pos = 'TE' OR pos = 'K') AND year != 'Pro') ORDER BY lastname, firstname";
	$FreeAgents = $cffldb->getAssoc($FreeAgentsQuery);
	DBerror($FreeAgentsQuery, $FreeAgents);

	echo "<table><tr>";
	?>
	    	<td>
	            <p align="center">
	                <input onclick="MoveItem('FreeAgents', 'PreDrafts');" type="button" value="Add player" />
	            </p>
	            <p align="center">
	                <input onclick="MoveItem('PreDrafts', 'FreeAgents');" type="button" value="Remove player" />
	            </p>
	        </td>

	<?PHP
	echo "<td align=\"center\">";
    echo "<br><select id=\"FreeAgents\" name=\"freeagents\">";
	foreach ($FreeAgents as $fa_playerid => $currFA)
	{
		extract ($currFA, EXTR_PREFIX_ALL, "fa");
		$FreeAgentName = $fa_lastname.', '.$fa_firstname.' - '.$fa_pos.' - '.$fa_lgteamabbr.'('.$fa_year.')';
//        echo "<option Value=".$fa_playerid.">".$FreeAgentName."</option>";
        echo "<option Value=".$fa_playerid.">".getPlayerNameLastFirst($fa_playerid, $cffldb)."</option>";
    }
    ?>
    </select></td></tr><tr>
	<td><br><br><br><br>
		<p align="center">
          	<INPUT TYPE=button VALUE="Move Up" onClick="moveOptionUp(this.form['PreDrafts'])">
        </p>
        <p align="center">
           	<INPUT TYPE=button VALUE="Move Down" onClick="moveOptionDown(this.form['PreDrafts'])">
        </p>
    </td>
        <td align="center">
            <select name="PreDrafts" id="PreDrafts" size=20 multiple width=100>
	<?PHP
	foreach ($predraftpicks as $pre_playerid)
	{
		echo "<option Value=".$pre_playerid.">".getPlayerNameLastFirst($pre_playerid, $cffldb)."</option>";
	}

	?>
    </select></td></tr></table><br>
    <tr><td align="center" colspan=2>
    <input type="submit" name="submittrans" value="Submit Picks" onClick="javascript:submitlist(document.forms[0].PreDrafts);">
    </td>
</tr>
	<input type="hidden" name="chosenleague" value="<?PHP echo $chosenleague; ?>">
	<input type="hidden" name="chosenteam" value="<?PHP echo $chosenteam; ?>">
	<input type="hidden" name="chosenpick" value="<?PHP echo $nextteampick; ?>">
	<input type="hidden" name="nextpick" value="<?PHP echo $nextpick; ?>">
	</form>
	</table></td></tr></table>

<?PHP
}
$cffldb->disconnect();
include ("../footer.php");
?>