<?php

namespace App\Services\Screens;

use Idei\Usim\Services\UIBuilder;
use Idei\Usim\Services\AbstractUIService;
use Idei\Usim\Services\Components\UIContainer;
use Idei\Usim\Services\Components\CalendarBuilder;

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
        $this->academic_calendar->events($monthEvents);
    }
}
