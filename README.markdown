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

    [development]
    <model-name> = <container-in-the-cloud-resource>

    [production]
    <model-name> = <container-in-the-cloud-resource>


Under each environment you can have as many <model-name> = <container-in-the-cloud-resource> tags as you need.