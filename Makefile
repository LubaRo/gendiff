install:
	composer install

lint:
	composer run-script phpcs -- --standard=PSR12,ruleset.xml src bin tests -s

test-covarage:
	composer run-script phpunit tests -- --coverage-text --coverage-clover ./clover.xml

test:
	composer run-script phpunit tests
