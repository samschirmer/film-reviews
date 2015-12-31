<?php
$db = new PDO('sqlite:../db/movies.db') or die("fail to connect db");

$submit_type = $_POST["type"];

##########################################
#
#	HANDLING RATINGS UPDATES
#
##########################################
if ($submit_type == "edit_rating") {

$filmid = $_POST["filmid"];
$last_id = $_POST["last_id"];

$ratings = [];
# Populating $userids[]
for ($i = 1; $i < ($last_id + 1); $i++) {
        if ($_POST[$i] != '') {
               $ratings[$i] = $_POST[$i];
        }
}

for ($i = 0; $i <= $last_id; $i++) {
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

header('Location: ../films?id=' . $filmid);
}

##########################################
#
#	ADD NEW FILMS
#
##########################################
elseif ($submit_type == "add_film") {

$title = $_POST["title"];
$year = $_POST["year"];
$dupes = [];

# checking for duplicates based on title
$select_dupes_sql = 'SELECT FilmID FROM tblFilms WHERE (RecordStatusID = 1) AND (Name = :title)';
$select_dupes = $db->prepare($select_dupes_sql);
$select_dupes->execute(array(':title' => $title)) or die(print_r($db->errorInfo(), true));

while ($rows = $select_dupes->fetch(PDO::FETCH_ASSOC)) {
	array_push($dupes, $row["FilmID"]);
}

if (count($dupes) > 0) {
	# redirecting to error page
	header('Location: ../error?type=dupe_film&id=' . $dupes[0]);
} else {
	# insert query
	$insert_film_sql = 'INSERT INTO tblFilms (Name, Year) VALUES (:title, :year)';
	$insert_film = $db->prepare($insert_film_sql);
	$insert_film->execute(array(':title' => $title, ':year' => $year)) or die(print_r($db->errorInfo(), true));
	
	# pulling filmid for redirect
	$select_new_film_sql = 'SELECT FilmID FROM tblFilms WHERE (RecordStatusID = 1) ORDER BY FilmID DESC LIMIT 1';
	$select_new_film = $db->prepare($select_new_film_sql);
	$select_new_film->execute() or die(print_r($db->errorInfo(), true));
	
	while ($rows = $select_new_film->fetch(PDO::FETCH_ASSOC)) {
		$filmid = $rows["FilmID"];
	}
	# redirecting to newly-created film page
	header('Location: ../films?id=' . $filmid);
}
}
##########################################
#
#	ADD NEW TAGS
#
##########################################
elseif ($submit_type == "add_tag") {

$tag_name = $_POST["tag"];
$dupes = [];

# checking for duplicates based on title
$select_dupes_sql = 'SELECT TagID FROM tbll_Tags WHERE (RecordStatusID = 1) AND (TagName = :tag_name)';
$select_dupes = $db->prepare($select_dupes_sql);
$select_dupes->execute(array(':tag_name' => $tag_name)) or die(print_r($db->errorInfo(), true));

while ($rows = $select_dupes->fetch(PDO::FETCH_ASSOC)) {
	array_push($dupes, $row["TagID"]);
}

if (count($dupes) > 0) {
	# redirecting to error page
	header('Location: ../error?type=dupe_tag&id=' . $dupes[0]);
} else {
	# insert query
	$insert_tag_sql = 'INSERT INTO tbll_Tags (TagName) VALUES (:tag_name)';
	$insert_tag = $db->prepare($insert_tag_sql);
	$insert_tag->execute(array(':tag_name' => $tag_name)) or die(print_r($db->errorInfo(), true));
	
	# pulling tagid for redirect
	$select_new_tag_sql = 'SELECT TagID FROM tbll_Tags WHERE (RecordStatusID = 1) ORDER BY TagID DESC LIMIT 1';
	$select_new_tag = $db->prepare($select_new_tag_sql);
	$select_new_tag->execute() or die(print_r($db->errorInfo(), true));
	
	while ($rows = $select_new_tag->fetch(PDO::FETCH_ASSOC)) {
		$tagid = $rows["TagID"];
	}
	# redirecting to newly-created tag page
	header('Location: ../tags?id=' . $tagid);
}
}


##########################################
#
#	ADD NEW USER
#
##########################################
elseif ($submit_type == "add_user") {

$firstname = $_POST["firstname"];
$lastname = $_POST["lastname"];
$dupes = [];

# checking for duplicates based on title
$select_dupes_sql = 'SELECT UserID FROM tblUsers WHERE (RecordStatusID = 1) AND ((FirstName = :firstname) AND (LastName = :lastname))';
$select_dupes = $db->prepare($select_dupes_sql);
$select_dupes->execute(array(':firstname' => $firstname, ':lastname' => $lastname)) or die(print_r($db->errorInfo(), true));

while ($rows = $select_dupes->fetch(PDO::FETCH_ASSOC)) {
	array_push($dupes, $row["UserID"]);
}

if (count($dupes) > 0) {
	# redirecting to error page
	header('Location: ../error?type=dupe_user&id=' . $dupes[0]);
} else {
	# insert query
	$insert_user_sql = 'INSERT INTO tblUsers (FirstName, LastName) VALUES (:firstname, :lastname)';
	$insert_user = $db->prepare($insert_user_sql);
	$insert_user->execute(array(':firstname' => $firstname, ':lastname' => $lastname)) or die(print_r($db->errorInfo(), true));
	
	# pulling userid for redirect
	$select_new_user_sql = 'SELECT UserID FROM tblUsers WHERE (RecordStatusID = 1) ORDER BY UserID DESC LIMIT 1';
	$select_new_user = $db->prepare($select_new_user_sql);
	$select_new_user->execute() or die(print_r($db->errorInfo(), true));
	
	while ($rows = $select_new_user->fetch(PDO::FETCH_ASSOC)) {
		$userid = $rows["UserID"];
	}
	# redirecting to newly-created user page
	header('Location: ../users?id=' . $userid);
}
}

##########################################
#
#	EDIT EXISTING FILMS
#
##########################################
elseif ($submit_type == "edit_film") {

$title = $_POST["title"];
$year = $_POST["year"];
$filmid = $_POST["filmid"];
$num_tags = $_POST["num_tags"];
$dupes = [];

# checking for duplicates based on title
$select_dupes_sql = 'SELECT FilmID FROM tblFilms WHERE (RecordStatusID = 1) AND (Name = :title)';
$select_dupes = $db->prepare($select_dupes_sql);
$select_dupes->execute(array(':title' => $title)) or die(print_r($db->errorInfo(), true));

while ($rows = $select_dupes->fetch(PDO::FETCH_ASSOC)) {
	array_push($dupes, $row["FilmID"]);
}

# processing the tag changes/additions
$update_tags = [];
$total_tags = [];
$tag_change_flag = 0;
for ($i = 1; $i <= $num_tags; $i++) {
	if (($_POST["tag_" . $i] != "") and ($_POST["tag_" . $i] != "delete")) {
		array_push($update_tags, $_POST["tag_" . $i]);
		$tag_change_flag = 1;
	} elseif ($_POST["tag_" . $i] != "delete") {
		$tag_change_flag = 1;
	}
}  

if ((count($dupes) > 0) and ($tag_change_flag == 0)) {
	# redirecting to error page
	header('Location: ../error?type=dupe_film&id=' . $dupes[0]);
} else {

	# update film query
	$update_film_sql = 'UPDATE tblFilms SET Name = :title, Year = :year WHERE FilmID = :filmid';
	$update_film = $db->prepare($update_film_sql);
	$update_film->execute(array(':title' => $title, ':year' => $year, ':filmid' => $filmid)) or die(print_r($db->errorInfo(), true));

	# setting RecordStatusID = 2 for each updated/deleted tag for this FilmID
	$remove_filmtags_sql = 'UPDATE tblFilmTags SET RecordStatusID = 2 WHERE FilmID = :filmid';
	$remove_filmtags = $db->prepare($remove_filmtags_sql);
	$remove_filmtags->execute(array(':filmid' => $filmid)) or die(print_r($db->errorInfo(), true));
	
	# inserting the new tag values for this film
	foreach ($update_tags as $update) {
		echo $update;
		$insert_filmtags_sql = 'INSERT INTO tblFilmTags (FilmID, TagID) VALUES (:filmid, :tagid)';
		$insert_filmtags = $db->prepare($insert_filmtags_sql);
		$insert_filmtags->execute(array(':filmid' => $filmid, ':tagid' => $update)) or die(print_r($db->errorInfo(), true));
	}
	
	# redirecting to newly-created film page
	header('Location: ../films?id=' . $filmid);
}
}
?>
