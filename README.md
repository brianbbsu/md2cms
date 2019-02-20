[![PRs Welcome](https://img.shields.io/badge/PRs-welcome-brightgreen.svg?style=flat-square)](http://makeapullrequest.com) 

# A dashboard to manage CP task statements on [HackMD](https://hackmd.io) for Contest Management System

This dashboard helps you manage your CP task statements on [HackMD](https://hackmd.io), converting markdown to pdf with LaTeX, and syncing task statement in Contest Management System ([CMS](https://github.com/cms-dev/cms)).

### Example

This dashboard can convert task statement from HackMD (In Markdown):

![HackMD Statement](https://i.imgur.com/TEdSoLH.png)

To PDF:

![PDF Statement](https://i.imgur.com/VCh5K1m.png)

### Dashboard Screenshot

![Dashboard screenshot](https://i.imgur.com/Vy5Osyd.png)

## Prerequisites

- PHP
- [Pandoc](https://github.com/jgm/pandoc)
- XeTeX
- [Contest Management System](https://github.com/cms-dev/cms)
- Configured and cmsInit-ed PostgreSQL server

Tested on Ubuntu 18.04, PHP 7.2.15, Pandoc 1.19.2, XeTeX 3.14159265, CMS v.1.3 rc0, PostgreSQL 9.5.14.

## Installing

1. Clone this repo.
2. Copy ```example.config.php``` into ```config.php```, modify it according to your setup.
3. Configure your web server (Apache, Nginx, etc.) to serve ```public``` folder, make sure the root folder is readable and writable by your web server.


## How To Use

Statements on HackMD should contains customized YAML metadata header, see ```example.md``` for more information.

After setting up, open ```index.php``` with your browser. Each row in the table represents one task.

![](https://i.imgur.com/qNNKmqT.png)

Explanation:

- ID: Internal ID of the task. Click to open corresponding HackMD note in new tab.
- Short Name: Short name of the task, extracted from markdown header. The dashboard uses this short name to find corresponding cms task.
- Preview: Click to view PDF statement in new tab, automatically re-compiles if changes are made.
- CMS Task Name: Task title set in CMS.
- Same as Hackmd: Indicates whether task statement for this task is the same as that on HackMD.
- Replace: Click to replace statement for this task in CMS with newer version, automatically re-compiles if changes are made.

In case of any error, I haven't setup error logging and error reporting, so you may need to head over to server log or inspect ajax response with browser developer tool yourself.

## Issue

In case of any issue, open new issue here or IM me. PRs are welcome!
