PHP shell
=========

I'm stopping the development of this script since I figured out that I just need to recompile PHP with the --with-readline option, to get the possibility to use interactive php shell. I decide what to do with this script after I check out this possibility. If I decide to stop this project I'll port the [Installation script](https://github.com/morii/Install-PHP-Shell) to work with php -a command instead.

It's a really simple interactive PHP shell.

Usage
-----

Just launch this PHP script and put PHP instructions and this script will execute them.

TODO List
---------

Things that still need to be done:

* More tests (also make PHPUnit tests)
* A configuration file
* A way of defining functions and saving them
* Better User Interface (Readline like)
* Enabling usage of console and window programs from this shell (i.e. vim, mplayer, vlc)
* Make a function providing php help (probably by using PHP manual)

Contributing
------------

You can contribute to this project by:

* generating a patch using git and sending it to me, or
* opening Pull Request on GitHub.

Just make sure that the changes that you're sending to me are in a separate branch.

Any kind of help is welcomed. 
