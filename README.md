BattMan
=======

BattMan is a tool to monitor battery usage. It is a client-side application based on [WinBinder](https://github.com/stefan-loewe/WinBinder "WinBinder") and [Woody](https://github.com/stefan-loewe/Woody "Woody").

What is needed to run BattMan?
------------------------------
You need to have PHP 5.4 or above installed, as well as the latest version of the [WinBinder extension](https://github.com/stefan-loewe/WinBinder/ "WinBinder extension").

All this is taken care of if you get the easy-peasy binary distribution from the <cod>dist</code> in this repository.

How to install BattMan?
-----------------------

- easy-peasy binary distribution
  - download the [binary distribution](https://github.com/stefan-loewe/BattMan/blob/master/dist/)
  - run `php.exe BattMan.phar`
- installation via git repository
  - `git clone https://github.com/stefan-loewe/BattMan.git`
  - `cd BattMan`
  - `php composer.phar install`
  - run `php bootstrap.php` from the main folder

How to run BattMan?
-------------------
After having installed BattMan and the respective dependencies, just run  
`php bootstrap.php`, or `php BattMan.phar` if you want to run it from the phar archive.

Which operating system does BattMan need?
-----------------------------------------
BattMan is only tested on Microsoft Windows 7, it may run on older versions, too.

Why does BattMan exist?
-----------------------
This is nothing more than an effort for show-casing [Woody](https://github.com/stefan-loewe/Woody/ "Woody") and the [WinBinder](https://github.com/stefan-loewe/WinBinder/ "WinBinder") extension in some non-trivial application.
