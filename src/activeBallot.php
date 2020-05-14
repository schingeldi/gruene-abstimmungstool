<?php

include_once 'bootstrap.php';


include_once 'header.php';



?>

<h1>Wahlgang eröffnet</h1>

<h2><?php echo $activeBallot->headline; ?></h2>

<p><?php echo $activeBallot->description; ?></p>

<form method="POST" >

<?php foreach( $activeBallot->options as $option ) { ?>


    <p><input type="radio" name="selection" value="<?php echo $option->id ?>" <?php echo isset($selection) && $selection == $option->id ? "checked='checked'" : "" ?> />    <?php echo $option->optionText ?></p>


    <?php } ?>


    <b>Code eingeben:</b> <br/>
    <input type="text" name="code" value="<?php echo isset($code) ? $code : ""?>"/>

    <input type="submit" value="Stimme abgeben" />
    <input type="hidden" name="ballot" value="<?php echo $activeBallot->ballotId ?>" />

</form>

<?php if( isset($errors)  ){ ?>

    <p style="color:#AA0000;">
    <?php foreach($errors as $error ) { ?>
        <?php echo $error ?><br/>


    <?php } ?>
    </p>



<?php } ?>



    <p>
        <a href="/result.php" >Zur Ergebnisseite</a><br/><br/>
    </p>

    <p>
        <a href="/index.php" >Zur Startseite</a><br/><br/>
    </p>


<?php

include_once 'footer.php';

