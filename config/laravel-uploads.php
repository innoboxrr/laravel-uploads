<?php

return [

	'disk' => 's3',

	'user_class' => 'App\Models\User',

	'excel_view' => 'innoboxrrlaraveluploads::excel.',

	'notification_via' => ['mail', 'database'],

	'export_disk' => 's3',
	
];