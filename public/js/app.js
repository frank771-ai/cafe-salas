document.documentElement.classList.add('js-enabled');

document.addEventListener('DOMContentLoaded', () => {
    const paymentRadios = document.querySelectorAll('input[name="payment_method"]');
    const cardFields = document.querySelector('#card-fields');
    const paypalFields = document.querySelector('#paypal-fields');

    const setPaymentFields = () => {
        if (!cardFields || !paypalFields) {
            return;
        }

        const method = document.querySelector('input[name="payment_method"]:checked')?.value ?? 'card';
        const usesCard = method === 'card';

        cardFields.classList.toggle('d-none', !usesCard);
        paypalFields.classList.toggle('d-none', usesCard);
        cardFields.querySelectorAll('input').forEach((input) => input.required = usesCard);
        paypalFields.querySelectorAll('input').forEach((input) => input.required = !usesCard);
    };

    paymentRadios.forEach((radio) => radio.addEventListener('change', setPaymentFields));
    setPaymentFields();

    const cardNumber = document.querySelector('#card_number');
    cardNumber?.addEventListener('input', () => {
        cardNumber.value = cardNumber.value.replace(/\D/g, '').slice(0, 19).replace(/(.{4})/g, '$1 ').trim();
    });

    const expiry = document.querySelector('#card_expiry');
    expiry?.addEventListener('input', () => {
        const digits = expiry.value.replace(/\D/g, '').slice(0, 4);
        expiry.value = digits.length > 2 ? `${digits.slice(0, 2)}/${digits.slice(2)}` : digits;
    });

    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const navigation = document.querySelector('[data-site-nav]');
    const progress = document.querySelector('.scroll-progress span');
    const backToTop = document.querySelector('[data-back-to-top]');

    const updateScrollInterface = () => {
        const top = window.scrollY;
        const available = document.documentElement.scrollHeight - window.innerHeight;
        const percentage = available > 0 ? Math.min((top / available) * 100, 100) : 0;

        navigation?.classList.toggle('is-scrolled', top > 24);
        backToTop?.classList.toggle('is-visible', top > 520);
        if (progress) {
            progress.style.width = `${percentage}%`;
        }
    };

    window.addEventListener('scroll', updateScrollInterface, { passive: true });
    updateScrollInterface();
    backToTop?.addEventListener('click', () => window.scrollTo({
        top: 0,
        behavior: reducedMotion ? 'auto' : 'smooth'
    }));

    const revealItems = document.querySelectorAll([
        '.section-heading',
        '.value-item',
        '.category-card',
        '.story-image',
        '.story-copy',
        '.product-card',
        '.process-card',
        '.filter-panel',
        '.summary-card',
        '.profile-card',
        '.checkout-panel',
        '.metric-card',
        '.report-card',
        '.order-card'
    ].join(','));

    revealItems.forEach((item, index) => {
        item.classList.add('reveal-ready');
        item.style.setProperty('--reveal-delay', `${Math.min(index % 4, 3) * 70}ms`);
    });

    if (reducedMotion || !('IntersectionObserver' in window)) {
        revealItems.forEach((item) => item.classList.add('is-visible'));
    } else {
        const revealObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach((entry) => {
                if (!entry.isIntersecting) {
                    return;
                }

                entry.target.classList.add('is-visible');
                observer.unobserve(entry.target);
            });
        }, { threshold: 0.12, rootMargin: '0px 0px -40px' });

        revealItems.forEach((item) => revealObserver.observe(item));
    }

    document.querySelectorAll('[data-counter]').forEach((counter) => {
        const target = Number(counter.dataset.counter);
        const prefix = counter.dataset.prefix ?? '';
        const suffix = counter.dataset.suffix ?? '';

        if (reducedMotion || !Number.isFinite(target)) {
            return;
        }

        let started = false;
        const counterObserver = new IntersectionObserver((entries, observer) => {
            if (!entries[0].isIntersecting || started) {
                return;
            }

            started = true;
            const startedAt = performance.now();
            const duration = 900;
            const tick = (now) => {
                const progressValue = Math.min((now - startedAt) / duration, 1);
                const eased = 1 - Math.pow(1 - progressValue, 3);
                counter.textContent = `${prefix}${Math.round(target * eased)}${suffix}`;
                if (progressValue < 1) {
                    requestAnimationFrame(tick);
                }
            };

            requestAnimationFrame(tick);
            observer.disconnect();
        }, { threshold: 0.7 });

        counterObserver.observe(counter);
    });

    if (!reducedMotion && window.matchMedia('(pointer: fine)').matches) {
        document.querySelectorAll('[data-tilt-card]').forEach((card) => {
            card.addEventListener('pointermove', (event) => {
                const bounds = card.getBoundingClientRect();
                const x = (event.clientX - bounds.left) / bounds.width - 0.5;
                const y = (event.clientY - bounds.top) / bounds.height - 0.5;
                card.style.setProperty('--tilt-x', `${y * -2.4}deg`);
                card.style.setProperty('--tilt-y', `${x * 3.2}deg`);
            });
            card.addEventListener('pointerleave', () => {
                card.style.removeProperty('--tilt-x');
                card.style.removeProperty('--tilt-y');
            });
        });

        const hero = document.querySelector('.hero');
        hero?.addEventListener('pointermove', (event) => {
            const x = event.clientX / window.innerWidth - 0.5;
            const y = event.clientY / window.innerHeight - 0.5;
            hero.style.setProperty('--hero-x', `${x * 18}px`);
            hero.style.setProperty('--hero-y', `${y * 14}px`);
        });
    }
});
