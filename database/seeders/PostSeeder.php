<?php

namespace Database\Seeders;

use App\Models\Post;
use App\Models\User;
use App\Models\Media;
use App\Enums\PostType;
use App\Models\Channel;
use App\Enums\PostStatus;
use Illuminate\Support\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class PostSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        if (Post::count() > 0) {
            return;
        }

        // 1. Obtener usuarios
        $adminUser = User::role('admin')->first();
        $regularUsers = User::role('user')->limit(5)->get();

        // 2. Validar usuarios
        if (!$adminUser || $regularUsers->isEmpty()) {
            $this->command->warn('No users found...');
            return;
        }

        // 3. Obtener canales y medios
        $channels = Channel::all();
        $medias = Media::where('is_active', true)->get();

        // 4. Validar canales
        if ($channels->isEmpty()) {
            $this->command->warn('No channels found...');
            return;
        }

        // 5. Definir array de 11 posts
        $posts = [
            // POST 1: TEXT - Convocatoria
            [
                'user_id' => $adminUser->id,
                'name' => 'Convocatoria: Conferencia Internacional 2025',
                'content' => 'Nos complace invitarlos a la Conferencia Internacional de Investigación Científica 2025. Este evento reunirá a destacados investigadores, académicos y estudiantes de diversas disciplinas. Plazo de presentación de ponencias: 30 de noviembre.',
                'type' => PostType::TEXT->value,
                'status' => PostStatus::APPROVED_BY_MODERATOR->value,
                'moderator_comments' => 'Aprobado para publicación inmediata',
                'scheduled_at' => Carbon::now()->addDays(2),
                'published_at' => null,
                'deadline' => Carbon::now()->addMonths(1),
                'timeout' => Carbon::now()->addMonths(2),
            ],

            // POST 2: TEXT - Curso
            [
                'user_id' => $regularUsers->random()->id,
                'name' => 'Curso: Inteligencia Artificial Aplicada',
                'content' => 'La Facultad de Ingeniería presenta el curso de especialización en Inteligencia Artificial Aplicada. Inicio: 5 de noviembre. Duración: 8 semanas. Modalidad híbrida. Cupos limitados. Inscripciones abiertas hasta el 1 de noviembre.',
                'type' => PostType::TEXT->value,
                'status' => PostStatus::APPROVED_BY_MODERATOR->value,
                'moderator_comments' => 'Curso aprobado para publicación',
                'scheduled_at' => null,
                'published_at' => Carbon::now()->subDays(3),
                'deadline' => Carbon::now()->addDays(10),
                'timeout' => Carbon::now()->addMonths(3),
            ],

            // POST 3: TEXT - Noticia
            [
                'user_id' => $adminUser->id,
                'name' => 'Noticia: Premio Nacional a Investigadores',
                'content' => 'Tres investigadores de nuestra universidad han sido galardonados con el Premio Nacional de Investigación Científica por sus aportes en biotecnología, energías renovables y ciencias sociales. La ceremonia de premiación se llevará a cabo el próximo 15 de noviembre.',
                'type' => PostType::TEXT->value,
                'status' => PostStatus::APPROVED_BY_MODERATOR->value,
                'moderator_comments' => 'Excelente noticia para compartir',
                'scheduled_at' => null,
                'published_at' => Carbon::now()->subDays(1),
                'deadline' => null,
                'timeout' => Carbon::now()->addMonths(1),
            ],

            // POST 4: IMAGE - Infografía
            [
                'user_id' => $regularUsers->random()->id,
                'name' => 'Infografía: Proceso de Matrícula 2026',
                'content' => 'Guía visual completa del proceso de matrícula para el semestre académico 2026-I. Incluye fechas importantes, requisitos, documentación necesaria y pasos a seguir. Consulta toda la información detallada en esta infografía.',
                'type' => PostType::IMAGE->value,
                'status' => PostStatus::APPROVED_BY_MODERATOR->value,
                'moderator_comments' => 'Infografía revisada y aprobada',
                'scheduled_at' => null,
                'published_at' => Carbon::now()->subDays(5),
                'deadline' => Carbon::now()->addDays(20),
                'timeout' => Carbon::now()->addMonths(2),
            ],

            // POST 5: IMAGE - Afiche evento
            [
                'user_id' => $adminUser->id,
                'name' => 'Feria de Ciencias y Tecnología 2025',
                'content' => 'Te invitamos a la XV Feria de Ciencias y Tecnología. Proyectos innovadores de estudiantes, demostraciones en vivo, competencias de robótica y conferencias magistrales. Fecha: 10-12 de noviembre. Entrada libre.',
                'type' => PostType::IMAGE->value,
                'status' => PostStatus::APPROVED_BY_MODERATOR->value,
                'moderator_comments' => 'Afiche aprobado',
                'scheduled_at' => Carbon::now()->addDays(1),
                'published_at' => null,
                'deadline' => Carbon::now()->addDays(15),
                'timeout' => Carbon::now()->addDays(20),
            ],

            // POST 6: VIDEO - Conferencia
            [
                'user_id' => $regularUsers->random()->id,
                'name' => 'Conferencia: Innovación en Educación Superior',
                'content' => 'Grabación completa de la conferencia magistral del Dr. Miguel Rodríguez sobre "Innovación y Transformación Digital en la Educación Superior". Organizado por el Vicerrectorado Académico. Disponible para toda la comunidad universitaria.',
                'type' => PostType::VIDEO->value,
                'status' => PostStatus::APPROVED_BY_MODERATOR->value,
                'moderator_comments' => 'Video aprobado para publicación',
                'scheduled_at' => null,
                'published_at' => Carbon::now()->subDays(7),
                'deadline' => null,
                'timeout' => Carbon::now()->addMonths(6),
            ],

            // POST 7: VIDEO - Tutorial
            [
                'user_id' => $adminUser->id,
                'name' => 'Tutorial: Uso del Campus Virtual',
                'content' => 'Video tutorial completo sobre cómo utilizar todas las funcionalidades del nuevo Campus Virtual: acceso a materiales, entrega de trabajos, foros de discusión, evaluaciones en línea y comunicación con docentes. Ideal para estudiantes nuevos.',
                'type' => PostType::VIDEO->value,
                'status' => PostStatus::SCHEDULED->value,
                'moderator_comments' => 'Material didáctico aprobado - programado',
                'scheduled_at' => Carbon::now()->addDays(5),
                'published_at' => null,
                'deadline' => null,
                'timeout' => null,
            ],

            // POST 8: AUDIO - Podcast
            [
                'user_id' => $regularUsers->random()->id,
                'name' => 'Podcast: Voces Universitarias - Emprendimiento',
                'content' => 'Episodio 12 de nuestro podcast institucional. En esta ocasión conversamos con egresados que han creado exitosas startups. Comparten sus experiencias, desafíos y consejos para estudiantes interesados en el emprendimiento.',
                'type' => PostType::AUDIO->value,
                'status' => PostStatus::DRAFT->value,
                'moderator_comments' => null,
                'scheduled_at' => null,
                'published_at' => null,
                'deadline' => null,
                'timeout' => Carbon::now()->addMonths(12),
            ],

            // POST 9: MULTIMEDIA - Evento
            [
                'user_id' => $adminUser->id,
                'name' => 'Semana Cultural 2025: Celebrando Nuestra Diversidad',
                'content' => 'Del 25 al 29 de noviembre celebramos nuestra tradicional Semana Cultural con exposiciones, presentaciones artísticas, concursos, gastronomía típica y mucho más. Galería de fotos, videos y transmisiones en vivo disponibles.',
                'type' => PostType::MULTIMEDIA->value,
                'status' => PostStatus::SCHEDULED->value,
                'moderator_comments' => 'Material multimedia verificado',
                'scheduled_at' => Carbon::now()->addDays(3),
                'published_at' => null,
                'deadline' => Carbon::now()->addDays(30),
                'timeout' => Carbon::now()->addDays(35),
            ],

            // POST 10: MULTIMEDIA - Inducción
            [
                'user_id' => $regularUsers->random()->id,
                'name' => 'Inducción Virtual: Bienvenida a Nuevos Estudiantes',
                'content' => 'Paquete completo de inducción para estudiantes ingresantes: videos explicativos, presentaciones interactivas, tour virtual del campus, información sobre servicios estudiantiles, vida universitaria y recursos académicos.',
                'type' => PostType::MULTIMEDIA->value,
                'status' => PostStatus::APPROVED_BY_MODERATOR->value,
                'moderator_comments' => 'Material de inducción aprobado',
                'scheduled_at' => null,
                'published_at' => Carbon::now()->subDays(10),
                'deadline' => null,
                'timeout' => Carbon::now()->addMonths(4),
            ],

            // POST 11: ARCHIVED - Convocatoria antigua
            [
                'user_id' => $adminUser->id,
                'name' => 'Convocatoria: Becas de Investigación 2024',
                'content' => 'Convocatoria para becas de investigación destinadas a estudiantes de pregrado y posgrado durante el año académico 2024. Plazo vencido. Resultados ya publicados en el portal institucional.',
                'type' => PostType::TEXT->value,
                'status' => PostStatus::ARCHIVED->value,
                'moderator_comments' => 'Convocatoria finalizada - archivado',
                'scheduled_at' => null,
                'published_at' => Carbon::now()->subMonths(6),
                'deadline' => Carbon::now()->subMonths(3),
                'timeout' => Carbon::now()->subMonths(2),
            ],
        ];

        // 6. Procesar cada post
        foreach ($posts as $postData) {
            $post = Post::firstOrCreate(
                ['name' => $postData['name'], 'user_id' => $postData['user_id']],
                $postData
            );

            // 7. Establecer relaciones N:M
            if ($post->wasRecentlyCreated) {
                // Asignar entre 1 y 3 canales aleatorios a cada post
                $randomChannels = $channels->random(rand(1, min(3, $channels->count())));
                $post->channels()->attach($randomChannels->pluck('id'));

                // Asignar entre 1 y 4 medios aleatorios a cada post (solo los activos)
                if ($medias->isNotEmpty()) {
                    $randomMedias = $medias->random(rand(1, min(4, $medias->count())));
                    $post->medias()->attach($randomMedias->pluck('id'));
                }
            }
        }

        $this->command->info('Posts seeded successfully with relationships!');
    }
}
