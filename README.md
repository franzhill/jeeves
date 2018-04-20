# jeeves

**Jeeves** is a simple, php5-based lightweight framework built upon some bricks like Symfony's Console,
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


See lib/fhibox/jeeves for **Jeeves**
See src/fhibox/nestor for **Nestor**
See src/fhibox/nestor/application/instructions for the instruction workflow mini-framework


The code is documented as much as possible, and figuring out how to use **Jeeves** should  
hopefully be understandable from perusing/reading the code comments.
This doesn't replace a good manual for sure, but be reassured, it's on its way.

