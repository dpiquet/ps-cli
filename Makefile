
PHP='php'
DEST='/usr/local/sbin/ps-cli'

all:
	rm -f build/ps-cli.phar
	$(PHP) make_phar.php
	chmod +x build/ps-cli.phar

install:
	@echo "Installing ps-cli in $(DEST)"
	cp build/ps-cli.phar $(DEST)
	chmod +x $(DEST)

clean:
	rm -f build/ps-cli.phar

.PHONY: clean
