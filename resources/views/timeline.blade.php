@extends('app.template')

@section('content')
    <style>
        .fc-now-indicator-line {
            border-color: red;
            border-width: 2px;
        }

        .fc-now-indicator-arrow {
            display: none;
        }

        #calendar {
            overflow-x: auto;
            overflow-y: hidden;
            touch-action: pan-x pan-y;
        }

        .fc-event-title {
            font-size: 12px;
            font-weight: 600;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;

        }

        .fc-event {
            border-radius: 4px;
        }

        .fc-event.plan {
            opacity: 0.85;
            background-color: #4f7db2;

        }

        .fc-event.actual {
            margin-top: 4px;
            height: 18px;
            border: 2px solid rgba(0, 0, 0, .2);
            background-color: #6fbf73;

        }

        .fc-event.running {
            background-image: repeating-linear-gradient(45deg,
                    rgba(255, 255, 255, .35),
                    rgba(255, 255, 255, .35) 10px,
                    transparent 10px,
                    transparent 20px);
        }
    </style>

    <div class="container mt-4">
        <h3 class="mb-3">Timeline</h3>

        <div class="d-flex gap-2 mb-3">
            <button class="btn btn-dark btn-sm" id="prevBtn">‹</button>
            <button class="btn btn-dark btn-sm" id="nextBtn">›</button>

            <button class="btn btn-primary btn-sm" id="zoomIn">Zoom In</button>
            <button class="btn btn-secondary btn-sm" id="zoomOut">Zoom Out</button>

            <button class="btn btn-success btn-sm" id="todayBtn">Today</button>

            <button class="btn btn-outline-primary btn-sm" id="dayView">Day</button>
            <button class="btn btn-outline-primary btn-sm" id="weekView">Week</button>
            <button class="btn btn-outline-primary btn-sm" id="yearView">Year</button>
            <button class="btn btn-danger btn-sm" onclick="downloadTimelinePdf()">
                Export PDF
            </button>
        </div>

        <div id="calendar"></div>
    </div>

    <link href="https://cdn.jsdelivr.net/npm/fullcalendar-scheduler@5.11.3/main.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar-scheduler@5.11.3/main.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {

            // =========================
            // ZOOM CONFIG (MOBISCROLL STYLE)
            // =========================
            const zoomLevels = [{
                    name: 'day',
                    slot: {
                        days: 1
                    },
                    range: 7,
                    step: 1,
                    label: {
                        weekday: 'short',
                        day: 'numeric',
                        month: 'short'
                    }
                },

                {
                    name: 'week',
                    slot: {
                        days: 7
                    },
                    range: 30,
                    step: 7,
                    label: {
                        month: 'short',
                        day: 'numeric'
                    }
                },

                {
                    name: 'month',
                    slot: {
                        months: 1
                    },
                    range: 180,
                    step: 30,
                    label: {
                        month: 'long',
                        year: 'numeric'
                    }
                },

                {
                    name: 'year',
                    slot: {
                        years: 1
                    },
                    range: 365,
                    step: 365,
                    label: {
                        year: 'numeric'
                    }
                }
            ];

            let currentZoom = 0; // MONTH

            let anchorToday = new Date(); // TODAY TETAP
            let currentDate = new Date();

            let calendar;

            // =========================
            // BUILD RANGE (CENTERED)
            // =========================
            function buildRange() {
                const z = zoomLevels[currentZoom];

                // jarak currentDate dari TODAY
                const offsetDays = Math.round(
                    (currentDate - anchorToday) / (1000 * 60 * 60 * 24)
                );

                // pusat = TODAY + offset
                const center = new Date(anchorToday);
                center.setDate(center.getDate() + offsetDays);

                const start = new Date(center);
                start.setDate(start.getDate() - Math.floor(z.range / 2));

                const end = new Date(start);
                end.setDate(end.getDate() + z.range);

                return {
                    start,
                    end
                };
            }

            // =========================
            // RENDER CALENDAR
            // =========================
            function renderCalendar() {
                if (calendar) calendar.destroy();

                const z = zoomLevels[currentZoom];
                const range = buildRange();

                calendar = new FullCalendar.Calendar(document.getElementById('calendar'), {
                    schedulerLicenseKey: 'CC-Attribution-NonCommercial-NoDerivatives',

                    //PLAN selalu di atas, ACTUAL selalu di bawah
                    eventOrder: function(a, b) {
                        return (a.extendedProps.order ?? 99) - (b.extendedProps.order ?? 99);
                    },
                    eventContent: function(arg) {

                        const type = arg.event.extendedProps.type;

                        const suffix = type === 'actual' ?
                            ' - Actual' :
                            ' - Rencana';

                        return {
                            html: `
                            <div class="fc-event-title">
                            ${arg.event.title}${suffix}
                             </div>
                            `
                        };
                    },

                    eventDidMount: function(info) {

                        const type = info.event.extendedProps.type;
                        const running = info.event.extendedProps.running ?? false;
                        const done = info.event.extendedProps.done ?? false;
                        const namaTahapan = info.event.title;

                        if (info.event.extendedProps.type === 'actual') {
                            info.el.style.marginTop = '18px';
                            info.el.style.height = '18px';
                        }
                        const titleEl = info.el.querySelector('.fc-event-title');

                        // =========================
                        // DONE (SUDAH SELESAI)
                        // =========================
                        if (done) {
                            info.el.style.opacity = '1';
                            info.el.style.backgroundImage = 'none';

                            const check = document.createElement('span');
                            check.textContent = '✅';
                            check.title = 'Selesai';

                            if (titleEl) titleEl.appendChild(check);
                        }


                        //  masih berjalan
                        if (info.event.extendedProps.running) {
                            const icon = document.createElement('span');
                            icon.innerHTML = ' ⏳';
                            icon.title = 'Running';
                            icon.style.fontSize = '13px';

                            const titleEl = info.el.querySelector('.fc-event-title');
                            if (titleEl) titleEl.appendChild(icon);

                            info.el.style.opacity = '0.6';
                            info.el.style.backgroundImage =
                                'repeating-linear-gradient(45deg, rgba(255,255,255,.3), rgba(255,255,255,.3) 10px, transparent 10px, transparent 20px)';
                        }

                        //tooltip
                        let selesaiText = '';

                        if (done) {
                            const end = new Date(info.event.end);
                            end.setDate(end.getDate() - 1); // FullCalendar end exclusive
                            selesaiText = end.toLocaleDateString('id-ID');
                        } else if (running) {
                            selesaiText = 'Sedang berjalan';
                        } else {
                            const end = new Date(info.event.end);
                            end.setDate(end.getDate() - 1);
                            selesaiText = end.toLocaleDateString('id-ID');
                        }

                        // const end = new Date(info.event.end);
                        // end.setDate(end.getDate() - 1);


                        const label = namaTahapan + (type === 'actual' ?
                            '  Actual' :
                            ' - Rencana');

                        info.el.title =
                            label +
                            "\nMulai : " + info.event.start.toLocaleDateString('id-ID') +
                            // "\nSelesai : " + end.toLocaleDateString('id-ID');
                            '\nSelesai : ' + selesaiText;

                    },

                    initialView: 'resourceTimeline',
                    initialDate: currentDate,

                    visibleRange: {
                        start: range.start,
                        end: range.end
                    },

                    nowIndicator: true,

                    slotDuration: z.slot,
                    slotLabelFormat: z.label,
                    slotMinWidth: window.innerWidth < 768 ? 50 : 70,

                    resourceAreaHeaderContent: 'Perusahaan',
                    resourceAreaWidth: window.innerWidth < 768 ? '200px' : '300px',

                    height: '70vh',

                    headerToolbar: {
                        left: '',
                        center: 'title',
                        right: ''
                    },

                    resources: @json($resources),
                    events: @json($events),

                    editable: false,
                    selectable: false
                });

                calendar.render();

                // Aktifkan gesture setelah render
                setTimeout(enableWheelScroll, 50);
                setTimeout(enableDragScroll, 50);
                setTimeout(enableTouchSwipe, 50);
            }

            // =========================
            // SHIFT + SCROLL (HORIZONTAL)
            // =========================

            function enableWheelScroll() {
                const calEl = document.getElementById('calendar');

                calEl.onwheel = function(e) {
                    if (!e.shiftKey) return;

                    e.preventDefault();

                    // PAKAI deltaX, BUKAN deltaY
                    const direction = Math.abs(e.deltaX) > Math.abs(e.deltaY) ?
                        e.deltaX :
                        e.deltaY;

                    if (direction > 0) {
                        // ➡️ scroll ke kanan → NEXT
                        currentDate.setDate(
                            currentDate.getDate() + zoomLevels[currentZoom].step
                        );
                    } else {
                        // ⬅️ scroll ke kiri → PREV
                        currentDate.setDate(
                            currentDate.getDate() - zoomLevels[currentZoom].step
                        );
                    }

                    renderCalendar();
                };
            }

            function enableTouchSwipe() {
                const cal = document.getElementById('calendar');

                let startX = 0;
                let startY = 0;

                cal.ontouchstart = e => {
                    const t = e.touches[0];
                    startX = t.clientX;
                    startY = t.clientY;
                };

                cal.ontouchend = e => {
                    const t = e.changedTouches[0];
                    const diffX = t.clientX - startX;
                    const diffY = t.clientY - startY;

                    //  ignore vertical scroll
                    if (Math.abs(diffX) < Math.abs(diffY)) return;

                    // threshold biar ga sensitif
                    if (Math.abs(diffX) < 50) return;

                    if (diffX < 0) {
                        //  swipe kiri → NEXT
                        currentDate.setDate(
                            currentDate.getDate() + zoomLevels[currentZoom].step
                        );
                    } else {
                        //  swipe kanan → PREV
                        currentDate.setDate(
                            currentDate.getDate() - zoomLevels[currentZoom].step
                        );
                    }

                    renderCalendar();
                };
            }


            // =========================
            // DRAG LEFT / RIGHT
            // =========================
            function enableDragScroll() {
                const cal = document.getElementById('calendar');
                let isDragging = false;
                let startX = 0;

                cal.onmousedown = e => {
                    isDragging = true;
                    startX = e.clientX;
                };

                document.onmouseup = () => isDragging = false;

                cal.onmousemove = e => {
                    if (!isDragging) return;

                    const diff = e.clientX - startX;

                    if (Math.abs(diff) > 120) {
                        currentDate.setDate(
                            currentDate.getDate() +
                            (diff < 0 ? zoomLevels[currentZoom].step : -zoomLevels[currentZoom].step)
                        );
                        isDragging = false;
                        renderCalendar();
                    }
                };
            }

            // =========================
            // INIT
            // =========================
            renderCalendar();

            // =========================
            // BUTTON NAVIGATION
            // =========================
            document.getElementById('prevBtn').onclick = () => {
                currentDate.setDate(currentDate.getDate() - zoomLevels[currentZoom].step);
                renderCalendar();
            };

            document.getElementById('nextBtn').onclick = () => {
                currentDate.setDate(currentDate.getDate() + zoomLevels[currentZoom].step);
                renderCalendar();
            };

            // =========================
            // ZOOM
            // =========================
            document.getElementById('zoomIn').onclick = () => {
                if (currentZoom > 0) {
                    currentZoom--;
                    renderCalendar();
                }
            };

            document.getElementById('zoomOut').onclick = () => {
                if (currentZoom < zoomLevels.length - 1) {
                    currentZoom++;
                    renderCalendar();
                }
            };
            //button today
            document.getElementById('todayBtn').onclick = () => {
                anchorToday = new Date(); // reset anchor
                currentDate = new Date(); // reset posisi
                renderCalendar(); // redraw
            };
            // =========================
            // QUICK VIEW BUTTON
            // =========================
            document.getElementById('dayView').onclick = () => {
                currentZoom = 0; // DAY
                anchorToday = new Date();
                currentDate = new Date();
                renderCalendar();
            };

            document.getElementById('weekView').onclick = () => {
                currentZoom = 1; // WEEK
                anchorToday = new Date();
                currentDate = new Date();
                renderCalendar();
            };

            document.getElementById('yearView').onclick = () => {
                currentZoom = 3; // YEAR
                anchorToday = new Date();
                currentDate = new Date();
                renderCalendar();
            };


        });
        //download
        function downloadTimelinePdf() {
            // hapus iframe lama jika ada
            const oldFrame = document.getElementById('pdfDownloader');
            if (oldFrame) oldFrame.remove();

            const iframe = document.createElement('iframe');
            iframe.style.display = 'none';
            iframe.id = 'pdfDownloader';
            iframe.src = "{{ route('timeline.export.pdf') }}";

            document.body.appendChild(iframe);
        }
    </script>
@endsection
