# AI Context: Calendar Component Implementation

## Identity
- **Component Type**: `calendar`
- **Backend Builder**: `App\Services\UI\Components\CalendarBuilder`
- **Frontend Class**: `CalendarComponent` (`public/js/calendar-component.js`)

## Backend Configuration (`CalendarBuilder`)
The builder provides a fluent interface to configure the component state.
- `year(int $year)`: Sets the initial year.
- `month(int $month)`: Sets the initial month (1-12).
- `events(array $events)`: Sets the array of event objects.
- `showSaturdayInfo(bool $show)`: Toggles visibility of event details on Saturdays.
- `showSundayInfo(bool $show)`: Toggles visibility of event details on Sundays.

## Data Structures

### Event Object
Events can be single-day or multi-day ranges.
```json
{
  "title": "Event Title",
  "type": "event_type", // Maps to CSS class .bg-{type}
  "date": "YYYY-MM-DD", // For single day
  // OR
  "start": "YYYY-MM-DD",
  "end": "YYYY-MM-DD"
}
```

### CSS Classes & Variables
- **Event Colors**: defined via CSS variables (e.g., `--color-feriado`, `--color-examen`).
- **Classes**: `.bg-{type}` applies the background color.

## Frontend Implementation Details (`calendar-component.js`)

### Rendering Logic (Concentric Squares Design)
The component uses a specific rendering strategy to visualize multiple events per day as concentric squares (nested frames).

1.  **Grid Generation**: Standard month grid with padding for previous/next month days.
2.  **Weekend Visibility**:
    - Checks `this.config.show_saturday_info` and `this.config.show_sunday_info`.
    - If `false`, events are NOT rendered for that day, only the day number.
3.  **Event Layering (The "Concentric" Logic)**:
    - Instead of sibling dots, events are rendered as **nested containers**.
    - **Structure**:
        ```html
        <div class="day">
            <!-- Layer 1 (Event A) -->
            <div class="bg-event-a" style="width:100%; height:100%; ...">
                <!-- Layer 2 (Event B) -->
                <div class="bg-event-b" style="padding: 4px; ...">
                    <!-- ... more layers ... -->
                        <!-- Center (Number) -->
                        <div style="background: white; ...">
                            <span class="day-number">15</span>
                        </div>
                </div>
            </div>
        </div>
        ```
    - **Mechanism**:
        - The loop iterates through events for the day.
        - `currentContainer` starts as the `.day` element.
        - For each event, it applies the background class to `currentContainer`.
        - It creates a new `inner` div with `padding: 4px` (this padding creates the visual "thickness" of the frame).
        - `currentContainer` updates to this new `inner` div.
        - Finally, a white container is appended to the last `currentContainer` to hold the day number, ensuring legibility.

### Key Methods
- `updateCalendar()`: Main render loop. Handles the concentric logic.
- `getEventsForDate(date)`: Filters events for a specific `Date` object.
- `renderMonthList()`: Renders the summary list below the calendar. Respects weekend visibility flags.

## Current Status & Known Constraints
- **Design**: Concentric squares implemented.
- **Interaction**: Hover effects on `.day` modified to only change `border-color` to avoid conflict with event backgrounds.
- **Weekend Logic**: Hardcoded to check `getDay() === 0` (Sun) or `6` (Sat).
- **Dependencies**: Relies on `UIComponent` base class.

## Future Work Context
- If modifying the render loop, preserve the nesting logic for the concentric effect.
- Ensure `numSpan` is not redeclared if reverting to simpler logic.
- The "white center" is crucial for readability; do not remove the final white container.
