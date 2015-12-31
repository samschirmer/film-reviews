<?php

function getNav($url) {
$db = new PDO('sqlite:db/movies.db') or die("fail to connect db");

# queries

# Getting tag names and ids
$select_tagname_sql = '	SELECT 	TagName, TagID
			FROM 	tbll_Tags 
			WHERE 	(RecordStatusID = 1)';
$select_tagname = $db->prepare($select_tagname_sql);
$select_tagname->execute() or die(print_r($db->errorInfo(), true));
$num_tags = 0;
$tagname = [];
$tagid = [];
while ($rows = $select_tagname->fetch(PDO::FETCH_ASSOC)) {
	array_push($tagname, $rows["TagName"]);
	array_push($tagid, $rows["TagID"]);
	$tag_counter ++;
}

# Getting films and ids
$select_films_sql = '	SELECT 	Name, FilmID
			FROM 	tblFilms
			WHERE 	(RecordStatusID = 1)
			ORDER BY CreatedDT DESC
			LIMIT 15';
$select_films = $db->prepare($select_films_sql);
$select_films->execute() or die(print_r($db->errorInfo(), true));
$num_films = 0;
$filmname = [];
$filmid = [];
while ($rows = $select_films->fetch(PDO::FETCH_ASSOC)) {
	array_push($filmname, $rows["Name"]);
	array_push($filmid, $rows["FilmID"]);
	$film_counter ++;
}

# Getting users and ids
$select_users_sql = '	SELECT 	FirstName, LastName, UserID
			FROM 	tblUsers
			WHERE 	(RecordStatusID = 1)';
$select_users = $db->prepare($select_users_sql);
$select_users->execute() or die(print_r($db->errorInfo(), true));
$num_users = 0;
$firstname = [];
$lastname = [];
$userid = [];
while ($rows = $select_users->fetch(PDO::FETCH_ASSOC)) {
	array_push($firstname, $rows["FirstName"]);
	array_push($lastname, $rows["LastName"]);
	array_push($userid, $rows["UserID"]);
	$user_counter ++;
}

echo '
<body role="document">
    <!-- Fixed navbar -->
    <nav class="navbar navbar-inverse navbar-fixed-top">
      <div class="container">
        <div class="navbar-header">
          <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <a class="navbar-brand" href="http://cantedreviews.com/dashboard">Canted Reviews</a>
        </div>
        <div id="navbar" class="navbar-collapse collapse">
          <ul class="nav navbar-nav">';

# checking to see if dashboard is active
if ($url == "Dashboard") {echo '<li class="active"><a href="dashboard">Home</a></li>';} 
else {echo '<li><a href="dashboard">Home</a></li>';}

# checking to see if films is active
if (strpos($url, "Films") !== false) {echo '<li class="dropdown active">';} 
else {echo '<li class="dropdown">';}
# building nav menu item for films
echo '<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Films<span class="caret"></span></a>
	<ul class="dropdown-menu">';
# populating options under Films dropdown
for ($i = 0; $i < $film_counter; $i++) {
	echo '<li><a href="films?id=' . $filmid[$i] . '">' . $filmname[$i] . '</a></li>';
}
# end of menu; add film
echo '	<li role="separator" class="divider"></li>
	<li><a href="add?type=film">Add a film</a></li>
              </ul>
        </li>';

# checking to see if users is active
if (strpos($url, "Users") !== false) {echo '<li class="dropdown active">';} 
else {echo '<li class="dropdown">';}
# building nav menu item for users
echo '<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Users<span class="caret"></span></a>
	<ul class="dropdown-menu">';
# populating options under Users dropdown
for ($i = 0; $i < $user_counter; $i++) {
	echo '<li><a href="users?id=' . $userid[$i] . '">' . $firstname[$i] . ' ' . $lastname[$i] . '</a></li>';
}
# end of menu; add user
echo '	<li role="separator" class="divider"></li>
	<li><a href="add?type=user">Add a user</a></li>
              </ul>
        </li>';

# checking to see if tags is active
if (strpos($url, "Tags") !== false) {echo '<li class="dropdown active">';} 
else {echo '<li class="dropdown">';}
# building nav menu item for tags
echo '	<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Tags <span class="caret"></span></a>
              <ul class="dropdown-menu">';
# populating options under Tags dropdown
for ($i = 0; $i < $tag_counter; $i++) {
	echo '<li><a href="tags?id=' . $tagid[$i] . '">' . $tagname[$i] . '</a></li>';
}
# end of menu; add user
echo '	<li role="separator" class="divider"></li>
	<li><a href="add?type=tag">Add a tag</a></li>
              </ul>
        </li>';

echo '
          </ul>
        </div><!--/.nav-collapse -->
      </div>
    </nav>
';
}
?>
