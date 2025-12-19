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
                --calendar-primary-color: #3b5585;
                --calendar-primary-gradient: linear-gradient(90deg, #3b5585 0%, #566f9e 100%);
                --calendar-secondary-color: #707070;
                --calendar-bg-color: #f4f7f6;
                --calendar-text-color: #333;

                /* Colores Eventos */
                --color-feriado: #e74c3c;
                --color-examen: #e67e22;
                --color-clases: #27ae60;
                --color-receso: #9b59b6;
                --color-admin: #f1c40f;
                --color-mensual: #1abc9c;

                /* Estilos */
                --calendar-weekend-bg: #f8f9fa;
                --calendar-weekend-text: #ccc;
                --calendar-other-month-text: #e0e0e0;
            }

            .calendar-wrapper {
                background: white;
                border-radius: 12px;
                box-shadow: none;
                border: 1px solid #e0e0e0;
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
                align-items: center;
            }
            .weekdays div { display: flex; justify-content: center; align-items: center; width: 100%; }

            .days-grid { padding: 10px; gap: 4px; background: #fff; }

            .day {
                aspect-ratio: 1 / 1;
                min-height: 30px;
                border: 1px solid #f0f0f0; border-radius: 4px;
                position: relative; background: white;
                display: flex; justify-content: center; align-items: center; padding: 1px;
            }
            .day:not(.weekend):not(.other-month):hover {
                background-color: #ebf5fb; border-color: var(--calendar-primary-color); cursor: pointer; z-index: 10;
            }
            .day.other-month { color: var(--calendar-other-month-text); pointer-events: none; }
            .day.weekend { background-color: var(--calendar-weekend-bg); color: var(--calendar-weekend-text); pointer-events: none; }

            /* Capas Concéntricas */
            .concentric-layer {
                width: 100%; height: 100%; box-sizing: border-box;
                display: flex; justify-content: center; align-items: center;
                border-style: solid; background-color: white;
                border-width: 7px;
            }

            .num-circle-web {
                width: 28px; height: 28px;
                background: white; color: black; border-radius: 50%;
                display: flex; align-items: center; justify-content: center;
                font-size: 1.1rem; font-weight: 800;
                box-shadow: 0 1px 2px rgba(0,0,0,0.3); z-index: 2;
            }

            /* Bordes de Eventos */
            .border-feriado { border-color: var(--color-feriado); }
            .border-examen { border-color: var(--color-examen); }
            .border-clases { border-color: var(--color-clases); }
            .border-receso { border-color: var(--color-receso); }
            .border-admin { border-color: var(--color-admin); }
            .border-mensual { border-color: var(--color-mensual); }

            /* Lista de Eventos */
            .month-events-list {
                padding: 12px 15px; border-top: 1px solid #eee; background: #fff;
                font-size: 0.85rem; color: #444; min-height: 40px;
            }
            .references-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 8px 20px; }
            .event-item { display: flex; align-items: center; line-height: 1.3; }
            .event-date-badge {
                background-color: #eff6ff; color: var(--calendar-primary-color);
                padding: 1px 6px; border-radius: 4px; font-weight: 700; margin-right: 8px;
                white-space: nowrap; font-size: 0.8rem; border: 1px solid #dbeafe;
            }
            .event-box-icon { width: 10px; height: 10px; display: inline-block; margin-right: 8px; flex-shrink: 0; border: 3px solid; background: white; }
            .event-title { font-weight: 500; }

            /* Clases de fondo para badges/iconos */
            .bg-feriado { border-color: var(--color-feriado); background: var(--color-feriado) !important; }
            .box-feriado { border-color: var(--color-feriado); }
            .box-examen { border-color: var(--color-examen); }
            .box-clases { border-color: var(--color-clases); }
            .box-receso { border-color: var(--color-receso); }
            .box-admin { border-color: var(--color-admin); }
            .box-mensual { border-color: var(--color-mensual); }
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

            // Calcular fecha
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

            // Limpiar contenido previo
            dayEl.innerHTML = '';

            // Construir capas concéntricas
            const content = this.buildConcentricLayers(eventsForDay, cell.num);
            dayEl.appendChild(content);

            this.daysGrid.appendChild(dayEl);
        });

        // Actualizar lista de eventos del mes
        this.renderMonthList(year, month);
    }

    buildConcentricLayers(events, dayNum) {
        const numEl = document.createElement('span');
        numEl.className = 'num-circle-web';
        numEl.textContent = dayNum;

        if (events.length === 0) return numEl;

        // Prioridad de eventos para el orden de capas (menor número = más externo)
        const priority = {
            'feriado': 1,
            'examen': 2,
            'mensual': 3,
            'receso': 4,
            'clases': 5,
            'admin': 6
        };

        // Ordenar eventos por prioridad
        events.sort((a, b) => {
            const pA = priority[a.type] || 99;
            const pB = priority[b.type] || 99;
            return pA - pB;
        });

        let root = null;
        let currentParent = null;

        events.forEach(ev => {
            const div = document.createElement('div');
            div.className = `concentric-layer border-${ev.type}`;
            div.title = ev.title;

            if (!root) {
                root = div;
            } else {
                currentParent.appendChild(div);
            }
            currentParent = div;
        });

        if (currentParent) {
            currentParent.appendChild(numEl);
        }

        return root;
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
                    eventsInMonth.set(key, {
                        title: ev.title,
                        type: ev.type,
                        dates: [d]
                    });
                } else {
                    eventsInMonth.get(key).dates.push(d);
                }
            });
        }

        if (eventsInMonth.size === 0) {
            this.eventsList.innerHTML = '<div style="color:#999; text-align:center;">Sin actividades especiales.</div>';
            return;
        }

        const gridDiv = document.createElement('div');
        gridDiv.className = 'references-grid';

        // Configurar columnas desde backend (1-3)
        const cols = this.config.references_columns || 2;
        const safeCols = Math.max(1, Math.min(3, cols));
        gridDiv.style.gridTemplateColumns = `repeat(${safeCols}, 1fr)`;

        eventsInMonth.forEach(ev => {
            const item = document.createElement('div');
            item.className = 'event-item';

            // Formatear fechas (ej: "1-3, 6-10")
            const dates = ev.dates.sort((a,b) => a-b);
            let dateStr = '';

            // Agrupar consecutivos
            let ranges = [];
            let start = dates[0];
            let prev = dates[0];

            for(let i=1; i<dates.length; i++) {
                if (dates[i] === prev + 1) {
                    prev = dates[i];
                } else {
                    ranges.push(start === prev ? `${start}` : `${start}-${prev}`);
                    start = dates[i];
                    prev = dates[i];
                }
            }
            ranges.push(start === prev ? `${start}` : `${start}-${prev}`);
            dateStr = ranges.join(', ');

            // Icono de color
            const icon = document.createElement('div');
            icon.className = `event-box-icon box-${ev.type} bg-${ev.type}`;

            item.appendChild(icon);

            // Badge de fecha (Ocultar si es muy largo, ej: periodos extensos > 15 chars)
            if (dateStr.length <= 15) {
                const badge = document.createElement('span');
                badge.className = 'event-date-badge';
                badge.textContent = dateStr;
                item.appendChild(badge);
            }

            // Título
            const title = document.createElement('span');
            title.className = 'event-title';
            title.textContent = ev.title;

            item.appendChild(title);
            gridDiv.appendChild(item);
        });

        this.eventsList.appendChild(gridDiv);
    }
}

// Exponer globalmente
window.CalendarComponent = CalendarComponent;
