# Altpapier-Web

## Live-Demos
http://altpapier-app.de/viewer  
http://altpapier-app.de/api/test/  

### Creator

Der Gast kann neue Artikel anlegen und angelegte Artikel editieren. Er kann keine Artikel bearbeiten oder löschen. Außerdem kann der Gast das CMS ansehen.

http://altpapier-app.de/creator
http://altpapier-app.de/creator/cms.php



## Api-Aufruf 
http://altpapier-app.de/api/v1/content  

### Parameter  
page - Seitenzahl der Ergebnisse  
pagesize - Anzahl der Ergebnisse pro Seite  

### Beispiel  
http://altpapier-app.de/api/v1/content?page=1&pagesize=10  
Der Aufruf liefert ein JSON mit 10 Artikeln zurück. Das sind die Artikel, die im CMS für die vergangegen Tage zur Veröffentlichung festgelegt wurden, sortiert nach absteigendem Datum. Der Aufruf mit page=2 liefert die 10 älteren Artikel zurück.  
### Felder  
id - unique ID des Inhalts in der Datenbank  
dateIssued - Veröffentlichungsdatum der Zeitungsausgabe, aus der der Artikel stammt  
newspaperTitle - Name der Zeitung  
volume - Jahrgangsnummer der Zeitung  
issue - Ausgabennummer der Zeitung  
headline - Artikelüberschrift  
text - Artikeltext  
imageUrl - Url zur Datei, die den Bildausschnitt der Zeitung enthält  
imageWidth - Bildbreite in px  
imageHeight - Bildhöhe in px  
imageSize - Bildgröße in Byte  
isDeleted - sagt ob der Artikel in der Datenbank gelöscht wurde und daher auch in der App gelöscht werden soll  
lastModified - letzter Bearbeitungszeitpunkt in der Datenbank  
pageNr - Seitenzahl des Artikels in der Zeitung  
date - Veröffentlichungsdatum für die App  
position - Reihenfolge der Artikel innerhalb eines Tages  

