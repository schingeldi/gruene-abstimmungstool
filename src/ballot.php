<?php

include_once 'bootstrap.php';


if ($_SERVER['REQUEST_METHOD'] === 'POST') {




    $code = isset($_POST['code']) ? $_POST['code'] : null;
    $selection = isset($_POST['selection']) ? $_POST['selection'] : null;
    $ballot = isset($_POST['ballot']) ? $_POST['ballot'] : null;


    $errors = $manager->submitBallotSelection($code, $selection, $ballot);
    if( count($errors) == 0 ) {
        header("Location: /selectionSubmitted.php");
        exit();
    }

}





$activeBallot = $manager->getActiveBallot();
if( $activeBallot !== null  ) {
    include 'activeBallot.php';
} else {
    include 'noActiveBallot.php';
}


