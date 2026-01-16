<?php

namespace Idei\Usim\Services\Enums;

/**
 * Enum for CSS align-items property values
 *
 * Defines how flex items are aligned along the cross axis
 */
enum AlignItems: string
{
    /**
     * Items are placed at the start of the cross axis
     */
    case START = 'flex-start';

    /**
     * Items are placed at the end of the cross axis
     */
    case END = 'flex-end';

    /**
     * Items are centered along the cross axis
     */
    case CENTER = 'center';

    /**
     * Items are aligned along their baselines
     */
    case BASELINE = 'baseline';

    /**
     * Items stretch to fill the container (default)
     */
    case STRETCH = 'stretch';
}
