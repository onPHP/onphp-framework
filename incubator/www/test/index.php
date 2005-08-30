<?php
	/*		Copyright 2005 Sveta Smirnova
		$Id$
	*/
    foreach ($_POST as $key => $value) {
        echo "\$POST[$key] = $value<br>";
    }
    foreach ($_GET as $key => $value) {
        echo "\$GET[$key] = $value<br>";
    }
?>
