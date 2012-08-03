@echo off

%PSC_CMS%psc-cms.bat build-phar --check -i MyNewProject && php -f cli.php %*