/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you import will output into a single css file (app.css in this case)
import 'bootstrap/dist/css/bootstrap.min.css'; // Bootstrap CSS
import 'bootstrap/dist/js/bootstrap.bundle.min.js'; // Bootstrap + Popper JS
// Importa tu CSS personalizado
import './styles/app.css';

// pass login template
document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('input[data-password-toggle="true"]').forEach(input => {
    // Crear el contenedor (wrapper)
    const wrapper = document.createElement('div');
    wrapper.classList.add('password-wrapper');
    
    // Estilos del wrapper
    wrapper.style.position = 'relative';
    wrapper.style.display = 'block';
    wrapper.style.width = '100%';

    // Insertar el wrapper en el DOM
    input.parentNode.insertBefore(wrapper, input);
    wrapper.appendChild(input);

    // Asegurar que el input ocupe todo el ancho y tenga espacio para el botón
    input.style.width = '100%';
    input.style.paddingRight = '3rem';
    input.style.boxSizing = 'border-box';

    // Crear botón
    const toggle = document.createElement('button');
    toggle.type = 'button';
    toggle.classList.add('toggle-password');
    toggle.innerHTML = '<i class="bi bi-eye"></i>'; // ← ICONO BOOTSTRAP
    toggle.setAttribute('aria-label', 'Mostrar contraseña');
    
    // Estilos del botón - ESTOS SON LOS IMPORTANTES
    toggle.style.position = 'absolute';
    toggle.style.right = '12px';
    toggle.style.top = '50%';
    toggle.style.transform = 'translateY(-50%)';
    toggle.style.background = 'none';
    toggle.style.border = 'none';
    toggle.style.cursor = 'pointer';
    toggle.style.fontSize = '1.2rem';
    toggle.style.padding = '0';
    toggle.style.margin = '0';
    toggle.style.zIndex = '10';
    toggle.style.lineHeight = '1';
    toggle.style.height = 'auto';
    toggle.style.width = 'auto';
    toggle.style.outline = 'none';
    toggle.style.color = '#6c757d'; // ← COLOR GRIS

    toggle.addEventListener('click', (e) => {
      e.preventDefault();
      const isPassword = input.type === 'password';
      input.type = isPassword ? 'text' : 'password';
      
      // Cambio de icono BOOTSTRAP
      toggle.innerHTML = isPassword ? '<i class="bi bi-eye-slash"></i>' : '<i class="bi bi-eye"></i>';
      toggle.setAttribute('aria-label', isPassword ? 'Ocultar contraseña' : 'Mostrar contraseña');
    });

    // Agregar efecto hover con eventos
    toggle.addEventListener('mouseenter', () => {
      toggle.style.color = '#5d0b0d'; // ← COLOR DE TU TEMA AL HACER HOVER
    });
    
    toggle.addEventListener('mouseleave', () => {
      toggle.style.color = '#6c757d'; // ← VUELVE AL COLOR GRIS
    });

    wrapper.appendChild(toggle);
  });
});