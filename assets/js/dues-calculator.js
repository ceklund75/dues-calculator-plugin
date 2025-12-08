(function() {
    function animateValue(el, end, duration) {
        if (!el) return;

        const start = 0;
        const startTime = performance.now();
        const formatter = new Intl.NumberFormat(undefined, {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });

        function frame(now) {
            const elapsed = now - startTime;
            const progress = Math.min(elapsed / duration, 1);
            const value = start + (end - start) * progress;

            el.textContent = '$' + formatter.format(value);

            if (progress < 1) {
                requestAnimationFrame(frame);
            }
        }

        requestAnimationFrame(frame);
    }

    document.addEventListener('DOMContentLoaded', function() {
        const wrappers = document.querySelectorAll('.hpc-wrapper');
        wrappers.forEach(function(wrapper) {
            const form      = wrapper.querySelector('.hpc-form');
            const inputRate = wrapper.querySelector('#hpc_hourly_rate');
            const btn       = wrapper.querySelector('.hpc-submit-btn');
            const errorBox  = wrapper.querySelector('.hpc-error');
            const monthEl   = wrapper.querySelector('[data-hpc-total="month"]');
            const yearEl    = wrapper.querySelector('[data-hpc-total="year"]');
            const fiveEl    = wrapper.querySelector('[data-hpc-total="five"]');

            if (!inputRate || !btn || !monthEl || !yearEl || !fiveEl) return;

            const factor = (window.DuesCalculatorSettings && window.DuesCalculatorSettings.factor)
                ? parseFloat(window.DuesCalculatorSettings.factor)
                : 2.5;

            if (form) {
                form.addEventListener('submit', function(ev) {
                    ev.preventDefault();
                });
            }

            btn.addEventListener('click', function() {
                if (errorBox) {
                    errorBox.style.display = 'none';
                    errorBox.textContent = '';
                }

                const rate = parseFloat(inputRate.value);
                if (!rate || rate <= 0) {
                    if (errorBox) {
                        errorBox.textContent = 'Please enter an hourly rate greater than 0 to see your dues.';
                        errorBox.style.display = 'block';
                    }
                    monthEl.textContent = '$0.00';
                    yearEl.textContent  = '$0.00';
                    fiveEl.textContent  = '$0.00';
                    return;
                }

                const monthly = rate * factor;
                const yearly  = monthly * 12;
                const five    = yearly * 5;

                animateValue(monthEl, monthly, 900);
                animateValue(yearEl,  yearly,  900);
                animateValue(fiveEl,  five,    900);
            });
        });
    });

})();