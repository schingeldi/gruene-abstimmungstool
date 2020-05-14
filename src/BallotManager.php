<?php



class BallotManager {

    private $db;

    private $transport;

    public function __construct( $db, $mailer ) {
        $this->db = mysqli_connect($db['HOST'], $db['USER'], $db['PASSWORD'], $db['NAME']);
        if (!$this->db) {
            echo "Error while creating connection: " . mysqli_connect_error() . PHP_EOL;
            exit;
        }


        $this->transport = (new Swift_SmtpTransport($mailer['HOST'], $mailer['PORT']))
            ->setUsername($mailer['USER'])
            ->setPassword($mailer['PASSWORD'])
            ->setAuthMode($mailer['AUTH_MODE'])
            ->setEncryption($mailer['ENCRYPTION'])

        ;



    }

    public function import( $electionId, $filePath ) {
        if( !file_exists($filePath)) {
            echo "File $filePath does not exist\n";
            exit();
        }
        $content = file_get_contents($filePath);
        $rows = explode("\n", $content);
        foreach( $rows as $row ) {
            $cols = str_getcsv($row);
            if( isset($cols[2]) && strpos($cols[2], '@') > 0 ) {
                $code = $this->insertCode($cols[1], $cols[0], $cols[2], $electionId);
                echo "$code\n";

            } else {
                echo "No import for $row\n";
            }

        }

        echo "Import successful\n";
    }


    public function insertCode( $firstname, $lastname, $email, $electionId ) {

        $code = $this->createUniqueCode($electionId);
        $sql = sprintf("INSERT INTO code (firstname, lastname, email, code, election_id) VALUES ('%s', '%s', '%s','%s', %d );",
            mysqli_real_escape_string($this->db, $firstname),
            mysqli_real_escape_string($this->db, $lastname),
            mysqli_real_escape_string($this->db,$email),
            mysqli_real_escape_string($this->db, $code),
            $electionId
            );

        $this->executeModify($sql);



        return $code;
    }

    private function createUniqueCode( $electionId ) {


        $loopCounter = 0;
        do {

            $code = $this->createCode();
            ++$loopCounter;

            if( $loopCounter > 5 ) {
                echo "Could not create unique code with 5 tries. Last code ($code). Exit \n";
                die();
            }

        } while( !$this->isCodeUnique($code, $electionId) );

        return $code;


    }

    private function createCode() {
        return substr(strtoupper( md5( rand(1,100000000).uniqid()) ), 0,20) ;
    }

    private function isCodeUnique( $code, $electionId ) {
        $sql = sprintf("SELECT id FROM code WHERE code = '%s' AND election_id = %d",
                mysqli_real_escape_string($this->db, $code),
                $electionId);

        $result = $this->executeSelect($sql);
        if( count($result) == 0 ) {
            return true;
        }
        return false;
    }

    private function executeModify( $sql ) {

        $this->executeQuery($sql);
    }

    private function executeSelect( $sql ) {

        $res = $this->executeQuery($sql);
        $return = array();
        while( $row = mysqli_fetch_object($res)) {
            $return[] = $row;
        }
        return $return;

    }

    private function executeQuery($sql) {

        $res = mysqli_query($this->db, $sql);
        if( !$res ) {
//            echo "Something went wront with query $sql \n";
            throw new \Exception(mysqli_error($this->db));
//            echo mysqli_error($this->db);
//            echo mysqli_errno($this->db);
//            echo "\n";
//            die();
        }
        return $res;
    }

    public function getActiveBallot() {

        $sql = "SELECT  b.id as ballot_id,
                        b.election_id as election_id, 
                        b.headline as headline, 
                        b.description as description, 
                        bo.option_text as option_text, 
                        bo.id as option_id
                FROM ballot b JOIN ballot_option bo ON b.id = bo.ballot_id 
                WHERE b.active = 1 AND bo.active = 1 AND ended IS NULL
                ORDER BY bo.sort ASC;";
        $result = $this->executeSelect($sql);
        if(  count($result)  == 0  ) {
            return null;
        }

        $ballot = new \StdClass();
        $ballot->ballotId = $result[0]->ballot_id;
        $ballot->electionId = $result[0]->election_id;
        $ballot->headline = $result[0]->headline;
        $ballot->description = $result[0]->description;
        $ballot->options = array();
        foreach( $result as $row ) {
            $option = new \StdClass();
            $option->id = $row->option_id;
            $option->optionText = $row->option_text;
            $ballot->options[] = $option;
        }

        return $ballot;

    }

    public function submitBallotSelection($code, $selection, $ballot) {



        if( empty($code) ) {
            $errors[] = 'Bitte gib Deinen Code ein';
        }
        if( empty($selection) ) {
            $errors[] = 'Bitte wähle genau eine Auswahl aus';
        }
        if( empty($ballot) ) {
            $errors[] = 'Es ist ein Fehler aufgetreten. Bitte melde Dich bei den Moderatoren';
        }

        if( count($errors) > 0 ) {
            return $errors;
        }

        $activeBallot = $this->getActiveBallot();

        if( $activeBallot->ballotId != $ballot ) {
            return array('Dieser Wahlgang ist nicht mehr gültig');
        }

        $selectionValid = false;
        foreach($activeBallot->options as $option ) {
            if( $option->id == $selection ) {
                $selectionValid = true;
            }
        }

        if( $selectionValid == false ) {
            return array('Die gewählte Option ist für diesen Wahlgang nicht gültig');
        }

        if( !$this->isCodeValid($code, $activeBallot->electionId) ) {
            return array('Der angegebene Code is ungültig');
        }

        $submit = $this->saveBallotSelection($activeBallot, $selection, $code );
        echo "-$submit-";
        if( !$submit ) {
            return array('Dieser Code wurde für diesen Wahlgang bereits benutzt');
        }



        return array();
    }


    private function saveBallotSelection($activeBallot, $selection, $code ) {

        $sql = sprintf("INSERT INTO ballot_selection (ballot_id, ballot_option_id, code_id, created_at) 
VALUES ( %d, %d, (SELECT id FROM code WHERE code = '%s' AND election_id = %d), NOW() );",
            $activeBallot->ballotId,
        $selection,
        mysqli_real_escape_string($this->db, trim($code)),
        $activeBallot->electionId
            );

        try {
            $this->executeModify($sql);
            return true;
        } catch(\Exception $e ) {
//            print_r($e);
            return false;
        }


    }

    private function isCodeValid($code, $electionId) {
        $sql = sprintf("SELECT id FROM code WHERE code = '%s' AND election_id = %d",
                mysqli_real_escape_string($this->db, trim($code)),
                $electionId);

        $result = $this->executeSelect($sql);
        return count($result) == 1 ? true : false;

    }

    public function getActiveBallotResult( $preview = false ) {


        $sql = sprintf("SELECT b.id as ballot_id, b.headline as headline, b.description as description, bo.option_text as option_text, bo.id as option_id, count(distinct bs.id) as votes
FROM ballot b JOIN ballot_option bo ON b.id = bo.ballot_id 
LEFT JOIN ballot_selection bs ON bs.ballot_option_id = bo.id

WHERE b.active = 1 AND bo.active = 1 %s
group by bo.id
ORDER BY bo.sort ASC;",
    $preview == true ? '' : mysqli_real_escape_string( $this->db, 'AND ended IS NOT NULL')
            );
        $result = $this->executeSelect($sql);
        if(  count($result)  == 0  ) {
            return null;
        }

        $ballot = new \StdClass();
        $ballot->totalVotes = 0;
        $ballot->ballotId = $result[0]->ballot_id;
        $ballot->headline = $result[0]->headline;
        $ballot->description = $result[0]->description;
        $ballot->options = array();
        foreach( $result as $row ) {
            $option = new \StdClass();
            $option->id = $row->option_id;
            $option->optionText = $row->option_text;
            $option->votes = $row->votes;
            $ballot->totalVotes += $option->votes;
            $ballot->options[] = $option;
        }

        return $ballot;

    }


    public function sendEmails( $electionId ) {


        $members = $this->executeSelect(sprintf("SELECT id, firstname, lastname, email, code FROM code WHERE election_id = %d AND email_sent_at IS NULL;", $electionId));

        foreach($members as $member ) {
            $result = $this->sendSingleEmail($member->email, $member->firstname, $member->code );

            $sql = sprintf("UPDATE code SET email_sent_at = NOW(), email_result = '%s' WHERE id = %d",
                mysqli_real_escape_string($this->db, $result), $member->id);

            $this->executeModify($sql);
            sleep(1);
        }
    }

    private function sendSingleEmail( $email, $firstname, $code ) {





        $mailer = new Swift_Mailer($this->transport);

        echo "Send email to $email \n";

        $message = (new Swift_Message())
            ->setSubject("Code zur Abstimmung beim Wahlprogramm-Auftakt")
            ->setFrom("wahltool@gruene-ts.de")
            ->setTo($email)
        ;



        $text = sprintf("Hallo %s,

an diesem Dienstag, den 12.Mai 2020  findet ab 19:30 Uhr unsere Bezirksgruppe zum Thema \"Wahlprogramm-Auftakt\" statt. Alle Informationen findest Du hier:

https://gruene-ts.de/ts_termin/bg-wahlprogramm-auftakt/

Dort werden wir auch einige Abstimmungen durchführen. Um an diesen Abstimmungen teilzunehmen, benötigst Du einen Browser (funktioniert auch auf dem Handy) und den folgenden Code:

Wahl-Code 12.Mai 2020: %s

Bitte halte die Information während der Bezirksgruppe bereit. Teile den Code mit keiner anderen Person. Er ist für Dich ganz persönlich.

Alle weiteren Informationen erhälst Du dann während der Veranstaltung.

Wir freuen uns Dich am Dienstag bei unserem Video-Call zum Wahlprogramm-Auftakt zu begrüßen.

Beste Grüße
Dein Kreisvorstand Bündnis90/Die Grünen - Tempelhof Schöneberg", $firstname, $code );

        $message->setBody( nl2br($text), 'text/html');
        $message->addPart($text,  'text/plain');


        $result = $mailer->send($message);

        echo "mail sent \n";

        return $result;

    }

}