
PHP='php'
DEST='/usr/local/sbin'

all:
	rm -f build/ps-cli.phar
	$(PHP) make_phar.php

install:
	@echo "Installing ps-cli in $(DEST)"
	cp build/ps-cli.phar $(DEST)/ps-cli
	chmod +x $(DEST)/ps-cli

clean:
	rm -f build/ps-cli.phar

.PHONY: clean
