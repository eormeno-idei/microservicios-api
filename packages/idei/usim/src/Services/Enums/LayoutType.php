<?php

namespace Idei\Usim\Services\Enums;

enum LayoutType: string
{
    case VERTICAL = 'vertical';
    case HORIZONTAL = 'horizontal';
    case GRID = 'grid';
    case FLEX = 'flex';
}