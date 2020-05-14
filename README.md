# Grüne Abstimmungstool

Kleines Tool um online geheime Wahlen durchzuführen

## Hintergrund

Um auch während der Corona-Zeit - in der Parteiveranstaltungen nur Online stattfinden können - auch die Möglichkeit zu haben, Posten mittels einer Wahl zu besetzen, haben wir im Kreisverband Tempelhof-Schöneberg (Berlin) innerhalb weniger Tage ein kleines Tool entwickelt, dass möglichst viele Anforderungen an eine geheime Wahl erfüllt.

## Konzept  / Ablauf

### Vor dem Wahltag

Alle Mitglieder des Kreisverbandes wurden mit Vornamen, Nachnamen und Email Adresse in die Datenbank importiert. Während des Imports wurde ein eindeutiger Code für jedes Mitglied erstellt und für dieses Mitglied gespeichert (mehr zur Geheimhaltung weiter unten). Dieser Code ist für eine bestimmte Wahl gültig. Eine Wahl kann beliebig viele Wahlgänge haben.

Anschließend wurde allen Mitgliedern dieser Code per Email zugeschickt. Über den gängigen Weg von Newsletter und Aktivenverteiler wurden alle Mitglieder informiert, dass eine entsprechende Email gesendet wurde und man ggf. seinen Spam-Ordner prüfen soll. Es wurde auch eine Kontaktadresse mitgeschickt, um ggf. zu helfen, weil der Code doch nicht angekommen ist.

Dadurch, dass der Code zusammen mit Vorname, Nachname und Email Adresse gespeichert wurde, kann man den gleichen Code der Person noch mal zuschicken, ohne doppelte Codes für Personen zu riskieren.

### Am Wahltag während der Online-Veranstaltung

Kurze Vorstellung des Tools und des Prinzips. Es wird nochmals auf den Code hingewiesen, den jeder bitte bereit halten soll. Wer keinen Code bekommen hat, kann sich per privater Chat Funktion beim Admin melden und ggf. seinen Code erneut erhalten.

### Vor dem ersten Wahlgang

Der Vorname, der Nachname und die Email Adresse werden zu jedem Code unwiderruflich gelöscht. Damit ist es auch für den Systemadmin nicht mehr möglich hinterher nachzuvollziehen, wer wie abgestimmt hat. 

Direkt in der Datenbank müssen die Wahlgänge und die Wahloptionen angelegt werden. Hierfür bietet das Tool noch keine Bedienoberfläche.

### Wahlgang

Über die Datenbank muss der jeweilige Wahlgang auf _active = 1_ gesetzt werden. Damit ist der Wahlgang für alle Benutzer sichtbar. 

Benutzer müssen jetzt eine Wahloption wählen und Ihren Code eingeben. Pro Wahlgang kann jeder Code nur einmal abstimmen. Eine Korrektur der Wahl ist nicht möglich.

Nach beendigung der Wahlgang setzt man über die Datanbank _ended_at = NOW()_ . Nund kann nicht mehr abgestimmt werden und das Ergebnis ist über die Ergebnisseite sichtbar für alle Benutzer.

Über die Datenbank kann das Ergebnis über das Setzen von _active = 0_ für den entsprechenden Wahlgang wieder deaktiviert werden. Dann kann der nächste Wahlgang beginnen. 









## Technische Informationen

### Voraussetzungen

Es wird ein Webserver mit PHP und MySQL benötigt.


### Installing

Das SQL Skript _database.sql_ muss in die Datenbank importiert werden. Damit wird die Tabellenstruktur angelegt. 

Der Quellcode muss ins Webverzeichnis des Webservers kopiert werden. 

PHP Abhängigkeiten müssen installiert werden:

```
composer install
```

## Authors

* **Roman Brunnemann** 

See also the list of [contributors](https://github.com/schingeldi/gruene-abstimmungstool/contributors) who participated in this project.

## License

This project is licensed under the GNU GPLv3 License

## Acknowledgments


