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

pei "# In this demo, you will see the basic features of Progress Bar for CLI app."
pei "# ProgressBar helps you perform looping actions and output extra info to profile your app."
pei "# Now we will take a look at its main features."
pei "# See it here './demo/Commands/DemoOutput.php'"
pei "# Let's get started!"
wait


pei ""
pei ""
pei "# At first, let me show you the output of the progress by default."
pei "./my-app progress-bar --case=simple"
wait
pei "clear"


pei "# Different levels of verbosity give different levels of detail."

pei ""
pei ""
pei "# Verbose level (-v)"
pei "./my-app progress-bar --case=messages -v"
wait

pei ""
pei "# Very Verbose level (-vv)"
pei "./my-app progress-bar --case=messages -vv"
wait

pei ""
pei "# Debug level, max (-vvv)"
pei "./my-app progress-bar --case=messages -vvv"
wait
pei "clear"


pei "# You can use any iterated object as a data source for the widget."
pei "# Let's look at an associative array as an example."
pei "./my-app progress-bar --case=array"
pei "# As you can see, you can customize the message. This is useful for logs."
wait
pei "clear"


pei "# You can easily disable progress bar"
pei "./my-app progress-bar --case=messages --no-progress"
wait

pei ""
pei "# Or quickly switch to crontab mode"
pei "./my-app progress-bar --case=messages --output-mode=cron"
wait
pei "clear"


pei ""
pei "# It's ready for ELK Stack (Logstash)."
pei "./my-app progress-bar --case=messages --output-mode=logstash | jq"
wait
pei "clear"


pei "# It is easy to interrupt the execution."
pei "./my-app progress-bar --case=break"
wait
pei "clear"


pei "# If an unexpected error occurs, it will stop execution and display detailed information on the screen."
pei "./my-app progress-bar --case=exception -vv"
wait
pei "clear"


pei "# You can catch all exceptions without interrupting execution."
pei "# And output only one single generic message at the end."
pei "./my-app progress-bar --case=exception-list -vv"
wait
pei "clear"


pei "##############################"
pei "# That's all for this demo.  #" 
pei "#        Have a nice day =)  #"
pei "#                Thank you!  #"
pei "##############################"
