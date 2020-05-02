install:
	composer install

lint:
	composer run-script phpcs -- --standard=PSR12,ruleset.xml src bin tests

test-covarage:
	vendor/bin/phpunit tests --testsuite=unit --coverage-text --coverage-clover ./clover.xml

test:
	vendor/bin/phpunit tests --testsuite=unit
