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

## Todo

- Yorm Objekte erstellen und dokumentieren
- flexible Synchronisation weiterer Metainfos implementieren (z.B. für besondere Hervorhebung bzw. Ausgabe auf der Startseite usw.).

## Danke

- Yakamara für REDAXO!
- Der Community für die großartige Unterstützung in allen Lebenslagen (es wären zu viele Namen um alle zu nennen)
- Christoph Böcker für den coolen Tipp https://friendsofredaxo.github.io/tricks/addons/yform/im-addon
- Kunden Felix & Oliver für die coole Idee und die kooperative Entwicklung