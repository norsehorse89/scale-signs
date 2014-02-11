<?php
if (!empty($_GET["room"])) {
   $room = str_replace(' ', '', $_GET["room"]);
} else {
   $room = "";
}
 
$xml = simplexml_load_file('http://www.socallinuxexpo.org/scale12x/sign.xml');

$starttime = mktime(0, 0, 0, 2, 21, 2014) / 60;

#$rightnow = round(time() / 60);
$rightnow = mktime(11, 30, 0, 2, 21, 2014) / 60;
$minsafter = $rightnow - $starttime;

$data = array();
$order = array();
$times = array();

$shorten_topics = array(
                        "BeginnerTutorials" => "Beginner Tutorials",
                        "CloudandVirtualization" => "Cloud and Virtualization",
                        "EveningEntertainment" => "Evening Entertainment",
                        "FileSystem" => "File System",
                        "OpenSourceSoftwareInEducation" => "OSSIE",                        
                        "SysAdmin" => "Sys Admin",
                        );

foreach ($xml->node AS $node) {

  // Remove HTML tags
  $node->{'Time'} = preg_replace('/<[^>]*>/', '', $node->{'Time'});
  $node->{'Day'} = preg_replace('/<[^>]*>/', '', $node->{'Day'});
  
  // Remove Spaces so we can use it for a CSS class
  $node->{'Topic'} = preg_replace('/\s+/', '', $node->{'Topic'});
  
	$pos = strpos((string) $node->{'Time'}, ",");
	$lpos = strrpos((string) $node->{'Time'}, ",");
	if ($pos === false) {
		$thistime = (string) $node->{'Time'};
		$thisend = (string) $node->{'Time'};
	} else {
		$thistime = substr((string) $node->{'Time'}, 0, $pos);
		$thisend = substr((string) $node->{'Time'}, $lpos + 2);
	}

	if ((string) $node->Speaker == "- -") {
		$name = '';
	} else {
		$name = (string) $node->Speaker;
	}
	$data[] = array((string) $node->Day, $thistime, $name, (string) $node->Title, (string) $node->Room, (string) $node->Topic, (string) $node->Photo, (string) $node->{'Short-abstract'});
		
	$realtime = $thistime;
	$realstime = $thisend;
	$handm = explode(":", $realtime);
	$handme = explode(":", $realstime);
	$mfromm = ($handm[0] * 60) + $handm[1];
	$mfromme = ($handme[0] * 60) + 60 + $handme[1];
	
	switch ((string) $node->Day) {
		case "Friday";
			$order[] = $mfromm;
			$times[] = array($mfromm, $mfromme);
			break;
		case "Saturday";
			$order[] = $mfromm + 1440;
			$times[] = array($mfromm + 1440, $mfromme + 1440);
			break;
		case "Sunday";
			$order[] = $mfromm + 2880;
			$times[] = array($mfromm + 2880, $mfromme + 2880);
			break;
		case "";
			$order[] = 0;
			$times[] = array(0, 0);
			break;
	}
}
asort($order, SORT_NUMERIC);

?>

	<style type="text/css" media="screen">
		font { font-family: Tahoma, Geneva, sans-serif; color:black; text-align:left; font-size:14px; }
	</style>
	<div id="scheduleCarousel" class="carousel carousel-fade">
	<div class="carousel-inner"> 

    <?php 
    $topics = array();
    $items_per_page = 8;
    $odd = 0; $count = 0; $schedule_page = 1;
    foreach ($order AS $key => $value) {
        //
        // Remove spaces from room to compare with passed argument
        //
        $xml_room = str_replace(' ', '', $data[$key][4]);
        if (($times[$key][0] - 60) <= $minsafter && ($times[$key][1]) >= $minsafter && $xml_room == $room) {
            //
            // Check if the topic of the current talk is in the array
            // ..if not. add it.
            //
            if (! in_array($data[$key][5], $topics)) {
                $topics[] = $data[$key][5];
            }
            $photo = $data[$key][6];
            ?>
    	    <div class="<?php if ($count == 0) { echo "active"; } ?> item">
                <div class="media">
                    <a class="pull-left" href="#">
                        <?php
                        if (strlen($photo) > 0) {
                            echo "$photo";
                        } else {
                            echo '<img src="images/headshot.png" width="480" height="480">';
                        }
                        ?>
                    </a>
                    <div class="media-body">
                        <h2 class="media-heading"><?php echo $data[$key][2]; ?></h2>
                        <h3 class="media-heading"><?php echo $data[$key][3]; ?></h3>
                        <h4><?php echo $data[$key][7]; ?></h4>
                        <hr/>
					    <?php
                            $talk_time = $data[$key][1];
                            list($start_time, $end_time) = split(" to ", $talk_time, 2);
                            $start_time = date("h:i a", strtotime($start_time));
                            $end_time = date("h:i a", strtotime($end_time));

                            if ($times[$key][0] <= $minsafter) {
                                echo "<h2 style='text-align: center;'>In-Progress until $end_time</h2>";
                            } else {
                                echo "<h2 style='text-align: center;'>$start_time to $end_time</h2>";
                            }
                        ?>
                    </div>
                </div>
            </div>
        <?php
        $count++;
        }
    }
    ?>
    	    <div class="<?php if ($count == 0) { echo "active"; } ?> item">
                <div class="media" style=">
                    <div class="row"
                        <div class="col-md-2 col-md-offset-5"><img src="images/wifi-pass.png"></div>
                    </div>
                </div>
            </div>

	</div>
    </div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>    
<script src="bootstrap/js/bootstrap.js"></script>
<script type="text/javascript">

  $(document).ready(function() {
    $('#scheduleCarousel').carousel({
      interval: 10000,
    });
  });

</script>

