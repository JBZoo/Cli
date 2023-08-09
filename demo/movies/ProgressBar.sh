#!/usr/bin/env sh

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

. demo-magic.sh

cd ..
clear
PROMPT_TIMEOUT=1

pei "# "
pei "# See it here './demo/Commands/ExamplesOutput.php'"
pei "# Let's get started!"
wait


pei "# At first, let me show you the output of the command by default."
pei "./my-app progress-bar --case=simple"
wait
pei "clear"


pei "# "
pei "./my-app progress-bar --case=simple -v"
wait
pei "clear"

pei "# "
pei "./my-app progress-bar --case=simple -vv"
wait
pei "clear"

pei "# "
pei "./my-app progress-bar --case=simple -vvv"
wait
pei "clear"

pei "# "
pei "./my-app progress-bar --case=simple --no-progress"
wait
pei "clear"

pei "# "
pei "./my-app progress-bar --case=simple --cron"
wait
pei "clear"

pei "# "
pei "./my-app progress-bar --case=assoc"
wait
pei "clear"

pei "# "
pei "./my-app progress-bar --case=break"
wait
pei "clear"

pei "# "
pei "./my-app progress-bar --case=exception"
wait
pei "clear"

pei "# "
pei "./my-app progress-bar --case=exception-list"
wait
pei "clear"




pei "##############################"
pei "# That's all for this demo.  #" 
pei "#        Have a nice day =)  #"
pei "#                Thank you!  #"
pei "##############################"
