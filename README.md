# Redaktionsassistent für REDAXO 5

## Einleitung

Der Redaktionsassistent dient zur Verwaltung von Artikeln in einer separaten yform Tabelle. Dabei bleiben die Inhalte wie gehabt in REDAXO Artikeln. Die Tabelle im Redaktionsassistenten kann zum schnellen Zugriff auf Artikel genutzt werden. Sie lässt sich für Notizen zum Bearbeitungsstand verwenden und man kann schnell den aktuellen Stand aller Artikel einsehen und filtern. Insbesondere für Seiten, die Inhalte wie Blogartikel darstellen, kann der Redaktionsassistent wertvolle Dienste leisten. Die Tabelle kann um eigene Felder erweitert werden.

Voraussetzung für die Verwendung des Redaktionsassistenten sind die Standard Metainfo Felder für den Artikel `art_online_from`, `art_online_to` und `art_description`. Wenn diese noch nicht vorhanden sind, werden sie bei der Installation des Redaktionsassistenten angelegt. Weiterhin wird das AddOn structure_plus vorausgesetzt. In diesem AddOn wird die Farbeinstellung für die Darstellung (geplant, Veröffentlichung in der Zukunft, online, offline, abgelaufen) konfiguriert. Der Status gesperrt wird berücksichtigt, wenn das AddOn accessdenied eingesetzt wird.

Der Redaktionsassistent vergibt für jeden Artikel eine eigene Id. Dies ist nicht die Artikel-Id des REDAXO Artikels sondern die Id des Datensatzes der yform Tabelle vom Redaktionsassistent. Durch die eigene Rewriter Klasse wird diese Id an die Url angehängt. Damit können auch Artikel mit gleichem Namen verwendet werden. Es entsteht kein Konflikt bei Artikeln mit gleichem Namen.

Damit REDAXO Artikel für die Verwaltung im Redaktionsassistenten verfügbar sind, müssen sie über die Funktion "Artikel anlegen" im jeweiligen Datensatz vom Redaktionsassistenten angelegt werden. Wenn ein Artikel über den Redaktionsassistent angelegt ist, kann er direkt aus der Liste zur Bearbeitung geöffnet werden.

Ein Artikel, der über den Redaktionsassistenten angelegt wurde, kann wie ein normaler REDAXO Artikel bearbeitet werden. Er kann auch verschoben oder umbenannt werden. Auch der Wert im art_online_from Feld kann direkt im Artikel geändert werden. Die Änderungen werden in den Datensatz des Redaktionsassistenten synchronisiert.

Für die Ausgabe gibt es Helper Funktionen. Diese sind als Vorlage gedacht und können den eigenen Projektanforderungen angepasst werden.

## Installation

Zunächst dass structure_plus AddOn installieren, anschließend den Redaktionsassistenten installieren. Jeweils hierfür das Repository herunterladen und in das AddOn Verzeichnis kopieren. Sobald das AddOn ausreichend getestet und entwickelt ist, kommt es auch in den Installer.
Im structure_plus AddOn die gewünschten Farben einstellen.

## Artikelarten und Templates

Mit dem Redaktionsassistenten wird eine weitere yform Tabelle installiert. Diese Tabelle kann verwendet werden, um unterschiedliche Artikelarten zu definieren. Zu jeder Artikelart kann zusätzlich entweder ein REDAXO Template oder ein REDAXO Artikel zugeordnet werden. Wenn ein Artikel zugeordnet wird, so wird dieser als Vorlage bei der Erstellung eines neuen Artikels verwendet. Dabei werden alle Inhalte des Vorlagenartikels in den neuen Artikel übernommen.

## Mehrsprachigkeit

Ist nicht getestet.

## Nutzung der Helper Funktionen

Mit diesem Code im Modul des Startartikels einer Kategorie bekommt man eine Liste mit Artikelobjekten. In dieser Liste ist bereits berücksichtigt: art_online_from, art_online_to und status. Der Startartikel der Kategorie ist in der Liste nicht enthalten.

```
$articles = ra_helper::find_newest_articles(0, rex_category::getCurrent()->getId());
dump($articles);
```

## Arbeiten mit dem Redaktionsassistent

- Menüpunkt "Redaktionsassistent" aufrufen
- neuen Datensatz anlegen
   - Zielkategorie auswählen
   - Name eintragen
   - Datensatz übernehmen (Verwaltungs Id wird angelegt)
   - Artikel anlegen

![Screenshot](https://github.com/dtpop/redaktionsassistent/blob/master/assets/bildschirm_redaktionsassistent_fuer_redaxo_bearb.jpg)


## Vorhandene REDAXO Artikel dem Redaktionsassistenten hinzufügen

Der Redaktionsassistent ist dafür gedacht, Arbeitsdatensätze zunächst im Redaktionsassistenten anzulegen und anschließend über den Redaktionsassistenten den REDAXO Artikel anzulegen.

Es kann aber Gründe geben, dass vorhandene REDAXO Artikel dem Redaktionsassistenten hinzugefügt werden sollen. Das kann der Fall sein, wenn der Redaktionsassistent in ein bereits existierende Seite installiert wird. Möglicherweise hat auch jemand versehentlich einen REDAXO Artikel ohne Redaktionsassistent angelegt und dieser soll trotzdem über den Redaktionsassistent verwaltet werden.

Hierfür verfügt der Redaktionsassistent über eine eigene Seite "Artikel zuordnen". Es werden dort nur Artikel aufgelistet, die auch über den Redaktionsassistent verwaltet werden können. Es werden keine Startartikel aufgeführt und es werden auch keine Artikel aufgeführt, die bereits über den Redaktionsassistent verwaltet werden.

Mit den Checkboxen bzw. mit dem Button "Alle markieren" können jene Artikel markiert werden, die in den Redaktionsassistenten aufgenommen werden sollen. Mit einem Klick auf den Button "Markierte Artikel zuordnen" geht es los.

In den Datensatz des Redaktionsassistenten werden übernommen: Name des Artikels, Kategorie, art_online_from und Status.

Anschließend kann der Artikel auch über den Redaktionsassistenten aufgerufen werden.


## Ausgabe

Die Ausgabe der Artikel erfolgt ganz normal über Module. In den Übersichten können Funktionen aus dem Redaktionsassistenten verwendet werden. Somit kann auch bei der Ausgabe eine automatische Sortierung nach Online-Datum (oder einem anderen Feld) umgesetzt werden.

Folgender vereinfachter Modulcode kann als Blaupause verwendet werden:

```php
<?php
$articles = ra_helper::find_newest_articles(0, rex_category::getCurrent()->getId());
?>
<ul>
<?php foreach ($articles as $art) : ?>
    <li>
        <p><?= date('d.m.Y', $art->getValue('art_online_from'))) ?></p>
        <h3><?= $art->getName() ?></h3>
        <p><?= $art->getValue('art_description') ?></p>
        <p><a href="<?= $art->getUrl() ?>">Mehr erfahren ...</a></p>
    </li>
<?php endforeach ?>
</ul>
```

## Yorm Ausgabe

Für die Ausgabe steht ein einfaches Yorm Objekt zur Verfügung, welches in eigenen Funktionen und Erweiterungen genutzt werden kann.

Angenommen, es wird in der Verwaltungstabelle des Redaktionsassistenten ein zusätzliches Feld `important` als Checkbox hinzugefügt, kann folgender Modulcode auf der Startseite die wichtigsten Teaser anzeigen:

```php
<?php
// Yorm Query holen
$query = ra_helper::get_ra_query();
// Query erweitern
$query
    ->orderBy('important', 'desc')
    ->orderBy('art_online_from', 'desc')
;
$query->limit(0,3);
// Daten holen
$items = $query->find();
?>

<ul>
<?php foreach ($items as $item) : ?>
    <li>
        <p><?= date('d.m.Y', $item->rex_art_online_from) ?></p>
        <h3><?= $item->name ?></h3>
        <p><?= $item->rex_art_description ?></p>
        <p><a href="<?= rex_getUrl($item->rex_article) ?>">Mehr erfahren ...</a></p>
    </li>
<?php endforeach; ?>
</ul>

```

Bei letzterem Modulcode werden die wichtigsten Artikel bevorzugt (durch die Sortierung important = desc). Anschließend wird mit den neuesten Artikel auf die gewünschte Anzahl von 3 Einträgen (limit(0,3)) aufgefüllt.
Das Yorm Objekt berücksichtigt bereits art_online_from, art_online_to und status. Da es als Query zur Verfügung steht, kann es über beliebige Yorm Funktionen erweitert werden.


## Todo

- flexible Synchronisation weiterer Metainfos implementieren (z.B. für besondere Hervorhebung bzw. Ausgabe auf der Startseite usw.).

## Danke

- Yakamara für REDAXO!
- Polarpixel für das gemeinsame Projekt, das dieses AddOn initiiert und zum Teil finanziert hat
- Der Community für die großartige Unterstützung in allen Lebenslagen (es wären zu viele Namen um alle zu nennen)
- Christoph Böcker für den coolen Tipp https://friendsofredaxo.github.io/tricks/addons/yform/im-addon
- Kunden Felix & Oliver für die coole Idee und die kooperative Entwicklung