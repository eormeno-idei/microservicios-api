<?php

namespace Idei\Usim\Services\Enums;

/**
 * Enum for CSS justify-content property values
 *
 * Defines how flex items are aligned along the main axis
 */
enum JustifyContent: string
{
    /**
     * Items are packed toward the start (default)
     */
    case START = 'flex-start';

    /**
     * Items are packed toward the end
     */
    case END = 'flex-end';

    /**
     * Items are centered along the main axis
     */
    case CENTER = 'center';

    /**
     * Items are evenly distributed; first item at start, last item at end
     * Perfect for: navigation bars, headers with left/right content
     */
    case SPACE_BETWEEN = 'space-between';

    /**
     * Items are evenly distributed with equal space around them
     */
    case SPACE_AROUND = 'space-around';

    /**
     * Items are evenly distributed with equal space between them
     */
    case SPACE_EVENLY = 'space-evenly';
}
