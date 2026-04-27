<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>

<!-- ====================================================
     FULMUV · Mobile Bottom Navigation Bar
     Visible solo en móvil / tablet  (d-lg-none)
     ==================================================== -->
<nav class="fulmuv-bottom-nav d-lg-none" role="navigation" aria-label="Navegación principal">
    <a href="index.php"
       class="fulmuv-bottom-nav-item<?= ($current_page === 'index.php') ? ' is-active' : '' ?>">
        <i class="fa-solid fa-house"></i>
        <span>Inicio</span>
    </a>
    <button type="button"
       class="fulmuv-bottom-nav-item<?= ($current_page === 'productos_categoria.php') ? ' is-active' : '' ?>"
       id="fulmuv-bottom-nav-products-trigger"
       aria-haspopup="dialog"
       aria-controls="fulmuv-products-modal">
        <i class="fa-solid fa-tags"></i>
        <span>Productos</span>
    </button>
    <a href="servicios.php"
       class="fulmuv-bottom-nav-item<?= ($current_page === 'servicios.php') ? ' is-active' : '' ?>">
        <i class="fa-solid fa-wrench"></i>
        <span>Servicios</span>
    </a>
    <a href="vehiculos.php"
       class="fulmuv-bottom-nav-item<?= ($current_page === 'vehiculos.php') ? ' is-active' : '' ?>">
        <i class="fa-solid fa-car"></i>
        <span>Vehículos</span>
    </a>
</nav>

<div class="fulmuv-products-modal-backdrop" id="fulmuv-products-modal-backdrop"></div>
<div class="fulmuv-products-modal" id="fulmuv-products-modal" role="dialog" aria-modal="true" aria-labelledby="fulmuv-products-modal-title">
    <div class="fulmuv-products-modal-card">
        <button type="button" class="fulmuv-products-modal-close" id="fulmuv-products-modal-close" aria-label="Cerrar">
            <i class="fa-solid fa-xmark"></i>
        </button>
        <div class="fulmuv-products-modal-icon">
            <i class="fa-solid fa-tags"></i>
        </div>
        <h3 class="fulmuv-products-modal-title" id="fulmuv-products-modal-title">Explora productos</h3>
        <p class="fulmuv-products-modal-copy">Elige la categoría que quieres ver dentro de FULMUV.</p>
        <div class="fulmuv-products-modal-actions">
            <a href="productos_categoria.php?q=1" class="fulmuv-products-choice">
                <span class="fulmuv-products-choice-icon"><i class="fa-solid fa-shield-heart"></i></span>
                <span class="fulmuv-products-choice-body">
                    <strong>Accesorios</strong>
                    <small>Explora accesorios para UTV y equipamiento.</small>
                </span>
                <span class="fulmuv-products-choice-arrow"><i class="fa-solid fa-chevron-right"></i></span>
            </a>
            <a href="productos_categoria.php?q=2" class="fulmuv-products-choice">
                <span class="fulmuv-products-choice-icon"><i class="fa-solid fa-gears"></i></span>
                <span class="fulmuv-products-choice-body">
                    <strong>Repuestos</strong>
                    <small>Busca repuestos y partes disponibles.</small>
                </span>
                <span class="fulmuv-products-choice-arrow"><i class="fa-solid fa-chevron-right"></i></span>
            </a>
        </div>
    </div>
</div>

<!-- Backdrop para el drawer de filtros -->
<div class="fulmuv-filter-backdrop" id="fulmuv-filter-backdrop"></div>

<style>
/* ============================================================
   FULMUV · Bottom Navigation Bar
   ============================================================ */
.fulmuv-bottom-nav {
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    z-index: 1030;
    background: #ffffff;
    border-top: 1px solid #e5e7eb;
    display: flex;
    height: 62px;
    box-shadow: 0 -4px 20px rgba(0, 0, 0, 0.08);
}

.fulmuv-bottom-nav-item {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 3px;
    font-size: 10px;
    font-weight: 600;
    color: #9ca3af;
    text-decoration: none !important;
    transition: color 0.2s;
    padding: 6px 4px;
    border: none;
    background: none;
}

.fulmuv-bottom-nav-item i {
    font-size: 19px;
    line-height: 1;
}

.fulmuv-bottom-nav-item.is-active {
    color: #004e60;
}

.fulmuv-bottom-nav-item:hover,
.fulmuv-bottom-nav-item:focus {
    color: #004e60;
    text-decoration: none !important;
}

.fulmuv-products-modal-backdrop {
    position: fixed;
    inset: 0;
    background: rgba(15, 23, 42, 0.45);
    backdrop-filter: blur(6px);
    z-index: 1090;
    opacity: 0;
    pointer-events: none;
    transition: opacity .22s ease;
}

.fulmuv-products-modal {
    position: fixed;
    inset: 0;
    display: flex;
    align-items: flex-end;
    justify-content: center;
    padding: 16px 16px 86px;
    z-index: 1091;
    opacity: 0;
    pointer-events: none;
    transition: opacity .22s ease;
}

.fulmuv-products-modal.is-open,
.fulmuv-products-modal-backdrop.is-open {
    opacity: 1;
    pointer-events: auto;
}

.fulmuv-products-modal-card {
    width: 100%;
    max-width: 420px;
    position: relative;
    border-radius: 28px;
    padding: 22px 18px 18px;
    background: linear-gradient(180deg, #ffffff 0%, #f8fbfc 100%);
    box-shadow: 0 28px 70px rgba(15, 23, 42, 0.24);
    border: 1px solid rgba(226, 232, 240, 0.9);
    transform: translateY(18px);
    transition: transform .22s ease;
}

.fulmuv-products-modal.is-open .fulmuv-products-modal-card {
    transform: translateY(0);
}

.fulmuv-products-modal-close {
    position: absolute;
    top: 14px;
    right: 14px;
    width: 38px;
    height: 38px;
    border: 0;
    border-radius: 999px;
    background: rgba(148, 163, 184, 0.14);
    color: #334155;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

.fulmuv-products-modal-icon {
    width: 52px;
    height: 52px;
    border-radius: 18px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, rgba(0, 78, 96, 0.12) 0%, rgba(34, 197, 94, 0.16) 100%);
    color: #004e60;
    font-size: 22px;
    margin-bottom: 14px;
}

.fulmuv-products-modal-title {
    margin: 0;
    font-size: 24px;
    font-weight: 900;
    color: #0f172a;
}

.fulmuv-products-modal-copy {
    margin: 8px 0 0;
    color: #64748b;
    font-size: 14px;
    line-height: 1.5;
}

.fulmuv-products-modal-actions {
    display: grid;
    gap: 12px;
    margin-top: 18px;
}

.fulmuv-products-choice {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 15px 14px;
    border-radius: 20px;
    text-decoration: none !important;
    background: #fff;
    border: 1px solid rgba(203, 213, 225, 0.75);
    box-shadow: 0 12px 24px rgba(15, 23, 42, 0.06);
}

.fulmuv-products-choice-icon {
    width: 46px;
    height: 46px;
    border-radius: 16px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: rgba(0, 78, 96, 0.08);
    color: #004e60;
    font-size: 18px;
    flex: 0 0 46px;
}

.fulmuv-products-choice-body {
    display: flex;
    flex-direction: column;
    min-width: 0;
}

.fulmuv-products-choice-body strong {
    font-size: 16px;
    font-weight: 800;
    color: #0f172a;
}

.fulmuv-products-choice-body small {
    margin-top: 3px;
    color: #64748b;
    font-size: 12px;
    line-height: 1.45;
}

.fulmuv-products-choice-arrow {
    margin-left: auto;
    color: #94a3b8;
    font-size: 14px;
}

@media (min-width: 992px) {
    .fulmuv-products-modal,
    .fulmuv-products-modal-backdrop {
        display: none !important;
    }
}

/* Espacio para que el bottom nav no tape el contenido */
@media (max-width: 991.98px) {
    body {
        padding-bottom: 70px !important;
    }
}

/* ============================================================
   FULMUV · Mobile Filter Drawer
   ============================================================ */

/* Backdrop oscuro detrás del drawer */
.fulmuv-filter-backdrop {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.45);
    z-index: 1048;
    cursor: pointer;
}
.fulmuv-filter-backdrop.fulmuv-is-open {
    display: block;
}

/* ---- Mobile: drawer desde la izquierda ---- */
@media (max-width: 991.98px) {
    .fulmuv-filter-panel {
        /* Forzar visible y overridear Bootstrap collapse */
        display: block !important;
        height: auto !important;
        overflow-y: auto !important;
        overflow-x: hidden !important;

        position: fixed !important;
        top: 0 !important;
        left: 0 !important;
        bottom: 0 !important;
        width: 82% !important;
        max-width: 300px !important;
        z-index: 1049 !important;
        background: #ffffff !important;

        /* Fuera de pantalla por defecto */
        transform: translateX(-108%) !important;
        transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1),
                    box-shadow 0.3s ease !important;
        box-shadow: none !important;
        padding: 0 !important;
        border: none !important;
    }

    .fulmuv-filter-panel.fulmuv-is-open {
        transform: translateX(0) !important;
        box-shadow: 8px 0 32px rgba(0, 0, 0, 0.2) !important;
    }

    /* Cabecera sticky del drawer (inyectada vía JS) */
    .fulmuv-filter-panel-header {
        position: sticky;
        top: 0;
        z-index: 2;
        background: #ffffff;
        border-bottom: 1px solid #e5e7eb;
        padding: 14px 16px;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .fulmuv-filter-panel-header .fulmuv-filter-title {
        font-weight: 700;
        font-size: 15px;
        color: #111827;
    }

    /* Quitar margin-top de los accordions dentro del drawer */
    .fulmuv-filter-panel .accordion.mt-30,
    .fulmuv-filter-panel .accordion.mt-2 {
        margin-top: 0 !important;
    }

    /* Padding interior del accordion para que no quede pegado */
    .fulmuv-filter-panel .accordion {
        padding: 12px 16px 72px;
    }

    /* Widget de info de vendor dentro del drawer */
    .fulmuv-filter-panel .sidebar-widget {
        padding: 16px;
        border-bottom: 1px solid #f3f4f6;
    }

    /* Ocultar column del sidebar en móvil para que no ocupe ancho */
    .fulmuv-sidebar-col {
        display: none !important;
    }
}

/* ---- Desktop: panel siempre visible e inline ---- */
@media (min-width: 992px) {
    .fulmuv-filter-panel {
        display: block !important;
        transform: none !important;
        position: static !important;
        width: auto !important;
        max-width: none !important;
        background: transparent !important;
        box-shadow: none !important;
        overflow: visible !important;
        padding: 0 !important;
        border: none !important;
    }

    .fulmuv-filter-backdrop {
        display: none !important;
    }

    /* Restablecer column visible en desktop */
    .fulmuv-sidebar-col {
        display: block !important;
    }
}

/* ============================================================
   FULMUV · Page-level Smart Search (brain icon input)
   ============================================================ */
.fulmuv-pgsearch-shell {
    position: relative;
    width: 100%;
}
.fulmuv-pgsearch-input {
    display: block;
    width: 100%;
    min-height: 50px;
    border-radius: 16px !important;
    border: 1.5px solid #d7e2ea !important;
    background: linear-gradient(180deg, #ffffff 0%, #f8fbfd 100%);
    box-shadow: 0 6px 20px rgba(15, 23, 42, 0.07);
    padding: 10px 44px 10px 52px !important;
    font-size: 14px;
    font-weight: 600;
    color: #0f172a;
    transition: border-color .2s, box-shadow .2s;
    outline: none;
}
.fulmuv-pgsearch-input:focus {
    border-color: rgba(0, 78, 96, 0.42) !important;
    box-shadow: 0 8px 28px rgba(0, 78, 96, 0.12) !important;
}
.fulmuv-pgsearch-input::placeholder {
    color: #94a3b8;
    font-weight: 500;
}
.fulmuv-pgsearch-brain {
    position: absolute;
    left: 14px;
    top: 50%;
    transform: translateY(-50%);
    width: 26px;
    height: 26px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 999px;
    background: rgba(0, 78, 96, 0.12);
    color: #004e60;
    pointer-events: none;
    font-size: 12px;
    box-shadow: inset 0 0 0 1px rgba(0, 78, 96, 0.08);
}
.fulmuv-pgsearch-clear {
    position: absolute;
    right: 12px;
    top: 50%;
    transform: translateY(-50%);
    width: 28px;
    height: 28px;
    border: 0;
    border-radius: 999px;
    background: transparent;
    color: #94a3b8;
    display: none;
    align-items: center;
    justify-content: center;
    padding: 0;
    cursor: pointer;
    font-size: 12px;
    line-height: 1;
}
.fulmuv-pgsearch-clear.is-visible {
    display: inline-flex;
}
.fulmuv-pgsearch-clear:hover {
    background: rgba(148, 163, 184, 0.15);
    color: #0f172a;
}
</style>

<script>
(function () {
    const trigger = document.getElementById('fulmuv-bottom-nav-products-trigger');
    const modal = document.getElementById('fulmuv-products-modal');
    const backdrop = document.getElementById('fulmuv-products-modal-backdrop');
    const closeBtn = document.getElementById('fulmuv-products-modal-close');

    if (!trigger || !modal || !backdrop || !closeBtn) return;

    const openModal = () => {
        modal.classList.add('is-open');
        backdrop.classList.add('is-open');
        document.body.style.overflow = 'hidden';
    };

    const closeModal = () => {
        modal.classList.remove('is-open');
        backdrop.classList.remove('is-open');
        document.body.style.overflow = '';
    };

    trigger.addEventListener('click', openModal);
    closeBtn.addEventListener('click', closeModal);
    backdrop.addEventListener('click', closeModal);
    modal.addEventListener('click', function (event) {
        if (event.target === modal) closeModal();
    });
    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && modal.classList.contains('is-open')) {
            closeModal();
        }
    });
})();
</script>

<!-- ====================================================
     FULMUV · Page-State Persistence (scroll + filters)
     Saves on navigation away; restores on back/forward.
     ==================================================== -->
<script>
(function () {
    'use strict';

    var KEY_PREFIX = 'fmv_bk_';

    function stateKey() {
        return KEY_PREFIX + location.pathname + location.search;
    }

    /* ---------- Save ---------- */
    function saveState() {
        var state = { scrollY: window.scrollY || window.pageYOffset || 0 };

        /* Checked checkboxes (skip "todos" / select-all values) */
        var checks = [];
        document.querySelectorAll('input[type="checkbox"]:checked').forEach(function (el) {
            if (el.name && el.value !== '__all__' && el.name !== '__all__') {
                checks.push({ n: el.name, v: el.value });
            }
        });
        state.checks = checks;

        /* Number / text / search inputs with a value */
        var inputs = {};
        document.querySelectorAll('input[type="number"], input[type="text"], input[type="search"]').forEach(function (el) {
            var k = el.id || el.name;
            if (k && el.value) inputs[k] = el.value;
        });
        state.inputs = inputs;

        /* Select elements */
        var selects = {};
        document.querySelectorAll('select').forEach(function (el) {
            var k = el.id || el.name;
            if (k && el.value) selects[k] = el.value;
        });
        state.selects = selects;

        try { sessionStorage.setItem(stateKey(), JSON.stringify(state)); } catch (e) {}
    }

    /* ---------- Restore ---------- */
    function restoreState() {
        var raw;
        try { raw = sessionStorage.getItem(stateKey()); } catch (e) {}
        if (!raw) return;

        var state;
        try { state = JSON.parse(raw); } catch (e) { return; }

        /* Inputs / selects: rendered in HTML, available immediately */
        if (state.inputs) {
            Object.keys(state.inputs).forEach(function (k) {
                var el = document.getElementById(k) ||
                         document.querySelector('[name="' + k + '"]');
                if (el) {
                    el.value = state.inputs[k];
                    el.dispatchEvent(new Event('input', { bubbles: true }));
                }
            });
        }
        if (state.selects) {
            Object.keys(state.selects).forEach(function (k) {
                var el = document.getElementById(k) ||
                         document.querySelector('[name="' + k + '"]');
                if (el) {
                    el.value = state.selects[k];
                    el.dispatchEvent(new Event('change', { bubbles: true }));
                }
            });
        }

        /* Checkboxes: rendered via AJAX — poll until found (max 12 s) */
        if (state.checks && state.checks.length) {
            var pending = state.checks.slice();
            var elapsed = 0;
            var ckTimer = setInterval(function () {
                elapsed += 250;
                var allCk = document.querySelectorAll('input[type="checkbox"]');
                var next  = [];
                pending.forEach(function (item) {
                    var found = false;
                    allCk.forEach(function (el) {
                        if (el.name === item.n && el.value === item.v) {
                            found = true;
                            if (!el.checked) {
                                el.checked = true;
                                el.dispatchEvent(new Event('change', { bubbles: true }));
                            }
                        }
                    });
                    if (!found) next.push(item);
                });
                pending = next;
                if (!pending.length || elapsed >= 12000) clearInterval(ckTimer);
            }, 250);
        }

        /* Scroll: wait for AJAX grid to populate, then scroll */
        var scrollY = state.scrollY || 0;
        if (scrollY > 0) {
            var restored = false;
            function tryScroll() {
                if (restored) return;
                /* Only scroll once the page is tall enough to reach the target */
                if (document.body.scrollHeight >= scrollY + window.innerHeight) {
                    window.scrollTo(0, scrollY);
                    restored = true;
                }
            }
            /* Progressive fallback — AJAX usually completes well within 3 s */
            setTimeout(tryScroll, 700);
            setTimeout(tryScroll, 1500);
            setTimeout(function () {
                /* Last attempt: scroll regardless of page height */
                if (!restored) { window.scrollTo(0, scrollY); restored = true; }
            }, 3000);
        }
    }

    /* ---------- Save triggers ---------- */

    /* Capture-phase click: fires before any anchor navigates */
    document.addEventListener('click', function (e) {
        var a = e.target.closest ? e.target.closest('a[href]') : null;
        if (!a) return;
        var href   = a.getAttribute('href') || '';
        var target = a.getAttribute('target') || '';
        /* Skip: anchors, javascript:, and _blank links (those open detail pages) */
        if (!href || href[0] === '#' || href.indexOf('javascript:') === 0 || target === '_blank') return;
        saveState();
    }, true);

    /* pagehide: catches unload / browser back button before page is gone */
    window.addEventListener('pagehide', saveState);

    /* ---------- Restore trigger ---------- */
    document.addEventListener('DOMContentLoaded', function () {
        try {
            var nav = performance.getEntriesByType('navigation');
            if (nav.length && nav[0].type === 'back_forward') restoreState();
        } catch (e) {}
    });

})();
</script>

<script>
(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        var filterPanel = document.getElementById('mobileFilters');
        var backdrop    = document.getElementById('fulmuv-filter-backdrop');
        var btnToggle   = document.getElementById('btnToggleMobileFilters');

        if (!filterPanel) return;

        /* Inyectar cabecera mobile en el drawer (oculta en desktop por CSS) */
        var header = document.createElement('div');
        header.className = 'fulmuv-filter-panel-header d-lg-none';
        header.innerHTML =
            '<span class="fulmuv-filter-title">' +
                '<i class="fi-rs-filter me-2"></i>Filtros' +
            '</span>' +
            '<button type="button" class="btn-close fulmuv-filter-close" aria-label="Cerrar"></button>';
        filterPanel.insertBefore(header, filterPanel.firstChild);

        /* Abrir drawer */
        if (btnToggle) {
            btnToggle.addEventListener('click', function () {
                filterPanel.classList.add('fulmuv-is-open');
                if (backdrop) backdrop.classList.add('fulmuv-is-open');
                document.body.style.overflow = 'hidden';
            });
        }

        /* Cerrar drawer */
        function closePanel() {
            filterPanel.classList.remove('fulmuv-is-open');
            if (backdrop) backdrop.classList.remove('fulmuv-is-open');
            document.body.style.overflow = '';
        }

        if (backdrop) {
            backdrop.addEventListener('click', closePanel);
        }

        /* Botón de cierre dentro del panel */
        filterPanel.addEventListener('click', function (e) {
            var closeBtn = e.target.closest
                ? e.target.closest('.fulmuv-filter-close')
                : (e.target.classList.contains('fulmuv-filter-close') ? e.target : null);
            if (closeBtn) closePanel();
        });

        /* Cerrar con tecla Escape */
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') closePanel();
        });
    });

    /* ── Brain search clear button ─────────────────────────────── */
    /* Show/hide X button as user types */
    document.addEventListener('input', function (e) {
        if (!e.target.classList.contains('fulmuv-pgsearch-input')) return;
        var btn = e.target.parentElement &&
                  e.target.parentElement.querySelector('.fulmuv-pgsearch-clear');
        if (btn) btn.classList[e.target.value ? 'add' : 'remove']('is-visible');
    }, true);

    /* Click X — clear input and re-trigger search */
    document.addEventListener('click', function (e) {
        var btn = e.target.closest ? e.target.closest('.fulmuv-pgsearch-clear') : null;
        if (!btn) return;
        var inp = btn.parentElement && btn.parentElement.querySelector('.fulmuv-pgsearch-input');
        if (!inp) return;
        inp.value = '';
        inp.dispatchEvent(new Event('input', { bubbles: true }));
        btn.classList.remove('is-visible');
        inp.focus();
    });
})();
</script>
