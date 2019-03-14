<?php

if (isset($_REQUEST['task'])) {
    $task = $_REQUEST['task'];
}
if (isset($_REQUEST['system'])) {
    $system = $_REQUEST['system'];
}
if (isset($_REQUEST['proceed'])) {
    $proceed = $_REQUEST['proceed'];
}
if (isset($_REQUEST['enddate'])) {
    $enddate = $_REQUEST['enddate'];
}

#If suspenson is set with no end date, a default one of 7 days is calulated
if (($suspend==1)&&(strlen($enddate)<2)) {
    $enddate = strtotime("+7 day");
    $enddate = date('Y-m-d', $enddate);
} else {
    $enddate = date('Y-m-d', strtotime(str_replace('-', '/', $enddate)));
}


if (($system == "none") || ($system == "")) {
    $action="stop";
} elseif ($proceed == "Proceed") {
    $action="doit";
} else {
    $action="go";
}



if ($action == "go") {
    if ($system == "DU") {
        $displaysystem="Dutchess BOCES";
    }
    if ($system == "MH") {
        $displaysystem="Mid-Hudson Library System";
    }
    if ($system == "OU") {
        $displaysystem="Orange Ulster BOCES";
    }
    if ($system == "RC") {
        $displaysystem="Ramapo Catskill Library System";
    }
    if ($system == "RB") {
        $displaysystem="Rockland BOCES";
    }
    if ($system == "SE") {
        $displaysystem="SENYLRC";
    }
    if ($system == "SB") {
        $displaysystem="Sullivan BOCES";
    }
    if ($system == "UB") {
        $displaysystem="Ulster BOCES";
    }
    echo "You have chosen to <b>$task lending</b> for all libraries of the <b>$displaysystem</b>.<br><br>";
    echo "This will overwrite the setting for these libraries. Are you sure you wish to proceed? "; ?><form action="/status-confirmation" method="post">
  <input type="hidden" name="task" value="<?php echo $task; ?>">
  <input type="hidden" name="system" value="<?php echo $system; ?>">
  <input type="hidden" name="enddate" value="<?php echo $enddate; ?>">
  <input type="submit" name="proceed" value="Proceed"> <a href='/adminlib'>Cancel</a></form><?php
} elseif ($action == "doit") {
        echo "<b>The libraries have been updated!<b>";
        #Connect to database
        require '../seal_script/seal_db.inc';
        $db = mysqli_connect($dbhost, $dbuser, $dbpass);
        mysqli_select_db($db, $dbname);


        if ($task == "suspend") {
            #Suspend
            $sqlupdate = "UPDATE `$sealLIB` SET suspend='1', SuspendDateEnd='$enddate' WHERE `participant` = '1' and `suspend` = '0' and `system` = '$system' ";
        } else {
            #Activate
            $sqlupdate = "UPDATE `$sealLIB` SET suspend='0' WHERE `participant` = '1' and `suspend` = '1' and `system` = '$system' ";
        }
        echo $sqlupdate;
        $result = mysqli_query($db, $sqlupdate);

        #Close the database
        mysqli_close($db);
    } else {
        echo "Sorry! We cannot complete your action.  <a href='/adminlib'>Please go back</a> and select a library system.";
    }
?>
