<p align="right">🌎 <a href="https://github.com/redaxo/redaxo/blob/master/README.md">English</a></p>

<p align="center"><a href="https://redaxo.org"><img src="https://raw.githubusercontent.com/redaxo/redaxo/assets/redaxo-logo.png" alt="REDAXO" width="280px" height="43px"></a></p>

<h3 align="center">Content-Management seit 2004. Einfach, flexibel, sinnvoll.</h3>

<p align="center"><a href="https://redaxo.org">REDAXO</a> bietet dir ein einfach zu erlernendes Website-Framework. Es ermöglicht, Inhalte über beliebige Module aufzubauen, deren Ein- und Ausgabe du vollständig kontrollieren kannst. Es ist mehrsprachig, enorm erweiterbar und passt sich deinem Arbeitsablauf an.</p>

<p align="center"><img src="https://raw.githubusercontent.com/redaxo/redaxo/assets/redaxo_02.png" width="100%" alt="Screenshots"></p>

&nbsp;

<p align="center">
	<a href="https://github.com/redaxo/redaxo/blob/master/redaxo/src/core/CHANGELOG.md">Changelog</a> &nbsp;|&nbsp;
	<a href="https://redaxo.org/doku/master">Documentation</a> &nbsp;|&nbsp;
	<a href="https://redaxo.org/api/master/">API&nbsp;Docs</a> &nbsp;|&nbsp;
	<a href="https://friendsofredaxo.github.io/tricks/">Tricks</a> &nbsp;|&nbsp;
	<a href="https://www.redaxo.org/slack/">Slack</a></a> &nbsp;|&nbsp;
	<a href="https://twitter.com/REDAXO">Twitter</a> &nbsp;|&nbsp;
	<a href="https://friendsofredaxo.github.io">Friends&nbsp;Of&nbsp;REDAXO</a> &nbsp;|&nbsp;
	<a href="https://friendsofredaxo.github.io/community/">Community Map</a>
</p>

<p align="center">
	<a href="https://github.com/redaxo/redaxo/releases"><img src="https://img.shields.io/github/release/redaxo/redaxo.svg?style=for-the-badge" alt=""></a>&nbsp;
	<a href="https://github.com/redaxo/redaxo/blob/master/LICENSE.md"><img src="https://img.shields.io/badge/license-MIT-green.svg?longCache=true&style=for-the-badge" alt=""></a>&nbsp;
	<a href="https://github.com/redaxo/redaxo/releases"><img src="https://img.shields.io/github/downloads/redaxo/redaxo/total.svg?style=for-the-badge" alt=""></a>
</p>

<br>

## Community ❤️

Die REDAXO-Community gilt als überaus hilfsbereit und freundlich, und wir werden dich unterstützen, wo wir können! ✌️

__REDAXO und die Community kennenlernen:__

### 1. Das CMS installieren und die Demos testen

Das ist vermutlich die beste Möglichkeit, REDAXO kennenzulernen: Installiere das [neuste Release](https://github.com/redaxo/redaxo/releases), melde dich im Adminbereich an und benutze das eingebaute _Installer_-AddOn, um eine der Demo-Websites einzurichten. Für Einsteiger bietet sich die »Basisdemo« (Suche nach `demo_base`) an, die alle wichtigen Themen im Umgang mit REDAXO erklärt.

Aktuelle Demo-Websites: [Basis](https://github.com/FriendsOfREDAXO/demo_base), [Community](https://github.com/FriendsOfREDAXO/demo_community), [OnePage](https://github.com/FriendsOfREDAXO/demo_onepage), [FullPage](https://github.com/FriendsOfREDAXO/demo_fullpage).

### 2. Besuche den Slack-Chat

Du wirst einen Haufen von Community-Mitgliedern im Slack vorfinden. Und auch die Entwickler. Hier ist die beste Möglichkeit, Fragen zu stellen und Leute kennenzulernen.
— Wir freuen uns, dich zu sehen. Eine Einladung bekommst du hier: [redaxo.org/slack](http://redaxo.org/slack/)!

### 3. Kontakte uns bei Twitter

Folge [@REDAXO](https://twitter.com/REDAXO) bei Twitter, um aktuelle Themen zu lesen und Fragen zu stellen. Wir helfen gerne!

### 4. Finde Leute auf der Community-Weltkarte

Wo überall auf der Welt nutzen Menschen REDAXO? [Die Karte zeigt es](https://friendsofredaxo.github.io/community/). 🌎
Bist du noch nicht drauf? [So kannst du deinen Eintrag hinzufügen](https://github.com/FriendsOfREDAXO/community/tree/master/_directory).

&nbsp;

## Mitarbeit

Um am Core und an den Core-Addons zu entwickeln, setze das Projekt mit diesen Schritten lokal auf:

- Klone das Repository: `git clone https://github.com/redaxo/redaxo redaxo && cd redaxo`
- Installiere die für die Entwicklung verwendeten Tools über [Composer](https://getcomposer.org): `composer install`
- Falls du PhpStorm verwendest, passe dort die Composer-Einstellungen (`Languages & Frameworks` > `PHP` > `Composer`) an und deaktiviere diese beiden Optionen:
    - [ ] Add packages as libraries
    - [ ] Synchronize IDE settings with composer.json
- Du kannst die hinterlegte Docker-Konfiguration nutzen, um eine lokale Entwicklungsumgebung zu starten: `docker-compose up -d`.  
  Falls du einen anderen Port als den Standardport 80 benötigst, ergänze die Angabe so: `REDAXO_PORT=8080 docker-compose up -d`.

&nbsp;

## Lizenz

REDAXO wurde von [Yakamara](http://www.yakamara.de) (Frankfurt am Main 🇩🇪) entwickelt, ist Open-Source seit 2004 und wird unter der [MIT-Lizenz](LICENSE.md) veröffentlicht.

&nbsp;

<p align="center">
    <a href="https://shepherd.dev/github/redaxo/redaxo">
         <img src="https://img.shields.io/endpoint?style=for-the-badge&url=https%3A%2F%2Fshepherd.dev%2Fgithub%2Fredaxo%2Fredaxo%2Fcoverage" alt="Psalm coverage">
    </a>
</p>
