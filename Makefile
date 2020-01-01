install:
	composer install

lint:
	composer run-script phpcs -- --standard=PSR12 src bin

test-covarage:
	vendor/bin/phpunit tests --testsuite=unit
