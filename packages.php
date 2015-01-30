<?php
// Plugin with update info
$version = '3.1.4';
$packages['tickera'] = array(
    'versions' => array(
        $version => array( //Array name should be set to current version of update
            'version' => $version, //Current version available
            'date' => '2015-01-24', //Date version was released
            'author' => 'Tickera', //Author name - can be linked using html - <a href="http://link-to-site.com">Author Name</a>
            'requires' => '4.1', // WP version required for plugin
            'tested' => '4.1', // WP version tested with
            'homepage' => 'http://tickera.com/', // Site devoted to your plugin if available
            'external' => '', // Unused
            'package' => 'http://tickera.com/api/download.php?key='.$_POST['key'].'&site_url='.$_POST['site_url'],//md5('tickera-'.$version.'.zip' . mktime(0,0,0,date("n"),date("j"),date("Y")))
            'file_name' => 'tickera-'.$version.'.zip',
            'sections' => array(
                'change log' => '<p>- Updated check-in API and prepared for the new iOS app (https://itunes.apple.com/us/app/ticket-checkin/id958838933)</p>
<p>- Fixed issue with orders page pagination in admin</p>'
                

            )
        )
    ),
    'info' => array(
        'url' => 'http://tickera.com/'  // Site devoted to your plugin if available
    )
);
