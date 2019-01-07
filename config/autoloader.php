<?php
function autoLoader($className) {

    $directories = array('',
        '../log4php/',
        '../../log4php/',
        '../source/models/',
        '../source/models/coupon/',
        '../source/components/login/dao/',
        '../source/components/subject/dao/',
        '../source/components/question/dao/',
        '../source/components/topic/dao/',
        '../source/components/video/dao/',
        '../source/components/menu/dao/',
        '../source/components/batch/dao/',
        '../source/components/course/dao/',
        '../source/components/news/dao/',
        '../source/components/coupon/dao/',
        '../source/components/student/dao/',
        '../source/components/product/dao/',
        '../source/components/category/dao/',
        '../source/components/package/dao/',
        '../source/components/dashboard/dao/',
        '../source/components/setting/dao/',
        '../source/components/instruction/dao/',
        '../source/sComponents/profile/',
        '../source/sComponents/test/',
        '../../source/sComponents/profile/',
        '../../source/sComponents/test/',
        '../../../source/sComponents/profile/',
        '../../../source/sComponents/test/',
         '../../../../../source/sComponents/profile/',
        '../../../../../source/sComponents/test/',
        '../source/components/benchmark/dao/',
        '../source/components/markScheme/dao/',
        '../source/components/systemsetting/dao/',
        '../source/components/studentenquiry/dao/',
        '../source/components/studentnews/dao/',
        '../source/components/result/dao/',
        '../source/components/institute/dao/',
        '../../source/components/markScheme/dao/',
        '../../../source/components/markScheme/dao/',
        '../../../../source/components/markScheme/dao/',
        '../../../../../source/components/markScheme/dao/',
        '../source/util/',
        //'OpenInviter/',
        '../source/components/test/dao/',
        '../source/components/mainlayout/dao/',
        '../../../log4php/',
        '../source/components/report/dao/',
        '../../../../log4php/',
        '../../../../../log4php/',
        '../../../../../../log4php/',
        '../../../../source/components/menu/dao/',
        '../../../../source/models/',
        '../../../../../source/components/subject/dao/',
        '../../../../../source/components/topic/dao/',
        '../../../../../source/components/video/dao/',
        '../../../../../source/components/question/dao/',
        '../../../../../source/components/systemsetting/dao/',
        '../../../../source/components/student/dao/',
        '../../../../source/components/studentenquiry/dao/',
        '../../../../../source/components/batch/dao/',
        '../../../../../source/components/course/dao/',
        '../../../../../source/components/news/dao/',
        '../../../../../source/components/coupon/dao/',
        '../../../../../source/components/student/dao/',
        '../../../../source/components/package/dao/',
        '../../../../source/components/test/dao/',
        '../../../../source/components/setting/dao/',
        '../../../../../source/components/instruction/dao/',
        '../../../../../source/components/benchmark/dao/',
        '../../../../../source/components/studentnews/dao/',
        '../../../../../source/components/result/dao/',
        '../../../../../source/components/institute/dao/',
        '../../../../../source/models/',
        '../../source/util/',
        '../../../source/util/',
        '../../../../source/util/',
        '../../../../../source/util/',
        '../source/util/student/',
        '../../source/util/student/',
        '../../../source/util/student/',
        '../../../../source/util/student/',
        '../../../../../source/util/student/'
        
    );
//student folder add by Gaurav




    $fileNameFormats = array(
        '%s.php',
        '%s.class.php',
        '%s.class',
        'class.%s.php',
        '%s.inc.php'
    );

    // this is to take care of the PEAR style of naming classes
    $path = str_ireplace('_', '/', $className);
    if (@include_once $path . '.php') {
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

spl_autoload_register('autoLoader');
?>

