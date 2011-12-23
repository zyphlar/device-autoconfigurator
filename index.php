<?php

// config variables
$dbpath = "configurator.db";

// import and sanitize GET variables
$id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
$configdevices = filter_input_array(INPUT_GET, array('configdevices' => array('filter' => FILTER_SANITIZE_NUMBER_INT, 'flags'  => FILTER_REQUIRE_ARRAY)));
if(isset($configdevices['configdevices']))
  $configdevices = $configdevices['configdevices'];
$applied = filter_input(INPUT_GET, 'applied', FILTER_SANITIZE_NUMBER_INT);
$download = filter_input(INPUT_GET, 'download', FILTER_SANITIZE_SPECIAL_CHARS);

// local vars
$db;

// init sql database if necessary
if ( ! file_exists( $dbpath ) )
  $newdb = new SQLiteDatabase( $dbpath, 0660 );
if ( ! file_exists( $dbpath ) )
  die( "Unable to create database file at " . $dbpath );

// Connect to the database with PDO
try {
  $db = new PDO('sqlite:'.$dbpath);
} catch (Exception $e) {
  die ($e);
}

// Create table if doesn't exist
$q = $db->query("PRAGMA table_info(DEVICES)");
if ( $q->rowCount() == 0 ) {
  $db->query( "CREATE TABLE DEVICES ( id INTEGER PRIMARY KEY, config TEXT, ready INTEGER, sent INTEGER, applied INTEGER );" );
}


/********** RUNTIME SECTIONS **********/
// Always get devices from database
try {
    $devicelist = $db->prepare('SELECT * FROM DEVICES;');
    $devicelist->execute();
} catch (Exception $e) {
    die ($e);
}

// if requested, download unconfigured devices from MyDatavation
if(strlen($download) > 0) {
  // TODO: placeholder
  $devices[time()] = time();
  $devices[time()+1] = time();
  $devices[time()+2] = time();
  $devices[time()+3] = time();

  // save devices in database
  try {
    $stmt = $db->prepare("INSERT INTO DEVICES (id, config) VALUES (:id, :config);");
    $stmt->bindParam(':id', $i);
    $stmt->bindParam(':config', $c);
 
    foreach($devices as $di => $dc) {
      $i = $di;
      $c = $dc;
      $stmt->execute();
    }
  } catch (Exception $e) {
    die ($e);
  }
}

// if requested, begin configuring devices
if($configdevices != false && count($configdevices) > 0) {
  // update device status in DB
  try {
    $stmt = $db->prepare("UPDATE DEVICES SET ready=1 WHERE id=:id");
    $stmt->bindParam(':id', $i);
  } catch (Exception $e) {
    die ($e);
  }
  foreach($configdevices as $d => $di) {
    $i = $di;
    $stmt->execute();
  }
}

?>
<html><head></head><body>

<form action="<?php echo $_SERVER['SCRIPT_NAME']; ?>" method="GET">
  <input type="submit" id="download" name="download" value="Download Device List" />
</form>

<form action="<?php echo $_SERVER['SCRIPT_NAME']; ?>" method="GET">
  <h3>Devices</h3>
  <input type="button" id="checkall" name="checkall" value="Check All" onClick="var c=new Array();c=document.getElementsByName('configdevices[]');for(var i=0;i<c.length;i++){c[i].checked=true;}" />
  <input type="button" id="uncheckall" name="uncheckall" value="Uncheck All" onClick="var c=new Array();c=document.getElementsByName('configdevices[]');for(var i=0;i<c.length;i++){c[i].checked=false;}" />
  <input type="button" value="Refresh" onclick="window.location.reload()">
  <ul id="devicelist">
<?php while ($device = $devicelist->fetchObject()): ?>
    <li>
      <input type="checkbox" name="configdevices[]" value="<?php echo $device->id ?>" /> 
      <?php echo $device->id ?>
      <?php if($device->ready=="1") { echo " <em>ready</em>";} ?>
      <?php if($device->sent=="1") { echo " <strong>sent</strong>";} ?>
      <?php if($device->applied=="1") { echo " <strong>applied</strong>";} ?>
    </li>
<?php endwhile; ?>
  </ul>
  <input type="submit" id="start" name="start" value="Start Configuring" />
</form>

</body></html>
