<?php

namespace Database\Seeders;

use App\Models\Post;
use App\Models\User;
use App\Models\Channel;
use App\Models\Media;
use App\Enums\PostType;
use App\Enums\PostStatus;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class PostSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener usuarios con rol admin y user
        $adminUser = User::role('admin')->first();
        $regularUsers = User::role('user')->limit(5)->get();

        if (!$adminUser || $regularUsers->isEmpty()) {
            $this->command->warn('No users found. Please run UserSeeder first.');
            return;
        }

        // Obtener canales y medios
        $channels = Channel::all();
        $medias = Media::where('is_active', true)->get();

        if ($channels->isEmpty()) {
            $this->command->warn('No channels found. Please run ChannelSeeder first.');
            return;
        }

        $posts = [
            // Posts de texto
            [
                'user_id' => $adminUser->id,
                'name' => 'Convocatoria: Conferencia Internacional 2025',
                'content' => 'Nos complace invitarlos a la Conferencia Internacional de Innovación y Tecnología 2025. El evento se realizará del 15 al 17 de marzo en nuestro auditorio principal. Inscripciones abiertas hasta el 28 de febrero. ¡No te lo pierdas!',
                'type' => PostType::TEXT->value,
                'status' => PostStatus::APPROVED_BY_MODERATOR->value,
                'moderator_comments' => 'Aprobado para publicación inmediata',
                'scheduled_at' => Carbon::now()->addDays(2),
                'published_at' => null,
                'deadline' => Carbon::now()->addMonths(1),
                'timeout' => Carbon::now()->addMonths(2),
            ],
            [
                'user_id' => $regularUsers->random()->id,
                'name' => 'Resultados del Concurso de Innovación',
                'content' => 'Felicitamos a todos los participantes del Concurso de Innovación 2024. Los proyectos ganadores serán anunciados en ceremonia especial el próximo viernes. Más de 50 proyectos participaron este año.',
                'type' => PostType::TEXT->value,
                'status' => PostStatus::SCHEDULED->value,
                'moderator_comments' => 'Revisar lista de ganadores antes de publicar',
                'scheduled_at' => Carbon::now()->addDays(5),
                'published_at' => null,
                'deadline' => Carbon::now()->addDays(4),
                'timeout' => Carbon::now()->addDays(10),
            ],
            [
                'user_id' => $adminUser->id,
                'name' => 'Nuevo horario de atención biblioteca',
                'content' => 'A partir del 1 de noviembre, la biblioteca extenderá su horario de atención. Lunes a viernes: 7:00 AM - 10:00 PM. Sábados: 9:00 AM - 6:00 PM. Domingos: cerrado.',
                'type' => PostType::TEXT->value,
                'status' => PostStatus::DRAFT->value,
                'moderator_comments' => null,
                'scheduled_at' => null,
                'published_at' => null,
                'deadline' => Carbon::now()->addDays(7),
                'timeout' => null,
            ],

            // Posts de imagen
            [
                'user_id' => $regularUsers->random()->id,
                'name' => 'Campaña de Reciclaje Institucional',
                'content' => 'Únete a nuestra campaña de reciclaje. Juntos podemos hacer la diferencia. Infografía con puntos de recolección y materiales aceptados.',
                'type' => PostType::IMAGE->value,
                'status' => PostStatus::APPROVED_BY_MODERATOR->value,
                'moderator_comments' => 'Excelente diseño, aprobado',
                'scheduled_at' => Carbon::now()->addDays(1),
                'published_at' => null,
                'deadline' => Carbon::now()->addWeeks(2),
                'timeout' => Carbon::now()->addMonths(1),
            ],
            [
                'user_id' => $regularUsers->random()->id,
                'name' => 'Graduación 2024 - Fotos Oficiales',
                'content' => 'Álbum oficial de la ceremonia de graduación 2024. Felicitamos a todos nuestros egresados. #Graduación2024 #Orgullo',
                'type' => PostType::IMAGE->value,
                'status' => PostStatus::APPROVED_BY_MODERATOR->value,
                'moderator_comments' => 'Publicar en todas las redes sociales',
                'scheduled_at' => Carbon::now(),
                'published_at' => Carbon::now(),
                'deadline' => null,
                'timeout' => null,
            ],

            // Posts de video
            [
                'user_id' => $adminUser->id,
                'name' => 'Tour Virtual por nuestras instalaciones',
                'content' => 'Conoce nuestras modernas instalaciones en este tour virtual de 360 grados. Laboratorios, aulas, áreas recreativas y más. Duración: 5 minutos.',
                'type' => PostType::VIDEO->value,
                'status' => PostStatus::APPROVED_BY_MODERATOR->value,
                'moderator_comments' => 'Video de alta calidad, aprobado',
                'scheduled_at' => Carbon::now()->addDays(3),
                'published_at' => null,
                'deadline' => Carbon::now()->addWeeks(1),
                'timeout' => Carbon::now()->addMonths(3),
            ],
            [
                'user_id' => $regularUsers->random()->id,
                'name' => 'Entrevista al Director de Investigación',
                'content' => 'El Dr. Carlos Mendoza nos cuenta sobre los nuevos proyectos de investigación y las alianzas estratégicas para 2025. Una visión del futuro de nuestra institución.',
                'type' => PostType::VIDEO->value,
                'status' => PostStatus::SCHEDULED->value,
                'moderator_comments' => 'Pendiente agregar subtítulos',
                'scheduled_at' => Carbon::now()->addWeek(),
                'published_at' => null,
                'deadline' => Carbon::now()->addDays(5),
                'timeout' => Carbon::now()->addMonths(1),
            ],

            // Posts de audio
            [
                'user_id' => $regularUsers->random()->id,
                'name' => 'Podcast: Historias de Éxito - Episodio 1',
                'content' => 'En este primer episodio entrevistamos a María González, exalumna y ahora emprendedora exitosa. Nos cuenta su historia y da consejos para futuros profesionales.',
                'type' => PostType::AUDIO->value,
                'status' => PostStatus::APPROVED_BY_MODERATOR->value,
                'moderator_comments' => 'Excelente contenido',
                'scheduled_at' => Carbon::now()->addDays(2),
                'published_at' => null,
                'deadline' => null,
                'timeout' => null,
            ],

            // Posts multimedia
            [
                'user_id' => $adminUser->id,
                'name' => 'Jornada de Puertas Abiertas 2025',
                'content' => 'Te invitamos a nuestra Jornada de Puertas Abiertas. Incluye: recorrido por instalaciones, charlas informativas, demostraciones en vivo y asesoramiento personalizado. Material multimedia completo disponible.',
                'type' => PostType::MULTIMEDIA->value,
                'status' => PostStatus::APPROVED_BY_MODERATOR->value,
                'moderator_comments' => 'Campaña integral aprobada',
                'scheduled_at' => Carbon::now()->addDays(7),
                'published_at' => null,
                'deadline' => Carbon::now()->addDays(6),
                'timeout' => Carbon::now()->addWeeks(3),
            ],
            [
                'user_id' => $regularUsers->random()->id,
                'name' => 'Semana de la Ciencia y Tecnología',
                'content' => 'Del 10 al 14 de noviembre celebramos la Semana de la Ciencia. Talleres, conferencias, experimentos en vivo y mucho más. Contenido multimedia con programa completo.',
                'type' => PostType::MULTIMEDIA->value,
                'status' => PostStatus::SCHEDULED->value,
                'moderator_comments' => 'Coordinar con departamento de comunicación',
                'scheduled_at' => Carbon::now()->addDays(15),
                'published_at' => null,
                'deadline' => Carbon::now()->addDays(14),
                'timeout' => Carbon::now()->addWeeks(4),
            ],

            // Posts archivados
            [
                'user_id' => $regularUsers->random()->id,
                'name' => 'Convocatoria Becas 2024 - Finalizada',
                'content' => 'Convocatoria para becas de investigación 2024. Esta convocatoria ya ha finalizado. Los resultados fueron publicados en septiembre.',
                'type' => PostType::TEXT->value,
                'status' => PostStatus::ARCHIVED->value,
                'moderator_comments' => 'Convocatoria cerrada y finalizada',
                'scheduled_at' => Carbon::now()->subMonths(3),
                'published_at' => Carbon::now()->subMonths(3),
                'deadline' => Carbon::now()->subMonth(),
                'timeout' => Carbon::now()->subWeek(),
            ],
        ];

        foreach ($posts as $postData) {
            // Verificar si el post ya existe por nombre
            $post = Post::firstOrCreate(
                [
                    'name' => $postData['name'],
                    'user_id' => $postData['user_id']
                ],
                $postData
            );

            // Asociar canales aleatorios (entre 1 y 3 canales)
            if ($post->wasRecentlyCreated && $channels->isNotEmpty()) {
                $randomChannels = $channels->random(rand(1, min(3, $channels->count())));
                $post->channels()->sync($randomChannels->pluck('id')->toArray());
            }

            // Asociar medios aleatorios (entre 1 y 4 medios)
            if ($post->wasRecentlyCreated && $medias->isNotEmpty()) {
                $randomMedias = $medias->random(rand(1, min(4, $medias->count())));
                $post->medias()->sync($randomMedias->pluck('id')->toArray());
            }
        }

        $this->command->info('Posts seeded successfully with relationships!');
    }
}
