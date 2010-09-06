VERSION=$(shell cat remote/inc/global.php | fgrep "['versions']['remote']" | cut -d "'" -f 6)
VERSIONED=remote-$(VERSION).tar

all: $(VERSIONED) remote-current.tar support.tar

$(VERSIONED): remote
	tar -cvf $(VERSIONED) remote

remote-current.tar: $(VERSIONED)
	cp $(VERSIONED) remote-current.tar

support.tar: support
	tar -cvf support.tar support

clean:
	rm remote-*.tar support.tar
