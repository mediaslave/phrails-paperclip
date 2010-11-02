# Requirements

The following PHP package will be required depending on what you are trying to do.

Save to cloud:
    
    Curl - http://www.php.net/manual/en/book.curl.php
    FileInfo - http://www.php.net/manual/en/book.fileinfo.php

Image manipulation:
    
    ImageMagick - http://www.php.net/manual/en/book.imagick.php

# Phrails-Paperclip Ini File

This plugin expects that a config/phrails-paperclip.ini in the Phrails project.

    It can be empty for use with File type attachments.

We currently only use it with Rackspace Cloud Files. Your File would look something like:

    [global]
    user = <username>
    key = <cloud-resource-key>

    [development:<model-name>]
    container = <container-in-the-cloud-resource>

    [production:<model-name>]
    container = <container-in-the-cloud-resource>


You can have as many of the development:<model-name> sections as you like.  You should have one "development" and one "production".


