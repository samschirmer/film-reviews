<?php

function getNav($url) {
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

	if ($url == "Films") {
		echo '
	            <li><a href="dashboard">Home</a></li>
	            <li class="active"><a href="films">Films</a></li>
	            <li><a href="users">Users</a></li>
	            <li class="dropdown">
		';
	} elseif ($url == "Users") {
		echo '
	            <li><a href="dashboard">Home</a></li>
	            <li><a href="films">Films</a></li>
	            <li class="active"><a href="users">Users</a></li>
	            <li class="dropdown">
		';
	} elseif ($url == "Dashboard") {
		echo '
	            <li class="active"><a href="dashboard">Home</a></li>
	            <li><a href="films">Films</a></li>
	            <li><a href="users">Users</a></li>
	            <li class="dropdown">
		';
	} else { 
		echo '
	            <li><a href="dashboard">Home</a></li>
	            <li><a href="films">Films</a></li>
	            <li><a href="users">Users</a></li>
	            <li class="dropdown">
		';
	}

echo '              <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Dropdown <span class="caret"></span></a>
              <ul class="dropdown-menu">
                <li><a href="#">Action</a></li>
                <li><a href="#">Another action</a></li>
                <li><a href="#">Something else here</a></li>
                <li role="separator" class="divider"></li>
                <li class="dropdown-header">Nav header</li>
                <li><a href="#">Separated link</a></li>
                <li><a href="#">One more separated link</a></li>
              </ul>
            </li>
          </ul>
        </div><!--/.nav-collapse -->
      </div>
    </nav>
';
}
?>
