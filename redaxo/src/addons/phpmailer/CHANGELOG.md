Changelog
=========

Version 2.1.2 – 08.01.2017
--------------------------

### Security

* Update phpmailer auf 5.2.21 mit Security-Fixes (remote code execution vulnerability)
  Mehr Informationen: https://github.com/PHPMailer/PHPMailer/wiki/About-the-CVE-2016-10033-and-CVE-2016-10045-vulnerabilities


Version 2.1.1 – 15.07.2016
--------------------------

* Update phpmailer auf 5.2.16
* SMTP-Debug kann in Backend konfiguriert werden (@alexplusde)


Version 2.1.0 – 24.03.2016
--------------------------

### Neu

* Es werden Backups der gesendeten Mails erstellt (@phoebusryan)

### Bugfixes

* Header X-Priority wird standardmäßig nicht mehr gesetzt, da die Mails sonst eher als Spam gewertet werden (@skerbis)

