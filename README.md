# contao-qgis-bundle
Dieses Bundle ermöglicht die einfache Darstellung von Karten und GIS-Vektor-Objekten im CMS Contao. Es ist von seinem Ansatz her auf die Vorbereitung der Karten mit dem Programm QGIS ausgerichtet. 

Zurzeit befindet sich das Bundle im experimentellen Stadium. Das Bundle stützt sich auf die Bibliotheken OpenLayers, olExt und [jsts](https://github.com/bjornharrtell/jsts).  

## Folgende Funktionen sind implementiert  

1. Karten (BaseMap aus OpenStreetMap)
2. Ebenen (implementiert ol.layers)
3. Eigenschaften (implementiert ol.features) 
4. Stile (implementiert ol.style)
5. augewählte Komponenten aus ol-ext
6. ausgewählte ol.controls
7. ansatzweise Implementierung von georeferenzierten ImageOverlays

> [!CAUTION]
> Das bundle ist experimentell und noch nicht für den produktiven Betrieb geeignet.

Beispieldaten folgen noch ...

### 0. Konzept (Schnelleinstieg - leider alles noch experimentell)
Das grundlegende Ziel des Bundles besteht darin, dem Redakteur / der Redakteurin ein Werkzeug an die Hand zu geben, mit dessen Hilfe sie eigene thematische Karten möglichst einfach und schnell erstellen und erweitern können. Dazu können vor allem auch Daten im Format GeoJSON verwendet werden. Idealerweise verfügen die Ersteller über Erfahrungen mit dem umfangreichen Open-Source-Tool QGIS. Mit diesem mächtigen Werkzeug kann man in wenigen Schritten umfangreiche und ansehnliche Karten erstellen und georeferenzierte Overlays erzeugen.

Ein weiteres Ziel ist es, Geo-Informationsdaten wiederverwendbar zu erfassen. Es ist also möglich, ein bestimmtes Objekt, beispielsweise ein Feld oder eine 


Eine typische Vorgehensweise könnte wie folgt aussehen:
1. Sie legen zuerst eine **&raquo;Konfiguration&laquo;** an.
2. Sie erfassen eine **Quelle**. Dabei bemerken Sie, dass der zugehörige **Autor** noch nicht erfasst wurde.
3. Sie erfassen also als Nächstes die zur Quelle gehörigen **Autoren** und **Autorinnen** und tragen diese in der Quelle nach.
4. Sie bemerken, dass es sich um einen **Reihentitel** handelt. Sie erfassen nun die zugehörige **Reihe oder Zeitschrift** und tragen diese in der Quelle nach.
5. Sie bemerken, dass der **Verlag** noch nicht erfasst wurde. Sie erfassen den **Verlag** und tragen ihn in der Quelle nach.
6. Dasselbe Konzept verfolgen Sie bei den **Bibliotheken (Archiven und Datengebern)**.
