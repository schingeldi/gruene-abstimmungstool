<?php

include_once 'bootstrap.php';


include_once 'header.php';



?>

<h1>Ergebnis:</h1>

<h2><?php echo $activeBallot->headline; ?></h2>

<p><?php echo $activeBallot->description; ?></p>



    <table style="border-spacing: 10px;">
<?php foreach( $activeBallot->options as $option ) { ?>
<tr>


    <td style="padding:10px 20px;"><?php echo $option->optionText ?></td>
    <td style="padding:10px 20px; text-align:right;"><?php echo $option->votes?> Stimmen</td>
    <td style="padding:10px 20px; text-align:right;"><?php echo $activeBallot->totalVotes == 0 ? 0:  round(($option->votes/$activeBallot->totalVotes * 100), 2)?>%</td>


</tr>
    <?php } ?>





    </table>


    <p>
        <a href="/index.php" >Zur Startseite</a><br/><br/>
    </p>


<?php

include_once 'footer.php';

