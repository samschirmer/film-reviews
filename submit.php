<?php
include 'php/config.php';

$filmid = $_POST["filmid"];
$submit_type = $_POST["submit_type"];
$last_id = $_POST["last_id"];

$ratings = [];
# Populating $userids[]
for ($i = 1; $i < ($last_id + 1); $i++) {
        if ($_POST[$i] != '') {
               $ratings[$i] = $_POST[$i];
        }
}

##########################################
#
#	HANDLING RATINGS UPDATES
#
##########################################

for ($i = 0; $i < $last_id; $i++) {
	if (isset($ratings[$i])) {
		# building array of userids that require INSERT
		$select_i_ratings_sql = 'SELECT u.UserID FROM tblUsers AS u WHERE (u.UserID = :userid) AND (u.UserID NOT IN (SELECT UserID FROM tblRatings WHERE (FilmID = :filmid) AND (RecordStatusID = 1)))';
		$select_i_ratings = $db->prepare($select_i_ratings_sql);
		$select_i_ratings->execute(array(':userid' => $i, ':filmid' => $filmid)) or die(print_r($db->errorInfo(), true));

		$insert_userids = [];
		while ($rows = $select_i_ratings->fetch(PDO::FETCH_ASSOC)) {
			array_push($insert_userids, $rows["UserID"]);
		}

		# building array of userids that require UPDATE
		$select_u_ratings_sql = 'SELECT u.UserID FROM tblUsers AS u WHERE (u.UserID = :userid) AND (u.UserID IN (SELECT UserID FROM tblRatings WHERE (FilmID = :filmid) AND (RecordStatusID = 1)))';
		$select_u_ratings = $db->prepare($select_u_ratings_sql);
		$select_u_ratings->execute(array(':userid' => $i, ':filmid' => $filmid)) or die(print_r($db->errorInfo(), true));
		
		$update_userids = [];
		while ($rows = $select_u_ratings->fetch(PDO::FETCH_ASSOC)) {
			array_push($update_userids, $rows["UserID"]);
		}
		
		if (in_array($i, $insert_userids)) {
			# insert query
			$insert_ratings_sql = 'INSERT INTO tblRatings (Rating, FilmID, UserID) VALUES (:rating, :filmid, :userid)';
			$insert_ratings = $db->prepare($insert_ratings_sql);
			$insert_ratings->execute(array(':rating' => $ratings[$i], ':userid' => $i, ':filmid' => $filmid)) or die(print_r($db->errorInfo(), true));
		} else {
			# update query
			$update_ratings_sql = 'UPDATE tblRatings SET Rating = :rating WHERE (UserID = :userid) AND (FilmID = :filmid)';
			$update_ratings = $db->prepare($update_ratings_sql);
			$update_ratings->execute(array(':rating' => $ratings[$i], ':userid' => $i, ':filmid' => $filmid)) or die(print_r($db->errorInfo(), true));
		}

	} elseif ( ! isset($ratings[$i])) {
		# building array of userids that require UPDATE
		$select_u_ratings_sql = 'SELECT u.UserID FROM tblUsers AS u WHERE (u.UserID = :userid) AND (u.UserID IN (SELECT UserID FROM tblRatings WHERE (FilmID = :filmid)))';
		$select_u_ratings = $db->prepare($select_u_ratings_sql);
		$select_u_ratings->execute(array(':userid' => $i, ':filmid' => $filmid)) or die(print_r($db->errorInfo(), true));
		
		$new_blank_userids = [];
		while ($rows = $select_u_ratings->fetch(PDO::FETCH_ASSOC)) {
			array_push($new_blank_userids, $rows["UserID"]);
		}

		if (in_array($i, $new_blank_userids)) {
			# setting recordstatusid = 2
			$update_ratings_sql = 'UPDATE tblRatings SET RecordStatusID = 2 WHERE (UserID = :userid) AND (FilmID = :filmid)';
			$update_ratings = $db->prepare($update_ratings_sql);
			$update_ratings->execute(array(':userid' => $i, ':filmid' => $filmid)) or die(print_r($db->errorInfo(), true));
		}
	}
}

header('Location: films?id=' . $filmid);



?>
