# JS Templates / Widget Specifications

## Hinzufügen

* Eine Datei mit dem Namen <KlassenName>.json <KlassenName>.mustache erstellen.
  - `cli cms:create-cs-widget MyWidget` --label="Frontend Label My Widget"
* Dann ``grunt hogan`` ausführen.
* Entity Erstellen ``cli cms:create-entity ContentStream\<KlassenName>`` (repository verschieben)
* ggf table name mit cs_ prefixen
* in Entities\ContentStream\Entry in die discriminationMap eintragen
* ``cli project:compile --con="tests"`` ``cli project:compile --con="default"`` ausführen

### JSON
name: <KlassenName>
fields: array von fields. type kann sein: image, link, text, string, file(todo)

### Mustache
div.row-fluid nehmen dann 12 spans vergeben div.spanx für jedes field gibt es fieldName.label und fieldName.input. Input ist hierbei das verschachtelte Widget erzeugt vom LayoutManager. Da dies HTML ist muss man {{&text.input}} z.b. benutzen. Für die Label-Spalte ist div.span2 ausreichend.