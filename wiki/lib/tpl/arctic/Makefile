# Makefile for DokuWiki Template Arctic
#
# @author Michael Klier <chi@chimeric.de>
# @author Samuel Fischer <sf@notomorrow.de>

DIST_VERSION=`cat VERSION`
DIST_NAME=template-arctic-$(DIST_VERSION)
DIST_DIR=.
APP_NAME=arctic
DOKU_DIR=$(DIST_DIR)
DOKU_DIR=/var/www/notomorrow.de/dokuwiki/lib/tpl/default

# {{{ DOCS
DOCS=$(DIST_DIR)/README \
	 $(DIST_DIR)/COPYING \
	 $(DIST_DIR)/VERSION
# }}}

# {{{ CSS
ARCTIC_CSS=$(DIST_DIR)/arctic_design.css \
	$(DIST_DIR)/arctic_layout.css \
	$(DIST_DIR)/arctic_media.css \
	$(DIST_DIR)/arctic_print.css \
	$(DIST_DIR)/arctic_rtl.css

DOKU_CSS=$(DOKU_DIR)/_admin.css \
	$(DOKU_DIR)/_fileuploader.css \
	$(DOKU_DIR)/_linkwiz.css \
	$(DOKU_DIR)/_mediamanager.css \
	$(DOKU_DIR)/_mediaoptions.css \
	$(DOKU_DIR)/_subscription.css \
	$(DOKU_DIR)/_tabs.css \
	$(DOKU_DIR)/design.css \
	$(DOKU_DIR)/layout.css \
	$(DOKU_DIR)/media.css \
	$(DOKU_DIR)/print.css \
	$(DOKU_DIR)/rtl.css

CSS=$(ARCTIC_CSS) $(DOKU_CSS)
# }}}

# {{{ STYLE_INI
STYLE_INI=$(DIST_DIR)/style.ini \
		  $(DIST_DIR)/style.ini.dist
# }}}

# {{{ PHP
ARCTIC_PHP=$(DOKU_DIR)/detail.php \
	$(DOKU_DIR)/mediamanager.php

DOKU_PHP=$(DIST_DIR)/main.php \
	$(DIST_DIR)/tpl_functions.php

PHP=$(ARCTIC_PHP) $(DOKU_PHP)

# }}}

# {{{ HTML
HTML=$(DIST_DIR)/footer.html
# }}}

# {{{ SCRIPT
SCRIPT=$(DIST_DIR)/script.js
# }}}

# {{{ IMAGES
IMAGES=$(DIST_DIR)/images/bullet.gif \
	   $(DIST_DIR)/images/button-apache.png \
	   $(DIST_DIR)/images/button-as.gif \
	   $(DIST_DIR)/images/button-bash.png \
	   $(DIST_DIR)/images/button-cc.gif \
	   $(DIST_DIR)/images/button-chimeric-de.png \
	   $(DIST_DIR)/images/button-css.png \
	   $(DIST_DIR)/images/button-debian.png \
	   $(DIST_DIR)/images/button-donate.gif \
	   $(DIST_DIR)/images/button-dw.png \
	   $(DIST_DIR)/images/button-email.png \
	   $(DIST_DIR)/images/button-firefox.png \
	   $(DIST_DIR)/images/button-gimp.png \
	   $(DIST_DIR)/images/button-gpg.gif \
	   $(DIST_DIR)/images/button-icq.gif \
	   $(DIST_DIR)/images/button-php.gif \
	   $(DIST_DIR)/images/button-rss.png \
	   $(DIST_DIR)/images/buttonshadow.png \
	   $(DIST_DIR)/images/button-vim.png \
	   $(DIST_DIR)/images/button-xhtml.png \
	   $(DIST_DIR)/images/closed.gif \
	   $(DIST_DIR)/images/favicon.ico \
	   $(DIST_DIR)/images/inputshadow.png \
	   $(DIST_DIR)/images/interwiki.png \
	   $(DIST_DIR)/images/link_icon.gif \
	   $(DIST_DIR)/images/mail_icon.gif \
	   $(DIST_DIR)/images/open.gif \
	   $(DIST_DIR)/images/tocdot2.gif \
	   $(DIST_DIR)/images/tool-admin.png \
	   $(DIST_DIR)/images/tool-backlink.png \
	   $(DIST_DIR)/images/tool-edit.png \
	   $(DIST_DIR)/images/tool-index.png \
	   $(DIST_DIR)/images/tool-login.png \
	   $(DIST_DIR)/images/tool-logout.png \
	   $(DIST_DIR)/images/tool-profile.png \
	   $(DIST_DIR)/images/tool-recent.png \
	   $(DIST_DIR)/images/tool-revisions.png \
	   $(DIST_DIR)/images/tool-source.png \
	   $(DIST_DIR)/images/tool-subscribe.png \
	   $(DIST_DIR)/images/tool-top.png \
	   $(DIST_DIR)/images/tool-revert.png \
	   $(DIST_DIR)/images/urlextern.png \
	   $(DIST_DIR)/images/windows.gif \
       $(DIST_DIR)/images/mediamanager.png \
       $(DIST_DIR)/images/resizecol.png
# }}}

# {{{ LANG
LANG=$(DIST_DIR)/lang/
# }}}

# {{{ CONF
CONF=$(DIST_DIR)/conf/default.php \
	 $(DIST_DIR)/conf/metadata.php
# }}}

DIST_FILES= $(DOCS) $(CSS) $(HTML) $(SCRIPT) $(PHP) $(STYLE_INI) $(IMAGES) $(LANG) $(CONF)

dist:
	@mkdir $(APP_NAME)
	cp $(DOCS) $(CSS) $(HTML) $(SCRIPT) $(PHP) $(STYLE_INI) $(APP_NAME)/
	cp -r $(LANG) $(APP_NAME)

	@mkdir -p $(APP_NAME)/images
	cp $(IMAGES) $(APP_NAME)/images

	@mkdir -p $(APP_NAME)/conf
	cp $(CONF) $(APP_NAME)/conf

	@mkdir -p $(APP_NAME)/sidebars
	@touch $(APP_NAME)/sidebars/_dummy

	mkdir pkg
	tar czf pkg/$(DIST_NAME).tgz $(APP_NAME)/
	rm -r $(APP_NAME)

clean: 
	rm $(DIST_NAME).tgz

# vim:ts=4:sw=4:fdm=marker:
