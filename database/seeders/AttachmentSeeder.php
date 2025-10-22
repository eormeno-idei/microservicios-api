<?php

namespace Database\Seeders;

use App\Models\Post;
use App\Models\Attachment;
use App\Enums\PostType;
use Illuminate\Database\Seeder;

class AttachmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener posts que necesitan attachments según su tipo
        $imagePosts = Post::where('type', PostType::IMAGE->value)->get();
        $videoPosts = Post::where('type', PostType::VIDEO->value)->get();
        $audioPosts = Post::where('type', PostType::AUDIO->value)->get();
        $multimediaPosts = Post::where('type', PostType::MULTIMEDIA->value)->get();

        // Attachments para posts de imagen
        foreach ($imagePosts as $post) {
            $attachments = [
                [
                    'post_id' => $post->id,
                    'mime_type' => 'image/jpeg',
                    'path' => 'storage/posts/' . $post->id . '/images/main_image.jpg',
                ],
                [
                    'post_id' => $post->id,
                    'mime_type' => 'image/png',
                    'path' => 'storage/posts/' . $post->id . '/images/thumbnail.png',
                ],
            ];

            foreach ($attachments as $attachmentData) {
                Attachment::firstOrCreate(
                    [
                        'post_id' => $attachmentData['post_id'],
                        'path' => $attachmentData['path']
                    ],
                    $attachmentData
                );
            }
        }

        // Attachments para posts de video
        foreach ($videoPosts as $post) {
            $attachments = [
                [
                    'post_id' => $post->id,
                    'mime_type' => 'video/mp4',
                    'path' => 'storage/posts/' . $post->id . '/videos/main_video.mp4',
                ],
                [
                    'post_id' => $post->id,
                    'mime_type' => 'image/jpeg',
                    'path' => 'storage/posts/' . $post->id . '/videos/thumbnail.jpg',
                ],
                [
                    'post_id' => $post->id,
                    'mime_type' => 'text/vtt',
                    'path' => 'storage/posts/' . $post->id . '/videos/subtitles_es.vtt',
                ],
            ];

            foreach ($attachments as $attachmentData) {
                Attachment::firstOrCreate(
                    [
                        'post_id' => $attachmentData['post_id'],
                        'path' => $attachmentData['path']
                    ],
                    $attachmentData
                );
            }
        }

        // Attachments para posts de audio
        foreach ($audioPosts as $post) {
            $attachments = [
                [
                    'post_id' => $post->id,
                    'mime_type' => 'audio/mpeg',
                    'path' => 'storage/posts/' . $post->id . '/audio/podcast.mp3',
                ],
                [
                    'post_id' => $post->id,
                    'mime_type' => 'image/jpeg',
                    'path' => 'storage/posts/' . $post->id . '/audio/cover.jpg',
                ],
            ];

            foreach ($attachments as $attachmentData) {
                Attachment::firstOrCreate(
                    [
                        'post_id' => $attachmentData['post_id'],
                        'path' => $attachmentData['path']
                    ],
                    $attachmentData
                );
            }
        }

        // Attachments para posts multimedia
        foreach ($multimediaPosts as $post) {
            $attachments = [
                [
                    'post_id' => $post->id,
                    'mime_type' => 'image/jpeg',
                    'path' => 'storage/posts/' . $post->id . '/multimedia/banner.jpg',
                ],
                [
                    'post_id' => $post->id,
                    'mime_type' => 'video/mp4',
                    'path' => 'storage/posts/' . $post->id . '/multimedia/promo.mp4',
                ],
                [
                    'post_id' => $post->id,
                    'mime_type' => 'application/pdf',
                    'path' => 'storage/posts/' . $post->id . '/multimedia/program.pdf',
                ],
                [
                    'post_id' => $post->id,
                    'mime_type' => 'image/png',
                    'path' => 'storage/posts/' . $post->id . '/multimedia/infographic.png',
                ],
            ];

            foreach ($attachments as $attachmentData) {
                Attachment::firstOrCreate(
                    [
                        'post_id' => $attachmentData['post_id'],
                        'path' => $attachmentData['path']
                    ],
                    $attachmentData
                );
            }
        }

        $this->command->info('Attachments seeded successfully!');
    }
}
