# AI Context: Calendar Component Implementation

## Identity
- **Component Type**: `calendar`
- **Backend Builder**: `App\Services\UI\Components\CalendarBuilder`
- **Factory Method**: `UIBuilder::calendar(?string $name)`
- **Frontend Class**: `CalendarComponent` (`public/js/calendar-component.js`)

## Backend Configuration (`CalendarBuilder`)
The builder provides a fluent interface to configure the component state.
- `year(int $year)`: Sets the initial year.
- `month(int $month)`: Sets the initial month (1-12).
- `events(array $events)`: Sets the array of event objects.
- `showSaturdayInfo(bool $show)`: Toggles visibility of event details on Saturdays.
- `showSundayInfo(bool $show)`: Toggles visibility of event details on Sundays.
- `referencesColumns(int $columns)`: Sets the number of columns for the references grid (1-3).
- `minHeight(string $height)`: Sets the minimum height of the day cells (e.g., '40px').
- `maxHeight(string $height)`: Sets the maximum height of the day cells.
- `cellSize(string $size)`: Sets a fixed width and height for the day cells (e.g., '40px'). Overrides min/max height logic and forces grid columns to match this size.

## Data Structures

### Event Object
Events can be single-day or multi-day ranges.
```json
{
  "title": "Event Title",
  "type": "event_type", // Maps to CSS class .bg-{type} and .border-{type}
  "date": "YYYY-MM-DD", // For single day
  // OR
  "start": "YYYY-MM-DD",
  "end": "YYYY-MM-DD"
}
```

### CSS Classes & Variables
- **Event Colors**: defined via CSS variables (e.g., `--color-feriado`, `--color-examen`).
- **Classes**: 
    - `.bg-{type}`: Applies background color.
    - `.border-{type}`: Applies border color.
    - `.box-{type}`: Used for legend icons.

## Frontend Implementation Details (`calendar-component.js`)

### Rendering Logic (Concentric Squares Design)
The component uses a specific rendering strategy to visualize multiple events per day as concentric squares (nested frames).

1.  **Grid Generation**: 
    - Standard month grid with padding for previous/next month days.
    - If `cellSize` is set, grid columns are fixed (`repeat(7, size)`). Otherwise, they are fluid (`repeat(7, 1fr)`).
2.  **Weekend Visibility**:
    - Checks `this.config.show_saturday_info` and `this.config.show_sunday_info`.
    - If `false`, events are NOT rendered for that day, only the day number.
3.  **Event Layering (The "Concentric" Logic)**:
    - Events are rendered as **nested containers** with thick borders.
    - **Priority Order**: Events are sorted by priority (Feriado > Examen > Mensual > Receso > Clases > Admin) to determine nesting order (outer to inner).
    - **Structure**:
        ```html
        <div class="day">
            <!-- Layer 1 (Outer Event) -->
            <div class="concentric-layer border-event-a" title="Event A">
                <!-- Layer 2 (Inner Event) -->
                <div class="concentric-layer border-event-b" title="Event B">
                    <!-- ... more layers ... -->
                        <!-- Center (Number) -->
                        <span class="num-circle-web">15</span>
                </div>
            </div>
        </div>
        ```
    - **Styling**:
        - `.day`: Compact square.
        - `.concentric-layer`: `border-width: 7px`, `border-style: solid`.
        - `.num-circle-web`: White circle (28px) containing the day number. **Crucial**: Has `flex-shrink: 0` and `min-width/height` to prevent deformation in small cells.

### Key Methods
- `updateCalendar()`: Main render loop. Handles grid generation and calls `buildConcentricLayers`.
- `buildConcentricLayers(events, dayNum)`: Generates the nested DOM structure for events.
- `getEventsForDate(date)`: Filters events for a specific `Date` object.
- `renderMonthList(year, month)`: Renders the summary list below the calendar.

### References List Logic
- **Grid Layout**: Uses CSS Grid with configurable columns (`this.config.references_columns`, default 2, max 3).
- **Date Grouping**: Consecutive dates are grouped (e.g., "1-3, 6-10").
- **Badge Visibility**: The date badge is hidden if the text string exceeds 15 characters (to handle long periods cleanly).

## Current Status & Known Constraints
- **Design**: Concentric squares with thick borders (7px).
- **Interaction**: Hover effects on `.day`.
- **Weekend Logic**: Hardcoded to check `getDay() === 0` (Sun) or `6` (Sat).
- **Dependencies**: Relies on `UIComponent` base class.

## Future Work Context
- If modifying the render loop, preserve the nesting logic (`buildConcentricLayers`).
- The "white center" (`.num-circle-web`) is crucial for readability.
