<?php

namespace App\Services\Screens;

use App\Services\UI\UIBuilder;
use App\Services\UI\AbstractUIService;
use App\Services\UI\Components\UIContainer;

class CalendarDemoService extends AbstractUIService
{
    protected function buildBaseUI(UIContainer $container, ...$params): void
    {
        $container
            ->maxWidth('600px')
            ->centerHorizontal()
            ->shadow(0)
            ->padding('30px');

        // Datos del calendario académico
        $events = [
            ['start' => '2026-03-09', 'end' => '2026-06-24', 'type' => 'clases', 'title' => '1º Cuatrimestre'],
            ['start' => '2026-08-10', 'end' => '2026-11-20', 'type' => 'clases', 'title' => '2º Cuatrimestre'],
            ['start' => '2026-07-06', 'end' => '2026-07-17', 'type' => 'receso', 'title' => 'Receso Invernal'],
            ['date' => '2026-04-02', 'type' => 'feriado', 'title' => 'Malvinas / Jueves Santo'],
            ['date' => '2026-04-03', 'type' => 'feriado', 'title' => 'Viernes Santo'],
            ['date' => '2026-05-01', 'type' => 'feriado', 'title' => 'Día del Trabajador'],
            ['date' => '2026-05-15', 'type' => 'feriado', 'title' => 'Asueto Docente'],
            ['date' => '2026-05-25', 'type' => 'feriado', 'title' => 'Revolución de Mayo'],
            ['date' => '2026-06-13', 'type' => 'feriado', 'title' => 'Fundación San Juan'],
            ['date' => '2026-06-15', 'type' => 'feriado', 'title' => 'Gral. Güemes (Trasl.)'],
            ['date' => '2026-06-20', 'type' => 'feriado', 'title' => 'Día de la Bandera'],
            ['date' => '2026-08-17', 'type' => 'feriado', 'title' => 'Gral. San Martín'],
            ['date' => '2026-09-11', 'type' => 'feriado', 'title' => 'Día del Maestro'],
            ['date' => '2026-10-10', 'type' => 'feriado', 'title' => 'Aniversario UNSJ'],
            ['date' => '2026-10-12', 'type' => 'feriado', 'title' => 'Div. Cultural'],
            ['date' => '2026-11-23', 'type' => 'feriado', 'title' => 'Soberanía (Trasl.)'],
            ['date' => '2026-11-26', 'type' => 'feriado', 'title' => 'Día No Docente'],
            ['date' => '2026-12-07', 'type' => 'feriado', 'title' => 'Mujer Universitaria'],
            ['date' => '2026-12-08', 'type' => 'feriado', 'title' => 'Inmaculada Concepción'],
            ['date' => '2026-12-25', 'type' => 'feriado', 'title' => 'Navidad'],
            ['date' => '2027-01-01', 'type' => 'feriado', 'title' => 'Año Nuevo'],
            ['date' => '2027-02-15', 'type' => 'feriado', 'title' => 'Carnaval'],
            ['date' => '2027-02-16', 'type' => 'feriado', 'title' => 'Carnaval'],
            ['date' => '2027-03-24', 'type' => 'feriado', 'title' => 'Memoria y Justicia'],
            ['start' => '2026-06-29', 'end' => '2026-07-04', 'type' => 'examen', 'title' => 'Mesa Ordinaria'],
            ['start' => '2026-07-27', 'end' => '2026-08-01', 'type' => 'examen', 'title' => 'Mesa Ordinaria'],
            ['start' => '2026-08-10', 'end' => '2026-08-14', 'type' => 'examen', 'title' => 'Mesa Ordinaria'],
            ['start' => '2026-11-23', 'end' => '2026-11-28', 'type' => 'examen', 'title' => 'Mesa Ordinaria'],
            ['start' => '2026-11-30', 'end' => '2026-12-05', 'type' => 'examen', 'title' => 'Mesa Ordinaria'],
            ['start' => '2026-12-14', 'end' => '2026-12-19', 'type' => 'examen', 'title' => 'Mesa Ordinaria'],
            ['start' => '2027-02-22', 'end' => '2027-02-27', 'type' => 'examen', 'title' => 'Mesa Ordinaria'],
            ['start' => '2027-03-01', 'end' => '2027-03-06', 'type' => 'examen', 'title' => 'Mesa Ordinaria'],
            ['start' => '2027-03-15', 'end' => '2027-03-20', 'type' => 'examen', 'title' => 'Mesa Ordinaria'],
            ['start' => '2026-05-26', 'end' => '2026-06-01', 'type' => 'examen', 'title' => 'Mesa Extra'],
            ['start' => '2026-10-26', 'end' => '2026-10-31', 'type' => 'examen', 'title' => 'Mesa Extra'],
            ['start' => '2026-04-20', 'end' => '2026-04-25', 'type' => 'mensual', 'title' => 'Mesa Mensual'],
            ['start' => '2026-09-22', 'end' => '2026-09-28', 'type' => 'mensual', 'title' => 'Mesa Mensual'],
            ['start' => '2026-03-25', 'end' => '2026-04-10', 'type' => 'admin', 'title' => 'Inscrip. Reválidas'],
            ['date' => '2026-04-24', 'type' => 'admin', 'title' => 'Límite Reválidas'],
            ['start' => '2026-08-03', 'end' => '2026-08-28', 'type' => 'admin', 'title' => 'Inscrip. 2º Cuat.']
        ];

        $container->add(
            UIBuilder::calendar('academic_calendar')
                ->year(2026)
                ->month(4)
                ->events($events)
                ->showSaturdayInfo(false)
                ->showSundayInfo(false)
                ->cellSize('60px')
                ->eventBorderRadius('50%')
                ->numberStyle([
                    'font_size' => '13px',
                    'background_color' => '#ffffff',
                    'color' => '#333333',
                    'box_shadow' => 'none'
                ])
                ->borderRadius('2px')
        );
    }

    /**
     * Handle month change event
     *
     * @param array $params Contains 'year' and 'month'
     */
    public function onMonthChanged(array $params): void
    {
        // Aquí podríamos cargar eventos dinámicamente para el nuevo mes/año
        // Por ahora, solo confirmamos la acción para evitar el error 404

        // $year = $params['year'];
        // $month = $params['month'];
        // $this->toast("Navegando a $month/$year", 'info');
    }
}
