.PHONY: test testall

vendor/autoload.php: composer.json
	rm -f composer.lock
	composer install

test: vendor/autoload.php
	php -dzend_extension=xdebug.so vendor/bin/phpunit --coverage-text --configuration phpunit.xml.dist

testall: test
	php56 -dzend_extension=xdebug.so vendor/bin/phpunit --coverage-text --configuration phpunit.xml.dist
	php70 -dzend_extension=xdebug.so vendor/bin/phpunit --coverage-text --configuration phpunit.xml.dist
	php71 -dzend_extension=xdebug.so vendor/bin/phpunit --coverage-text --configuration phpunit.xml.dist

