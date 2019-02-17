# A dashboard to manage CP task statements on [HackMD](hackmd.io) for Contest Management System

This dashboard helps you manage your CP task statements on [HackMD](hackmd.io), converting markdown to pdf with LaTeX, and syncing task statement in Contest Management System ([CMS](https://github.com/cms-dev/cms)).

## Prerequisites

- PHP
- [Pandoc](https://github.com/jgm/pandoc)
- XeTeX

Tested on Ubuntu 18.04, PHP 7.2.15, Pandoc 1.19.2, XeTeX 3.14159265

## Installing

Just clone this repo and serve the ```public``` folder with your web server (Apache, Nginx, etc.), make sure the root folder is readable and writable by your web server.

Copy ```example.config.php``` into ```config.php``` and modify it according to your setup.

## How To Use

After setting up, open ```index.php``` with your browser. Each row in the table represents one problem.

Statements on HacmMD should contains customized YAML metadata header, see ```example.md``` for more information.

In case of any error, I haven't setup error logging and error reporting, so you may need to head over to server log or inspect ajax response with browser developer tool yourself.
