<?php
include 'php/config.php';

if (isset($_GET['id'])) {

$userid = $_GET['id'];

# Getting user metadata
$select_user_sql = '	SELECT 	FirstName, LastName 
			FROM 	tblUsers 
			WHERE 	(UserID = :userid)';
$select_user = $db->prepare($select_user_sql);
$select_user->execute(array(':userid' => $userid)) or die(print_r($db->errorInfo(), true));
while ($rows = $select_user->fetch(PDO::FETCH_ASSOC)) {
	$firstname = $rows["FirstName"];
	$lastname = $rows["LastName"];
}

# Getting user metadata
$select_ratings_sql = '	SELECT 	Rating 
			FROM 	tblRatings 
			WHERE 	(UserID = :userid) AND (RecordStatusID = 1)';
$select_ratings = $db->prepare($select_ratings_sql);
$select_ratings->execute(array(':userid' => $userid)) or die(print_r($db->errorInfo(), true));
$rating_counter = 0;
$rating_sum = 0;
while ($rows = $select_ratings->fetch(PDO::FETCH_ASSOC)) {
	$rating_sum += $rows["Rating"];	
	$rating_counter ++;
}

echo '<div class="row">
	<div class="col-sm-6">
		<div class="panel panel-primary">
			<div class="panel-heading">
			<h1 class="panel-title">User Stats</h1>
			</div>
		<div class="panel-body">
			<table>
				<tr><td>Name</td><td># Ratings</td><td>Avg. Rating</td></tr>
				<tr>	<td>' . $firstname . ' ' . $lastname . '</td>
					<td>' . $rating_counter . '</td>
					<td>' . round(($rating_sum / $rating_counter), 2) . '</td>
				</tr>';
echo'			</table>';
echo '		</div>
		</div>
	</div>';		

# Getting reviews
$select_reviews_sql = '	SELECT 	r.Rating, f.FilmID, f.Name, f.Year 
			FROM 	tblRatings AS r LEFT JOIN 
				tblFilms AS f ON f.FilmID = r.FilmID
			WHERE 	(r.UserID = :userid) AND ((f.RecordStatusID = 1) AND (r.RecordStatusID = 1))';
$select_reviews = $db->prepare($select_reviews_sql);
$select_reviews->execute(array(':userid' => $userid)) or die(print_r($db->errorInfo(), true));

echo'	<div class="col-sm-6">
		<div class="panel panel-primary">
			<div class="panel-heading">
			<h1 class="panel-title">Reviews</h1>
			</div>
		<div class="panel-body">
			<table class="user_leaderboard">
				<tr><td>Name</td><td># Ratings</td><td>Avg. Rating</td></tr>';
				# insert table data here

while ($rows = $select_reviews->fetch(PDO::FETCH_ASSOC)) {
	$filmid = $rows["FilmID"];
	$title = $rows["Name"];
	$filmyear = $rows["Year"];
	$rating = $rows["Rating"];
	
			echo '	<tr>
					<td><a href="films?id=' . $filmid . '">' . $title . '</a></td>
					<td>' . $filmyear . '</td>
					<td>' . $rating . '</td>
				</tr>';
}
		echo '	</table>';
	echo '	</div>
		</div>
	</div>
</div>';
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
	echo '	<tr>
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
