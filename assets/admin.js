import './styles/admin.css';

//== Buscador en tiempo real==//
let searchTimeout;
let observerInitialized = false;

// Esta función hará la búsqueda asíncrona (AJAX)
function triggerSearch(inputElement) {
    // console.log("🔍 Ejecutando búsqueda...", inputElement.value);
    
    const searchForm = inputElement.closest('form');
    if (!searchForm) {
        // console.error("❌ No se encontró el formulario");
        return;
    }

    const searchQuery = inputElement.value;
    
    // Obtener la URL de la petición
    const searchUrl = new URL(searchForm.action);
    searchUrl.searchParams.set('query', searchQuery);
    
    // IMPORTANTE: Preservar parámetros existentes (clienteId, categoriaId)
    const urlParams = new URLSearchParams(window.location.search);
    // console.log("📋 Parámetros URL actuales:", urlParams.toString());
    
    if (urlParams.has('clienteId')) {
        searchUrl.searchParams.set('clienteId', urlParams.get('clienteId'));
        // console.log("✅ Preservando clienteId:", urlParams.get('clienteId'));
    }
    if (urlParams.has('categoriaId')) {
        searchUrl.searchParams.set('categoriaId', urlParams.get('categoriaId'));
        // console.log("✅ Preservando categoriaId:", urlParams.get('categoriaId'));
    }
    
    // console.log("🌐 URL de búsqueda:", searchUrl.toString());
    
    // Envía la petición sin recargar la página
    fetch(searchUrl.toString())
        .then(response => {
            // console.log("📥 Respuesta recibida:", response.status);
            return response.text();
        })
        .then(html => {
            // Reemplazar solo la tabla de resultados en el DOM
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            const newTable = doc.querySelector('.datagrid');

            const oldTable = document.querySelector('.datagrid');
            if (oldTable && newTable) {
                oldTable.innerHTML = newTable.innerHTML;
                // console.log("✅ Tabla actualizada correctamente");
            } else {
                // console.error("❌ No se encontró la tabla (.datagrid)");
            }

            // Mantiene el foco en el input después de la actualización
            inputElement.focus();
        })
        .catch(error => console.error("❌ Error en la búsqueda:", error));
}

function setupDynamicSearch() {
    // console.log("🔧 Intentando configurar buscador...");
    const searchInput = document.querySelector('input[name="query"]');

    if (!searchInput) {
        // console.warn("⚠️ No se encontró input[name='query']");
        return false;
    }

    // Verificar si ya está configurado
    if (searchInput.dataset.searchConfigured === 'true') {
        // console.log("ℹ️ Buscador ya configurado, saltando...");
        return true;
    }

    // console.log("✅ Input encontrado, configurando eventos...");
    
    // Marcar como configurado
    searchInput.dataset.searchConfigured = 'true';
    
    searchInput.addEventListener('keyup', () => {
        // console.log("⌨️ Evento keyup detectado");
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            triggerSearch(searchInput);
        }, 300);
    });

    searchInput.addEventListener('keydown', (event) => {
        if (event.key === 'Enter') {
            console.log("↵ Enter presionado");
            event.preventDefault();
            clearTimeout(searchTimeout);
            triggerSearch(searchInput);
        }
    });
    
    // console.log("🎉 Buscador dinámico configurado correctamente");
    return true;
}

function initObserver() {
    if (observerInitialized) {
        return;
    }
    
    if (!document.body) {
        // console.warn("⚠️ document.body no disponible aún");
        return;
    }
    
    // Configurar el observer para detectar cambios en el DOM
    const observer = new MutationObserver((mutationsList) => {
        // Filtrar solo mutaciones relevantes para evitar loops infinitos
        const relevantMutation = mutationsList.some(mutation => {
            return mutation.addedNodes.length > 0 && 
                   Array.from(mutation.addedNodes).some(node => 
                       node.nodeType === 1 && // Es un elemento
                       (node.classList?.contains('content') || 
                        node.querySelector?.('input[name="query"]'))
                   );
        });
        
        if (relevantMutation) {
            // console.log("🔄 DOM mutado (relevante), reconfigurando buscador...");
            setupDynamicSearch();
        }
    });

    // Observar cambios en el body
    observer.observe(document.body, { 
        childList: true, 
        subtree: true 
    });
    
    observerInitialized = true;
    // console.log("👁️ Observer iniciado correctamente");
}

// Configuración inicial cuando carga el DOM
document.addEventListener('DOMContentLoaded', () => {
    // console.log("🚀 DOM cargado completamente");
    // console.log("📍 URL actual:", window.location.href);
    
    // Iniciar observer
    initObserver();
    
    // Intentar configurar el buscador
    const configured = setupDynamicSearch();
    
    if (!configured) {
        // Si no se configuró, reintentar con delays progresivos
        // console.log("⏰ Programando reintentos...");
        
        setTimeout(() => {
            // console.log("⏰ Reintento 100ms");
            if (!setupDynamicSearch()) {
                setTimeout(() => {
                    // console.log("⏰ Reintento 500ms");
                    if (!setupDynamicSearch()) {
                        setTimeout(() => {
                            // console.log("⏰ Reintento 1000ms (último intento)");
                            setupDynamicSearch();
                        }, 500);
                    }
                }, 400);
            }
        }, 100);
    }
});

// Escuchar cuando la ventana se carga completamente (backup)
window.addEventListener('load', () => {
    // console.log("🌐 Window load event");
    initObserver();
    setupDynamicSearch();
});

//== Fin Buscador en tiempo real==//

//== Js para mostrar fecha expira o no ==//
document.addEventListener("DOMContentLoaded", () => {
    const toggle = document.querySelector('.js-expira-toggle');
    const fecha = document.querySelector('.js-expira-field');

    if (!toggle || !fecha) {
        return; // No estamos en el form, salimos sin error
    }

    const fechaGroup = fecha.closest('.form-group');

    function updateVisibility() {
        if (toggle.checked) {
            fechaGroup.style.display = '';
        } else {
            fechaGroup.style.display = 'none';
        }
    }

    toggle.addEventListener('change', updateVisibility);
    updateVisibility(); // inicial
});

//== Para ocultar y mostrar pass ==//
document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('input[data-password-toggle="true"]').forEach(input => {
    // Crear wrapper
    const wrapper = document.createElement('div');
    wrapper.classList.add('password-wrapper');
    input.parentNode.insertBefore(wrapper, input);
    wrapper.appendChild(input);

    // Crear botón
    const toggle = document.createElement('button');
    toggle.type = 'button';
    toggle.classList.add('toggle-password');
    toggle.innerHTML = '👁'; //

    toggle.addEventListener('click', () => {
      input.type = input.type === 'password' ? 'text' : 'password';
    });

    wrapper.appendChild(toggle);
  });
});

//== Fin Js para ocultar y mostrar pass ==//

//== Js para añadir y eliminar formularios de archivos en subida masiva ==//
// document.addEventListener('DOMContentLoaded', function () {
//     const collectionHolder = document.getElementById('archivos-collection');
//     const addButton = document.getElementById('add-archivo');
//     let index = collectionHolder.querySelectorAll('.archivo-item').length;

//     function addArchivoForm() {
//         const prototype = collectionHolder.dataset.prototype;
//         const newForm = prototype.replace(/__name__/g, index);

//         // IDs únicos para el acordeón
//         const itemId = `accordion-item-${index}`;
//         const headingId = `heading-${index}`;
//         const collapseId = `collapse-${index}`;

//         const newDiv = document.createElement('div');
//         newDiv.classList.add('accordion-item', 'archivo-item', 'mb-3', 'shadow-sm');
//         newDiv.innerHTML = `
//             <h2 class="accordion-header" id="${headingId}">
//                 <button class="accordion-button fw-bold collapsed" type="button"
//                         data-bs-toggle="collapse" data-bs-target="#${collapseId}"
//                         aria-expanded="false" aria-controls="${collapseId}">
//                     Archivo #${index + 1}
//                 </button>
//             </h2>
//             <div id="${collapseId}" class="accordion-collapse collapse"
//                  aria-labelledby="${headingId}" data-bs-parent="#archivos-collection">
//                 <div class="accordion-body">
//                     <div class="row g-3">${newForm}</div>
//                     <div class="mt-3 text-end">
//                         <button type="button" class="btn btn-outline-danger btn-sm remove-archivo">
//                             <i class="fa fa-trash"></i> Eliminar
//                         </button>
//                     </div>
//                 </div>
//             </div>
//         `;

//         // Botón eliminar
//         newDiv.querySelector('.remove-archivo').addEventListener('click', () => {
//             newDiv.remove();
//         });

//         collectionHolder.appendChild(newDiv);
//         index++;
//     }

//     addButton.addEventListener('click', addArchivoForm);

//     // Inicial: conectar botones eliminar existentes
//     document.querySelectorAll('.remove-archivo').forEach(btn => {
//         btn.addEventListener('click', function () {
//             btn.closest('.archivo-item').remove();
//         });
//     });
// });
//== Js para añadir y eliminar formularios de archivos en subida masiva ==//
document.addEventListener('DOMContentLoaded', function () {
    const collectionHolder = document.getElementById('archivos-collection');
    const addButton = document.getElementById('add-archivo');
    
    // VALIDAR QUE EXISTAN ANTES DE CONTINUAR
    if (!collectionHolder || !addButton) {
        return; // Salir sin error si no estamos en la vista de subida masiva
    }
    
    let index = collectionHolder.querySelectorAll('.archivo-item').length;

    function addArchivoForm() {
        const prototype = collectionHolder.dataset.prototype;
        const newForm = prototype.replace(/__name__/g, index);

        const itemId = `accordion-item-${index}`;
        const headingId = `heading-${index}`;
        const collapseId = `collapse-${index}`;

        const newDiv = document.createElement('div');
        newDiv.classList.add('accordion-item', 'archivo-item', 'mb-3', 'shadow-sm');
        newDiv.innerHTML = `
            <h2 class="accordion-header" id="${headingId}">
                <button class="accordion-button fw-bold collapsed" type="button"
                        data-bs-toggle="collapse" data-bs-target="#${collapseId}"
                        aria-expanded="false" aria-controls="${collapseId}">
                    Archivo #${index + 1}
                </button>
            </h2>
            <div id="${collapseId}" class="accordion-collapse collapse"
                 aria-labelledby="${headingId}" data-bs-parent="#archivos-collection">
                <div class="accordion-body">
                    <div class="row g-3">${newForm}</div>
                    <div class="mt-3 text-end">
                        <button type="button" class="btn btn-outline-danger btn-sm remove-archivo">
                            <i class="fa fa-trash"></i> Eliminar
                        </button>
                    </div>
                </div>
            </div>
        `;

        newDiv.querySelector('.remove-archivo').addEventListener('click', () => {
            newDiv.remove();
        });

        collectionHolder.appendChild(newDiv);
        index++;
    }

    addButton.addEventListener('click', addArchivoForm);

    document.querySelectorAll('.remove-archivo').forEach(btn => {
        btn.addEventListener('click', function () {
            btn.closest('.archivo-item').remove();
        });
    });
});
