<?php

return [

	'disk' => 's3',

	'user_class' => 'App\Models\User',

	'excel_view' => 'innoboxrrlaraveluploads::excel.',

	'notification_via' => ['mail', 'database'],

	'export_disk' => 's3',

	'compress_images' => true,

	'compress_images_quality' => 60,

	'compress_images_max_width' => 1024,
	
];