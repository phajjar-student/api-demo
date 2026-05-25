In a typical webserver setup, PHP files are included as HTML files, so
they are stored in the "html" directory, even though they are parsed by
the PHP interpreter.

While you can use this container as an inspiration to build your own
API-enabled apps, you must never expose files which:

	* Generate Access Tokens
	* Show your PHP information
	* Test your configuration

to the general public. 

Note that Chrome and some other browsers don't display http pages by default.
You may need to use Safari or Firefox to browse these pages if you don't
set up SSL.
