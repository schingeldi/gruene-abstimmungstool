<?php

include_once 'bootstrap.php';


$preview = isset($_GET['myaktion']) && $_GET['myaktion'] == 'preview' ? true : false;

$activeBallot = $manager->getActiveBallotResult($preview);
if( $activeBallot !== null  ) {
    include 'activeResult.php';
} else {
    include 'noActiveResult.php';
}


