<?php

namespace App\Services\Screens;

use App\Services\UI\UIBuilder;
use App\Services\UI\AbstractUIService;
use App\Services\UI\Components\UIContainer;
use App\Services\UI\Components\CalendarBuilder;

class CalendarDemoService extends AbstractUIService
{
    protected CalendarBuilder $academic_calendar;

    protected function buildBaseUI(UIContainer $container, ...$params): void
    {
        $container
            ->maxWidth('600px')
            ->centerHorizontal()
            ->shadow(0)
            ->padding('30px');

        $container->add(
            UIBuilder::calendar('academic_calendar')
                ->year(2026)
                ->month(4)
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
        $year = $params['year'];
        $month = $params['month'];
        $monthEvents = CalendarioAcadémico::getMonthEvents($year, $month);
        // Only for demonstration, if month changed is 5/2026,
        // set an event in the 5th
        if ($year === 2026 && $month === 5) {
            $monthEvents[] = [
                'date' => '2026-05-05',
                'type' => 'special',
                'title' => 'Mi Cumpleaños!',
            ];
        }
        $this->academic_calendar->events($monthEvents);

    }
}
