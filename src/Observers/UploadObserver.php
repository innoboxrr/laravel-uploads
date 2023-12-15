<?php
 
namespace Innoboxrr\LaravelUploads\Observers;
 
use Innoboxrr\LaravelUploads\Models\Upload;
 
class UploadObserver
{

    /**
     * Handle events after all transactions are committed.
     *
     * @var bool
     */
    // public $afterCommit = true;

    /**
     * Handle the Upload "created" event.
     */
    public function created(Upload $upload): void
    {
        // ...
    }
 
    /**
     * Handle the Upload "updated" event.
     */
    public function updated(Upload $upload): void
    {
        // ...
    }
 
    /**
     * Handle the Upload "deleted" event.
     */
    public function deleted(Upload $upload): void
    {
        // ...
    }
 
    /**
     * Handle the Upload "restored" event.
     */
    public function restored(Upload $upload): void
    {
        // ...
    }
 
    /**
     * Handle the Upload "forceDeleted" event.
     */
    public function forceDeleted(Upload $upload): void
    {
        // ...
    }
}