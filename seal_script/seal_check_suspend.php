<?php




#####Connect to database
require 'seal_db.inc';
$db = mysqli_connect($dbhost, $dbuser, $dbpass);
mysqli_select_db($db,$dbname);


#Generate Time Stamp
$today = date("Y-m-d");
$sqlselect="SELECT * FROM `SENYLRC-SEAL2-Library-Data` WHERE `participant` = 1 AND `suspend` = 1";
$retval = mysqli_query($db,$sqlselect);
$GETLISTCOUNT = mysqli_num_rows ($retval);

while ($row = mysqli_fetch_assoc($retval)) {
			$illemail	= $row["ILL Email"];
			$loc   		= $row["loc"];
                        $suspendenddate    = $row["SuspendDateEnd"];
			$suscheckdate	= $row["SuspendCheckDate"];


#Check Date for every location returned
if ($today > $suspendenddate){
    echo " I will end the suspension for $loc\n";
    $updatesql = "UPDATE `SENYLRC-SEAL2-Library-Data` SET `suspend` = '0',`SuspendDateEnd` = NULL , `SuspendCheckDate` = '$today' WHERE `loc` = '$loc'";
    mysqli_query($db, $updatesql);
   echo "$updatesql\n";

}else{
    echo " I will NOT end the suspension for $loc\n";
   $updatesql = "UPDATE `SENYLRC-SEAL2-Library-Data` SET `SuspendCheckDate` = '$today' WHERE `loc` = '$loc'";
   mysqli_query($db, $updatesql);
}

}
?>
