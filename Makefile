#
# JBZoo Toolbox - Cli.
#
# This file is part of the JBZoo Toolbox project.
# For the full copyright and license information, please view the LICENSE
# file that was distributed with this source code.
#
# @license    MIT
# @copyright  Copyright (C) JBZoo.com, All rights reserved.
# @see        https://github.com/JBZoo/Cli
#


ifneq (, $(wildcard ./vendor/jbzoo/codestyle/src/init.Makefile))
    include ./vendor/jbzoo/codestyle/src/init.Makefile
endif


update: ##@Project Install/Update all 3rd party dependencies
	$(call title,"Install/Update all 3rd party dependencies")
	@echo "Composer flags: $(JBZOO_COMPOSER_UPDATE_FLAGS)"
	@composer update $(JBZOO_COMPOSER_UPDATE_FLAGS)


test-all: ##@Project Run all project tests at once
	@make test
	@make codestyle
	@make codestyle PATH_SRC=./demo


test-logstash: ##@Project Run Logstash manual tests (direct)
	rm -f $(PATH_BUILD)/logstash.log
	$(PHP_BIN) $(PATH_ROOT)/demo/my-app test --output-mode=logstash >> $(PATH_BUILD)/logstash.log 2>&1
	cat $(PATH_BUILD)/logstash.log | jq
	cat $(PATH_BUILD)/logstash.log | nc -c localhost 50000
