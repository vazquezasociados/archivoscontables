import './styles/admin.css';

//== Buscador en tiempo real==//
let searchTimeout;

// Esta función hará la búsqueda asíncrona (AJAX)
function triggerSearch(inputElement) {
    // console.log("admin.js: Realizando búsqueda con AJAX...");

    const searchForm = inputElement.closest('form');
    if (!searchForm) {
        // console.error("No se encontró el formulario de búsqueda.");
        return;
    }

    const searchQuery = inputElement.value;
    const currentUrl = new URL(window.location.href);
    
    // Obtener la URL de la petición. La acción del formulario ya tiene la URL correcta.
    const searchUrl = new URL(searchForm.action);
    searchUrl.searchParams.set('query', searchQuery);
    
    // Envía la petición sin recargar la página
    fetch(searchUrl.toString())
        .then(response => response.text())
        .then(html => {
            // Reemplazar solo la tabla de resultados en el DOM
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            const newTable = doc.querySelector('.datagrid'); // Clase CSS de la tabla en EasyAdmin

            const oldTable = document.querySelector('.datagrid');
            if (oldTable && newTable) {
                oldTable.innerHTML = newTable.innerHTML;
            }

            // Mantiene el foco en el input después de la actualización
            inputElement.focus();
        })
        .catch(error => console.error("Error en la búsqueda:", error));
}

function setupDynamicSearch() {
    // console.log("admin.js: Intentando configurar la búsqueda...");
    const searchInput = document.querySelector('input[name="query"]');

    if (searchInput) {
        // console.log("admin.js: Elemento de búsqueda encontrado.", searchInput);
        
        searchInput.addEventListener('keyup', () => {
            // console.log("admin.js: Evento 'keyup' detectado. Reiniciando temporizador...");
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                triggerSearch(searchInput);
            }, 300);
        });

        searchInput.addEventListener('keydown', (event) => {
            if (event.key === 'Enter') {
                // console.log("admin.js: Tecla Enter presionada. Búsqueda inmediata.");
                event.preventDefault();
                clearTimeout(searchTimeout);
                triggerSearch(searchInput);
            }
        });
    } else {
        // console.log("admin.js: Elemento de búsqueda NO encontrado.");
    }
}

const observer = new MutationObserver((mutationsList) => {
    for (const mutation of mutationsList) {
        if (mutation.type === 'childList' && mutation.addedNodes.length > 0) {
            // console.log("admin.js: Mutación del DOM detectada. Re-configurando la búsqueda...");
            setupDynamicSearch();
        }
    }
});

observer.observe(document.body, { childList: true, subtree: true });

document.addEventListener('DOMContentLoaded', () => {
    // console.log("admin.js: DOM cargado. Configurando la búsqueda...");
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
document.addEventListener('DOMContentLoaded', function () {
    const collectionHolder = document.getElementById('archivos-collection');
    const addButton = document.getElementById('add-archivo');
    let index = collectionHolder.querySelectorAll('.archivo-item').length;

    function addArchivoForm() {
        const prototype = collectionHolder.dataset.prototype;
        const newForm = prototype.replace(/__name__/g, index);

        // IDs únicos para el acordeón
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

        // Botón eliminar
        newDiv.querySelector('.remove-archivo').addEventListener('click', () => {
            newDiv.remove();
        });

        collectionHolder.appendChild(newDiv);
        index++;
    }

    addButton.addEventListener('click', addArchivoForm);

    // Inicial: conectar botones eliminar existentes
    document.querySelectorAll('.remove-archivo').forEach(btn => {
        btn.addEventListener('click', function () {
            btn.closest('.archivo-item').remove();
        });
    });
});
