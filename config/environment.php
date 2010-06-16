<?php

//Load the correct environment off the bat.
include $dirname . '/config/environments/' . Registry::get('pr-environment') . '.php';
