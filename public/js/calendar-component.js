/**
 * Calendar Component
 *
 * Componente para visualizar calendarios mes a mes.
 * Basado en HTML/JS/CSS provisto.
 */
class CalendarComponent extends UIComponent {
    constructor(id, config) {
        super(id, config);
        this.currentDate = new Date();

        // Configuración inicial
        if (this.config.year && this.config.month) {
            // Mes en JS es 0-11, asumimos que config viene 1-12 o 0-11?
            // Generalmente en backend se usa 1-12. Ajustaremos si es necesario.
            // Vamos a asumir que el backend envía 1-12.
            this.currentDate = new Date(this.config.year, this.config.month - 1, 1);
        }

        this.events = this.config.events || [];

        this.injectStyles();
    }

    injectStyles() {
        if (document.getElementById('calendar-component-styles')) return;

        const style = document.createElement('style');
        style.id = 'calendar-component-styles';
        style.textContent = `
            :root {
                --calendar-primary-color: #e67e22;
                --calendar-primary-gradient: linear-gradient(90deg, #e67e22 0%, #f39c12 100%);
                --calendar-weekend-bg: #f8f9fa;
                --calendar-weekend-text: #ccc;
                --calendar-other-month-text: #e0e0e0;

                /* Colores Eventos */
                --color-feriado: #e74c3c;
                --color-examen: #e67e22;
                --color-clases: #27ae60;
                --color-receso: #9b59b6;
                --color-admin: #f1c40f;
                --color-mensual: #2980b9;
            }

            .calendar-wrapper {
                background: white; border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.1);
                overflow: hidden; width: 100%; max-width: 700px;
                display: flex; flex-direction: column; margin-bottom: 20px;
                font-family: 'Roboto', sans-serif;
            }

            .calendar-header {
                display: flex; justify-content: space-between; align-items: center;
                padding: 15px 20px; background: var(--calendar-primary-gradient); color: white;
            }
            .calendar-header button {
                background: rgba(255,255,255,0.2); border: 1px solid rgba(255,255,255,0.4);
                color: white; padding: 5px 12px; border-radius: 4px; cursor: pointer; font-weight: bold;
            }
            .current-month { font-size: 1.3rem; font-weight: bold; text-transform: capitalize; }

            .weekdays, .days-grid {
                display: grid; grid-template-columns: 0.6fr repeat(5, 1fr) 0.6fr;
            }
            .weekdays {
                background: #f8f9fa; padding: 8px 0; text-align: center;
                font-size: 0.9rem; font-weight: bold; color: #7f8c8d; border-bottom: 1px solid #eee;
            }
            .days-grid { padding: 10px; gap: 4px; background: #fff; }

            .day {
                height: 50px; border: 1px solid #f0f0f0; border-radius: 4px;
                padding: 0; position: relative; background: white;
                display: flex; flex-direction: column;
            }
            .day:not(.weekend):not(.other-month):hover {
                border-color: var(--calendar-primary-color); cursor: pointer;
                /* Remove background hover effect as it interferes with event layers */
            }
            .day.other-month { color: var(--calendar-other-month-text); pointer-events: none; }
            .day.weekend { background-color: var(--calendar-weekend-bg); color: var(--calendar-weekend-text); pointer-events: none; }

            .day-number { font-weight: bold; font-size: 0.9rem; display: block; margin-left: 2px; }

            /* Event Dots / Bars */
            .event-dot { width: 6px; height: 6px; border-radius: 50%; display: inline-block; margin: 1px; }

            /* Colores */
            .bg-feriado { background-color: var(--color-feriado); }
            .bg-examen { background-color: var(--color-examen); }
            .bg-clases { background-color: var(--color-clases); }
            .bg-receso { background-color: var(--color-receso); }
            .bg-admin { background-color: var(--color-admin); }
            .bg-mensual { background-color: var(--color-mensual); }

            .month-events-list {
                padding: 12px 15px; border-top: 1px solid #eee; background: #fff;
                font-size: 0.85rem; color: #444; min-height: 40px;
            }
            .event-item { display: flex; align-items: center; margin-bottom: 4px; }
            .event-title { margin-left: 8px; }
        `;
        document.head.appendChild(style);
    }

    render() {
        const container = document.createElement('div');
        container.className = 'calendar-wrapper';
        this.applyCommonAttributes(container);

        // Header
        const header = document.createElement('div');
        header.className = 'calendar-header';

        const prevBtn = document.createElement('button');
        prevBtn.textContent = '«';
        prevBtn.onclick = () => this.changeMonth(-1);

        const monthDisplay = document.createElement('div');
        monthDisplay.className = 'current-month';
        this.monthDisplay = monthDisplay; // Guardar referencia

        const nextBtn = document.createElement('button');
        nextBtn.textContent = '»';
        nextBtn.onclick = () => this.changeMonth(1);

        header.appendChild(prevBtn);
        header.appendChild(monthDisplay);
        header.appendChild(nextBtn);
        container.appendChild(header);

        // Weekdays
        const weekdays = document.createElement('div');
        weekdays.className = 'weekdays';
        ['D','L','M','M','J','V','S'].forEach(d => {
            const div = document.createElement('div');
            div.textContent = d;
            weekdays.appendChild(div);
        });
        container.appendChild(weekdays);

        // Days Grid
        const daysGrid = document.createElement('div');
        daysGrid.className = 'days-grid';
        this.daysGrid = daysGrid; // Guardar referencia
        container.appendChild(daysGrid);

        // Events List
        const eventsList = document.createElement('div');
        eventsList.className = 'month-events-list';
        this.eventsList = eventsList; // Guardar referencia
        container.appendChild(eventsList);

        // Initial Render
        this.updateCalendar();

        return container;
    }

    changeMonth(delta) {
        this.currentDate.setMonth(this.currentDate.getMonth() + delta);
        this.updateCalendar();

        // Notificar al backend del cambio de mes (opcional, si queremos cargar eventos dinámicamente)
        this.sendEventToBackend('change', 'month_changed', {
            year: this.currentDate.getFullYear(),
            month: this.currentDate.getMonth() + 1
        });
    }

    updateCalendar() {
        const year = this.currentDate.getFullYear();
        const month = this.currentDate.getMonth();

        // Actualizar título
        const monthName = this.currentDate.toLocaleDateString('es-ES', { month: 'long' });
        this.monthDisplay.textContent = `${monthName.charAt(0).toUpperCase() + monthName.slice(1)} ${year}`;

        // Generar grid
        const gridData = this.generateMonthData(year, month);
        this.daysGrid.innerHTML = '';

        gridData.forEach(cell => {
            const dayEl = document.createElement('div');
            dayEl.className = 'day';

            if (cell.type === 'prev' || cell.type === 'next') {
                dayEl.classList.add('other-month');
            }

            // Calcular si es fin de semana
            // Necesitamos saber el día de la semana.
            // cell.num es el día del mes.
            // Si es 'prev', corresponde al mes anterior.
            // Si es 'next', al siguiente.
            // Simplificación: Usamos Date para saber el día de la semana
            let dateToCheck;
            if (cell.type === 'prev') {
                dateToCheck = new Date(year, month - 1, cell.num);
            } else if (cell.type === 'next') {
                dateToCheck = new Date(year, month + 1, cell.num);
            } else {
                dateToCheck = new Date(year, month, cell.num);
            }

            if (dateToCheck.getDay() === 0 || dateToCheck.getDay() === 6) {
                dayEl.classList.add('weekend');
            }

            // Check visibility config for weekends
            let showInfo = true;
            const dayOfWeek = dateToCheck.getDay();
            if (dayOfWeek === 6 && this.config.show_saturday_info === false) showInfo = false;
            if (dayOfWeek === 0 && this.config.show_sunday_info === false) showInfo = false;

            // Renderizar eventos (concentric squares)
            const eventsForDay = showInfo ? this.getEventsForDate(dateToCheck) : [];

            // Limpiar contenido previo (número) para reconstruir estructura
            dayEl.innerHTML = '';

            // Contenedor base
            let currentContainer = dayEl;

            // Si hay eventos, crear capas
            if (eventsForDay.length > 0) {
                // Ordenar eventos si es necesario (por ahora orden de llegada)
                // Crear capas concéntricas
                eventsForDay.forEach((ev, index) => {
                    const layer = document.createElement('div');
                    layer.className = `event-layer bg-${ev.type}`;
                    layer.title = ev.title;

                    // Estilo para que ocupe todo el padre
                    layer.style.width = '100%';
                    layer.style.height = '100%';
                    layer.style.display = 'flex';
                    layer.style.alignItems = 'center';
                    layer.style.justifyContent = 'center';

                    // Padding para el efecto concéntrico (el siguiente hijo será más pequeño visualmente debido al padding del padre)
                    // Pero si usamos padding en el padre, el hijo se reduce.
                    // Vamos a aplicar padding al contenedor actual antes de añadir el hijo.
                    // O mejor: el layer es el hijo, y le damos padding.
                    // No, el layer debe ser el fondo.

                    // Estrategia:
                    // dayEl (bg-event1) -> div (padding, bg-event2) -> div (padding, bg-white) -> number

                    // Pero dayEl ya tiene estilos. Mejor añadir un div hijo que ocupe 100% y tenga el color.
                    // Y dentro de ese, otro div con padding.

                    // Simplificación:
                    // El 'currentContainer' recibe el color de fondo del evento.
                    // Luego creamos un hijo con padding que será el nuevo 'currentContainer'.

                    currentContainer.classList.add(`bg-${ev.type}`);
                    currentContainer.title = (currentContainer.title ? currentContainer.title + ', ' : '') + ev.title;

                    const inner = document.createElement('div');
                    inner.style.width = '100%';
                    inner.style.height = '100%';
                    inner.style.padding = '4px'; // Grosor del borde de color
                    inner.style.boxSizing = 'border-box';

                    currentContainer.appendChild(inner);
                    currentContainer = inner;
                });
            }

            // El último contenedor debe tener fondo blanco para el número (salvo que queramos que el último evento sea el fondo del número)
            // La imagen muestra fondo blanco para el número.
            const numberContainer = document.createElement('div');
            numberContainer.style.width = '100%';
            numberContainer.style.height = '100%';
            numberContainer.style.backgroundColor = 'white';
            numberContainer.style.display = 'flex';
            numberContainer.style.alignItems = 'center';
            numberContainer.style.justifyContent = 'center';
            numberContainer.style.borderRadius = '2px'; // Opcional

            // Si no hubo eventos, currentContainer es dayEl.
            // Si hubo eventos, currentContainer es el inner del último evento.
            currentContainer.appendChild(numberContainer);

            const numSpan = document.createElement('span');
            numSpan.className = 'day-number';
            numSpan.textContent = cell.num;
            numberContainer.appendChild(numSpan);

            this.daysGrid.appendChild(dayEl);
        });

        // Actualizar lista de eventos del mes
        this.renderMonthList(year, month);
    }

    generateMonthData(year, month) {
        const firstDayOfMonth = new Date(year, month, 1);
        const daysInMonth = new Date(year, month + 1, 0).getDate();
        const padding = firstDayOfMonth.getDay();
        const prevMonthLastDate = new Date(year, month, 0).getDate();

        let grid = [];

        // Días previos
        for (let i = 0; i < padding; i++) {
            grid.push({ type: 'prev', num: prevMonthLastDate - padding + i + 1 });
        }

        // Días actuales
        for (let i = 1; i <= daysInMonth; i++) {
            grid.push({ type: 'current', num: i });
        }

        // Días siguientes (rellenar hasta 35 o 42)
        const remaining = 35 - grid.length;
        // Si remaining < 0, necesitamos 42 celdas
        const totalCells = remaining < 0 ? 42 : 35;

        while (grid.length < totalCells) {
            grid.push({ type: 'next', num: grid.length - (daysInMonth + padding) + 1 });
        }

        return grid;
    }

    getEventsForDate(date) {
        // Formato YYYY-MM-DD
        const dateStr = date.toISOString().split('T')[0];

        return this.events.filter(ev => {
            if (ev.date) return ev.date === dateStr;
            if (ev.start && ev.end) {
                return dateStr >= ev.start && dateStr <= ev.end;
            }
            return false;
        });
    }

    renderMonthList(year, month) {
        this.eventsList.innerHTML = '';

        // Filtrar eventos que ocurren en este mes
        // Simplificación: iterar todos los días del mes y recolectar eventos únicos
        const eventsInMonth = new Map(); // Key: title+type

        const daysInMonth = new Date(year, month + 1, 0).getDate();
        for(let d=1; d<=daysInMonth; d++) {
            const date = new Date(year, month, d);

            // Check visibility config
            const dayOfWeek = date.getDay();
            if (dayOfWeek === 6 && this.config.show_saturday_info === false) continue;
            if (dayOfWeek === 0 && this.config.show_sunday_info === false) continue;

            const evs = this.getEventsForDate(date);
            evs.forEach(ev => {
                const key = ev.title + '|' + ev.type;
                if (!eventsInMonth.has(key)) {
                    eventsInMonth.set(key, ev);
                }
            });
        }

        if (eventsInMonth.size === 0) {
            this.eventsList.innerHTML = '<div style="color:#999; text-align:center;">Sin actividades especiales.</div>';
            return;
        }

        eventsInMonth.forEach(ev => {
            const item = document.createElement('div');
            item.className = 'event-item';

            const dot = document.createElement('div');
            dot.className = `event-dot bg-${ev.type}`;
            dot.style.width = '10px';
            dot.style.height = '10px';
            dot.style.marginRight = '8px';

            const title = document.createElement('div');
            title.textContent = ev.title;

            item.appendChild(dot);
            item.appendChild(title);
            this.eventsList.appendChild(item);
        });
    }
}

// Exponer globalmente
window.CalendarComponent = CalendarComponent;
