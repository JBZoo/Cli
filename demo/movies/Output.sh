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

# Hide the evidence
clear

PROMPT_TIMEOUT=7

pei "# This is demo of different output levels of JBZoo/Cli framework."
pei "# We have a lot of output levels, which can be used for different purposes."
pei "# For the demo I've prepared a simple script, which will print some messages."
pei "# See it here './demo/Commands/ExamplesOutput.php'"
pei "# Let's get started!"
wait


pei "# At first, let me show you the output of the command by default."
pei "./my-app output"
wait
pei "clear"


pei "# There are several lines that written in Standard Error output (stderr)."
pei "./my-app output > /dev/null"
wait
pei "clear"


pei "# There is a special level to show a messge forever."
pei "./my-app output --quiet"
wait
pei "clear"


pei "# And pay attentin on old school style output."
pei "./my-app output --stdout-only | grep 'Legacy Output'"
wait
pei "clear"


pei "# Let's increase the output level. Just add the flag '-v'. Look at 'Info:'"
pei "./my-app output -v"
wait
pei "clear"


pei "# Next, let's look at more detailed logs (-vv). Look at 'Warning:'"
pei "./my-app output -vv"
wait
pei "clear"


pei "# And messages that are only useful to developers during application debugging (-vvv). Look at 'Debug:'"
pei "./my-app output -vvv"
wait
pei "clear"


pei "# There is an easy way to find memory leaks and performance issues. Just add '--profile' flag."
pei "./my-app output --profile"
wait
pei "clear"


pei "# Also, we can use the output as logs. It's pretty useful for cron jobs."
pei "./my-app output --profile --timestamp"
wait
pei "clear"


pei "# Let's simulate a fatal error."
pei "./my-app output --throw-custom-exception"
wait
pei "clear"


pei "# Sometimes we have to ignore exception not to break the pipeline."
pei "./my-app output --throw-custom-exception --mute-errors -vvv"
pei "# Look at the last lines."
wait
pei "clear"


pei "# In rare cases we can use the flag '--non-zero-on-error' to return ExitCode=1 if any stderr happend."
pei "./my-app output --non-zero-on-error -vvv"
pei "# Look at the last lines."
wait
pei "clear"


pei "##############################"
pei "# That's all for this demo.  #" 
pei "#        Have a nice day =)  #"
pei "#                Thank you!  #"
pei "##############################"
