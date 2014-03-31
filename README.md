Info
====

Das Mitgliederstatistiktool ist eine PHP-Anwendung, die ich mal zur Erstellung der
[Mitgliederheatmap](http://wiki.piratenpartei.de/Datei:LSA_Mitgliederheatmap.png)
f�r den LV LSA gebaut hab. Mittlerweile kann das Tool etwas mehr, ist aber nicht
viel bedienbarer geworden...

Zugang
------

http://projects.martoks-place.de/piraten/map/

Code: https://github.com/PiratenLSA/piratenmap

Datenbasis
----------

Die Datenbasis f�r die Grafiken stellen CSV-Dateien auf dem Server dar, die mit
einer relativ brauchbaren relationalen Algebra verkocht werden, um die Karten zu
erstellen.

Die Karten werden aus SVG-Basiskarten erstellt, welche einem bestimmten Format
folgen und durch eine spezielle Klasse manipuliert werden.

Benutzung
---------

Oben auf der Seite wird die Art des Reports ausgew�hlt, meistens legt das auch
die Art der Basiskarte fest. Entweder automatisch oder nach klick auf "Los->"
�ffnet sich links dann die Reportabh�ngige Konfiguration. Ein Klick auf "Rendern"
erstellt ein Vorschaubild rechts, "Runterladen" bietet dieses zum Download an.


