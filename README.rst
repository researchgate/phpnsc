======
phpnsc
======

.. image:: https://travis-ci.org/researchgate/phpnsc.svg?branch=master
    :target: https://travis-ci.org/researchgate/phpnsc
.. image:: https://img.shields.io/packagist/v/rg/phpnsc.svg
    :target: https://packagist.org/packages/rg/phpnsc

The PHP namespace checker, checks all files in a project for invalid class references.

It prints an error, if a class was used with it's simple or relative name but is:

- not in the same namespace
- or not in the corresponding sub namespace
- or does not have a corresponding use statement

Usage
-----

- Add a JSON config file to your project root (see config.sample.json)
- Run phpnsc:
  
 phpnsc run path/to/config.json

Configuration
-------------

The configuration has to be in a JSON file, that usually is placed in the root of
your project. Example::

 {
    "vendor" : "vendorNamespace",
    "folders" : {
        "root"    : "path/to/sources",
        "include" : ["subpackage", "subpackage_two"],
        "exclude" : ["subpackage/excluded"]
    },
    "filetypes" : {
        "include" : [".php"],
        "exclude" : [".config.php"]
    },
    "output" : [{
        "class": "rg\\tools\\phpnsc\\CheckstyleOutput",
        "parameter": "build/logs/phpnsc.xml"
    },{
        "class": "rg\\tools\\phpnsc\\ConsoleOutput",
        "parameter": ""
    }]
 }

- vendor: specifies the root vendor namespace of your project, see (https://gist.github.com/1234504)

- folders/root: the root folder of your project

- include: which subfolders of your project root should be included?

- exclude: are there be any subsubfolders that should be excluded?

- filetypes: filetypes that should be included and excluded from analyzis

- output: array of output classes that should be used. Currently support are:
 
  - CheckstyleOutput (checkstyle compatible XML file, e.g. for a CI server)

  - ConsoleOutput

How it works
------------

First the tool scans for all files matching the criteria defined in the config file.
For each file it strips out all unnecessary stuff like comments, strings or non php
content. After that it analyzes all defined classes and interfaces in your project 
and all used andreferenced classes and interfaces in each file based on regular 
expressions.
In the next step, it then uses this information to deduct if all classes and inter-
faces are referenced correctly.

Current limitations
-------------------

This tool is still alpha. It is certainly possible, that it does not find all errors
of that it finds false positives.
Additionally it can not handle importing several namespaces with one use statement and 
may have problems with using aliases.
