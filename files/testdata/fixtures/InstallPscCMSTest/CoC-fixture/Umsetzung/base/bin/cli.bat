@echo off

%PSC_CMS%psc-cms.bat build-phar --check -i CoC && php -f cli.php %*