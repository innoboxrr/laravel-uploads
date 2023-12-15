<?php

namespace Innoboxrr\LaravelUploads\Tests\Feature\Models;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Innoboxrr\LaravelUploads\Tests\TestCase;

class UploadEndpointsTest extends TestCase
{

    use RefreshDatabase,
        WithFaker;

    public function test_upload_policies_endpoint()
    {

        $upload = \Innoboxrr\LaravelUploads\Models\Upload::factory()->create();
        
        $headers = [
            'Authorization' => config('test.token'),
            'Content-Type' => 'application/json',
            'Accept' => 'application/json'
        ];  

        $payload = [
            'id' => $upload->id
        ];

        $this->json('GET', '/api/innoboxrr/laraveluploads/upload/policies', $payload, $headers)
            ->assertStatus(200);

    }

    public function test_upload_policy_endpoint()
    {
        $headers = [
            'Authorization' => config('test.token'),
            'Content-Type' => 'application/json',
            'Accept' => 'application/json'
        ];  

        $payload = [
            'policy' => 'index'
        ];

        $this->json('GET', '/api/innoboxrr/laraveluploads/upload/policy', $payload, $headers)
            ->assertJsonStructure([
                'index'
            ])
            ->assertStatus(200);

    }

    public function test_upload_index_auth_endpoint()
    {

        $headers = [
            'Authorization' => config('test.token'),
            'Content-Type' => 'application/json',
            'Accept' => 'application/json'
        ];  

        $payload = [
            'managed' => true
        ];

        $this->json('GET', '/api/innoboxrr/laraveluploads/upload/index', $payload, $headers)
            ->assertStatus(200);

    }

    public function test_upload_index_guest_endpoint()
    {

        $headers = [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json'
        ];  

        $payload = [
            'managed' => true
        ];

        $this->json('GET', '/api/innoboxrr/laraveluploads/upload/index', $payload, $headers)
            ->assertStatus(401);
            
    }
    
    public function test_upload_show_auth_endpoint()
    {

        $upload = \Innoboxrr\LaravelUploads\Models\Upload::latest()->first();

        $headers = [
            'Authorization' => config('test.token'),
            'Content-Type' => 'application/json',
            'Accept' => 'application/json'
        ];  

        $payload = [
            'upload_id' => $upload->id
        ];

        $this->json('GET', '/api/innoboxrr/laraveluploads/upload/show', $payload, $headers)
            ->assertStatus(200);
            
    }

    public function test_upload_show_guest_endpoint()
    {

        $upload = \Innoboxrr\LaravelUploads\Models\Upload::latest()->first();

        $headers = [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json'
        ];  

        $payload = [
            'upload_id' => $upload->id
        ];

        $this->json('GET', '/api/innoboxrr/laraveluploads/upload/show', $payload, $headers)
            ->assertStatus(401);
            
    }

    public function test_upload_create_endpoint()
    {

        $user = \Innoboxrr\LaravelUploads\Models\User::first();

        $headers = [
            'Authorization' => config('test.token'),
            'Content-Type' => 'application/json',
            'Accept' => 'application/json'
        ];  

        $payload = \Innoboxrr\LaravelUploads\Models\Upload::factory()->make()->getAttributes();

        $this->json('POST', '/api/innoboxrr/laraveluploads/upload/create', $payload, $headers)
            ->assertStatus(201);
            
    }

    public function test_upload_update_endpoint()
    {

        $upload = \Innoboxrr\LaravelUploads\Models\Upload::factory()->create();

        $headers = [
            'Authorization' => config('test.token'),
            'Content-Type' => 'application/json',
            'Accept' => 'application/json'
        ];  

        $payload = [
            ...\Innoboxrr\LaravelUploads\Models\Upload::factory()->make()->getAttributes(),
            'upload_id' => $upload->id
        ];

        $this->json('PUT', '/api/innoboxrr/laraveluploads/upload/update', $payload, $headers)
            ->assertStatus(200);
            
    }

    public function test_upload_delete_endpoint()
    {

        $upload = \Innoboxrr\LaravelUploads\Models\Upload::latest()->first();

        $headers = [
            'Authorization' => config('test.token'),
            'Content-Type' => 'application/json',
            'Accept' => 'application/json'
        ];  

        $payload = [
            'upload_id' => $upload->id
        ];

        $this->json('DELETE', '/api/innoboxrr/laraveluploads/upload/delete', $payload, $headers)
            ->assertStatus(200);
            
    }

    public function test_upload_restore_endpoint()
    {

        $upload = \Innoboxrr\LaravelUploads\Models\Upload::first();

        $headers = [
            'Authorization' => config('test.token'),
            'Content-Type' => 'application/json',
            'Accept' => 'application/json'
        ];  

        $payload = [
            'upload_id' => $upload->id
        ];

        $this->json('POST', '/api/innoboxrr/laraveluploads/upload/restore', $payload, $headers)
            ->assertStatus(200);
            
    }

    public function test_upload_force_delete_endpoint()
    {

        $upload = \Innoboxrr\LaravelUploads\Models\Upload::latest()->first();

        $headers = [
            'Authorization' => config('test.token'),
            'Content-Type' => 'application/json',
            'Accept' => 'application/json'
        ];  

        $payload = [
            'upload_id' => $upload->id
        ];

        $this->json('DELETE', '/api/innoboxrr/laraveluploads/upload/force-delete', $payload, $headers)
            ->assertStatus(403);
            
    }

    public function test_upload_export_endpoint()
    {   

        $headers = [
            'Authorization' => config('test.token'),
            'Content-Type' => 'application/json',
            'Accept' => 'application/json'
        ];  

        $payload = [
            //
        ];

        $this->json('POST', '/api/innoboxrr/laraveluploads/upload/export', $payload, $headers)
            ->assertStatus(200);
            
    }

}
