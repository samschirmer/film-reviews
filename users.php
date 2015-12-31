<?php
include 'php/config.php';

if (isset($_GET['id'])) {

$userid = $_GET['id'];

# Getting user metadata
$select_user_sql = '	SELECT 	u.FirstName, u.LastName, t.TagName 
			FROM 	tblUsers AS u LEFT JOIN 
				tblUserTags AS ut ON ut.UserID = u.UserID LEFT JOIN
				tbll_Tags AS t on t.TagID = ut.TagID
			WHERE 	(u.UserID = :userid)';
$select_user = $db->prepare($select_user_sql);
$select_user->execute(array(':userid' => $userid)) or die(print_r($db->errorInfo(), true));

echo '<table>';
while ($rows = $select_user->fetch(PDO::FETCH_ASSOC)) {
	$firstname = $rows["FirstName"];
	$lastname = $rows["LastName"];
	$tags = $rows["TagName"];

	echo '	<tr>
			<td>' . $firstname . '</td>
			<td>' . $lastname . '</td>
		</tr>
	';
}
echo '</table>';


# Getting reviews
$select_reviews_sql = '	SELECT 	r.Rating, f.FilmID, f.Name, f.Year 
			FROM 	tblRatings AS r LEFT JOIN 
				tblFilms AS f ON f.FilmID = r.FilmID
			WHERE 	(r.UserID = :userid) AND ((f.RecordStatusID = 1) AND (r.RecordStatusID = 1))';
$select_reviews = $db->prepare($select_reviews_sql);
$select_reviews->execute(array(':userid' => $userid)) or die(print_r($db->errorInfo(), true));

echo '<table class="user_leaderboard">';
while ($rows = $select_reviews->fetch(PDO::FETCH_ASSOC)) {
	$filmid = $rows["FilmID"];
	$title = $rows["Name"];
	$filmyear = $rows["Year"];
	$rating = $rows["Rating"];
	
	echo '	<tr>
			<td><a href="films?id=' . $filmid . '">' . $title . '</a></td>
			<td>' . $filmyear . '</td>
			<td>' . $rating . '</td>
		</tr>
	';
}
echo '</table>';
}
#####################################
#
#  Default; no userid set
#
#####################################
else {

echo '<div class="row">
	<div class="col-xl-12">
	<div class="panel panel-primary div-center">
	<div class="panel-heading">
		<h1 class="panel-title">Users List</h1>
	</div>
	<div class="panel-body">
	<table>
	<tr><td>Name</td><td># Ratings</td><td>Avg. Rating</td></tr>';
# Getting film metadata
$select_users_sql = '	SELECT DISTINCT	u.UserID, u.FirstName, u.LastName
			FROM 		tblUsers AS u LEFT JOIN
					tblRatings AS r ON r.UserID = u.UserID
			WHERE		(u.RecordStatusID = 1) AND (r.RecordStatusID = 1)
			';
$select_users = $db->prepare($select_users_sql);
$select_users->execute() or die(print_r($db->errorInfo(), true));
while ($rows = $select_users->fetch(PDO::FETCH_ASSOC)) {
	$fname = $rows["FirstName"];
	$lname = $rows["LastName"];
	$userid = $rows["UserID"];

	$select_avg_sql = '	SELECT 	Rating
				FROM 	tblRatings 
				WHERE	(UserID = :userid) AND (RecordStatusID = 1)
				';
	$select_avg = $db->prepare($select_avg_sql);
	$select_avg->execute(array(':userid' => $userid)) or die(print_r($db->errorInfo(), true));

	$u_ratings = [];
	while ($avg = $select_avg->fetch(PDO::FETCH_ASSOC)) {
	array_push($u_ratings, $avg["Rating"]);
	}

	$u_avg = array_sum($u_ratings) / count($u_ratings);
	echo '<tr>
			<td><a href="users?id=' . $userid . '">' . $fname . ' ' . $lname . '</a></td>
			<td>' . count($u_ratings) . '</td>
			<td>' . round($u_avg, 2) . '</td>
		</tr>';
}
echo '</table>
</div></div>';
}


require_once 'php/footer.php';

?>
