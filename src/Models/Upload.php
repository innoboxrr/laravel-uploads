<?php

namespace Innoboxrr\LaravelUploads\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Innoboxrr\Traits\MetaOperations;
use Innoboxrr\LaravelUploads\Models\Traits\Relations\UploadRelations;
use Innoboxrr\LaravelUploads\Models\Traits\Storage\UploadStorage;
use Innoboxrr\LaravelUploads\Models\Traits\Operations\UploadOperations;
use Innoboxrr\LaravelUploads\Models\Traits\Mutators\UploadMutators;

class Upload extends Model
{

    use HasFactory,
        SoftDeletes,
        MetaOperations,
        UploadRelations,
        UploadStorage,
        UploadOperations,
        UploadMutators;
        
    protected $fillable = [
        'uuid',
        'filename',
        'mime_type',
        'extension',
        'size',
        'path',
        'disk',
        'visibility',
        'uploadable_type',
        'uploadable_id',
        'user_id',
    ];

    protected $creatable = [
        'uuid',
        'filename',
        'mime_type',
        'extension',
        'size',
        'path',
        'disk',
        'visibility',
        'uploadable_type',
        'uploadable_id',
        'user_id',
    ];

    protected $updatable = [
        'filename',
    ];

    protected $casts = [];

    protected $protected_metas = [];

    protected $editable_metas = [];

    public static $export_cols = [
        'uuid',
        'filename',
        'mime_type',
        'extension',
        'size',
        'path',
        'disk',
        'visibility',
        'user_id',
    ];

    public static $loadable_relations = [
        'user',
    ];

    public static $loadable_counts = [
        'user',
    ];

    protected static function newFactory()
    {
        return \Innoboxrr\LaravelUploads\Database\Factories\UploadFactory::new();
    }

}
