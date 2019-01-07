<?php

function autoLoaderStudent($className) {

    $directories = array('',
        'log4php/',
        '../../log4php/',
        '../../../log4php/',
        '../../../../log4php/',
        'log4php/',
        'OpenInviter/',
        'config/',
        'source/sComponents/profile/',
        'source/sComponents/templatedb/',
        'source/sComponents/test/',
        'source/sComponents/mail/',
        'source/sComponents/registration/',
        'source/sComponents/randomtest/',
        'source/sComponents/studentquiz/',
        'source/sComponents/adminquiz/',
        '../source/sComponents/mail/',
        '../source/sComponents/registration/',
        'source/util/',
        'source/util/student/',
        'source/sComponents/reports/',
        '../../../../source/sComponents/reports/',
        '../../../../source/sComponents/adminquiz/',
        'source/sComponents/omr/',
        'source/sComponents/profile/',
        '../source/sComponents/profile/',
         '../source/sComponents/studentquiz/',
         '../source/sComponents/adminquiz/',
          '../../source/sComponents/profile/',
         '../../../source/sComponents/profile/',
         '../../../../source/sComponents/profile/',
        '../source/sComponents/templatedb/',
        '../source/util/',
        '../source/util/student/',
        '../source/sComponents/reports/',
        '../source/sComponents/omr/',
        '../../source/sComponents/registration/',
    );




    $fileNameFormats = array(
        '%s.php',
        '%s.class.php',
        '%s.class',
        'class.%s.php',
        '%s.inc.php'
    );

    // this is to take care of the PEAR style of naming classes
    $path = str_ireplace('_', '/', $className);
    if (@include_once $path.'.php') {
        return;
    }

    foreach ($directories as $directory) {
        foreach ($fileNameFormats as $fileNameFormat) {
            $path = $directory . sprintf($fileNameFormat, $className);
            if (file_exists($path)) {

                include_once $path;
                return;
            }
        }
    }
}

spl_autoload_register('autoLoaderStudent');
?>
