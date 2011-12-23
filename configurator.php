<?php

// config variables
$dbpath = "configurator.db";

// import and sanitize GET variables
$id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
$applied = filter_input(INPUT_GET, 'applied', FILTER_SANITIZE_NUMBER_INT);
$getconfig = filter_input(INPUT_GET, 'getconfig', FILTER_SANITIZE_NUMBER_INT);

// local vars
$db;

// Connect to the database with PDO
try {
  $db = new PDO('sqlite:'.$dbpath);
} catch (Exception $e) {
  die ($e);
}


/********** RUNTIME SECTIONS **********/
// When called without args, return nothing

// When called with getconfig=1, return the first ready config, and mark as sent
// TODO: make this a transaction so there's less chance of giving out two identical configs
if($getconfig == "1") {
  try {
    $deviceq = $db->prepare('SELECT * FROM DEVICES WHERE READY=1 AND SENT ISNULL LIMIT 1;');
    $deviceq->execute();
    $device = $deviceq->fetchObject();

    $updatesent = $db->prepare("UPDATE DEVICES SET SENT=1 WHERE id=:id;");
    $updatesent->bindParam(':id', $device->id);
    $updatesent->execute();

    // OUTPUT CONFIG
    if(isset($device->config)){
      echo $device->config;
    }

  } catch (Exception $e) {
    die ($e);
  }
}

// When called with id and applied=1, mark as applied
if(strlen($id) > 0 && $applied == "1") {
  try {
    $updateapplied = $db->prepare("UPDATE DEVICES SET APPLIED=1, CURRENTIP=:currentip WHERE id=:id;");
    $updateapplied->bindParam(':currentip', $_SERVER['REMOTE_ADDR']); // store the remote client IP for calling blink.php later
    $updateapplied->bindParam(':id', $id);
    $updateapplied->execute();
  } catch (Exception $e) {
    die ($e);
  }
}
