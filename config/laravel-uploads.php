<?php

return [

	/*
	 * Disco donde se guardan los archivos. Sigue siendo s3 por defecto para
	 * que las aplicaciones que ya lo usan no cambien; una aplicacion nueva
	 * puede usar LARAVEL_UPLOADS_DISK=public (o local) sin publicar la config.
	 */
	'disk' => env('LARAVEL_UPLOADS_DISK', 's3'),

	/*
	 * Tamano maximo de un archivo, en kilobytes (la unidad de la regla max de
	 * Laravel). PHP tiene su propio limite (upload_max_filesize y
	 * post_max_size): un archivo que lo supera llega invalido y la subida
	 * responde 422 igualmente.
	 */
	'max_size' => (int) env('LARAVEL_UPLOADS_MAX_SIZE', 10240),

	/*
	 * Extensiones aceptadas por la regla mimes de Laravel, que las deduce del
	 * contenido del archivo y no del nombre que envia el cliente. SVG no esta
	 * por defecto: se sirve inline y puede ejecutar scripts.
	 */
	'allowed_mimes' => [
		'jpg', 'jpeg', 'png', 'gif', 'webp',
		'pdf',
		'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'odt', 'ods',
		'csv', 'txt',
	],

	'user_class' => 'App\Models\User',

	'excel_view' => 'innoboxrrlaraveluploads::excel.',

	'notification_via' => ['mail', 'database'],

	'export_disk' => 's3',

	'compress_images' => true,

	'compress_images_quality' => 60,

	'compress_images_max_width' => 1024,

	'production_dir' => 'files',

	'development_dir' => 'test',

];
