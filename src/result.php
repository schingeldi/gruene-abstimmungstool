<?php

include_once 'bootstrap.php';


$preview = false;

$activeBallot = $manager->getActiveBallotResult($preview);
if( $activeBallot !== null  ) {
    include 'activeResult.php';
} else {
    include 'noActiveResult.php';
}


