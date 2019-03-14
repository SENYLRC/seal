<?php
#Get Drupal account email of the person making the changes
global $user; // load the user entity so to pick the field from.
$user_contaning_field = user_load($user->uid); // Check if we're dealing with an authenticated user
if ($user->uid) { // Get field value;
    $field_first_name = field_get_items('user', $user_contaning_field, 'field_first_name');
    $field_last_name = field_get_items('user', $user_contaning_field, 'field_last_name');
    $field_your_institution = field_get_items('user', $user_contaning_field, 'field_your_institution');
    $field_loc_location_code = field_get_items('user', $user_contaning_field, 'field_loc_location_code');
    $field_street_address = field_get_items('user', $user_contaning_field, 'field_street_address');
    $field_city_state_zip = field_get_items('user', $user_contaning_field, 'field_city_state_zip');
    $field_work_phone = field_get_items('user', $user_contaning_field, 'field_work_phone');
    $field_home_library_system = field_get_items('user', $user_contaning_field, 'field_home_library_system');
    $field_filter_own_system = field_get_items('user', $user_contaning_field, 'field_filter_own_system');
    $email = $user->mail;
$AuthedUser ="1";
$firstname = $field_first_name[0]['value'];
$lastname = $field_last_name[0]['value'];
$wholename = "$firstname $lastname";

}

function build_notes($reqnote, $lendnote) {
    $displaynotes = "";
    if ((strlen($reqnote) > 2) && (strlen($lendnote) > 2)) {
        $displaynotes = $reqnote . "</br>Lender Note: " . $lendnote;
    }
    if ((strlen($reqnote) > 2) && (strlen($lendnote) < 2)) {
        $displaynotes=$reqnote;
    }
    if ((strlen($reqnote) < 2) && (strlen($lendnote) > 2)) {
        $displaynotes= "Lender Note: " . $lendnote;
    }
    return $displaynotes;
}
function build_renewnotes($renewNote, $renewNoteLender) {
    $displayrenewnotes = "";
    if ((strlen($renewNote) > 2) && (strlen($renewNoteLender) > 2)) {
        $displayrenewnotes = "Renew Note: ".$renewNote . "</br>Lender Note: " . $renewNoteLender;
    }
    if ((strlen($renewNote) > 2) && (strlen($renewNoteLender) < 2)) {
        $displayrenewnotes="Renew Note: ".$renewNote;
    }
    if ((strlen($renewNote) < 2) && (strlen($renewNoteLender) > 2)) {
        $displaynotes= "Lender Note: " . $renewNoteLender;
    }
    return $displayrenewnotes;
}
function build_return_notes($returnnote, $returnmethodtxt) {
    if ((strlen($returnnote) > 2) || (strlen($returnmethod) > 2)) {
        $dispalyreturnnotes = "Return Note: " .$returnnote."  <Br>Return Method: ".$returnmethodtxt;
    }
    return $dispalyreturnnotes;
}
function checked($filter_value) {
    if (($filter_value == "yes")) {
        $filterout="checked";
    } else {
        $filterout="";
    }
    return $filterout;
}
function shipmtotxt($shipmethod) {
    if ($shipmethod=="usps") {
        $shiptxt='US Mail';
    }
    if ($shipmethod=="mhls") {
        $shiptxt='Mid-Hudson Courier';
    }
    if ($shipmethod=="rcls") {
        $shiptxt='RCLS Courier';
    }
    if ($shipmethod=="empire") {
        $shiptxt='Empire Delivery';
    }
    if ($shipmethod=="ups") {
        $shiptxt='UPS';
    }
    if ($shipmethod=="fedex") {
        $shiptxt='FedEx';
    }
    if ($shipmethod=="other") {
        $shiptxt='Other';
    }
    if ($shipmethod=="") {
        $shiptxt='';
    }
    return $shiptxt;
}
function itemstatus($fill, $receiveaccount, $returnaccount, $returndate, $receivedate, $checkinaccount, $checkindate) {
    if ($fill=="1") {
        $fill="Filled";
    }
    if ($fill=="0") {
        $fill="Not Filled";
    }
    if ($fill=="3") {
        $fill="No Answer";
    }
    if ($fill=="4") {
        $fill="Expired";
    }
    if ($fill=="6") {
        $fill="Canceled";
    }
    if ((strlen($receiveaccount)>1)&&(strlen($returnaccount)<1)&&(strlen($checkinaccount)<1)) {
        $fill="Loan Item Received<br>".$receivedate."";
    }
    if ((strlen($checkinaccount)<1)&&(strlen($receiveaccount)>1)&&(strlen($returnaccount)>1)) {
        $fill="Loan Item Returned<Br>".$returndate."";
    }
    if (strlen($checkinaccount)>1) {
        $fill="Item Checkin by Lender<Br>".$checkindate."";
    }
    return $fill;
}
function selected($days, $filter_value) {
    if ($days == $filter_value) {
        $filterout = "selected";
    } else {
        $filterout = "";
    }
    return $filterout;
}
function elementHunt($startdated, $hunting) {
    switch ($hunting) {
    case "D":
      $hunted = substr($startdated, 3, 2);
      break;
    case "M":
      $hunted = substr($startdated, 0, 2);
      break;
    case "Y":
      $hunted = substr($startdated, 6, 4);
      break;
  }
    return $hunted;
}
function convertDate($InputDate) {
    $Y = elementHunt($InputDate, "Y");
    $M = elementHunt($InputDate, "M");
    $D = elementHunt($InputDate, "D");
    $OutputDate = $Y . "-" . $M . "-" . $D;
    return $OutputDate;
}
function returnLimits($Offset, $filter_numresults) {
    if (($Offset == "") || ($$Offset = 0)) {
        $startint = 0;
    } else {
        $startint = $Offset * $filter_numresults;
    }
    $endint = $startint + $filter_numresults;
}
