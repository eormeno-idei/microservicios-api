<?php

namespace Database\Seeders;

use App\Models\Channel;
use App\Enums\ChannelType;
use Illuminate\Database\Seeder;

class ChannelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (Channel::count() > 10) {
            return;
        }
        $channels = [
            // Departamentos
            [
                'name' => 'Departamento de Comunicación',
                'description' => 'Responsable de la comunicación institucional y relaciones públicas',
                'type' => ChannelType::DEPARTMENT->value,
                'semantic_context' => 'Comunicación corporativa, prensa, relaciones públicas, eventos institucionales',
            ],
            [
                'name' => 'Departamento de Recursos Humanos',
                'description' => 'Gestión del talento humano y desarrollo organizacional',
                'type' => ChannelType::DEPARTMENT->value,
                'semantic_context' => 'Personal, capacitación, cultura organizacional, bienestar laboral',
            ],
            [
                'name' => 'Departamento de Sistemas',
                'description' => 'Tecnologías de la información y soporte técnico',
                'type' => ChannelType::DEPARTMENT->value,
                'semantic_context' => 'IT, infraestructura, desarrollo software, ciberseguridad',
            ],
            [
                'name' => 'Departamento de Marketing',
                'description' => 'Estrategias de marketing digital y tradicional',
                'type' => ChannelType::DEPARTMENT->value,
                'semantic_context' => 'Marketing digital, campañas, branding, publicidad',
            ],

            // Institutos
            [
                'name' => 'Instituto de Investigación Científica',
                'description' => 'Centro de investigación y desarrollo científico',
                'type' => ChannelType::INSTITUTE->value,
                'semantic_context' => 'Investigación, ciencia, desarrollo, innovación, papers académicos',
            ],
            [
                'name' => 'Instituto de Capacitación Profesional',
                'description' => 'Formación continua y desarrollo profesional',
                'type' => ChannelType::INSTITUTE->value,
                'semantic_context' => 'Educación, cursos, certificaciones, capacitación',
            ],
            [
                'name' => 'Instituto Tecnológico',
                'description' => 'Desarrollo e innovación tecnológica',
                'type' => ChannelType::INSTITUTE->value,
                'semantic_context' => 'Tecnología, innovación, transformación digital, I+D',
            ],

            // Secretarías
            [
                'name' => 'Secretaría Académica',
                'description' => 'Gestión y coordinación académica institucional',
                'type' => ChannelType::SECRETARY->value,
                'semantic_context' => 'Educación, programas académicos, estudiantes, docentes',
            ],
            [
                'name' => 'Secretaría de Extensión',
                'description' => 'Proyectos de extensión y vinculación con la comunidad',
                'type' => ChannelType::SECRETARY->value,
                'semantic_context' => 'Comunidad, proyectos sociales, vinculación, responsabilidad social',
            ],
            [
                'name' => 'Secretaría de Cultura',
                'description' => 'Promoción de actividades culturales y artísticas',
                'type' => ChannelType::SECRETARY->value,
                'semantic_context' => 'Cultura, arte, eventos culturales, patrimonio',
            ],

            // Centros
            [
                'name' => 'Centro de Innovación Digital',
                'description' => 'Hub de innovación y transformación digital',
                'type' => ChannelType::CENTER->value,
                'semantic_context' => 'Innovación, startups, emprendimiento, tecnología disruptiva',
            ],
            [
                'name' => 'Centro de Atención al Cliente',
                'description' => 'Soporte y atención a usuarios',
                'type' => ChannelType::CENTER->value,
                'semantic_context' => 'Servicio al cliente, soporte, consultas, atención',
            ],
            [
                'name' => 'Centro de Documentación',
                'description' => 'Gestión documental y biblioteca institucional',
                'type' => ChannelType::CENTER->value,
                'semantic_context' => 'Documentación, biblioteca, archivo, recursos bibliográficos',
            ],
        ];

        foreach ($channels as $channelData) {
            Channel::firstOrCreate(
                ['name' => $channelData['name']],
                $channelData
            );
        }

        $this->command->info('Canales sembrados correctamente.');
    }
}
