REDAXO CMS
==========

*Einfach, flexibel, sinnvoll!*

![Screenshot](https://raw.githubusercontent.com/redaxo/redaxo/assets/redaxo_01.png)

REDAXO ist ein freies Content-Management-System (CMS) für Websites. Es basiert auf der Skriptsprache PHP und verwendet als Datenbank MySQL oder MariaDB. REDAXO wurde ab 1999 von der Agentur Pergopa (später Yakamara) mit dem Ziel als einfaches, schnell zu erlernendes Redaktionssystem entwickelt, steht seit Anfang 2004 unter der GNU General Public License und mit Erscheinen der Version 5 Anfang 2016 unter der MIT-Lizenz.

[www.redaxo.org](https://www.redaxo.org) | [Dokumentation](https://redaxo.org/doku/master) | [Api Doc](https://redaxo.org/api/master/)

[![Build Status](https://secure.travis-ci.org/redaxo/redaxo.svg?branch=master)](https://travis-ci.org/redaxo/redaxo)

Installation
------------

*Entwicklungsstand* installieren:

```sh
git clone https://github.com/redaxo/redaxo.git
cd redaxo
git submodule init
git submodule update
```

*Beachte:*
- Die von Github bereit gestellten Downloads ("Download ZIP" Button) enthalten *nicht* die nötigen GIT Submodule, funktionieren daher nicht.
- Die auf Github angebotenen REDAXO versionen sollten *nicht* im Produkiveinsatz verwendet werden!

[Download der offiziellen REDAXO Releases](https://github.com/redaxo/redaxo/releases)
