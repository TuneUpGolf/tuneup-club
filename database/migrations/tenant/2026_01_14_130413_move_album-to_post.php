<?php

use App\Models\Post;
use App\Models\Album;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $albums = Album::all();

        foreach ($albums as $album) {
            Post::create([
                'influencer_id' => $album->instructor_id,
                'tenant_id' => $album->tenant_id,
                'title' => $album->title,
                'description' => $album->description,
                'file' => $album->media,
                'file_type' => $album->file_type,
                'status' => $album->status,
                'column_order' => $album->column_order ?? 0,
                'created_at' => $album->created_at,
                'updated_at' => $album->updated_at,
                'slug' => $album->slug,
                'album_category_id' => $album->album_category_id,
                'follower_id' => null,
                'paid' => 0,
                'price' => '0.00',
                'isFollowerPost' => 0,
                'short_description' => substr($album->description ?? '', 0, 100),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
};
