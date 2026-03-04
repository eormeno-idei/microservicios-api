<?php

namespace Database\Seeders;

use App\Models\Media;
use App\Enums\MediaType;
use Illuminate\Database\Seeder;

class MediaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        if (Media::count() > 0) {
            return;
        }

        $medias = [
            // Pantallas Físicas
            [
                'name' => 'Pantalla Principal Hall de Entrada',
                'type' => MediaType::PHYSICAL_SCREEN->value,
                'configuration' => json_encode([
                    'location' => 'Hall Principal - Planta Baja',
                    'resolution' => '1920x1080',
                    'orientation' => 'horizontal',
                    'display_time' => 15,
                ]),
                'semantic_context' => 'Información institucional, anuncios generales, eventos principales',
                'url_webhook' => 'https://display-system.example.com/api/webhook/main-hall',
                'is_active' => true,
            ],
            [
                'name' => 'Pantalla Cafetería',
                'type' => MediaType::PHYSICAL_SCREEN->value,
                'configuration' => json_encode([
                    'location' => 'Cafetería - Piso 2',
                    'resolution' => '1920x1080',
                    'orientation' => 'horizontal',
                    'display_time' => 20,
                ]),
                'semantic_context' => 'Menú, eventos sociales, actividades recreativas',
                'url_webhook' => 'https://display-system.example.com/api/webhook/cafeteria',
                'is_active' => true,
            ],
            [
                'name' => 'Pantalla Auditorio',
                'type' => MediaType::PHYSICAL_SCREEN->value,
                'configuration' => json_encode([
                    'location' => 'Auditorio Principal',
                    'resolution' => '3840x2160',
                    'orientation' => 'horizontal',
                    'display_time' => 10,
                ]),
                'semantic_context' => 'Conferencias, eventos académicos, presentaciones',
                'url_webhook' => 'https://display-system.example.com/api/webhook/auditorium',
                'is_active' => true,
            ],
            [
                'name' => 'Pantalla Biblioteca',
                'type' => MediaType::PHYSICAL_SCREEN->value,
                'configuration' => json_encode([
                    'location' => 'Biblioteca - Piso 3',
                    'resolution' => '1920x1080',
                    'orientation' => 'vertical',
                    'display_time' => 30,
                ]),
                'semantic_context' => 'Recursos bibliográficos, horarios, actividades culturales',
                'url_webhook' => 'https://display-system.example.com/api/webhook/library',
                'is_active' => true,
            ],

            // Redes Sociales
            [
                'name' => 'Facebook Institucional',
                'type' => MediaType::SOCIAL_MEDIA->value,
                'configuration' => json_encode([
                    'platform' => 'facebook',
                    'page_id' => 'institucional.oficial',
                    'access_token' => 'fb_token_placeholder',
                    'auto_publish' => true,
                ]),
                'semantic_context' => 'Comunicación institucional, eventos, noticias, comunidad',
                'url_webhook' => 'https://api.facebook.com/v18.0/webhook',
                'is_active' => true,
            ],
            [
                'name' => 'Instagram Oficial',
                'type' => MediaType::SOCIAL_MEDIA->value,
                'configuration' => json_encode([
                    'platform' => 'instagram',
                    'account_id' => '@institucion_oficial',
                    'access_token' => 'ig_token_placeholder',
                    'preferred_formats' => ['image', 'carousel', 'reels'],
                ]),
                'semantic_context' => 'Contenido visual, lifestyle institucional, estudiantes, cultura',
                'url_webhook' => 'https://graph.instagram.com/webhook',
                'is_active' => true,
            ],
            [
                'name' => 'Twitter/X Corporativo',
                'type' => MediaType::SOCIAL_MEDIA->value,
                'configuration' => json_encode([
                    'platform' => 'twitter',
                    'handle' => '@institucion',
                    'api_key' => 'twitter_key_placeholder',
                    'max_characters' => 280,
                ]),
                'semantic_context' => 'Noticias rápidas, comunicados oficiales, trending topics',
                'url_webhook' => 'https://api.twitter.com/2/webhook',
                'is_active' => true,
            ],
            [
                'name' => 'LinkedIn Corporativo',
                'type' => MediaType::SOCIAL_MEDIA->value,
                'configuration' => json_encode([
                    'platform' => 'linkedin',
                    'company_id' => 'institution-inc',
                    'access_token' => 'li_token_placeholder',
                    'content_type' => 'professional',
                ]),
                'semantic_context' => 'Contenido profesional, logros institucionales, networking',
                'url_webhook' => 'https://api.linkedin.com/v2/webhook',
                'is_active' => true,
            ],
            [
                'name' => 'YouTube Institucional',
                'type' => MediaType::SOCIAL_MEDIA->value,
                'configuration' => json_encode([
                    'platform' => 'youtube',
                    'channel_id' => 'UCinstitucion123',
                    'api_key' => 'yt_key_placeholder',
                    'default_privacy' => 'public',
                ]),
                'semantic_context' => 'Videos educativos, conferencias, eventos grabados',
                'url_webhook' => 'https://www.googleapis.com/youtube/v3/webhook',
                'is_active' => true,
            ],

            // Plataformas Editoriales
            [
                'name' => 'Portal Web Institucional',
                'type' => MediaType::EDITORIAL_PLATFORM->value,
                'configuration' => json_encode([
                    'platform' => 'wordpress',
                    'url' => 'https://www.institucion.edu',
                    'api_endpoint' => 'https://www.institucion.edu/wp-json/wp/v2',
                    'auth_type' => 'jwt',
                ]),
                'semantic_context' => 'Noticias institucionales, artículos, comunicados oficiales',
                'url_webhook' => 'https://www.institucion.edu/api/webhook',
                'is_active' => true,
            ],
            [
                'name' => 'Blog Institucional',
                'type' => MediaType::EDITORIAL_PLATFORM->value,
                'configuration' => json_encode([
                    'platform' => 'medium',
                    'publication_id' => 'institucion-oficial',
                    'api_key' => 'medium_key_placeholder',
                    'auto_publish' => false,
                ]),
                'semantic_context' => 'Artículos de opinión, investigación, contenido académico',
                'url_webhook' => 'https://api.medium.com/v1/webhook',
                'is_active' => true,
            ],
            [
                'name' => 'Newsletter Email',
                'type' => MediaType::EDITORIAL_PLATFORM->value,
                'configuration' => json_encode([
                    'platform' => 'mailchimp',
                    'list_id' => 'newsletter_main',
                    'api_key' => 'mailchimp_key_placeholder',
                    'sender_name' => 'Institución Oficial',
                    'sender_email' => 'newsletter@institucion.edu',
                ]),
                'semantic_context' => 'Newsletter semanal, actualizaciones, contenido exclusivo',
                'url_webhook' => 'https://us1.api.mailchimp.com/3.0/webhook',
                'is_active' => true,
            ],
        ];

        foreach ($medias as $mediaData) {
            Media::firstOrCreate(
                ['name' => $mediaData['name']],
                $mediaData
            );
        }

        $this->command->info('Medias seeded successfully!');
    }
}
