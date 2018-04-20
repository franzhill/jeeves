# jeeves

## Intro

**Jeeves** is a simple, php-based lightweight framework built upon some bricks like Symfony's Console,
designed to speed up and industrialize development of command-line applications
(i.e. command line apps with subset of commands with some common options and arguments, 
some common checks and behaviours etc.).

Its main intents are:
 - ease of expansion (adding new subcommands, options etc.)
 - convention over configuration
 - offer just the right amount of coding "power" through a "code over configuration"
   approach: specific command behaviours are PHP-coded, and all common behaviours 
   (checks, validations etc.) can be overridden (through code here again).
   
As a consequence however, making an app with Jeeves *will* require coding
(as opposed to what you could do with an 'Ant' approach for example).

As itself, **Jeeves** is just an empty case, so its use has been showcased here 
through a concrete usecase, the command line appliaction "**Nestor**".

**Nestor** is an example of a command line application built with the command 
line application framework **Jeeves**.

**Nestor** would be an command line operations/maintenance application tailored
to a specific setup and needs -however nonetheless instructive-, serving 
commands like "deploy to production" or "compare this version of that file 
against what we have on that environement" ...

**Nestor** consequently does not  purport to offer a workable let alone 
state-of-the-art opeations/maintenance framework or anything else
(there are many tools out there to suit every kind of setup)
but simply tries to offer an an example of how **Jeeves** can be used to 
build an command line app to meet some specific needs.

**Nestor** also exhibits an instruction workflow mini-framework that provides a simple
and concise syntax to define behaviours revolving around a core instruction 
(that could be a Unix shell instruction for example). By behaviours we mean e.g. 
what to do in case of instruction fail (should we prompt? rollback? what's the rollback
instruction(s)?) or instruction success (should we display a message etc.)
or other events as such.


## Code

-> See lib/fhibox/jeeves for **Jeeves** <br />
-> See src/fhibox/nestor for **Nestor** <br />
-> See src/fhibox/nestor/application/instructions for the instruction workflow mini-framework <br />

Php version : **Jeeves** and **Nestor** were developped against PHP 5.3. They however seem to be working fine as-is under PHP 7.


# Documentation

The code is documented as much as possible, and figuring out how to use **Jeeves** should  
hopefully be understandable from perusing/reading the code comments.
This doesn't replace a good manual for sure, but be reassured, it's on its way.


## Examples

<pre>
francois.hill@xxx $ Nestor
Nestor version 0.9

Usage:
  command [options] [arguments]

Options:
  -h, --help            Display this help message
  -q, --quiet           Do not output any message
  -V, --version         Display this application version
      --ansi            Force ANSI output
      --no-ansi         Disable ANSI output
  -n, --no-interaction  Do not ask any interactive question
  -v|vv|vvv, --verbose  Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug

Available commands:
  compare          Compare a same source file between two environments
  deploy           Deploys a/several file/directory/project into the specified environment
  deploy_log       Display production deployments logs
  deploy_rollback  Cancels last production deployment(s)
  help             Displays help for a command
  list             Lists commands
  more             Display a file (source or log) from the production environment



francois.hill@xxx $ Nestor compare -h
Usage:
  compare [options] [--] <envs> <source-file> (<source-file>)...

Arguments:
  envs                       Environments, example : DEV:PROD
  source-file                Target (file or directory)

Options:
  -N, --test-mode            aka dry-run
  -w, --virtual-mode         Vitual mode, i.e. only print out/describe commands that would be executed (but don't really execute them)
  -t, --verbosity-transmit   Transmit verbosity.
                             Chosen verbosity (via options -v, -vv ...) will not be applied
                             directly to this command but passed down onto the subcommands that it calls.
                             (E.g. a Nestor command might call shell commands. In this case verbosity will be
                             applied to these shell commands, wherever possible, and not to the Nestor command
                             itself.)

  -d, --diff-tool=DIFF-TOOL  Diff tool to be used. Possible values:
                             - vimdiff : the best visual tool, with side to side comparison
                               and highlighted differences. However, exiting is a bit tricky
                               (to exit, type the following sequence twice: <ESC>:q! )
                             - sdiff
                             - diff
  -R, --rep=REP              VCS where source files lie:
                             - fhibox1   (source files...)
                             - XYZ       (certifications file...)
                             - constants (const files)
  -h, --help                 Display this help message
  -q, --quiet                Do not output any message
  -V, --version              Display this application version
      --ansi                 Force ANSI output
      --no-ansi              Disable ANSI output
  -n, --no-interaction       Do not ask any interactive question
  -v|vv|vvv, --verbose       Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug

Help:

 Compare a same source file between two different environments

 Nota: if comparend versions are identical, then comparison tools used by this
 command may not show screen or message.

 Examples:
 ---------
 $ Nestor compare -d sdiff -R fhibox1 dev:prod /my/www/.htaccess
 $ Nestor compare -d diff  -R fhibox1 dev:staging /my/repos/fhibox1/trunk/product_X/clients/marks_and_spenders/custom/templates_html/index.html

 Troubleshooting:
 ----------------
 - If nothing is shown on output of this command, please check under which user you are running.
 - There may be some issues with vimdiff under user fhibox...

</pre>
