/**
 * MoneyFlow - JavaScript Principal
 * Funciones de utilidad y mejoras de UX
 */

// Formatear números como moneda paraguaya
function formatearMoneda(numero) {
    return new Intl.NumberFormat('es-PY', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
    }).format(numero) + ' Gs';
}

// Validar formularios
function validarFormulario(formId) {
    const form = document.getElementById(formId);
    if (!form) return;

    form.addEventListener('submit', function(e) {
        const inputs = form.querySelectorAll('[required]');
        let valido = true;

        inputs.forEach(input => {
            if (!input.value.trim()) {
                valido = false;
                input.classList.add('error');
            } else {
                input.classList.remove('error');
            }
        });

        if (!valido) {
            e.preventDefault();
            alert('Por favor completa todos los campos obligatorios');
        }
    });
}

// Auto-ocultar alertas
function autoOcultarAlertas() {
    const alertas = document.querySelectorAll('.alert-success');
    alertas.forEach(alerta => {
        setTimeout(() => {
            alerta.style.transition = 'opacity 0.3s';
            alerta.style.opacity = '0';
            setTimeout(() => alerta.remove(), 300);
        }, 3000);
    });
}

// Inicializar cuando el DOM carga
document.addEventListener('DOMContentLoaded', function() {
    // Validar formularios
    validarFormulario('formGasto');
    
    // Auto-ocultar alertas de éxito
    autoOcultarAlertas();
    
    // Animación de KPIs
    animarValores();
});

// Animar valores numéricos
function animarValores() {
    const valores = document.querySelectorAll('.kpi-value');
    
    valores.forEach(valor => {
        const textoOriginal = valor.textContent;
        const numeroMatch = textoOriginal.match(/[\d.,]+/);
        
        if (!numeroMatch) return;
        
        const numeroFinal = parseFloat(numeroMatch[0].replace(/\./g, '').replace(',', '.'));
        const unidad = textoOriginal.replace(numeroMatch[0], '').trim();
        
        let contador = 0;
        const incremento = numeroFinal / 50;
        const duracion = 1000;
        const pasos = 50;
        const intervalo = duracion / pasos;
        
        const timer = setInterval(() => {
            contador += incremento;
            if (contador >= numeroFinal) {
                contador = numeroFinal;
                clearInterval(timer);
            }
            valor.textContent = formatearMoneda(Math.floor(contador));
        }, intervalo);
    });
}

// Función para recargar datos sin refrescar página (AJAX)
function actualizarDashboard() {
    console.log('Actualizando dashboard...');
    // Implementación opcional para actualización asíncrona
}

// Exportar funciones para uso global
window.MoneyFlow = {
    formatearMoneda,
    validarFormulario,
    actualizarDashboard
};
